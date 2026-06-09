<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [AuthController::class, 'login'])->name('login.submit');
    });

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');

        Route::middleware('admin.feature:dashboard')->group(function () {
            Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
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

        Route::middleware('admin.feature:roles')->group(function () {
            Route::resource('roles', RoleController::class)->except(['show']);
            Route::patch('roles/{role}/status', [RoleController::class, 'toggleStatus'])->name('roles.status');
        });

        Route::middleware('admin.feature:orders')->group(function () {
            Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
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
