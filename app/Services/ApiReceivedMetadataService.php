<?php

namespace App\Services;

use App\Models\ApiReceivedItem;

class ApiReceivedMetadataService
{
    /** @var array<int, string> */
    private const BRAND_KEYS = ['brand', 'brand_name', 'brandName'];

    /** @var array<int, string> */
    private const CATEGORY_KEYS = ['ecommerce_category_name', 'category_name', 'category', 'categoryName'];

    /** @var array<int, string> */
    private const VENDOR_KEYS = ['vendor', 'vendor_name', 'vendorName', 'seller', 'seller_name', 'shop', 'shop_name', 'store', 'store_name'];

    /** @return array{brand: ?string, vendor: ?string, category_name: ?string} */
    public function extract(array $data): array
    {
        return [
            'brand' => $this->firstString($data, self::BRAND_KEYS),
            'vendor' => $this->firstString($data, self::VENDOR_KEYS),
            'category_name' => $this->extractCategory($data),
        ];
    }

    public function extractCategory(array $data): ?string
    {
        return $this->firstString($data, self::CATEGORY_KEYS);
    }

    public function syncItem(ApiReceivedItem $item): bool
    {
        $payload = $item->payloadData();

        if ($payload === []) {
            return false;
        }

        $metadata = $this->extract($payload);
        $stored = $item->getAttributes();
        $updates = [];

        if ($metadata['category_name'] !== null && ($stored['category_name'] ?? null) !== $metadata['category_name']) {
            $updates['category_name'] = $metadata['category_name'];
        }

        if (ApiReceivedItem::hasBrandVendorColumns()) {
            if ($metadata['brand'] !== null && ($stored['brand'] ?? null) !== $metadata['brand']) {
                $updates['brand'] = $metadata['brand'];
            }

            if ($metadata['vendor'] !== null && ($stored['vendor'] ?? null) !== $metadata['vendor']) {
                $updates['vendor'] = $metadata['vendor'];
            }
        }

        if ($updates === []) {
            return false;
        }

        $item->update($updates);

        if (isset($updates['category_name'])) {
            app(ApiReceivedCategoryService::class)->attachToItem($item->fresh(), $updates['category_name']);
        }

        if (isset($updates['brand'])) {
            app(ApiReceivedBrandService::class)->attachToItem($item->fresh(), $updates['brand']);
        }

        return true;
    }

    /** @param array<int, string> $keys */
    private function firstString(array $data, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (! array_key_exists($key, $data)) {
                continue;
            }

            $value = $this->stringValue($data[$key]);

            if ($value !== null) {
                return $value;
            }
        }

        foreach (['product', 'data', 'details'] as $nestedKey) {
            if (! isset($data[$nestedKey]) || ! is_array($data[$nestedKey])) {
                continue;
            }

            $nested = $this->firstString($data[$nestedKey], $keys);

            if ($nested !== null) {
                return $nested;
            }
        }

        return null;
    }

    private function stringValue(mixed $value): ?string
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }
}
