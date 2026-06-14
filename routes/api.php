<?php

use App\Http\Controllers\Api\ContentReceiveController;
use App\Http\Middleware\VerifyApiSource;
use Illuminate\Support\Facades\Route;

Route::middleware(VerifyApiSource::class)->group(function () {
    Route::post('/content/receive', [ContentReceiveController::class, 'receive']);
});

Route::get('/content/ping', [ContentReceiveController::class, 'ping']);
