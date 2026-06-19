<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderTransferSetting;
use Illuminate\Support\Facades\Http;
use Throwable;

class OrderTransferService
{
    public function transfer(Order $order): bool
    {
        $setting = OrderTransferSetting::current();

        if (! $setting->isConfigured()) {
            $order->forceFill([
                'external_transfer_status' => 'skipped',
                'external_transfer_message' => 'Order transfer setting is inactive or incomplete.',
            ])->save();

            return false;
        }

        if ($order->external_transfer_status === 'sent') {
            return true;
        }

        $endpoint = $setting->endpointUrl();

        if (! $endpoint) {
            $order->forceFill([
                'external_transfer_status' => 'failed',
                'external_transfer_message' => 'Transfer endpoint URL is missing.',
            ])->save();

            return false;
        }

        try {
            $response = Http::timeout(20)
                ->acceptJson()
                ->withHeaders([
                    'X-API-Key' => $setting->api_key,
                    'Authorization' => 'Bearer '.$setting->access_token,
                ])
                ->post($endpoint, $this->payload($order));

            if ($response->successful()) {
                $order->forceFill([
                    'external_transfer_status' => 'sent',
                    'external_transfer_message' => 'Transferred successfully.',
                    'external_transferred_at' => now(),
                ])->save();

                return true;
            }

            $order->forceFill([
                'external_transfer_status' => 'failed',
                'external_transfer_message' => 'HTTP '.$response->status().': '.(string) str($response->body())->limit(500),
            ])->save();
        } catch (Throwable $exception) {
            $order->forceFill([
                'external_transfer_status' => 'failed',
                'external_transfer_message' => (string) str($exception->getMessage())->limit(500),
            ])->save();
        }

        return false;
    }

    private function payload(Order $order): array
    {
        $order->loadMissing(['items', 'payments', 'user']);

        return [
            'order' => [
                'number' => $order->number,
                'status' => $order->status,
                'type' => $order->order_type,
                'customer_name' => $order->customer_name,
                'customer_email' => $order->customer_email,
                'customer_phone' => $order->customer_phone,
                'address' => $order->address,
                'city' => $order->city,
                'zip' => $order->zip,
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status,
                'reference_code' => $order->reference_code,
                'bank_name' => $order->bank_name,
                'notes' => $order->notes,
                'coupon_code' => $order->coupon_code,
                'subtotal' => (float) $order->subtotal,
                'discount' => (float) $order->discount,
                'shipping' => (float) $order->shipping,
                'total' => (float) $order->total,
                'amount_paid' => (float) $order->amount_paid,
                'created_at' => $order->created_at?->toIso8601String(),
            ],
            'items' => $order->items->map(fn ($item) => [
                'product_slug' => $item->product_slug,
                'product_name' => $item->product_name,
                'product_link' => $item->product_link,
                'image' => $this->absoluteImageUrl($item->imageUrl()),
                'size' => $item->size,
                'color' => $item->color,
                'quantity' => (int) $item->quantity,
                'price' => (float) $item->price,
            ])->values()->all(),
            'payments' => $order->payments->map(fn ($payment) => [
                'amount' => (float) $payment->amount,
                'payment_method' => $payment->payment_method,
                'bank_name' => $payment->bank_name,
                'notes' => $payment->notes,
                'created_at' => $payment->created_at?->toIso8601String(),
            ])->values()->all(),
        ];
    }

    private function absoluteImageUrl(?string $imageUrl): ?string
    {
        if (! $imageUrl) {
            return null;
        }

        if (str_starts_with($imageUrl, 'http://') || str_starts_with($imageUrl, 'https://')) {
            return $imageUrl;
        }

        return url($imageUrl);
    }
}
