<?php

use App\Http\Controllers\Admin\ApiContentController;
use App\Http\Controllers\Admin\ApiProcessedController;
use App\Http\Controllers\Admin\ApiReceivedImageController;
use App\Http\Controllers\Admin\ApiSettingsController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FooterLinkController;
use App\Http\Controllers\Admin\HomeBannerController;
use App\Http\Controllers\Admin\HomeFeatureController;
use App\Http\Controllers\Admin\HomepageController;
use App\Http\Controllers\Admin\HomeSliderController;
use App\Http\Controllers\Admin\NewsletterSubscriberController;
use App\Models\User;
use App\Http\Controllers\Admin\MigrationController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\OrderTransferSettingController;
use App\Http\Controllers\Admin\PaymentBankController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\StorageLinkController;
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

        Route::middleware('admin.feature:payment_banks')->group(function () {
            Route::get('payment-banks', [PaymentBankController::class, 'index'])->name('payment-banks.index');
            Route::post('payment-banks', [PaymentBankController::class, 'store'])->name('payment-banks.store');
            Route::put('payment-banks/{paymentBank}', [PaymentBankController::class, 'update'])->name('payment-banks.update');
            Route::delete('payment-banks/{paymentBank}', [PaymentBankController::class, 'destroy'])->name('payment-banks.destroy');
        });

        Route::middleware('admin.feature:api_settings')->group(function () {
            Route::get('api-settings', [ApiSettingsController::class, 'index'])->name('api-settings.index');
            Route::put('api-settings/webhook', [ApiSettingsController::class, 'updateWebhook'])->name('api-settings.webhook');
            Route::post('api-settings/sources', [ApiSettingsController::class, 'storeSource'])->name('api-settings.sources.store');
            Route::post('api-settings/sources/generate', [ApiSettingsController::class, 'generateSource'])->name('api-settings.sources.generate');
            Route::put('api-settings/sources/{source}', [ApiSettingsController::class, 'updateSource'])->name('api-settings.sources.update');
            Route::delete('api-settings/sources/{source}', [ApiSettingsController::class, 'destroySource'])->name('api-settings.sources.destroy');
        });

        Route::middleware('admin.feature:api_content')->group(function () {
            Route::get('content', [ApiContentController::class, 'index'])->name('content.index');
            Route::post('content/logo', [ApiContentController::class, 'uploadLogo'])->name('content.logo');
            Route::put('content/logo-scale', [ApiContentController::class, 'updateLogoScale'])->name('content.logo-scale');
            Route::post('content/repair-images', [ApiContentController::class, 'repairImages'])->name('content.repair-images');
            Route::post('content/process-batch', [ApiContentController::class, 'processBatch'])->name('content.process-batch');
            Route::get('content/{item}', [ApiContentController::class, 'show'])->name('content.show');
            Route::put('content/{item}', [ApiContentController::class, 'update'])->name('content.update');
            Route::post('content/{item}/process', [ApiContentController::class, 'process'])->name('content.process');
            Route::post('content/{item}/reject', [ApiContentController::class, 'reject'])->name('content.reject');
        });

        Route::middleware('admin.feature:api_processed')->group(function () {
            Route::get('processed', [ApiProcessedController::class, 'index'])->name('processed.index');
            Route::get('processed/live/all', [ApiProcessedController::class, 'liveIndex'])->name('processed.live');
            Route::delete('processed/live/{item}', [ApiProcessedController::class, 'destroyLive'])->name('processed.destroy-live');
            Route::post('processed/live-batch', [ApiProcessedController::class, 'liveBatch'])->name('processed.live-batch');
            Route::post('processed/download-images', [ApiProcessedController::class, 'downloadImages'])->name('processed.download-images');
            Route::get('received-images/{item}/processed', [ApiReceivedImageController::class, 'processed'])->name('received-images.processed');
            Route::delete('processed/batch', [ApiProcessedController::class, 'destroyBatch'])->name('processed.destroy-batch');
            Route::post('processed/purge-manual-products', [ApiProcessedController::class, 'purgeManualProducts'])->name('processed.purge-manual-products');
            Route::get('processed/{item}/download-image', [ApiProcessedController::class, 'downloadImage'])->name('processed.download-image');
            Route::get('processed/{item}', [ApiProcessedController::class, 'show'])->name('processed.show');
            Route::put('processed/{item}', [ApiProcessedController::class, 'update'])->name('processed.update');
            Route::post('processed/{item}/live', [ApiProcessedController::class, 'live'])->name('processed.live-item');
            Route::delete('processed/{item}', [ApiProcessedController::class, 'destroy'])->name('processed.destroy');
        });

        Route::middleware('admin.feature:orders')->group(function () {
            Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
            Route::get('orders/create', [OrderController::class, 'create'])->name('orders.create');
            Route::post('orders', [OrderController::class, 'store'])->name('orders.store');
            Route::get('orders/transfer-settings', [OrderTransferSettingController::class, 'edit'])->name('orders.transfer-settings.edit');
            Route::get('orders/transfer-scripts', [OrderTransferSettingController::class, 'scripts'])->name('orders.transfer-settings.scripts');
            Route::put('orders/transfer-settings', [OrderTransferSettingController::class, 'update'])->name('orders.transfer-settings.update');
            Route::post('orders/transfer-settings/generate', [OrderTransferSettingController::class, 'generate'])->name('orders.transfer-settings.generate');
            Route::get('orders/{order}/edit', [OrderController::class, 'edit'])->name('orders.edit');
            Route::put('orders/{order}', [OrderController::class, 'update'])->name('orders.update');
            Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
            Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
            Route::post('orders/{order}/approve', [OrderController::class, 'approve'])->name('orders.approve');
            Route::post('orders/{order}/payments', [OrderController::class, 'storePayment'])->name('orders.payments.store');
            Route::delete('orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');
        });

        Route::middleware('admin.feature:coupons')->group(function () {
            Route::resource('coupons', CouponController::class)->except(['show']);
        });

        Route::middleware('admin.feature:settings')->group(function () {
            Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
            Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
        });

        Route::middleware('admin.feature:storage_link')->group(function () {
            Route::get('storage-link', [StorageLinkController::class, 'index'])->name('storage-link.index');
            Route::post('storage-link', [StorageLinkController::class, 'store'])->name('storage-link.store');
        });

        Route::middleware('admin.feature:database_migration')->group(function () {
            Route::get('migration', [MigrationController::class, 'index'])->name('migration.index');
            Route::post('migration', [MigrationController::class, 'store'])->name('migration.store');
        });
    });
});
