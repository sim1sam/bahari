<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class FooterLink extends Model
{
    public const GROUP_SHOP = 'shop';

    public const GROUP_SUPPORT = 'support';

    public const GROUP_LEGAL = 'legal';

    public const GROUPS = [
        self::GROUP_SHOP => 'Shop',
        self::GROUP_SUPPORT => 'Support',
        self::GROUP_LEGAL => 'Legal',
    ];

    protected $fillable = ['group', 'label', 'url', 'page_id', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'page_id' => 'integer',
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Resolve the public URL for this footer link.
     * Prefers linked page, then /pages/{slug} paths, then custom URL.
     */
    public function resolvedUrl(): string
    {
        $page = $this->relationLoaded('page') ? $this->page : $this->page()->first();

        // Auto-match by label when page_id was never set (old "#" links).
        if (! $page) {
            $page = Page::query()
                ->active()
                ->where(function ($q) {
                    $q->where('title', $this->label)
                        ->orWhere('slug', Str::slug((string) $this->label));
                })
                ->first();
        }

        if ($page && $page->is_active) {
            return '/pages/'.$page->slug;
        }

        $url = trim((string) $this->url);

        if ($url === '' || $url === '#') {
            return '#';
        }

        if (preg_match('#/pages/([A-Za-z0-9\-]+)#', $url, $matches)) {
            return '/pages/'.$matches[1];
        }

        return $url;
    }
}
