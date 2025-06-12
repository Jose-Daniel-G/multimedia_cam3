<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PermissionController;

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

// Rutas protegidas con JWT
Route::middleware(['middleware' => 'api'])->group(function () {
    Route::get('me', [AuthController::class, 'me']);            // Datos del usuario autenticado
    Route::post('logout', [AuthController::class, 'logout']);    // Cerrar sesión (invalidar token)
    Route::post('refresh', [AuthController::class, 'refresh']);  // Renovar token
    Route::get('user-profile', [AuthController::class, 'profile']); // Perfil personalizado del usuario
});
Route::middleware('auth')->group(function () {
    //PERMISIONS ROUTE
    Route::get('/permissions', [PermissionController::class, 'index'])->name('admin.permissions.index');
    Route::get('/permissions/create', [PermissionController::class, 'create'])->name('admin.permissions.create');
    Route::post('/permissions', [PermissionController::class, 'store'])->name('admin.permissions.store');
    Route::get('/permissions/{id}/edit', [PermissionController::class, 'edit'])->name('admin.permissions.edit');
    Route::put('/permissions/{id}', [PermissionController::class, 'update'])->name('admin.permissions.update');
    Route::delete('/permissions/{id}', [PermissionController::class, 'destroy'])->name('admin.permissions.destroy');
    //ROLES ROUTES
    //Route::resource('roles', RoleController::class)->names('admin.roles');
});
