<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderTransferSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class OrderStatusUpdateController extends Controller
{
    /** @var array<int, string> */
    public const ADMIN_STATUSES = [
        'pending',
        'confirmed',
        'kolkata_warehouse',
        'shipped',
        'dhaka_warehouse',
        'ready_for_delivery',
        'dispatched',
        'delivered',
        'cancelled',
    ];

    public function __invoke(Request $request): JsonResponse
    {
        $setting = OrderTransferSetting::current();

        if (! $setting->isConfigured()) {
            return response()->json(['message' => 'Order status API is not configured.'], 403);
        }

        if (! hash_equals((string) $setting->api_key, (string) $request->header('X-API-Key'))) {
            Log::warning('Order status-update: invalid API key', [
                'order_number' => $request->input('order_number'),
            ]);

            return response()->json(['message' => 'Invalid API key.'], 401);
        }

        if (! hash_equals((string) $setting->access_token, (string) $request->bearerToken())) {
            Log::warning('Order status-update: invalid access token', [
                'order_number' => $request->input('order_number'),
            ]);

            return response()->json(['message' => 'Invalid access token.'], 401);
        }

        // Prefer admin_status; keep legacy "status" for older receivers.
        $rawStatus = $request->input('admin_status') ?: $request->input('status');
        $normalizedStatus = Order::normalizeAdminStatus(is_string($rawStatus) ? $rawStatus : null);

        if (filled($normalizedStatus)) {
            $request->merge([
                'admin_status' => $normalizedStatus,
                'status' => $normalizedStatus,
            ]);
        }

        $validated = $request->validate([
            'order_number' => 'required|string|max:100',
            'admin_status' => ['nullable', 'string', 'max:100', Rule::in(self::ADMIN_STATUSES)],
            'status' => 'required_without:admin_status|nullable|string|max:100',
            'payment_status' => 'nullable|string|max:50',
            'amount_paid' => 'nullable|numeric|min:0',
            'message' => 'nullable|string|max:500',
        ]);

        $adminStatus = $validated['admin_status']
            ?? Order::normalizeAdminStatus($validated['status'] ?? null)
            ?? null;

        if (! filled($adminStatus) || ! in_array($adminStatus, self::ADMIN_STATUSES, true)) {
            return response()->json([
                'message' => 'admin_status is invalid.',
                'allowed' => self::ADMIN_STATUSES,
                'received' => $rawStatus,
            ], 422);
        }

        $order = Order::findByTransferNumber($validated['order_number']);

        if (! $order) {
            Log::warning('Order status-update: order not found', [
                'order_number' => $validated['order_number'],
                'admin_status' => $adminStatus,
            ]);

            return response()->json([
                'message' => 'Order not found.',
                'order_number' => $validated['order_number'],
                'hint' => 'Send the original website order number (e.g. BF-A5045D18), not only the receiver OR- prefix number.',
            ], 404);
        }

        $order->applyReceiverStatusUpdate(
            (string) $adminStatus,
            $validated['payment_status'] ?? null,
            array_key_exists('amount_paid', $validated) ? (float) $validated['amount_paid'] : null,
            $validated['message'] ?? 'Status updated by receiver.',
        );
        $order->save();

        Log::info('Order status-update: applied', [
            'order_number' => $order->number,
            'received_order_number' => $validated['order_number'],
            'admin_status' => $order->receiver_status,
            'status' => $order->status,
        ]);

        return response()->json([
            'message' => 'Order status updated.',
            'order_number' => $order->number,
            'admin_status' => $order->receiver_status,
            'receiver_status' => $order->receiver_status,
            'status' => $order->status,
            'customer_status' => $order->customerFacingStatus(),
            'customer_status_label' => $order->statusLabel(),
            'payment_status' => $order->payment_status,
        ]);
    }
}
