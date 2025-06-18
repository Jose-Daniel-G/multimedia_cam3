<?php

// use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\Admin\HomeController;
// use App\Http\Controllers\Admin\CategoryController;
// use App\Http\Controllers\Admin\PostController;
// use App\Http\Controllers\Admin\TagController;
// use App\Http\Controllers\Admin\UserController;
// use App\Http\Controllers\PermissionController;
// use App\Http\Controllers\RoleController;

// Route::get("/", [HomeController::class, "index"])->name("admin.home");
// //RUTAS USUARIOS ADMIN
// Route::resource('/users', UserController::class)->names('admin.users')->middleware('auth', 'can:admin.users');
// Route::patch('/users/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggleStatus');

// Route::middleware('auth')->group(function () {
//     //PERMISIONS ROUTE
//     Route::get('/permissions', [PermissionController::class, 'index'])->name('admin.permissions.index');
//     Route::get('/permissions/create', [PermissionController::class, 'create'])->name('admin.permissions.create');
//     Route::post('/permissions', [PermissionController::class, 'store'])->name('admin.permissions.store');
//     Route::get('/permissions/{id}/edit', [PermissionController::class, 'edit'])->name('admin.permissions.edit');
//     Route::put('/permissions/{id}', [PermissionController::class, 'update'])->name('admin.permissions.update');
//     Route::delete('/permissions/{id}', [PermissionController::class, 'destroy'])->name('admin.permissions.destroy');
//     //ROLES ROUTES
//     Route::resource('roles', RoleController::class)->names('admin.roles');

//     //USERS ROUTES
//     // Route::get('/users', [UserController::class, 'index'])->name('users.index'); //->middleware('can:users.index');
//     // Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
//     // Route::post('/users', [UserController::class, 'store'])->name('users.store');
//     // Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit'); //->middleware('can:users.edit');

//     // Route::post('/users/{id}', [UserController::class, 'update'])->name('users.update');
//     // Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
// });


