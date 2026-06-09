<?php

namespace App\Http\Middleware;

use App\Support\AdminFeatures;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        if (! auth()->user()->hasActiveRole()) {
            auth()->logout();

            return redirect()->route('login')->with('error', 'Your account role has been deactivated.');
        }

        if (auth()->user()->isAdmin()) {
            $route = AdminFeatures::firstAccessibleRoute(auth()->user()) ?? 'admin.dashboard';

            return redirect()->route($route);
        }

        return $next($request);
    }
}
