<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule; 
// bootstrap/app.php
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        api: __DIR__.'/../routes/api.php', // Asegúrate de que api.php esté aquí
    )
    ->withMiddleware(function (Middleware $middleware) {
        // ... tus middlewares
        $middleware->web(append: [
            // ...
        ]);

        // ¡IMPORTANTE! Asegúrate de tener los middlewares de Sanctum para API si los necesitas
        $middleware->api(append: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // ... otros alias si tienes
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // ...
    })->create();
