<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderTransferSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
            'order_number' => 'required|string|max:100',
            'status' => ['required', Rule::in(['pending', 'processing', 'shipped', 'completed', 'cancelled'])],
            'payment_status' => ['nullable', Rule::in(['pending', 'paid', 'partial', 'due'])],
            'amount_paid' => 'nullable|numeric|min:0',
            'message' => 'nullable|string|max:500',
        ]);

        $order = Order::query()
            ->where('number', $validated['order_number'])
            ->first();

        if (! $order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        $order->status = $validated['status'];

        if (array_key_exists('payment_status', $validated)) {
            $order->payment_status = $validated['payment_status'];
        }

        if (array_key_exists('amount_paid', $validated)) {
            $order->amount_paid = min(round((float) $validated['amount_paid'], 2), (float) $order->total);
        }

        $order->external_transfer_message = $validated['message'] ?? 'Status updated by API.';
        $order->save();

        return response()->json([
            'message' => 'Order status updated.',
            'order_number' => $order->number,
            'status' => $order->status,
            'payment_status' => $order->payment_status,
        ]);
    }
}
