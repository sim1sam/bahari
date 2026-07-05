<?php

namespace App\Services;

use App\Models\ApiReceivedBrand;
use App\Models\ApiReceivedItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ApiReceivedBrandService
{
    public function register(?string $name): ?ApiReceivedBrand
    {
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
        $synced = 0;

        ApiReceivedItem::query()
            ->get(['id', 'brand', 'payload', 'api_received_brand_id'])
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
        return ApiReceivedBrand::query()
            ->active()
            ->orderBy('name')
            ->pluck('name');
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
