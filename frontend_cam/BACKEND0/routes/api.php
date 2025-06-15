<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;

// Rutas públicas
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Rutas protegidas por Sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return response()->json([
            'name' => $request->user()->name,
            'email' => $request->user()->email,
            'profile_photo_url' => $request->user()->profile_photo_url,
            'roles' => $request->user()->getRoleNames(),
        ]);
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    // Permisos
    Route::get('/permissions', [PermissionController::class, 'index'])->name('admin.permissions.index');
    Route::get('/permissions/create', [PermissionController::class, 'create'])->name('admin.permissions.create');
    Route::post('/permissions', [PermissionController::class, 'store'])->name('admin.permissions.store');
    Route::get('/permissions/{id}/edit', [PermissionController::class, 'edit'])->name('admin.permissions.edit');
    Route::put('/permissions/{id}', [PermissionController::class, 'update'])->name('admin.permissions.update');
    Route::delete('/permissions/{id}', [PermissionController::class, 'destroy'])->name('admin.permissions.destroy');

    // Roles
    Route::resource('/roles', RoleController::class)->names('admin.roles');
});
