<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetAdminLocale
{
    /**
     * Force the application locale to Russian for any admin request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        app()->setLocale('ru');

        return $next($request);
    }
}
