<?php

use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->group('web', [
            // These middlewares are run in order; StartSession comes first
            \Illuminate\Session\Middleware\StartSession::class,
            \App\Http\Middleware\SetLocale::class,
        ]);
        // $middleware->prepend(SetLocale::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
