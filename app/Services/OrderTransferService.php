<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderTransferSetting;
use Illuminate\Support\Facades\Http;
use Throwable;

class OrderTransferService
{
    public function transfer(Order $order, bool $force = false): bool
    {
        $setting = OrderTransferSetting::current();

        if (! $setting->isConfigured()) {
            $order->forceFill([
                'external_transfer_status' => 'skipped',
                'external_transfer_message' => 'Order transfer setting is inactive or incomplete.',
            ])->save();

            return false;
        }

        if (! $force && $order->external_transfer_status === 'sent') {
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

        $shippingZone = $order->shipping_zone;
        $shippingFee = (float) $order->shipping;
        $shippingZoneLabel = $shippingZone
            ? \App\Support\ShippingZone::label($shippingZone)
            : null;

        return [
            'order' => [
                'number' => $order->number,
                'status' => $order->status,
                'type' => $order->order_type,
                'customer_name' => $order->customer_name,
                'customer_email' => $order->customer_email,
                'customer_phone' => $order->customer_phone,
                // Delivery / shipping address
                'address' => $order->address,
                'city' => $order->city,
                'zip' => $order->zip,
                'shipping_address' => $order->address,
                'shipping_city' => $order->city,
                'shipping_zip' => $order->zip,
                'shipping_phone' => $order->customer_phone,
                'shipping_name' => $order->customer_name,
                'delivery_address' => $order->address,
                'delivery_city' => $order->city,
                'delivery_zip' => $order->zip,
                'delivery_phone' => $order->customer_phone,
                'delivery_name' => $order->customer_name,
                'postal_code' => $order->zip,
                // Shipping zone + fee
                'shipping_zone' => $shippingZone,
                'shipping_zone_label' => $shippingZoneLabel,
                'shipping' => $shippingFee,
                'shipping_fee' => $shippingFee,
                'shipping_cost' => $shippingFee,
                'shipping_amount' => $shippingFee,
                'payment_method' => $order->payment_method,
                'payment_status' => $this->mapPaymentStatusForReceiver($order->payment_status),
                'reference_code' => $order->reference_code,
                'bank_name' => $order->bank_name,
                'notes' => $order->notes,
                'coupon_code' => $order->coupon_code,
                'subtotal' => (float) $order->subtotal,
                'discount' => (float) $order->discount,
                'total' => (float) $order->total,
                'amount_paid' => (float) $order->amount_paid,
                'created_at' => $order->created_at?->toIso8601String(),
            ],
            'shipping' => [
                'name' => $order->customer_name,
                'phone' => $order->customer_phone,
                'email' => $order->customer_email,
                'address' => $order->address,
                'city' => $order->city,
                'zip' => $order->zip,
                'postal_code' => $order->zip,
                'zone' => $shippingZone,
                'zone_label' => $shippingZoneLabel,
                'fee' => $shippingFee,
                'cost' => $shippingFee,
                'amount' => $shippingFee,
            ],
            'items' => $order->items->map(function ($item) {
                $imageUrl = $this->resolveItemImageUrl($item);
                $item->ensureBrandSaved();
                $brand = \App\Models\OrderItem::resolveBrandForSlug($item->product_slug, $item->brand);

                return [
                    'product_slug' => $item->product_slug,
                    'product_name' => $item->product_name,
                    'brand' => $brand,
                    'brand_name' => $brand,
                    'product_brand' => $brand,
                    'product_link' => $item->product_link,
                    // Multiple keys — receiver sites look for different field names
                    'image' => $imageUrl,
                    'image_url' => $imageUrl,
                    'product_image' => $imageUrl,
                    'size' => $item->size,
                    'color' => $item->color,
                    'quantity' => (int) $item->quantity,
                    'price' => (float) $item->price,
                ];
            })->values()->all(),
            'payments' => $order->payments->map(fn ($payment) => [
                'amount' => (float) $payment->amount,
                'payment_method' => $payment->payment_method,
                'bank_name' => $payment->bank_name,
                'notes' => $payment->notes,
                'created_at' => $payment->created_at?->toIso8601String(),
            ])->values()->all(),
        ];
    }

    /**
     * Receiver sites (e.g. fb_orders) often use a fixed ENUM that does not include "due".
     * Map local payment statuses to compatible receiver values.
     */
    private function mapPaymentStatusForReceiver(?string $status): string
    {
        return match ($status) {
            'paid' => 'paid',
            'partial' => 'partial',
            // COD / unpaid balance — receiver ENUM commonly uses unpaid|pending, not due
            'due' => 'unpaid',
            'pending' => 'pending',
            default => 'pending',
        };
    }

    /**
     * Build a publicly downloadable absolute image URL for the receiver site.
     */
    private function resolveItemImageUrl($item): ?string
    {
        $raw = trim((string) ($item->image ?? ''));

        if ($raw === '') {
            return null;
        }

        $media = app(MediaStorageService::class);

        // Already an absolute external URL
        if ($media->isExternal($raw)) {
            return $raw;
        }

        // Prefer MediaStorageService resolved URL when the file exists
        $resolved = $media->url($raw);

        if ($resolved) {
            return $this->toAbsoluteUrl($resolved);
        }

        // Local storage path — use /media/{path} so it works even without storage symlink
        $path = ltrim(str_replace('\\', '/', $raw), '/');
        $path = str_starts_with($path, 'storage/') ? substr($path, 8) : $path;

        if ($path !== '' && \Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            return route('storage.file', ['path' => $path], absolute: true);
        }

        // Last resort: turn relative path into absolute site URL
        return $this->toAbsoluteUrl($raw);
    }

    private function toAbsoluteUrl(string $url): string
    {
        $url = trim($url);

        if (str_starts_with($url, '//')) {
            return 'https:'.$url;
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        if (! str_starts_with($url, '/')) {
            $url = '/'.ltrim($url, '/');
        }

        return url($url);
    }
}
