<?php

namespace App\Services;

use App\Models\ApiReceivedItem;

class ApiReceivedPriceService
{
    /** @var array<int, string> */
    private const SALE_KEYS = [
        'converted_price',
        'price',
        'selling_price',
        'sale_price',
        'unit_price',
        'amount',
        'final_price',
        'current_price',
        'discounted_price',
        'discount_price',
        'product_price',
        'retail_price',
        'cost',
    ];

    /** @var array<int, string> */
    private const ORIGINAL_KEYS = [
        'converted_original_price',
        'converted_mrp',
        'original_price',
        'regular_price',
        'mrp',
        'compare_at_price',
        'list_price',
        'old_price',
        'was_price',
        'compare_price',
    ];

    /** @return array{price: float, original_price: ?float} */
    public function extract(array $data): array
    {
        $sale = $this->firstAmount($data, self::SALE_KEYS, ignoreZero: true);
        $original = $this->firstAmount($data, self::ORIGINAL_KEYS, ignoreZero: true);

        foreach (['product', 'pricing', 'data', 'details'] as $nestedKey) {
            if (! isset($data[$nestedKey]) || ! is_array($data[$nestedKey])) {
                continue;
            }

            $nested = $data[$nestedKey];
            $sale ??= $this->firstAmount($nested, self::SALE_KEYS, ignoreZero: true);
            $original ??= $this->firstAmount($nested, self::ORIGINAL_KEYS, ignoreZero: true);

            if (isset($nested['pricing']) && is_array($nested['pricing'])) {
                $pricing = $nested['pricing'];
                $sale ??= $this->firstAmount($pricing, array_merge(self::SALE_KEYS, ['sale', 'sell', 'current']), ignoreZero: true);
                $original ??= $this->firstAmount($pricing, array_merge(self::ORIGINAL_KEYS, ['regular', 'original', 'compare']), ignoreZero: true);
            }
        }

        if (isset($data['pricing']) && is_array($data['pricing'])) {
            $pricing = $data['pricing'];
            $sale ??= $this->firstAmount($pricing, array_merge(self::SALE_KEYS, ['sale', 'sell', 'current']), ignoreZero: true);
            $original ??= $this->firstAmount($pricing, array_merge(self::ORIGINAL_KEYS, ['regular', 'original', 'compare']), ignoreZero: true);
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
        ];
    }

    /** @return array<string, mixed> */
    public function mergeIntoItem(array $item): array
    {
        $prices = $this->extract($item);
        $item['price'] = $prices['price'];

        if ($prices['original_price'] !== null) {
            $item['original_price'] = $prices['original_price'];
        }

        return $item;
    }

    public function syncItem(ApiReceivedItem $item): bool
    {
        $item->loadMissing('product');

        $payload = $item->payload ?? [];

        if ($payload === []) {
            return false;
        }

        $prices = $this->extract($payload);

        if ($prices['price'] <= 0 && $prices['original_price'] === null) {
            return false;
        }

        $changed = (float) $item->price !== $prices['price']
            || ($item->original_price === null ? $prices['original_price'] !== null : (float) $item->original_price !== (float) ($prices['original_price'] ?? 0));

        if (! $changed) {
            return false;
        }

        $item->update([
            'price' => $prices['price'],
            'original_price' => $prices['original_price'],
        ]);

        if ($item->product_id && $item->product) {
            $item->product->update([
                'price' => $prices['price'],
                'original_price' => $prices['original_price'],
            ]);
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
}
