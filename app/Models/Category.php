<?php

namespace App\Models;

use App\Services\MediaStorageService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    /** Non-product names that must not appear in the storefront menu. */
    public const BLOCKED_NAV_SLUGS = [
        'payment', 'payments', 'pay', 'billing', 'invoice', 'invoices',
        'accounting', 'finance', 'bank', 'banks', 'cod', 'wallet',
        'refund', 'refunds', 'transaction', 'transactions', 'due',
        'advance', 'partial', 'paid', 'unpaid',
    ];

    protected $fillable = [
        'slug', 'name', 'description', 'color', 'image', 'card_image',
        'is_sale', 'sort_order', 'is_active', 'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'is_sale' => 'boolean',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * True when this name/slug is a payment/system label, not a product category.
     */
    public static function isNonProductName(?string $nameOrSlug): bool
    {
        $raw = strtolower(trim((string) $nameOrSlug));

        if ($raw === '') {
            return false;
        }

        $slug = Str::slug($raw);

        if (in_array($slug, self::BLOCKED_NAV_SLUGS, true) || in_array($raw, self::BLOCKED_NAV_SLUGS, true)) {
            return true;
        }

        foreach (['payment', 'invoice', 'billing', 'accounting', 'refund', 'transaction'] as $needle) {
            if (str_contains($slug, $needle) || str_contains($raw, $needle)) {
                return true;
            }
        }

        return false;
    }

    public function isNonProduct(): bool
    {
        return self::isNonProductName($this->slug) || self::isNonProductName($this->name);
    }

    public function receivedItems(): HasMany
    {
        return $this->hasMany(ApiReceivedItem::class);
    }

    public function imageUrl(): ?string
    {
        return app(MediaStorageService::class)->url($this->image);
    }

    public function cardImageUrl(): ?string
    {
        return app(MediaStorageService::class)->url($this->card_image);
    }

    public function imagePath(): ?string
    {
        return app(MediaStorageService::class)->storedPath($this->image);
    }

    public function cardImagePath(): ?string
    {
        return app(MediaStorageService::class)->storedPath($this->card_image);
    }

    public function isExternalImage(?string $path): bool
    {
        return app(MediaStorageService::class)->isExternal($path);
    }

    public function toCatalogArray(): array
    {
        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description,
            'color' => $this->color,
            'image' => $this->imageUrl(),
            'card_image' => $this->cardImageUrl(),
            'filter' => $this->is_sale ? 'sale' : 'category',
            'product_categories' => $this->is_sale ? [] : [$this->name],
            'is_featured' => (bool) $this->is_featured,
        ];
    }
}
