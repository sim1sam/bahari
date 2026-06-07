<?php

namespace App\Models;

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

    public function toCatalogArray(): array
    {
        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description,
            'color' => $this->color,
            'image' => $this->image,
            'card_image' => $this->card_image,
            'filter' => $this->is_sale ? 'sale' : 'category',
            'product_categories' => $this->is_sale ? [] : [$this->name],
        ];
    }
}
