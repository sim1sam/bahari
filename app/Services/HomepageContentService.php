<?php

namespace App\Services;

use App\Models\FooterLink;
use App\Models\HomeBanner;
use App\Models\HomeFeature;
use App\Models\HomeSlider;
use Illuminate\Support\Facades\Cache;

class HomepageContentService
{
    private const BANNERS_CACHE_KEY = 'homepage_banners_v2';

    public function sliders(): array
    {
        return Cache::remember('homepage_sliders', 3600, function () {
            return HomeSlider::active()
                ->orderBy('sort_order')
                ->get()
                ->map(fn ($s) => $s->toSlideArray())
                ->all();
        });
    }

    public function banners(): array
    {
        return Cache::remember(self::BANNERS_CACHE_KEY, 3600, function () {
            return HomeBanner::active()
                ->orderBy('sort_order')
                ->get()
                ->map(fn ($b) => $b->toBannerArray())
                ->all();
        });
    }

    public function features(): array
    {
        return Cache::remember('homepage_features', 3600, function () {
            return HomeFeature::active()
                ->orderBy('sort_order')
                ->get()
                ->map(fn ($f) => $f->toFeatureArray())
                ->all();
        });
    }

    public function footerLinks(string $group): array
    {
        // v3: page-aware URLs (busts old cached "#" support links)
        return Cache::remember("footer_links_v3_{$group}", 3600, function () use ($group) {
            return FooterLink::active()
                ->with('page')
                ->where('group', $group)
                ->orderBy('sort_order')
                ->get()
                ->map(fn ($l) => ['label' => $l->label, 'url' => $l->resolvedUrl()])
                ->all();
        });
    }

    public function clearCache(): void
    {
        Cache::forget('homepage_sliders');
        Cache::forget('homepage_banners');
        Cache::forget(self::BANNERS_CACHE_KEY);
        Cache::forget('homepage_features');

        foreach (['shop', 'support', 'legal'] as $group) {
            Cache::forget("footer_links_{$group}");
            Cache::forget("footer_links_v2_{$group}");
            Cache::forget("footer_links_v3_{$group}");
        }
    }
}
