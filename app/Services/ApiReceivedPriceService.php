<?php

namespace App\Services;

use App\Models\ApiReceivedItem;

class ApiReceivedPriceService
{
    /** @var array<int, string> */
    private const SALE_KEYS = [
        'price_bdt',
        'priceBdt',
        'converted_price',
        'convertedPrice',
        'price',
        'selling_price',
        'sellingPrice',
        'sale_price',
        'salePrice',
        'unit_price',
        'unitPrice',
        'amount',
        'final_price',
        'finalPrice',
        'current_price',
        'currentPrice',
        'discounted_price',
        'discount_price',
        'product_price',
        'productPrice',
        'retail_price',
        'retailPrice',
        'display_price',
        'displayPrice',
        'price_text',
        'priceText',
        'price_label',
        'priceLabel',
        'formatted_price',
        'formattedPrice',
    ];

    /** @var array<int, string> */
    private const ORIGINAL_KEYS = [
        'converted_original_price',
        'convertedOriginalPrice',
        'converted_mrp',
        'convertedMrp',
        'original_price',
        'originalPrice',
        'regular_price',
        'mrp',
        'compare_at_price',
        'list_price',
        'old_price',
        'was_price',
        'compare_price',
    ];

    /** @var array<int, string> */
    private const PURCHASE_KEYS = [
        'purchase_price_bdt',
        'purchasePriceBdt',
        'purchase_price',
        'purchasePrice',
    ];

    /** @return array{price: float, original_price: ?float, purchase_price: ?float} */
    public function extract(mixed $data): array
    {
        if (! is_array($data)) {
            return ['price' => 0, 'original_price' => null, 'purchase_price' => null];
        }

        $data = $this->normalizeKeys($data);

        $sale = $this->firstAmount($data, self::SALE_KEYS, ignoreZero: true);
        $original = $this->firstAmount($data, self::ORIGINAL_KEYS, ignoreZero: true);
        $purchase = $this->firstAmount($data, self::PURCHASE_KEYS, ignoreZero: true);

        foreach (['product', 'pricing', 'data', 'details'] as $nestedKey) {
            if (! isset($data[$nestedKey]) || ! is_array($data[$nestedKey])) {
                continue;
            }

            $nested = $data[$nestedKey];
            $sale ??= $this->firstAmount($nested, self::SALE_KEYS, ignoreZero: true);
            $original ??= $this->firstAmount($nested, self::ORIGINAL_KEYS, ignoreZero: true);
            $purchase ??= $this->firstAmount($nested, self::PURCHASE_KEYS, ignoreZero: true);

            if (isset($nested['pricing']) && is_array($nested['pricing'])) {
                $pricing = $nested['pricing'];
                $sale ??= $this->firstAmount($pricing, array_merge(self::SALE_KEYS, ['sale', 'sell', 'current']), ignoreZero: true);
                $original ??= $this->firstAmount($pricing, array_merge(self::ORIGINAL_KEYS, ['regular', 'original', 'compare']), ignoreZero: true);
                $purchase ??= $this->firstAmount($pricing, array_merge(self::PURCHASE_KEYS, ['purchase', 'cost']), ignoreZero: true);
            }
        }

        if (isset($data['pricing']) && is_array($data['pricing'])) {
            $pricing = $data['pricing'];
            $sale ??= $this->firstAmount($pricing, array_merge(self::SALE_KEYS, ['sale', 'sell', 'current']), ignoreZero: true);
            $original ??= $this->firstAmount($pricing, array_merge(self::ORIGINAL_KEYS, ['regular', 'original', 'compare']), ignoreZero: true);
            $purchase ??= $this->firstAmount($pricing, array_merge(self::PURCHASE_KEYS, ['purchase', 'cost']), ignoreZero: true);
        }

        if ($sale === null && $original !== null) {
            $sale = $original;
            $original = null;
        }

        if ($sale !== null && $original !== null && $original <= $sale) {
            $original = null;
        }

        return [
            'price' => round((float) ($sale ?? 0), 2),
            'original_price' => $original !== null ? round((float) $original, 2) : null,
            'purchase_price' => $purchase !== null ? round((float) $purchase, 2) : null,
        ];
    }

    /** @return array<string, mixed> */
    public function mergeIntoItem(array $item): array
    {
        $item = $this->normalizeKeys($item);
        $prices = $this->extract($item);
        $item['price'] = $prices['price'];
        $item['converted_price'] = $prices['price'];

        if ($prices['original_price'] !== null) {
            $item['original_price'] = $prices['original_price'];
        }

        if ($prices['purchase_price'] !== null) {
            $item['purchase_price'] = $prices['purchase_price'];
            $item['purchase_price_bdt'] = $prices['purchase_price'];
        }

        return $item;
    }

    /** @return array{price: float, original_price: ?float, purchase_price: ?float} */
    public function resolve(ApiReceivedItem $item): array
    {
        $fromPayload = $this->extract($item->payloadData());
        $manualPrice = (float) $item->price;
        $manualOriginal = $item->original_price !== null ? (float) $item->original_price : null;
        $manualPurchase = $item->purchase_price !== null ? (float) $item->purchase_price : null;

        if ($manualPrice > 0) {
            return [
                'price' => round($manualPrice, 2),
                'original_price' => $manualOriginal,
                'purchase_price' => $manualPurchase ?? $fromPayload['purchase_price'],
            ];
        }

        return $fromPayload;
    }

    public function applyToItem(ApiReceivedItem $item): bool
    {
        return $this->syncItem($item);
    }

    public function syncItem(ApiReceivedItem $item): bool
    {
        $item->loadMissing('product');

        $payload = $item->payloadData();

        if ($payload === []) {
            return false;
        }

        $prices = $this->extract($payload);

        if ($prices['price'] <= 0 && $prices['original_price'] === null && $prices['purchase_price'] === null) {
            return false;
        }

        $changed = (float) $item->price !== $prices['price']
            || ($item->original_price === null ? $prices['original_price'] !== null : (float) $item->original_price !== (float) ($prices['original_price'] ?? 0))
            || ($item->purchase_price === null ? $prices['purchase_price'] !== null : (float) $item->purchase_price !== (float) ($prices['purchase_price'] ?? 0));

        if (! $changed) {
            return false;
        }

        $item->update([
            'price' => $prices['price'],
            'original_price' => $prices['original_price'],
            'purchase_price' => $prices['purchase_price'],
        ]);

        if ($item->product_id && $item->product) {
            $productUpdate = [
                'price' => $prices['price'],
                'original_price' => $prices['original_price'],
            ];

            if ($prices['purchase_price'] !== null) {
                $productUpdate['purchase_price'] = $prices['purchase_price'];
            }

            $item->product->update($productUpdate);
        }

        return true;
    }

    /** @param array<int, string> $keys */
    private function firstAmount(array $data, array $keys, bool $ignoreZero = false): ?float
    {
        foreach ($keys as $key) {
            if (! array_key_exists($key, $data)) {
                continue;
            }

            $parsed = $this->parseAmount($data[$key]);

            if ($parsed !== null && (! $ignoreZero || $parsed > 0)) {
                return $parsed;
            }
        }

        return null;
    }

    private function parseAmount(mixed $value): ?float
    {
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        $normalized = str_replace([',', ' '], '', $value);
        $normalized = preg_replace('/[^\d.]/', '', $normalized) ?? '';

        if ($normalized === '' || ! is_numeric($normalized)) {
            return null;
        }

        return (float) $normalized;
    }

    /** @return array<string, mixed> */
    private function normalizeKeys(array $data): array
    {
        $aliases = [
            'priceBdt' => 'price_bdt',
            'convertedPrice' => 'converted_price',
            'convertedOriginalPrice' => 'converted_original_price',
            'convertedMrp' => 'converted_mrp',
            'originalPrice' => 'original_price',
            'sellingPrice' => 'selling_price',
            'salePrice' => 'sale_price',
            'displayPrice' => 'display_price',
            'priceText' => 'price_text',
            'formattedPrice' => 'formatted_price',
            'purchasePriceBdt' => 'purchase_price_bdt',
            'purchasePrice' => 'purchase_price',
        ];

        foreach ($aliases as $from => $to) {
            if (array_key_exists($from, $data) && ! array_key_exists($to, $data)) {
                $data[$to] = $data[$from];
            }
        }

        return $data;
    }
}
