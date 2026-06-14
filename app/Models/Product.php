<?php

namespace App\Models;

use App\Services\MediaStorageService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'slug', 'name', 'price', 'original_price', 'image', 'images',
        'badge', 'badge_variant', 'rating', 'description', 'sizes', 'colors',
        'is_featured', 'is_new_arrival', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'original_price' => 'decimal:2',
            'rating' => 'decimal:1',
            'images' => 'array',
            'sizes' => 'array',
            'colors' => 'array',
            'is_featured' => 'boolean',
            'is_new_arrival' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function imageUrl(): ?string
    {
        return app(MediaStorageService::class)->url($this->image);
    }

    /** @return array<int, string|null> */
    public function imageUrls(): array
    {
        $images = $this->images ?? ($this->image ? [$this->image] : []);

        return collect($images)
            ->map(fn ($img) => app(MediaStorageService::class)->url($img))
            ->filter()
            ->values()
            ->all();
    }

    public function toCatalogArray(): array
    {
        $image = $this->imageUrl();

        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'price' => (float) $this->price,
            'original_price' => $this->original_price ? (float) $this->original_price : null,
            'image' => $image,
            'images' => $this->imageUrls() ?: ($image ? [$image] : []),
            'badge' => $this->badge,
            'badge_variant' => $this->badge_variant,
            'rating' => (float) $this->rating,
            'category' => $this->category?->name ?? 'Dresses',
            'description' => $this->description,
            'sizes' => $this->sizes ?? ['XS', 'S', 'M', 'L', 'XL'],
            'colors' => $this->colors ?? ['Black', 'White', 'Rose'],
        ];
    }
}
