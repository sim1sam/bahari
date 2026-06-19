<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderPayment;
use App\Models\OrderTransferSetting;
use App\Services\MediaStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Throwable;

class OrderImportController extends Controller
{
    public function __construct(private MediaStorageService $media) {}

    public function __invoke(Request $request): JsonResponse
    {
        $setting = OrderTransferSetting::current();

        if (! $setting->isConfigured()) {
            return response()->json(['message' => 'Order import API is not configured.'], 403);
        }

        if (! hash_equals((string) $setting->api_key, (string) $request->header('X-API-Key'))) {
            return response()->json(['message' => 'Invalid API key.'], 401);
        }

        if (! hash_equals((string) $setting->access_token, (string) $request->bearerToken())) {
            return response()->json(['message' => 'Invalid access token.'], 401);
        }

        $validated = $request->validate([
            'order' => 'required|array',
            'order.number' => 'required|string|max:100',
            'order.status' => ['nullable', Rule::in(['pending', 'processing', 'shipped', 'completed', 'cancelled'])],
            'order.type' => 'nullable|string|max:50',
            'order.customer_name' => 'nullable|string|max:200',
            'order.customer_email' => 'nullable|email|max:150',
            'order.customer_phone' => 'nullable|string|max:50',
            'order.address' => 'nullable|string|max:255',
            'order.city' => 'nullable|string|max:100',
            'order.zip' => 'nullable|string|max:50',
            'order.payment_method' => 'nullable|string|max:50',
            'order.payment_status' => ['nullable', Rule::in(['pending', 'paid', 'partial', 'due'])],
            'order.reference_code' => 'nullable|string|max:100',
            'order.bank_name' => 'nullable|string|max:100',
            'order.notes' => 'nullable|string|max:2000',
            'order.coupon_code' => 'nullable|string|max:30',
            'order.subtotal' => 'nullable|numeric|min:0',
            'order.discount' => 'nullable|numeric|min:0',
            'order.shipping' => 'nullable|numeric|min:0',
            'order.total' => 'nullable|numeric|min:0',
            'order.amount_paid' => 'nullable|numeric|min:0',
            'order.created_at' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.product_slug' => 'nullable|string|max:255',
            'items.*.product_name' => 'nullable|string|max:255',
            'items.*.product_link' => 'nullable|string|max:500',
            'items.*.image' => 'nullable|string|max:500',
            'items.*.size' => 'nullable|string|max:50',
            'items.*.color' => 'nullable|string|max:50',
            'items.*.quantity' => 'nullable|integer|min:1',
            'items.*.price' => 'nullable|numeric|min:0',
            'payments' => 'nullable|array',
            'payments.*.amount' => 'nullable|numeric|min:0',
            'payments.*.payment_method' => 'nullable|string|max:50',
            'payments.*.bank_name' => 'nullable|string|max:100',
            'payments.*.notes' => 'nullable|string|max:500',
            'payments.*.created_at' => 'nullable|string',
        ]);

        try {
            $order = DB::transaction(function () use ($validated) {
                $data = $validated['order'];

                $order = Order::query()->updateOrCreate(
                    ['number' => $data['number']],
                    [
                        'order_type' => $data['type'] ?? 'standard',
                        'customer_name' => $data['customer_name'] ?? 'Unknown',
                        'customer_email' => $data['customer_email'] ?? '',
                        'customer_phone' => $data['customer_phone'] ?? null,
                        'address' => $data['address'] ?? null,
                        'city' => $data['city'] ?? null,
                        'zip' => $data['zip'] ?? null,
                        'payment_method' => $data['payment_method'] ?? 'card',
                        'payment_status' => $data['payment_status'] ?? 'pending',
                        'reference_code' => $data['reference_code'] ?? null,
                        'bank_name' => $data['bank_name'] ?? null,
                        'notes' => $data['notes'] ?? null,
                        'coupon_code' => $data['coupon_code'] ?? null,
                        'subtotal' => round((float) ($data['subtotal'] ?? 0), 2),
                        'discount' => round((float) ($data['discount'] ?? 0), 2),
                        'shipping' => round((float) ($data['shipping'] ?? 0), 2),
                        'total' => round((float) ($data['total'] ?? 0), 2),
                        'amount_paid' => round((float) ($data['amount_paid'] ?? 0), 2),
                        'status' => $data['status'] ?? 'processing',
                        'external_transfer_status' => 'sent',
                        'external_transfer_message' => 'Imported from API.',
                    ]
                );

                $order->items()->delete();

                foreach ($validated['items'] ?? [] as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_slug' => $item['product_slug'] ?? 'imported-item',
                        'product_name' => $item['product_name'] ?? 'Imported item',
                        'product_link' => $item['product_link'] ?? null,
                        'image' => $this->storeItemImage($item['image'] ?? null),
                        'size' => $item['size'] ?? null,
                        'color' => $item['color'] ?? null,
                        'quantity' => (int) ($item['quantity'] ?? 1),
                        'price' => round((float) ($item['price'] ?? 0), 2),
                    ]);
                }

                if (! empty($validated['payments'])) {
                    $order->payments()->delete();

                    foreach ($validated['payments'] as $payment) {
                        OrderPayment::create([
                            'order_id' => $order->id,
                            'amount' => round((float) ($payment['amount'] ?? 0), 2),
                            'payment_method' => $payment['payment_method'] ?? 'bank_transfer',
                            'bank_name' => $payment['bank_name'] ?? null,
                            'notes' => $payment['notes'] ?? null,
                        ]);
                    }
                }

                return $order->fresh(['items', 'payments']);
            });
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Server Error',
                'error' => $exception->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Order received',
            'order_number' => $order->number,
        ]);
    }

    private function storeItemImage(?string $image): ?string
    {
        $image = trim((string) $image);

        if ($image === '') {
            return null;
        }

        if ($this->media->isExternal($image)) {
            try {
                return $this->media->storeFromUrl($image, 'orders/items', field: 'image');
            } catch (Throwable) {
                return $image;
            }
        }

        $absolute = url($image);

        if ($this->media->isExternal($absolute)) {
            try {
                return $this->media->storeFromUrl($absolute, 'orders/items', field: 'image');
            } catch (Throwable) {
                return $absolute;
            }
        }

        return $image;
    }
}
