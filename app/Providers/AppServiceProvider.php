<?php

namespace App\Providers;

use App\Services\CartService;
use App\Services\SiteSettingsService;
use App\Services\WishlistService;
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
        $this->app->singleton(\App\Services\ProductCatalog::class);
        $this->app->singleton(\App\Services\CategoryCatalog::class);
        $this->app->singleton(\App\Services\ShopPageService::class);
        $this->app->singleton(\App\Services\MediaStorageService::class);
        $this->app->singleton(\App\Services\CartService::class);
        $this->app->singleton(\App\Services\WishlistService::class);
        $this->app->singleton(\App\Services\SiteSettingsService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFour();

        // Keep '*' so every component (PWA, footer, admin, etc.) gets $site.
        // Shared payload is built once per request — no repeated wishlist/DB work.
        View::composer('*', function ($view) {
            static $shared = null;

            if ($shared === null) {
                $settings = app(SiteSettingsService::class);
                $wishlistCount = 0;
                $wishlistSlugs = [];

                try {
                    $wishlist = app(WishlistService::class);
                    $wishlistSlugs = $wishlist->slugs();
                    $wishlistCount = count($wishlistSlugs);
                } catch (\Throwable) {
                    // Guest / unavailable wishlist.
                }

                $shared = [
                    'cartCount' => app(CartService::class)->count(),
                    'wishlistCount' => $wishlistCount,
                    'wishlistSlugs' => $wishlistSlugs,
                    'siteSettings' => $settings->get(),
                    'site' => $settings,
                    'currencySymbol' => config('currency.symbol', '৳'),
                    'currencyCode' => config('currency.code', 'BDT'),
                ];
            }

            $view->with($shared);
        });
    }
}
