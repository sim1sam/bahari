<?php

namespace Database\Seeders;

use App\Models\FooterLink;
use App\Models\HomeBanner;
use App\Models\HomeFeature;
use App\Models\HomeSlider;
use App\Models\Page;
use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class HomepageContentSeeder extends Seeder
{
    public function run(): void
    {
        if (HomeSlider::count() === 0) {
            HomeSlider::insert([
                [
                    'image' => 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?auto=format&fit=crop&w=1920&h=1080&q=80',
                    'badge' => 'Spring Collection 2026',
                    'title' => 'Elegance in Every Stitch',
                    'subtitle' => 'Discover stunning dresses and timeless fashion pieces designed for the modern woman.',
                    'primary_btn' => 'Shop Now',
                    'primary_href' => '/',
                    'secondary_btn' => 'View Deals',
                    'secondary_href' => '/deals',
                    'features' => json_encode(['Free Shipping $50+', 'Easy Returns']),
                    'sort_order' => 0,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'image' => 'https://images.unsplash.com/photo-1469334031218-e382a71b716b?auto=format&fit=crop&w=1920&h=1080&q=80',
                    'badge' => 'Trending Now',
                    'title' => 'Girls Fashion & Party Dresses',
                    'subtitle' => 'From casual day dresses to glamorous evening gowns — find your perfect look.',
                    'primary_btn' => 'Shop Party Wear',
                    'primary_href' => '/categories/party-wear',
                    'secondary_btn' => 'New Arrivals',
                    'secondary_href' => '/',
                    'features' => json_encode(['New Styles Weekly', 'Premium Fabrics']),
                    'sort_order' => 1,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        if (HomeBanner::count() === 0) {
            HomeBanner::create([
                'image' => 'https://images.unsplash.com/photo-1469334031218-e382a71b716b?auto=format&fit=crop&w=1200&h=500&q=80',
                'badge' => 'Limited Time',
                'title' => 'Up to 40% Off Dresses',
                'subtitle' => 'Shop our summer dress collection — floral, maxi, party & casual styles at amazing prices.',
                'button_text' => 'Shop Dresses',
                'button_href' => '/categories/dresses',
                'sort_order' => 0,
                'is_active' => true,
            ]);
        }

        if (HomeFeature::count() === 0) {
            HomeFeature::insert([
                ['title' => 'Free Shipping', 'description' => 'Complimentary delivery on all dress orders over $50.', 'icon' => 'truck', 'sort_order' => 0, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['title' => 'Easy Returns', 'description' => '30-day hassle-free returns on all clothing items.', 'icon' => 'return', 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['title' => 'Size Guide', 'description' => 'Detailed sizing charts to help you find the perfect fit.', 'icon' => 'ruler', 'sort_order' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['title' => 'Style Support', 'description' => 'Our fashion experts are here to help you style the perfect outfit.', 'icon' => 'support', 'sort_order' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        if (Page::count() === 0) {
            $supportPages = [
                ['title' => 'Contact Us', 'slug' => 'contact-us', 'sort_order' => 0, 'content' => '<p>Contact our support team using the details in the website footer.</p>'],
                ['title' => 'FAQs', 'slug' => 'faqs', 'sort_order' => 1, 'content' => '<p>Find answers to common questions about orders, shipping, and returns.</p>'],
                ['title' => 'Shipping Info', 'slug' => 'shipping-info', 'sort_order' => 2, 'content' => '<p>Shipping fees and delivery times are shown at checkout based on your address.</p>'],
                ['title' => 'Returns', 'slug' => 'returns', 'sort_order' => 3, 'content' => '<p>Review our return policy and contact us with your order number to start a return.</p>'],
            ];

            foreach ($supportPages as $page) {
                Page::create([
                    'title' => $page['title'],
                    'slug' => $page['slug'],
                    'content' => $page['content'],
                    'sort_order' => $page['sort_order'],
                    'is_active' => true,
                ]);
            }
        }

        if (FooterLink::count() === 0) {
            $pageIds = Page::query()->pluck('id', 'slug');

            FooterLink::insert([
                ['group' => 'shop', 'label' => 'Dresses', 'url' => '/categories/dresses', 'page_id' => null, 'sort_order' => 0, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['group' => 'shop', 'label' => 'Tops & Blouses', 'url' => '/categories/tops', 'page_id' => null, 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['group' => 'shop', 'label' => 'Party Wear', 'url' => '/categories/party-wear', 'page_id' => null, 'sort_order' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['group' => 'shop', 'label' => 'Sale', 'url' => '/deals', 'page_id' => null, 'sort_order' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['group' => 'support', 'label' => 'Contact Us', 'url' => '/pages/contact-us', 'page_id' => $pageIds['contact-us'] ?? null, 'sort_order' => 0, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['group' => 'support', 'label' => 'FAQs', 'url' => '/pages/faqs', 'page_id' => $pageIds['faqs'] ?? null, 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['group' => 'support', 'label' => 'Shipping Info', 'url' => '/pages/shipping-info', 'page_id' => $pageIds['shipping-info'] ?? null, 'sort_order' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['group' => 'support', 'label' => 'Returns', 'url' => '/pages/returns', 'page_id' => $pageIds['returns'] ?? null, 'sort_order' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['group' => 'legal', 'label' => 'Privacy', 'url' => '#', 'page_id' => null, 'sort_order' => 0, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['group' => 'legal', 'label' => 'Terms', 'url' => '#', 'page_id' => null, 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['group' => 'legal', 'label' => 'Cookies', 'url' => '#', 'page_id' => null, 'sort_order' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);
        } else {
            // Link existing support footer rows to pages when still pointing to "#"
            foreach (['Contact Us' => 'contact-us', 'FAQs' => 'faqs', 'Shipping Info' => 'shipping-info', 'Returns' => 'returns'] as $label => $slug) {
                $page = Page::query()->where('slug', $slug)->first();
                if (! $page) {
                    continue;
                }

                FooterLink::query()
                    ->where('label', $label)
                    ->where(function ($q) {
                        $q->whereNull('page_id')->orWhere('url', '#');
                    })
                    ->update([
                        'page_id' => $page->id,
                        'url' => '/pages/'.$page->slug,
                    ]);
            }
        }

        $settings = SiteSetting::current();
        $updates = [];

        foreach ($this->defaultSiteSettingValues() as $key => $value) {
            if (blank($settings->{$key})) {
                $updates[$key] = $value;
            }
        }

        if ($updates !== []) {
            $settings->update($updates);
        }
    }

    /** @return array<string, mixed> */
    private function defaultSiteSettingValues(): array
    {
        return [
            'top_bar_text' => 'Free shipping on dress orders over $50 ✦ New styles every week',
            'top_bar_text_mobile' => 'Free shipping on orders $50+',
            'top_bar_bg_color' => '#164e63',
            'top_bar_text_color' => '#ffffff',
            'top_bar_link_color' => '#cffafe',
            'newsletter_title' => 'Stay Updated',
            'newsletter_text' => 'Get exclusive deals and new arrivals in your inbox.',
            'newsletter_placeholder' => 'Your email',
            'newsletter_button_text' => 'Join',
            'newsletter_enabled' => true,
            'newsletter_success_message' => 'Thanks for subscribing! Check your inbox for updates.',
            'footer_shop_title' => 'Shop',
            'footer_support_title' => 'Support',
            'footer_copyright' => '© {year} {site}. All rights reserved.',
            'theme_primary' => '#0891b2',
            'theme_primary_dark' => '#164e63',
            'theme_footer_bg' => '#1c1917',
            'theme_text' => '#1c1917',
            'theme_background' => '#f8fafc',
        ];
    }
}
