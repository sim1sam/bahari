<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminFeatures;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (auth()->check() && auth()->user()->isAdmin()) {
            $route = AdminFeatures::firstAccessibleRoute(auth()->user()) ?? 'admin.dashboard';

            return redirect()->route($route);
        }

        return view('admin.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (auth()->check() && ! auth()->user()->isAdmin()) {
            auth()->logout();
        }

        if (! auth()->attempt($credentials, $request->boolean('remember'))) {
            return back()->with('error', 'Invalid credentials.')->onlyInput('email');
        }

        auth()->user()->load('role');

        if (! auth()->user()->hasActiveRole()) {
            auth()->logout();

            return back()->with('error', 'Your role has been deactivated.')->onlyInput('email');
        }

        if (! auth()->user()->isAdmin()) {
            auth()->logout();

            return back()->with('error', 'You do not have admin access.')->onlyInput('email');
        }

        $request->session()->regenerate();

        $route = AdminFeatures::firstAccessibleRoute(auth()->user());

        if (! $route) {
            auth()->logout();

            return back()->with('error', 'Your role has no admin features assigned.')->onlyInput('email');
        }

        return redirect()->intended(route($route));
    }

    public function logout(Request $request): RedirectResponse
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
