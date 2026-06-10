<?php

namespace App\Http\Middleware;

use App\Support\AdminFeatures;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminGuestMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->isAdmin()) {
            $route = AdminFeatures::firstAccessibleRoute(auth()->user()) ?? 'admin.dashboard';

            return redirect()->route($route);
        }

        return $next($request);
    }
}
