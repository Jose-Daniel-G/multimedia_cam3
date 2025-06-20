<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organismo;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash; // <-- Agregar esta línea
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('can:admin.user.index')->only('index');
    //     $this->middleware('can:admin.user.edit')->only('edit', 'update');
    // }
    public function index()
    {
        $users = User::all();
        $organismos = Organismo::all();
        // dd($users);
        return view('admin.users.index', compact('users','organismos'));
    }
    public function create()
    {
        $organismos = Organismo::all();
        return view('admin.users.create', compact('organismos'));
    }
    public function store(Request $request)
    {
        // $datos = $request->all();
        // return response()->json($datos);
        $request->validate([
            'name' => 'required|max:250',
            'email' => 'required|email|max:250|unique:users',
            'organismo_id' => 'required',
            'password' => 'required|min:8|max:250|confirmed',
        ]);
        //  dd($request->all());
        $usuario = new User();
        $usuario->name = $request->name;
        $usuario->email = $request->email;
        $usuario->organismo_id = $request->organismo_id;
        $usuario->password = Hash::make($request->password);
        $usuario->save();

        // Extraer la parte antes del @ del email
        $username = explode('@', $usuario->email)[0];

        // Crear la carpeta en storage/app/users/{username}
        Storage::makeDirectory("users/{$username}");

        return redirect()->route('admin.users.index')
            ->with('info', 'Se registro al usuario de forma correcta')
            ->with('icono', 'success');
    }
    public function show($id)
    {
        //
    }
    public function edit(User $user)
    {
        $roles = Role::all();
        return view('admin.users.edit', compact('user', 'roles'));
    }
    public function update(Request $request, User $user)
    {
        $user->roles()->sync($request->roles);
        return redirect()->route('admin.users.edit', $user)->with('info', 'Se asigno los roles correctamente');
    }

    public function toggleStatus($id) //DEACTIVATE
    {
        $user = User::findOrFail($id);
        $user->status = !$user->status;
        $user->save();
    
        return redirect()->back()->with('success', 'Estado del usuario actualizado.');
    }
}
