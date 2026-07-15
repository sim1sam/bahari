<?php

namespace App\Support;

use Illuminate\Support\Str;

class TrackingPayload
{
    public static function currency(): string
    {
        return strtoupper((string) config('currency.code', 'BDT'));
    }

    public static function eventId(?string $existing = null): string
    {
        return $existing ?: (string) Str::uuid();
    }

    /**
     * Flat product fields for GTM DLVs (Shopify-style container).
     *
     * @param  array<string, mixed>  $product
     * @return array<string, mixed>
     */
    public static function fromProduct(array $product, int $quantity = 1, ?int $position = null): array
    {
        $id = (string) ($product['slug'] ?? $product['id'] ?? $product['sku'] ?? '');
        $sku = (string) ($product['sku'] ?? $id);
        $name = (string) ($product['name'] ?? '');
        $price = (float) ($product['price'] ?? 0);
        $category = (string) ($product['category'] ?? $product['product_type'] ?? '');
        $brand = (string) ($product['brand'] ?? config('app.name'));
        $variant = trim(implode(' / ', array_filter([
            $product['size'] ?? null,
            $product['color'] ?? null,
            $product['variant_title'] ?? null,
        ])));

        $payload = [
            'product_id' => $id,
            'product_sku' => $sku,
            'google_product_id' => $sku,
            'product_name' => $name,
            'product_price' => $price,
            'product_type' => $category,
            'product_brand' => $brand,
            'quantity' => $quantity,
            'variant_id' => $variant !== '' ? $variant : $id,
            'variant_title' => $variant,
            'price' => $price,
            'total_value' => round($price * $quantity, 2),
            'currency' => self::currency(),
            'presentment_currency' => self::currency(),
            'page_currency' => self::currency(),
            'meta_currency' => self::currency(),
            'meta_value' => round($price * $quantity, 2),
            'meta_content_ids' => [$id],
            'meta_content_type' => 'product',
            'meta_content_name' => $name,
            'meta_contents' => [[
                'id' => $id,
                'quantity' => $quantity,
                'item_price' => $price,
            ]],
            'ecomm_prodid' => $id,
        ];

        if ($position !== null) {
            $payload['product_position'] = $position;
        }

        return $payload;
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<string, mixed>
     */
    public static function fromCartItems(array $items, float $total, ?string $coupon = null, float $discount = 0, float $shipping = 0): array
    {
        $productNames = [];
        $productPrices = [];
        $quantities = [];
        $brands = [];
        $types = [];
        $ids = [];
        $skus = [];
        $variants = [];
        $contents = [];
        $totalQty = 0;

        foreach ($items as $item) {
            $id = (string) ($item['slug'] ?? $item['product_slug'] ?? $item['id'] ?? '');
            $sku = (string) ($item['sku'] ?? $id);
            $name = (string) ($item['name'] ?? $item['product_name'] ?? '');
            $price = (float) ($item['price'] ?? 0);
            $qty = (int) ($item['quantity'] ?? 1);
            $category = (string) ($item['category'] ?? $item['product_type'] ?? '');
            $brand = (string) ($item['brand'] ?? config('app.name'));
            $variant = trim(implode(' / ', array_filter([
                $item['size'] ?? null,
                $item['color'] ?? null,
            ])));

            $productNames[] = $name;
            $productPrices[] = $price;
            $quantities[] = $qty;
            $brands[] = $brand;
            $types[] = $category;
            $ids[] = $id;
            $skus[] = $sku;
            $variants[] = $variant !== '' ? $variant : $id;
            $contents[] = [
                'id' => $id,
                'quantity' => $qty,
                'item_price' => $price,
            ];
            $totalQty += $qty;
        }

        $currency = self::currency();

        return [
            'product_id' => $ids,
            'product_sku' => $skus,
            'google_product_id' => $skus,
            'product_name' => $productNames,
            'product_price' => $productPrices,
            'product_type' => $types,
            'product_brand' => $brands,
            'quantity' => $quantities,
            'variant_id' => $variants,
            'total_value' => round($total, 2),
            'presentment_totalValue' => round($total, 2),
            'total_quantity' => $totalQty,
            'currency' => $currency,
            'presentment_currency' => $currency,
            'page_currency' => $currency,
            'meta_currency' => $currency,
            'meta_value' => round($total, 2),
            'meta_content_ids' => $ids,
            'meta_content_type' => 'product',
            'meta_contents' => $contents,
            'ecomm_prodid' => $ids,
            'coupon' => $coupon,
            'discount' => $discount,
            'shipping' => $shipping,
            'FB - Purchase Product' => array_map(fn ($id, $name, $price, $qty) => [
                'item_id' => $id,
                'item_name' => $name,
                'price' => $price,
                'quantity' => $qty,
            ], $ids, $productNames, $productPrices, $quantities),
        ];
    }

    /**
     * @param  array<string, mixed>  $customer
     * @return array<string, mixed>
     */
    public static function userFields(?array $customer, ?int $userId = null): array
    {
        if (! $customer && ! $userId) {
            return [];
        }

        $name = trim((string) ($customer['name'] ?? ''));
        $parts = preg_split('/\s+/', $name, 2) ?: [];

        return array_filter([
            'user.id' => $userId,
            'user.email' => $customer['email'] ?? null,
            'user.phone' => $customer['phone'] ?? null,
            'user.address.first_name' => $parts[0] ?? null,
            'user.address.last_name' => $parts[1] ?? null,
            'user.address.address1' => $customer['address'] ?? null,
            'user.address.city' => $customer['city'] ?? null,
            'user.address.zip' => $customer['zip'] ?? null,
            'user.address.country_code' => 'BD',
            'user.address.country' => 'Bangladesh',
            'email_address' => $customer['email'] ?? null,
            'phone_number' => $customer['phone'] ?? null,
            'first_name' => $parts[0] ?? null,
            'city' => $customer['city'] ?? null,
            'postal_code' => $customer['zip'] ?? null,
            'country' => 'BD',
        ], fn ($v) => $v !== null && $v !== '');
    }
}
