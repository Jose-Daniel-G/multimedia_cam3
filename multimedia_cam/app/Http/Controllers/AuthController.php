<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth:api', ['except' => ['login']]);
    // }
    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:2|max:100',
            'email' => 'required|string|email|max:100|unique:users',
            // 'organismo_id' => 'required|integer|exists:organismos,id',
            'password' => 'required|string|confirmed|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'organismo_id' => 11,
        ]);

        return response()->json([
            'message' => 'Usuario registrado exitosamente',
            'user' => $user,
        ], 201);
    }

    /**
     * Iniciar sesión y obtener un token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        Log::info('[BACKEND INFO BEFORE LOGIN] Register request data: ', $request->all());

        // 1. Validar los datos de entrada (email y password)
        try {
            $request->validate([
                'email' => ['required', 'string', 'email'],
                'password' => ['required', 'string'],
            ]);
        } catch (ValidationException $e) {
            // Si la validación falla, devuelve un error 422 con los mensajes.
            return response()->json([
                'message' => 'Credenciales inválidas o datos faltantes.',
                'errors' => $e->errors()
            ], 422);
        }

        // 2. Intentar autenticar al usuario usando las credenciales
        // Auth::attempt() verifica las credenciales y, si son correctas,
        // establece el usuario en la sesión actual.
        if (! Auth::attempt($request->only('email', 'password'))) {
            // Si las credenciales son incorrectas, lanza una excepción de validación
            // con un mensaje genérico para seguridad.
            throw ValidationException::withMessages([
                'email' => [trans('auth.failed')], // Usa el mensaje de Laravel para "falló autenticación"
            ]);
        }

        // 3. Regenerar la ID de la sesión para prevenir ataques de fijación de sesión.
        // Esto es crucial para Sanctum y la seguridad de la sesión.
        $request->session()->regenerate();

        // 4. Obtener el usuario autenticado (ahora ya está en la sesión)
        $user = $request->user();

        // 5. Devolver una respuesta exitosa con los datos del usuario.
        // El navegador ahora enviará la cookie 'laravel_session' automáticamente.
        Log::info('[BACKEND INFO AFTER LOGIN] User authenticated successfully: ', [
            'user_id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
        ]);
        return response()->json([
            'message' => 'Inicio de sesión exitoso',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'profile_photo_url' => $user->profile_photo_url, // Asegúrate de que esto no sea nulo o un error
                'roles' => $user->getRoleNames(), // Asumiendo que usas Spatie/Permission
            ]
        ], 200);
    }


    /**
     * Obtener los datos del usuario autenticado.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile()
    {
        return response()->json(auth('api')->user());
    }

    /**
     * Cerrar sesión (invalidar el token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada']);
    }


    /**
     * Refrescar el token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth('api')->refresh());
    }

    /**
     * Formato de respuesta con el token.
     *
     * @param string $token
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => auth()->user(),
        ]);
    }
}
