<?php

namespace App\Services;

use App\Models\Product;

class ProductCatalog
{
    public function all(): array
    {
        return $this->products();
    }

    public function find(string $slug): ?array
    {
        return $this->products()[$slug] ?? null;
    }

    public function featured(): array
    {
        if ($this->usesDatabase()) {
            return Product::with('category')->where('is_active', true)->where('is_featured', true)->get()
                ->map(fn ($p) => $p->toCatalogArray())->values()->all();
        }

        $slugs = [
            'floral-summer-dress', 'satin-evening-gown', 'linen-wrap-dress', 'off-shoulder-dress',
            'boho-maxi-dress', 'classic-sheath-dress', 'silk-slip-dress', 'cocktail-party-dress',
        ];

        return array_values(array_intersect_key($this->staticProducts(), array_flip($slugs)));
    }

    public function newArrivals(): array
    {
        if ($this->usesDatabase()) {
            return Product::with('category')->where('is_active', true)->where('is_new_arrival', true)->get()
                ->map(fn ($p) => $p->toCatalogArray())->values()->all();
        }

        $slugs = ['spring-floral-dress', 'knit-sweater-dress', 'denim-shirt-dress', 'runway-gown'];

        return array_values(array_intersect_key($this->staticProducts(), array_flip($slugs)));
    }

    public function related(string $slug, int $limit = 4): array
    {
        $products = array_values(array_filter(
            $this->products(),
            fn ($p) => $p['slug'] !== $slug
        ));

        return array_slice($products, 0, $limit);
    }

    public function toCard(array $product): array
    {
        return [
            'slug' => $product['slug'],
            'name' => $product['name'],
            'price' => $product['price'],
            'original_price' => $product['original_price'] ?? null,
            'image' => $product['image'],
            'badge' => $product['badge'] ?? null,
            'badge_variant' => $product['badge_variant'] ?? 'default',
            'rating' => $product['rating'] ?? null,
            'href' => route('products.show', $product['slug']),
        ];
    }

    private function products(): array
    {
        if ($this->usesDatabase()) {
            return Product::with('category')->where('is_active', true)->get()
                ->mapWithKeys(fn ($p) => [$p->slug => $p->toCatalogArray()])
                ->all();
        }

        return $this->staticProducts();
    }

    private function usesDatabase(): bool
    {
        try {
            return Product::count() > 0;
        } catch (\Throwable) {
            return false;
        }
    }

    private function staticProducts(): array
    {
        $IMG = 'https://images.unsplash.com/%s?auto=format&fit=crop&w=600&h=750&q=80';
        $IMG_LG = 'https://images.unsplash.com/%s?auto=format&fit=crop&w=900&h=1100&q=80';
        $img = fn (string $id) => sprintf($IMG, $id);
        $imgLg = fn (string $id) => sprintf($IMG_LG, $id);

        $product = fn (string $slug, string $name, float $price, string $photoId, array $extra = []) => array_merge([
            'slug' => $slug, 'name' => $name, 'price' => $price, 'original_price' => null,
            'image' => $img($photoId), 'images' => [$imgLg($photoId), $img($photoId)],
            'badge' => null, 'badge_variant' => 'default', 'rating' => 4.5, 'category' => 'Dresses',
            'description' => 'Premium quality fashion piece.', 'sizes' => ['XS','S','M','L','XL'], 'colors' => ['Black','White','Rose'],
        ], $extra);

        return collect([
            $product('floral-summer-dress', 'Floral Summer Midi Dress', 68.00, 'photo-1595777457583-95e059d581b8', ['original_price' => 89.00, 'badge' => 'Sale', 'badge_variant' => 'sale', 'rating' => 4.8, 'category' => 'Dresses']),
            $product('satin-evening-gown', 'Elegant Satin Evening Gown', 129.00, 'photo-1539533018447-63fcce2678e3', ['badge' => 'New', 'badge_variant' => 'new', 'rating' => 4.9, 'category' => 'Party Wear']),
            $product('linen-wrap-dress', 'Linen Wrap Dress', 74.99, 'photo-1581044777550-4cfa60707c03', ['rating' => 4.6, 'category' => 'Dresses']),
            $product('off-shoulder-dress', 'Chic Off-Shoulder Dress', 59.99, 'photo-1496747611176-843222e1e57c', ['original_price' => 79.99, 'badge' => 'Hot', 'badge_variant' => 'hot', 'rating' => 4.7, 'category' => 'Dresses']),
            $product('boho-maxi-dress', 'Boho Maxi Dress', 82.00, 'photo-1558618666-fcd25c85cd64', ['rating' => 4.5, 'category' => 'Casual']),
            $product('classic-sheath-dress', 'Classic Sheath Dress', 95.00, 'photo-1594633312681-425c7b97ccd1', ['badge' => 'Sale', 'badge_variant' => 'sale', 'rating' => 4.8, 'category' => 'Dresses']),
            $product('silk-slip-dress', 'Silk Slip Dress', 110.00, 'photo-1469334031218-e382a71b716b', ['rating' => 4.7, 'category' => 'Party Wear']),
            $product('cocktail-party-dress', 'Cocktail Party Dress', 99.99, 'photo-1487222477894-8943e31ef7b2', ['original_price' => 135.00, 'rating' => 4.6, 'category' => 'Party Wear']),
            $product('spring-floral-dress', 'Spring Floral Mini Dress', 54.99, 'photo-1595777457583-95e059d581b8', ['badge' => 'New', 'badge_variant' => 'new', 'rating' => 4.7, 'category' => 'New In']),
            $product('knit-sweater-dress', 'Knit Sweater Dress', 72.00, 'photo-1434389677669-e08b4cac3105', ['badge' => 'New', 'badge_variant' => 'new', 'rating' => 4.5, 'category' => 'Casual']),
            $product('denim-shirt-dress', 'Denim Shirt Dress', 64.99, 'photo-1542272604-787c3835535d', ['rating' => 4.8, 'category' => 'Casual']),
            $product('runway-gown', 'Runway Inspired Gown', 149.00, 'photo-1509631179647-0177331693ae', ['badge' => 'New', 'badge_variant' => 'new', 'rating' => 4.9, 'category' => 'New In']),
        ])->keyBy('slug')->all();
    }
}
