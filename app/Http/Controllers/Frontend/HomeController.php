<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\CategoryCatalog;
use App\Services\ProductCatalog;

class HomeController extends Controller
{
    private const HERO_IMG = 'https://images.unsplash.com/%s?auto=format&fit=crop&w=1920&h=1080&q=80';

    public function __construct(
        private ProductCatalog $catalog,
        private CategoryCatalog $categories,
    ) {}

    public function index()
    {
        return view('pages.home.index', [
            'categories' => collect($this->categories->all())->map(fn ($c) => $this->categories->toCard($c))->all(),
            'featuredProducts' => collect($this->catalog->featured())->map(fn ($p) => $this->catalog->toCard($p))->all(),
            'newArrivals' => collect($this->catalog->newArrivals())->map(fn ($p) => $this->catalog->toCard($p))->all(),
            'features' => $this->features(),
            'heroSlides' => $this->heroSlides(),
        ]);
    }

    private function img(string $id): string
    {
        return sprintf('https://images.unsplash.com/%s?auto=format&fit=crop&w=400&h=300&q=80', $id);
    }

    private function heroImg(string $id): string
    {
        return sprintf(self::HERO_IMG, $id);
    }

    private function heroSlides(): array
    {
        return [
            [
                'image' => $this->heroImg('photo-1490481651871-ab68de25d43d'),
                'badge' => 'Spring Collection 2026',
                'title' => 'Elegance in Every Stitch',
                'subtitle' => 'Discover stunning dresses and timeless fashion pieces designed for the modern woman.',
                'primary_btn' => 'Shop Dresses',
                'primary_href' => route('products.show', 'floral-summer-dress'),
                'secondary_btn' => 'New Arrivals',
                'secondary_href' => route('products.show', 'spring-floral-dress'),
                'features' => ['Free Shipping $50+', 'Easy Returns'],
            ],
            [
                'image' => $this->heroImg('photo-1469334031218-e382a71b716b'),
                'badge' => 'Trending Now',
                'title' => 'Girls Fashion & Party Dresses',
                'subtitle' => 'From casual day dresses to glamorous evening gowns — find your perfect look.',
                'primary_btn' => 'Shop Party Wear',
                'primary_href' => route('products.show', 'satin-evening-gown'),
                'secondary_btn' => 'View Lookbook',
                'secondary_href' => route('home'),
                'features' => ['New Styles Weekly', 'Premium Fabrics'],
            ],
            [
                'image' => $this->heroImg('photo-1509631179647-0177331693ae'),
                'badge' => 'Up to 40% Off',
                'title' => 'Summer Dress Sale',
                'subtitle' => 'Refresh your wardrobe with our best-selling dresses at unbeatable prices.',
                'primary_btn' => 'Shop the Sale',
                'primary_href' => route('products.show', 'floral-summer-dress'),
                'secondary_btn' => 'All Dresses',
                'secondary_href' => route('products.show', 'cocktail-party-dress'),
                'features' => ['Limited Time', 'Free Delivery'],
            ],
        ];
    }

    private function features(): array
    {
        $icon = fn (string $path) => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="'.$path.'"/></svg>';

        return [
            ['title' => 'Free Shipping', 'description' => 'Complimentary delivery on all dress orders over $50.', 'icon' => $icon('M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4')],
            ['title' => 'Easy Returns', 'description' => '30-day hassle-free returns on all clothing items.', 'icon' => $icon('M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15')],
            ['title' => 'Size Guide', 'description' => 'Detailed sizing charts to help you find the perfect fit.', 'icon' => $icon('M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z')],
            ['title' => 'Style Support', 'description' => 'Our fashion experts are here to help you style the perfect outfit.', 'icon' => $icon('M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z')],
        ];
    }
}
