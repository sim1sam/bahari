<?php

namespace App\Models;

use App\Services\MediaStorageService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApiReceivedBrand extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'is_active',
        'notes',
        'image',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function receivedItems(): HasMany
    {
        return $this->hasMany(ApiReceivedItem::class, 'api_received_brand_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
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
}
