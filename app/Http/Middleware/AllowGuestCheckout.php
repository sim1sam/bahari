<?php

namespace App\Http\Middleware;

use App\Services\SiteSettingsService;
use App\Support\AdminFeatures;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AllowGuestCheckout
{
    public function __construct(private SiteSettingsService $settings) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            if (! $this->settings->guestCheckoutEnabled()) {
                return redirect()->route('login');
            }

            return $next($request);
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
