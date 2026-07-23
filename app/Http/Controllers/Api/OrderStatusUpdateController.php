<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderTransferSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderStatusUpdateController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $setting = OrderTransferSetting::current();

        if (! $setting->isConfigured()) {
            return response()->json(['message' => 'Order status API is not configured.'], 403);
        }

        if (! hash_equals((string) $setting->api_key, (string) $request->header('X-API-Key'))) {
            return response()->json(['message' => 'Invalid API key.'], 401);
        }

        if (! hash_equals((string) $setting->access_token, (string) $request->bearerToken())) {
            return response()->json(['message' => 'Invalid access token.'], 401);
        }

        $validated = $request->validate([
            // Receiver may send their own workflow labels (purchase, warehouse, etc.)
            'order_number' => 'required|string|max:100',
            'status' => 'required|string|max:100',
            'payment_status' => 'nullable|string|max:50',
            'amount_paid' => 'nullable|numeric|min:0',
            'message' => 'nullable|string|max:500',
        ]);

        $order = Order::query()
            ->where('number', $validated['order_number'])
            ->first();

        if (! $order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        $order->applyReceiverStatusUpdate(
            $validated['status'],
            $validated['payment_status'] ?? null,
            array_key_exists('amount_paid', $validated) ? (float) $validated['amount_paid'] : null,
            $validated['message'] ?? 'Status updated by receiver.',
        );
        $order->save();

        return response()->json([
            'message' => 'Order status updated.',
            'order_number' => $order->number,
            'receiver_status' => $order->receiver_status,
            'status' => $order->status,
            'customer_status' => $order->customerFacingStatus(),
            'payment_status' => $order->payment_status,
        ]);
    }
}
