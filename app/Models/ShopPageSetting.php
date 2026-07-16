<?php

namespace App\Models;

use App\Services\MediaStorageService;
use Illuminate\Database\Eloquent\Model;

class ShopPageSetting extends Model
{
    protected $fillable = [
        'is_enabled',
        'hero_title',
        'hero_subtitle',
        'hero_image',
        'hero_cta_label',
        'section_title',
        'section_subtitle',
        'show_all_when_empty',
        'featured_product_ids',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'show_all_when_empty' => 'boolean',
            'featured_product_ids' => 'array',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'is_enabled' => true,
            'hero_title' => 'Shop the Latest',
            'hero_subtitle' => 'Newest arrivals first — curated brands, live from our store.',
            'hero_cta_label' => 'Browse products',
            'section_title' => 'All products',
            'section_subtitle' => 'Filter by brand, price, size, and more.',
            'show_all_when_empty' => true,
        ]);
    }

    public function heroImageUrl(): ?string
    {
        if (! $this->hero_image) {
            return null;
        }

        if (str_starts_with($this->hero_image, 'http://') || str_starts_with($this->hero_image, 'https://')) {
            return $this->hero_image;
        }

        return app(MediaStorageService::class)->url($this->hero_image);
    }

    /** @return array<int, int> */
    public function featuredProductIds(): array
    {
        return array_values(array_map('intval', array_filter($this->featured_product_ids ?? [])));
    }
}
