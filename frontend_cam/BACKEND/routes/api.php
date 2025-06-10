<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;


Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

// Rutas protegidas con JWT
Route::middleware(['middleware' => 'api'])->group(function () {
    Route::get('me', [AuthController::class, 'me']);            // Datos del usuario autenticado
    Route::post('logout', [AuthController::class, 'logout']);    // Cerrar sesión (invalidar token)
    Route::post('refresh', [AuthController::class, 'refresh']);  // Renovar token
    Route::get('user-profile', [AuthController::class, 'profile']); // Perfil personalizado del usuario
});