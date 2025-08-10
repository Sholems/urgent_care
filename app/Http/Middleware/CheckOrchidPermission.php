<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class CheckOrchidPermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $route = Route::current();
        $requiredPermission = $route?->defaults['permission'] ?? null;

        if ($requiredPermission && !$request->user()?->hasAccess($requiredPermission)) {
            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}
