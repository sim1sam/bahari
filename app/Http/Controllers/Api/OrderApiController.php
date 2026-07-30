<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Single inbound Order Transfer API.
 * Same auth (X-API-Key + Bearer) for:
 * - order import
 * - status update (admin_status)
 */
class OrderApiController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        if ($this->isStatusUpdateRequest($request)) {
            return app(OrderStatusUpdateController::class)($request);
        }

        return app(OrderImportController::class)($request);
    }

    private function isStatusUpdateRequest(Request $request): bool
    {
        $action = strtolower(trim((string) $request->input('action', $request->input('type', ''))));

        if (in_array($action, ['status_update', 'status', 'update_status', 'admin_status'], true)) {
            return true;
        }

        if ($request->filled('admin_status')) {
            return true;
        }

        // Flat status payload (no nested order/items import body)
        if (
            $request->filled('order_number')
            && $request->filled('status')
            && ! $request->has('order')
            && ! $request->has('items')
        ) {
            return true;
        }

        return false;
    }
}
