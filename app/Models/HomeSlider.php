<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeSlider extends Model
{
    protected $fillable = [
        'image', 'badge', 'title', 'subtitle',
        'primary_btn', 'primary_href', 'secondary_btn', 'secondary_href',
        'features', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function imageUrl(): ?string
    {
        return app(\App\Services\MediaStorageService::class)->url($this->image);
    }

    public function toSlideArray(): array
    {
        return [
            'image' => $this->imageUrl() ?? '',
            'badge' => $this->badge,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'primary_btn' => $this->primary_btn,
            'primary_href' => $this->primary_href,
            'secondary_btn' => $this->secondary_btn,
            'secondary_href' => $this->secondary_href,
            'features' => $this->features ?? [],
        ];
    }
}
