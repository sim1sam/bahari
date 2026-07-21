<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_slug', 'product_name', 'brand', 'product_link', 'image',
        'size', 'color', 'quantity', 'price',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $item) {
            if (! Schema::hasColumn($item->getTable(), 'brand')) {
                return;
            }

            if (filled(trim((string) ($item->brand ?? '')))) {
                return;
            }

            $item->brand = self::resolveBrandForSlug($item->product_slug);
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function imageUrl(): ?string
    {
        if (! $this->image) {
            return null;
        }

        return app(\App\Services\MediaStorageService::class)->url($this->image) ?? $this->image;
    }

    /**
     * Prefer an explicit brand, otherwise copy from the linked product (or API source).
     */
    public static function resolveBrandForSlug(?string $slug, ?string $explicitBrand = null): ?string
    {
        $explicit = trim((string) $explicitBrand);

        if ($explicit !== '') {
            return $explicit;
        }

        $slug = trim((string) $slug);

        if ($slug === '') {
            return null;
        }

        $product = Product::query()->where('slug', $slug)->first(['id', 'brand']);
        $productBrand = trim((string) ($product?->brand ?? ''));

        if ($productBrand !== '') {
            return $productBrand;
        }

        if (! $product) {
            return null;
        }

        $apiBrand = trim((string) (
            ApiReceivedItem::query()
                ->where('product_id', $product->id)
                ->whereNotNull('brand')
                ->where('brand', '!=', '')
                ->value('brand') ?? ''
        ));

        return $apiBrand !== '' ? $apiBrand : null;
    }

    /**
     * Persist missing brands on existing order lines from their products.
     */
    public function ensureBrandSaved(): bool
    {
        if (! Schema::hasColumn($this->getTable(), 'brand')) {
            return false;
        }

        if (filled(trim((string) ($this->brand ?? '')))) {
            return false;
        }

        $brand = self::resolveBrandForSlug($this->product_slug);

        if ($brand === null) {
            return false;
        }

        $this->forceFill(['brand' => $brand])->saveQuietly();

        return true;
    }
}
