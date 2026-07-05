<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApiReceivedBrand extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'is_active',
        'notes',
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
}
