    <?php

use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\NotificacionAvisoController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


    // Route::get('/prueba', function () {return view('index-prueba');});
    // Route::get('/', function () {return view('welcome');});
    Route::get('/', function () {
        return Auth::check() ? app(HomeController::class)->index() : view('auth.login');
    });
    // Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])
    Route::middleware(['auth:sanctum', config('jetstream.auth_session')])
        ->group(function () {
            Route::get('/dashboard', function () {
                return view('/admin/index'); //dashboard
            })->name('dashboard');
        });


    // Route::get('/vista-principal', [NotificacionAvisoController::class, 'index'])->name('main.index');
    // Route::post('/vista-principal', [NotificacionAvisoController::class, 'store'])->name('main.store');
    Route::get('/notificacion', [NotificacionAvisoController::class, 'index'])->name('main.index');
    Route::get('/notificacion/create', [NotificacionAvisoController::class, 'create'])->name('main.create');
    Route::post('/notificacion/store', [NotificacionAvisoController::class, 'store'])->name('main.store');
    Route::get('/notificacion/edit', [NotificacionAvisoController::class, 'edit'])->name('main.edit');
        // Route::get('/notificacion/edit', [NotificacionAvisoController::class, 'edit'])->name('main.edit');