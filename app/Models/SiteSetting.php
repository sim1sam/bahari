<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'site_name', 'tagline', 'logo', 'favicon',
        'meta_title', 'meta_description', 'meta_keywords',
        'og_title', 'og_description', 'og_image',
        'gtm_container_id', 'gtm_enabled',
        'sslcommerz_enabled', 'sslcommerz_sandbox', 'sslcommerz_store_id', 'sslcommerz_store_password',
        'footer_description', 'contact_email', 'contact_phone',
        'facebook_url', 'instagram_url', 'tiktok_url', 'youtube_url',
        'top_bar_text', 'top_bar_text_mobile',
        'top_bar_bg_color', 'top_bar_text_color', 'top_bar_link_color',
        'newsletter_title', 'newsletter_text', 'newsletter_placeholder', 'newsletter_button_text',
        'newsletter_enabled', 'newsletter_success_message',
        'footer_shop_title', 'footer_support_title', 'footer_copyright',
        'theme_primary', 'theme_primary_dark', 'theme_footer_bg', 'theme_text', 'theme_background',
        'api_webhook_url', 'api_auto_publish', 'api_logo',
    ];

    protected function casts(): array
    {
        return [
            'newsletter_enabled' => 'boolean',
            'api_auto_publish' => 'boolean',
            'gtm_enabled' => 'boolean',
            'sslcommerz_enabled' => 'boolean',
            'sslcommerz_sandbox' => 'boolean',
            'sslcommerz_store_password' => 'encrypted',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'site_name' => config('app.name'),
        ]);
    }
}
