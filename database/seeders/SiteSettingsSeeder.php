<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingsSeeder extends Seeder
{
    public function run(): void
    {
        SiteSetting::updateOrCreate(['id' => 1], [
            'site_name' => config('app.name', 'LuxeWear'),
            'tagline' => 'Premium women\'s fashion — dresses, tops, and party wear for every occasion.',
            'meta_title' => config('app.name', 'LuxeWear').' — Women\'s Fashion Online',
            'meta_description' => 'Shop the latest women\'s fashion. Dresses, tops, party wear and more with fast delivery.',
            'meta_keywords' => 'fashion, dresses, women clothing, party wear, online shop',
            'og_title' => config('app.name', 'LuxeWear').' — Women\'s Fashion',
            'og_description' => 'Discover premium women\'s fashion. Shop dresses, tops, and party wear.',
            'footer_description' => 'Premium women\'s fashion — dresses, tops, and party wear for every occasion.',
            'contact_email' => 'hello@ecommerce.com',
            'contact_phone' => '+1 (555) 123-4567',
            'facebook_url' => 'https://facebook.com',
            'instagram_url' => 'https://instagram.com',
            'tiktok_url' => 'https://tiktok.com',
            'youtube_url' => 'https://youtube.com',
        ]);
    }
}
