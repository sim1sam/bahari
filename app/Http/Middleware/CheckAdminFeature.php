<?php

namespace App\Http\Middleware;

use App\Support\AdminFeatures;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminFeature
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        if (auth()->user()->canAccessAdminFeature($feature)) {
            return $next($request);
        }

        $fallback = AdminFeatures::firstAccessibleRoute(auth()->user());

        if ($fallback && $fallback !== $request->route()?->getName()) {
            return redirect()->route($fallback)->with('error', 'You do not have permission to access that section.');
        }

        abort(403, 'You do not have permission to access this section.');
    }
}
