<?php

namespace App\Models;

use App\Services\MediaStorageService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    public function apiReceivedItem(): HasOne
    {
        return $this->hasOne(ApiReceivedItem::class);
    }

    public function scopeLiveFromApi(Builder $query): Builder
    {
        return $query->whereHas('apiReceivedItem', function (Builder $relation) {
            $relation->where('status', ApiReceivedItem::STATUS_IMPORTED);
        });
    }

    public function isLiveFromApi(): bool
    {
        return $this->apiReceivedItem()
            ->where('status', ApiReceivedItem::STATUS_IMPORTED)
            ->exists();
    }

    public function imageUrl(): ?string
    {
        if (! $this->image) {
            return null;
        }

        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }

        return app(MediaStorageService::class)->url($this->image);
    }

    /** @return array<int, string|null> */
    public function imageUrls(): array
    {
        $images = $this->images ?? ($this->image ? [$this->image] : []);

        return collect($images)
            ->map(function ($img) {
                if (str_starts_with((string) $img, 'http://') || str_starts_with((string) $img, 'https://')) {
                    return $img;
                }

                return app(MediaStorageService::class)->url($img);
            })
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
            'sizes' => $this->normalizedList($this->sizes),
            'colors' => $this->normalizedList($this->colors),
        ];
    }

    /** @return array<int, string> */
    private function normalizedList(?array $values): array
    {
        return array_values(array_filter(
            $values ?? [],
            fn ($value) => trim((string) $value) !== ''
        ));
    }
}
