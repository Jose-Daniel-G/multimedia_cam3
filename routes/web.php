    <?php

    use App\Http\Controllers\NotificacionesPorAvisoController;
    use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificacionAvisoController;
use App\Http\Controllers\PostController;
    use Illuminate\Support\Facades\Route;


    // Route::get('/prueba', function () {return view('index-prueba');});
    // Route::get('/', function () {return view('welcome');});
    Route::get('/', function () {return view('auth.login');});

    // Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])
    Route::middleware(['auth:sanctum', config('jetstream.auth_session')])
        ->group(function () {
            Route::get('/dashboard', function () {
                return view('/admin/index');//dashboard
            })->name('dashboard');
        });


        Route::get('/vista-principal', [NotificacionAvisoController::class, 'index'])->name('main.index');
        Route::post('/vista-principal', [NotificacionAvisoController::class, 'store'])->name('main.store');
