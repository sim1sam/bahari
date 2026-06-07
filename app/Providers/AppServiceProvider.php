<?php

namespace App\Providers;

use App\Services\CartService;
use App\Services\SiteSettingsService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFour();

        View::composer('*', function ($view) {
            $settings = app(SiteSettingsService::class);
            $view->with('cartCount', app(CartService::class)->count());
            $view->with('siteSettings', $settings->get());
            $view->with('site', $settings);
        });
    }
}
