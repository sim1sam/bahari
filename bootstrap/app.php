<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'admin.guest' => \App\Http\Middleware\AdminGuestMiddleware::class,
            'admin.feature' => \App\Http\Middleware\CheckAdminFeature::class,
            'customer' => \App\Http\Middleware\CustomerMiddleware::class,
        ]);

        $middleware->redirectGuestsTo(fn (Request $request) => match (true) {
            $request->is('admin', 'admin/*') => route('admin.login'),
            $request->is('account', 'account/*') => route('login'),
            default => route('login'),
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
