<?php

namespace App\Models;

use App\Services\MediaStorageService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'slug', 'name', 'description', 'color', 'image', 'card_image',
        'is_sale', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_sale' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
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
        ];
    }
}
