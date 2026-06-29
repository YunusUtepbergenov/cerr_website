<?php

use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureAdminOnly;
use App\Http\Middleware\EnsureCanAccessPanel;
use App\Http\Middleware\EnsureCanManageContent;
use App\Http\Middleware\EnsureCanManageOpenData;
use App\Http\Middleware\SetAdminLocale;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->appendToGroup('web', SetLocale::class);
        $middleware->appendToGroup('admin', [
            SetAdminLocale::class,
            EnsureAdmin::class,
        ]);
        $middleware->alias([
            'admin-only' => EnsureAdminOnly::class,
            'manage-content' => EnsureCanManageContent::class,
            'manage-open-data' => EnsureCanManageOpenData::class,
            'panel-access' => EnsureCanAccessPanel::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
