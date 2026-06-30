<?php

namespace App\Services;

use App\Models\SiteSetting;
use App\Support\ShippingZone;
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

    public function orderNumberPrefix(): string
    {
        $skip = ['by', 'the', 'and', 'of', '&'];
        $words = array_values(array_filter(
            preg_split('/\s+/', trim($this->siteName())) ?: [],
            fn (string $word) => ! in_array(strtolower($word), $skip, true)
        ));

        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1).substr($words[1], 0, 1));
        }

        $compactName = preg_replace('/\s+/', '', $this->siteName()) ?: 'BF';

        return strtoupper(substr($compactName, 0, 2));
    }

    public function generateOrderNumber(bool $custom = false): string
    {
        $prefix = $this->orderNumberPrefix();
        $suffix = strtoupper(substr(uniqid(), $custom ? -7 : -8));

        return $custom
            ? "{$prefix}-C{$suffix}"
            : "{$prefix}-{$suffix}";
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

    public function sslCommerzEnabled(): bool
    {
        return (bool) ($this->get()->sslcommerz_enabled ?? false);
    }

    public function sslCommerzSandbox(): bool
    {
        return (bool) ($this->get()->sslcommerz_sandbox ?? true);
    }

    public function sslCommerzStoreId(): ?string
    {
        $id = trim((string) ($this->get()->sslcommerz_store_id ?? ''));

        return $id !== '' ? $id : null;
    }

    public function sslCommerzStorePassword(): ?string
    {
        $password = $this->get()->sslcommerz_store_password;

        return filled($password) ? (string) $password : null;
    }

    public function sslCommerzConfigured(): bool
    {
        return $this->sslCommerzEnabled()
            && $this->sslCommerzStoreId() !== null
            && $this->sslCommerzStorePassword() !== null;
    }

    public function sslCommerzApiUrl(): string
    {
        return $this->sslCommerzSandbox()
            ? 'https://sandbox.sslcommerz.com/gwprocess/v4/api.php'
            : 'https://securepay.sslcommerz.com/gwprocess/v4/api.php';
    }

    public function sslCommerzValidationUrl(): string
    {
        return $this->sslCommerzSandbox()
            ? 'https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php'
            : 'https://securepay.sslcommerz.com/validator/api/validationserverAPI.php';
    }

    private function assetUrl(?string $path): ?string
    {
        return app(MediaStorageService::class)->url($path);
    }

    public function apiReceiveUrl(): string
    {
        $base = trim((string) ($this->get()->api_webhook_url ?: ''));

        if ($base === '') {
            $base = rtrim((string) config('app.url'), '/');
        }

        if (str_contains($base, '/api/content/receive')) {
            return rtrim($base, '/');
        }

        return rtrim($base, '/').'/api/content/receive';
    }

    public function apiLogoUrl(): ?string
    {
        return $this->assetUrl($this->get()->api_logo);
    }

    public function shippingFeeInsideDhaka(): float
    {
        $settings = $this->get();

        if ($settings->shipping_fee_inside_dhaka !== null) {
            return (float) $settings->shipping_fee_inside_dhaka;
        }

        return (float) config('currency.shipping_fee_inside_dhaka', 80);
    }

    public function shippingFeeOutsideDhaka(): float
    {
        $settings = $this->get();

        if ($settings->shipping_fee_outside_dhaka !== null) {
            return (float) $settings->shipping_fee_outside_dhaka;
        }

        return (float) config('currency.shipping_fee_outside_dhaka', 150);
    }

    public function shippingFeeForZone(?string $zone): float
    {
        return $zone === ShippingZone::OUTSIDE_DHAKA
            ? $this->shippingFeeOutsideDhaka()
            : $this->shippingFeeInsideDhaka();
    }

    public function freeShippingThreshold(): float
    {
        $settings = $this->get();

        if ($settings->free_shipping_threshold !== null) {
            return (float) $settings->free_shipping_threshold;
        }

        return (float) config('currency.free_shipping_threshold', 2000);
    }

    public function calculateShipping(float $subtotal, ?string $zone = null): float
    {
        if ($subtotal <= 0 || $subtotal >= $this->freeShippingThreshold()) {
            return 0.0;
        }

        return $this->shippingFeeForZone($zone);
    }
}
