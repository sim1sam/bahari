<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Page extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'content',
        'meta_description',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function footerLinks(): HasMany
    {
        return $this->hasMany(FooterLink::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function url(): string
    {
        return url('/pages/'.$this->slug);
    }

    public function path(): string
    {
        return '/pages/'.$this->slug;
    }

    public static function makeSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'page';
        $slug = $base;
        $i = 2;

        while (
            static::query()
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }
}
