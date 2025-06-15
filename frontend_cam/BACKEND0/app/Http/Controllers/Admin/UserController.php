<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organismo;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator; // Importar Validator
use Symfony\Component\HttpFoundation\Response; // Importar Response para códigos HTTP

class UserController extends Controller
{
    // Si usas Spatie/Permission, tus middlewares deberían ser algo como esto:
    // public function __construct()
    // {
    //     $this->middleware('auth:sanctum'); // Asegúrate de que el usuario esté autenticado para API
    //     $this->middleware('can:admin.user.index')->only('index');
    //     $this->middleware('can:admin.user.create')->only('create', 'store'); // Añadido para 'create' y 'store'
    //     $this->middleware('can:admin.user.edit')->only('edit', 'update');
    //     $this->middleware('can:admin.user.toggle_status')->only('toggleStatus'); // Nuevo para toggleStatus
    // }

    /**
     * Display a listing of the users.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $users = User::all();
        $organismos = Organismo::all();

        return response()->json([
            'message' => 'Lista de usuarios obtenida exitosamente',
            'data' => [
                'users' => $users,
                'organismos' => $organismos
            ]
        ], Response::HTTP_OK);
    }

    /**
     * Show the form for creating a new user (returns data for frontend form).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create()
    {
        $organismos = Organismo::all();
        return response()->json([
            'message' => 'Datos para creación de usuario obtenidos exitosamente',
            'data' => [
                'organismos' => $organismos
            ]
        ], Response::HTTP_OK);
    }

    /**
     * Store a newly created user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:250',
            'email' => 'required|email|max:250|unique:users',
            'organismo_id' => 'required|integer|exists:organismos,id', // 'integer' y 'exists' para mayor validación
            'password' => 'required|string|min:8|max:250|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY); // 422 Unprocessable Entity para errores de validación
        }

        $usuario = new User();
        $usuario->name = $request->name;
        $usuario->email = $request->email;
        $usuario->organismo_id = $request->organismo_id;
        $usuario->password = Hash::make($request->password);
        $usuario->save();

        // Extraer la parte antes del @ del email
        $username = explode('@', $usuario->email)[0];

        // Crear la carpeta en storage/app/users/{username}
        // Asegúrate de que el disco 'local' (o el que uses) permita esto.
        Storage::makeDirectory("public/users/{$username}"); // Usar 'public' disk para accesibilidad web si es necesario

        return response()->json([
            'message' => 'Usuario registrado exitosamente',
            'data' => $usuario // Devuelve el usuario creado
        ], Response::HTTP_CREATED); // 201 Created para creación exitosa
    }

    /**
     * Display the specified user.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(User $user) // Usar Route Model Binding para obtener el usuario
    {
        // Se puede cargar relaciones si son necesarias
        // $user->load('roles', 'organismo');

        return response()->json([
            'message' => 'Usuario obtenido exitosamente',
            'data' => $user
        ], Response::HTTP_OK);
    }

    /**
     * Show the form for editing the specified user (returns data for frontend form).
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        // Si usas Spatie, podrías querer cargar los roles del usuario para el frontend
        // $user->load('roles');

        return response()->json([
            'message' => 'Datos de usuario y roles para edición obtenidos exitosamente',
            'data' => [
                'user' => $user,
                'roles' => $roles
            ]
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, User $user)
    {
        // Validar que los roles enviados existan si es necesario
        // $request->validate([
        //     'roles' => 'array',
        //     'roles.*' => 'exists:roles,id',
        // ]);

        $user->roles()->sync($request->roles);

        // Puedes añadir más lógica de actualización de usuario aquí si la necesitas
        // $user->update($request->only('name', 'email', 'organismo_id'));

        return response()->json([
            'message' => 'Roles asignados correctamente',
            'data' => $user->load('roles') // Recargar roles para confirmar
        ], Response::HTTP_OK);
    }

    /**
     * Toggle the status of the specified user.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleStatus(User $user) // Usar Route Model Binding
    {
        $user->status = !$user->status;
        $user->save();

        return response()->json([
            'message' => 'Estado del usuario actualizado exitosamente',
            'data' => $user // Devuelve el usuario con el estado actualizado
        ], Response::HTTP_OK);
    }
}
