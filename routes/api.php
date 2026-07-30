<?php

use App\Http\Controllers\Api\OrderApiController;
use App\Http\Controllers\Api\ContentReceiveController;
use App\Http\Middleware\VerifyApiSource;
use Illuminate\Support\Facades\Route;

/*
| Same Order Transfer API (X-API-Key + Bearer token from Admin → Order Transfer settings):
| - POST /api/orders              → import OR status update (auto-detect)
| - POST /api/orders/import       → same
| - POST /api/orders/status-update → status update (explicit)
|
| Status update body:
| { "order_number": "BF-A5045D18", "admin_status": "kolkata_warehouse" }
*/

Route::post('/orders', OrderApiController::class);
Route::post('/orders/import', OrderApiController::class);
Route::post('/orders/status-update', OrderApiController::class);

Route::middleware(VerifyApiSource::class)->group(function () {
    Route::post('/content/receive', [ContentReceiveController::class, 'receive']);
});

Route::get('/content/ping', [ContentReceiveController::class, 'ping']);
