<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response; // Importar para códigos HTTP

class AuthController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth:api', ['except' => ['login']]);
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
            return response()->json($validator->errors(), Response::HTTP_BAD_REQUEST);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'organismo_id' => 11, // Asegúrate de que este valor sea el correcto o se envíe desde el frontend
        ]);

        return response()->json([
            'message' => 'Usuario registrado exitosamente',
            'user' => $user,
        ], Response::HTTP_CREATED);
    }

    /**
     * Iniciar sesión y obtener un token Sanctum.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        Log::info('Intentando login con: ' . json_encode($request->only('email', 'password')));

        // Buscar el usuario por email
        $user = User::where('email', $request->email)->first();

        // Verificar si el usuario existe y la contraseña es correcta
        if (!$user || !Hash::check($request->password, $user->password)) {
            Log::warning('Intento de login fallido para email: ' . $request->email);
            return response()->json(['message' => 'Credenciales incorrectas'], Response::HTTP_UNAUTHORIZED);
        }

        // Crear un token de Sanctum para el usuario
        // El nombre del token es opcional, puedes usar 'auth_token', 'api_token', etc.
        $token = $user->createToken('auth_token')->plainTextToken;

        // Si necesitas la profile_photo_url en la respuesta del login
        $profilePhotoUrl = $user->profile_photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&color=7F9CF5&background=EBF4FF';


        Log::info('Login exitoso para usuario ID: ' . $user->id . ' Email: ' . $user->email);

        return response()->json([
            'message' => 'Login exitoso',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'organismo_id' => $user->organismo_id,
                'status' => $user->status,
                'profile_photo_url' => $profilePhotoUrl, // Incluir la URL de la foto de perfil
                // Puedes incluir otros campos del usuario aquí según necesites en el frontend
            ],
            'access_token' => $token,
            'token_type' => 'Bearer',
            // 'expires_in' => config('sanctum.expiration'), // Si quieres indicar la expiración del token
        ], Response::HTTP_OK);
    }

    /**
     * Obtener los datos del usuario autenticado.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function user(Request $request) // Renombrado de 'profile' a 'user' para coincidir con tu frontend '/api/user'
    {
        // El middleware 'auth:sanctum' ya se encarga de que $request->user() no sea nulo aquí.
        return response()->json($request->user()->makeHidden('password')); // Ocultar la contraseña
    }

    /**
     * Cerrar sesión (invalidar el token).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Elimina el token actual que se usó para esta petición
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada exitosamente'], Response::HTTP_OK);
    }

    // Los métodos 'refresh' y 'respondWithToken' no son típicos de Laravel Sanctum
    // y generalmente se usaban con JWT-Auth. Dado que estás migrando a Sanctum,
    // es recomendable que los elimines si no los estás utilizando para otra lógica.
    // protected function respondWithToken($token) { ... }
    // public function refresh() { ... }
}
