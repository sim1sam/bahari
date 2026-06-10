<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'site_name', 'tagline', 'logo', 'favicon',
        'meta_title', 'meta_description', 'meta_keywords',
        'og_title', 'og_description', 'og_image',
        'footer_description', 'contact_email', 'contact_phone',
        'facebook_url', 'instagram_url', 'tiktok_url', 'youtube_url',
        'top_bar_text', 'top_bar_text_mobile', 'newsletter_title', 'newsletter_text',
        'footer_shop_title', 'footer_support_title',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'site_name' => config('app.name'),
        ]);
    }
}
