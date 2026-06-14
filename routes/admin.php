<?php

use App\Http\Controllers\Admin\ApiReceivedController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FooterLinkController;
use App\Http\Controllers\Admin\HomeBannerController;
use App\Http\Controllers\Admin\HomeFeatureController;
use App\Http\Controllers\Admin\HomepageController;
use App\Http\Controllers\Admin\HomeSliderController;
use App\Http\Controllers\Admin\NewsletterSubscriberController;
use App\Models\User;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('admin.guest')->group(function () {
        Route::get('login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [AuthController::class, 'login'])->name('login.submit');
    });

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');

        Route::middleware('admin.feature:dashboard')->group(function () {
            Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        });

        Route::middleware('admin.feature:homepage')->group(function () {
            Route::get('homepage', [HomepageController::class, 'index'])->name('homepage.index');
            Route::resource('homepage/sliders', HomeSliderController::class)->except(['show'])->names('homepage.sliders')->parameters(['sliders' => 'slider']);
            Route::resource('homepage/banners', HomeBannerController::class)->except(['show'])->names('homepage.banners')->parameters(['banners' => 'banner']);
            Route::resource('homepage/features', HomeFeatureController::class)->except(['show'])->names('homepage.features')->parameters(['features' => 'feature']);
            Route::resource('homepage/footer-links', FooterLinkController::class)->except(['show'])->names('homepage.footer-links')->parameters(['footer-links' => 'footerLink']);
            Route::get('newsletter', [NewsletterSubscriberController::class, 'index'])->name('newsletter.index');
            Route::delete('newsletter/{newsletterSubscriber}', [NewsletterSubscriberController::class, 'destroy'])->name('newsletter.destroy');
        });

        Route::middleware('admin.feature:products')->group(function () {
            Route::resource('products', ProductController::class)->except(['show']);
        });

        Route::middleware('admin.feature:categories')->group(function () {
            Route::resource('categories', CategoryController::class)->except(['show']);
        });

        Route::middleware('admin.feature:users')->group(function () {
            Route::resource('users', UserController::class)->except(['show']);
        });

        Route::middleware('admin.feature:customers')->group(function () {
            Route::bind('customer', fn (string $value) => User::customers()->findOrFail($value));
            Route::resource('customers', CustomerController::class)->except(['show']);
        });

        Route::middleware('admin.feature:roles')->group(function () {
            Route::resource('roles', RoleController::class)->except(['show']);
            Route::patch('roles/{role}/status', [RoleController::class, 'toggleStatus'])->name('roles.status');
        });

        Route::middleware('admin.feature:transactions')->group(function () {
            Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');
            Route::get('transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
            Route::post('transactions/{transaction}/approve', [TransactionController::class, 'approve'])->name('transactions.approve');
            Route::post('transactions/{transaction}/reject', [TransactionController::class, 'reject'])->name('transactions.reject');
        });

        Route::middleware('admin.feature:api_received')->group(function () {
            Route::get('api-received', [ApiReceivedController::class, 'index'])->name('api-received.index');
            Route::put('api-received/settings', [ApiReceivedController::class, 'updateSettings'])->name('api-received.settings');
            Route::post('api-received/logo', [ApiReceivedController::class, 'uploadLogo'])->name('api-received.logo');
            Route::post('api-received/sources', [ApiReceivedController::class, 'storeSource'])->name('api-received.sources.store');
            Route::post('api-received/sources/generate', [ApiReceivedController::class, 'generateSource'])->name('api-received.sources.generate');
            Route::delete('api-received/sources/{source}', [ApiReceivedController::class, 'destroySource'])->name('api-received.sources.destroy');
            Route::get('api-received/{item}', [ApiReceivedController::class, 'show'])->name('api-received.show');
            Route::put('api-received/{item}', [ApiReceivedController::class, 'updateItem'])->name('api-received.update');
            Route::post('api-received/{item}/process', [ApiReceivedController::class, 'process'])->name('api-received.process');
            Route::post('api-received/{item}/publish', [ApiReceivedController::class, 'publish'])->name('api-received.publish');
            Route::post('api-received/{item}/reject', [ApiReceivedController::class, 'reject'])->name('api-received.reject');
        });

        Route::middleware('admin.feature:orders')->group(function () {
            Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
            Route::get('orders/{order}/edit', [OrderController::class, 'edit'])->name('orders.edit');
            Route::put('orders/{order}', [OrderController::class, 'update'])->name('orders.update');
            Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
            Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
            Route::post('orders/{order}/approve', [OrderController::class, 'approve'])->name('orders.approve');
            Route::post('orders/{order}/payments', [OrderController::class, 'storePayment'])->name('orders.payments.store');
            Route::delete('orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');
        });

        Route::middleware('admin.feature:settings')->group(function () {
            Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
            Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
        });
    });
});
