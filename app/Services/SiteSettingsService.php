<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SiteSettingsService
{
    private const CACHE_KEY = 'site_settings';

    public function get(): SiteSetting
    {
        $data = Cache::remember(self::CACHE_KEY, 3600, fn () => SiteSetting::current()->toArray());

        $settings = new SiteSetting;
        $settings->forceFill($data);
        $settings->exists = true;

        return $settings;
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function siteName(): string
    {
        return $this->get()->site_name ?: config('app.name', 'Shop');
    }

    public function tagline(): string
    {
        return $this->get()->tagline ?: 'Premium women\'s fashion for every occasion.';
    }

    public function logoUrl(): ?string
    {
        return $this->assetUrl($this->get()->logo);
    }

    public function faviconUrl(): ?string
    {
        return $this->assetUrl($this->get()->favicon);
    }

    public function ogImageUrl(): ?string
    {
        return $this->assetUrl($this->get()->og_image);
    }

    public function pageTitle(?string $pageTitle = null): string
    {
        $site = $this->siteName();

        if ($pageTitle) {
            return "{$pageTitle} — {$site}";
        }

        return $this->get()->meta_title ?: $site;
    }

    public function metaDescription(?string $fallback = null): string
    {
        return $this->get()->meta_description
            ?: $fallback
            ?: 'Shop the latest fashion with fast delivery and great prices.';
    }

    public function metaKeywords(): ?string
    {
        return $this->get()->meta_keywords;
    }

    public function ogTitle(?string $pageTitle = null): string
    {
        return $this->get()->og_title ?: $this->pageTitle($pageTitle);
    }

    public function ogDescription(?string $fallback = null): string
    {
        return $this->get()->og_description ?: $this->metaDescription($fallback);
    }

    public function footerDescription(): string
    {
        return $this->get()->footer_description ?: $this->tagline();
    }

    public function footerCopyright(): string
    {
        $template = $this->get()->footer_copyright ?: '© {year} {site}. All rights reserved.';

        return str_replace(
            ['{year}', '{site}'],
            [date('Y'), $this->siteName()],
            $template
        );
    }

    public function newsletterPlaceholder(): string
    {
        return $this->get()->newsletter_placeholder ?: 'Your email';
    }

    public function newsletterButtonText(): string
    {
        return $this->get()->newsletter_button_text ?: 'Join';
    }

    public function footerShopTitle(): string
    {
        return $this->get()->footer_shop_title ?: 'Shop';
    }

    public function footerSupportTitle(): string
    {
        return $this->get()->footer_support_title ?: 'Support';
    }

    public function newsletterTitle(): string
    {
        return $this->get()->newsletter_title ?: 'Stay Updated';
    }

    public function newsletterText(): string
    {
        return $this->get()->newsletter_text ?: 'Get exclusive deals and new arrivals in your inbox.';
    }

    public function newsletterEnabled(): bool
    {
        return (bool) ($this->get()->newsletter_enabled ?? true);
    }

    public function newsletterSuccessMessage(): string
    {
        return $this->get()->newsletter_success_message ?: 'Thanks for subscribing! Check your inbox for updates.';
    }

    public function logoInitial(): string
    {
        return strtoupper(substr($this->siteName(), 0, 1));
    }

    public function gtmContainerId(): ?string
    {
        $id = strtoupper(trim((string) ($this->get()->gtm_container_id ?? '')));

        return $id !== '' ? $id : null;
    }

    public function gtmEnabled(): bool
    {
        return (bool) ($this->get()->gtm_enabled ?? false) && $this->gtmContainerId() !== null;
    }

    private function assetUrl(?string $path): ?string
    {
        return app(MediaStorageService::class)->url($path);
    }

    public function apiReceiveUrl(): string
    {
        $override = trim((string) (env('API_WEBHOOK_URL') ?: $this->get()->api_webhook_url ?: ''));

        if ($override !== '') {
            if (str_contains($override, '/api/content/receive')) {
                return rtrim($override, '/');
            }

            return rtrim($override, '/').'/api/content/receive';
        }

        return rtrim(config('app.url'), '/').'/api/content/receive';
    }

    public function apiLogoUrl(): ?string
    {
        return $this->assetUrl($this->get()->api_logo);
    }
}
