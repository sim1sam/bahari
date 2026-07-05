<?php

namespace App\Services;

use App\Models\ApiReceivedBrand;
use App\Models\ApiReceivedItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ApiReceivedBrandService
{
    public static function isAvailable(): bool
    {
        static $available = null;

        return $available ??= Schema::hasTable('api_received_brands');
    }

    public function register(?string $name): ?ApiReceivedBrand
    {
        if (! self::isAvailable()) {
            return null;
        }
        $name = trim((string) $name);

        if ($name === '') {
            return null;
        }

        return ApiReceivedBrand::firstOrCreate(
            ['name' => $name],
            [
                'slug' => $this->uniqueSlug($name),
                'is_active' => true,
            ]
        );
    }

    public function attachToItem(ApiReceivedItem $item, ?string $brandName): void
    {
        if (! ApiReceivedItem::hasBrandVendorColumns()) {
            return;
        }

        $brandName = trim((string) $brandName);

        if ($brandName === '') {
            return;
        }

        $brand = $this->register($brandName);

        if (! $brand) {
            if (($stored['brand'] ?? null) !== $brandName) {
                $item->update(['brand' => $brandName]);
            }

            return;
        }

        $stored = $item->getAttributes();
        $updates = [];

        if (($stored['brand'] ?? null) !== $brandName) {
            $updates['brand'] = $brandName;
        }

        if (ApiReceivedItem::hasReceivedBrandRelationColumn() && ($stored['api_received_brand_id'] ?? null) !== $brand->id) {
            $updates['api_received_brand_id'] = $brand->id;
        }

        if ($updates !== []) {
            $item->update($updates);
        }
    }

    public function syncFromReceivedItems(): int
    {
        if (! self::isAvailable()) {
            return 0;
        }

        $columns = ['id', 'brand', 'payload'];
        if (ApiReceivedItem::hasReceivedBrandRelationColumn()) {
            $columns[] = 'api_received_brand_id';
        }

        $synced = 0;

        ApiReceivedItem::query()
            ->get($columns)
            ->each(function (ApiReceivedItem $item) use (&$synced) {
                $brandName = $item->brand;

                if (! filled($brandName)) {
                    return;
                }

                $before = $item->getAttributes()['api_received_brand_id'] ?? null;
                $this->attachToItem($item, $brandName);

                if (($item->fresh()->getAttributes()['api_received_brand_id'] ?? null) !== $before) {
                    $synced++;
                }
            });

        return $synced;
    }

    /** @return Collection<int, string> */
    public function activeBrandNames(): Collection
    {
        if (! self::isAvailable()) {
            return $this->brandNamesFromItems();
        }

        return ApiReceivedBrand::query()
            ->active()
            ->orderBy('name')
            ->pluck('name');
    }

    /** @return Collection<int, string> */
    private function brandNamesFromItems(): Collection
    {
        return ApiReceivedItem::query()
            ->get(['id', 'brand', 'payload'])
            ->map(fn (ApiReceivedItem $item) => $item->brand)
            ->filter()
            ->unique()
            ->sort()
            ->values();
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $base = $base !== '' ? $base : 'brand';
        $slug = $base;
        $counter = 1;

        while (ApiReceivedBrand::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
