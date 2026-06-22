<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeBanner extends Model
{
    protected $fillable = [
        'image', 'badge', 'title', 'subtitle',
        'button_text', 'button_href', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function imageUrl(): ?string
    {
        return app(\App\Services\MediaStorageService::class)->url($this->image);
    }

    public function toBannerArray(): array
    {
        return [
            'image' => $this->imageUrl(),
            'badge' => $this->badge,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'button_text' => $this->button_text,
            'button_href' => $this->resolvedButtonHref(),
        ];
    }

    public function resolvedButtonHref(): string
    {
        $href = trim((string) ($this->button_href ?? ''));

        if ($href === '') {
            return route('categories.index');
        }

        if ($href === '/deals' && str_contains(strtolower($this->button_text ?? ''), 'dress')) {
            return route('categories.show', 'dresses');
        }

        if ($href === '/deals') {
            return route('deals');
        }

        if (str_starts_with($href, '/')) {
            return url($href);
        }

        if (preg_match('/^[\w-]+$/', $href)) {
            return route('categories.show', $href);
        }

        return $href;
    }
}
