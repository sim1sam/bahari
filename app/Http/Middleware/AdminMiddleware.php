<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->route('admin.login')->with('error', 'Please login as admin.');
        }

        if (! auth()->user()->hasActiveRole()) {
            auth()->logout();

            return redirect()->route('admin.login')->with('error', 'Your role has been deactivated.');
        }

        if (! auth()->user()->isAdmin()) {
            return redirect()->route('admin.login')->with('error', 'Please login as admin.');
        }

        return $next($request);
    }
}
