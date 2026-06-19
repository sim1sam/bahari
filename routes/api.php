<?php

use App\Http\Controllers\Api\ContentReceiveController;
use App\Http\Controllers\Api\OrderImportController;
use App\Http\Controllers\Api\OrderStatusUpdateController;
use App\Http\Middleware\VerifyApiSource;
use Illuminate\Support\Facades\Route;

Route::post('/orders/import', OrderImportController::class);
Route::post('/orders/status-update', OrderStatusUpdateController::class);

Route::middleware(VerifyApiSource::class)->group(function () {
    Route::post('/content/receive', [ContentReceiveController::class, 'receive']);
});

Route::get('/content/ping', [ContentReceiveController::class, 'ping']);
