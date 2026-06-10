<?php

namespace App\Services;

use App\Models\FooterLink;
use App\Models\HomeBanner;
use App\Models\HomeFeature;
use App\Models\HomeSlider;
use Illuminate\Support\Facades\Cache;

class HomepageContentService
{
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
        return Cache::remember('homepage_banners', 3600, function () {
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
        return Cache::remember("footer_links_{$group}", 3600, function () use ($group) {
            return FooterLink::active()
                ->where('group', $group)
                ->orderBy('sort_order')
                ->get()
                ->map(fn ($l) => ['label' => $l->label, 'url' => $l->url])
                ->all();
        });
    }

    public function clearCache(): void
    {
        Cache::forget('homepage_sliders');
        Cache::forget('homepage_banners');
        Cache::forget('homepage_features');
        Cache::forget('footer_links_shop');
        Cache::forget('footer_links_support');
        Cache::forget('footer_links_legal');
    }
}
