<?php

namespace App\Services;

class ProductCatalog
{
    private const IMG = 'https://images.unsplash.com/%s?auto=format&fit=crop&w=600&h=750&q=80';

    private const IMG_LG = 'https://images.unsplash.com/%s?auto=format&fit=crop&w=900&h=1100&q=80';

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
        $slugs = [
            'floral-summer-dress', 'satin-evening-gown', 'linen-wrap-dress', 'off-shoulder-dress',
            'boho-maxi-dress', 'classic-sheath-dress', 'silk-slip-dress', 'cocktail-party-dress',
        ];

        return array_values(array_intersect_key($this->products(), array_flip($slugs)));
    }

    public function newArrivals(): array
    {
        $slugs = ['spring-floral-dress', 'knit-sweater-dress', 'denim-shirt-dress', 'runway-gown'];

        return array_values(array_intersect_key($this->products(), array_flip($slugs)));
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
        $items = [
            $this->product('floral-summer-dress', 'Floral Summer Midi Dress', 68.00, 'photo-1595777457583-95e059d581b8', [
                'original_price' => 89.00, 'badge' => 'Sale', 'badge_variant' => 'sale', 'rating' => 4.8,
                'category' => 'Dresses',
                'description' => 'A breezy floral midi dress perfect for summer outings. Lightweight fabric with a flattering A-line silhouette.',
            ]),
            $this->product('satin-evening-gown', 'Elegant Satin Evening Gown', 129.00, 'photo-1539533018447-63fcce2678e3', [
                'badge' => 'New', 'badge_variant' => 'new', 'rating' => 4.9, 'category' => 'Party Wear',
                'description' => 'Luxurious satin gown with a flowing skirt and delicate straps. Ideal for formal events and evening parties.',
            ]),
            $this->product('linen-wrap-dress', 'Linen Wrap Dress', 74.99, 'photo-1581044777550-4cfa60707c03', [
                'rating' => 4.6, 'category' => 'Dresses',
                'description' => 'Effortlessly chic linen wrap dress with adjustable tie waist. Breathable and perfect for warm days.',
            ]),
            $this->product('off-shoulder-dress', 'Chic Off-Shoulder Dress', 59.99, 'photo-1496747611176-843222e1e57c', [
                'original_price' => 79.99, 'badge' => 'Hot', 'badge_variant' => 'hot', 'rating' => 4.7, 'category' => 'Dresses',
                'description' => 'Romantic off-shoulder dress with ruffle detailing. A go-to piece for date nights and brunch.',
            ]),
            $this->product('boho-maxi-dress', 'Boho Maxi Dress', 82.00, 'photo-1558618666-fcd25c85cd64', [
                'rating' => 4.5, 'category' => 'Casual',
                'description' => 'Free-spirited boho maxi with tiered skirt and elastic waist. Comfortable all-day wear.',
            ]),
            $this->product('classic-sheath-dress', 'Classic Sheath Dress', 95.00, 'photo-1594633312681-425c7b97ccd1', [
                'badge' => 'Sale', 'badge_variant' => 'sale', 'rating' => 4.8, 'category' => 'Dresses',
                'description' => 'Tailored sheath dress with a timeless silhouette. Perfect for office and professional settings.',
            ]),
            $this->product('silk-slip-dress', 'Silk Slip Dress', 110.00, 'photo-1469334031218-e382a71b716b', [
                'rating' => 4.7, 'category' => 'Party Wear',
                'description' => 'Minimalist silk slip dress with a lustrous finish. Layer it or wear solo for an elegant look.',
            ]),
            $this->product('cocktail-party-dress', 'Cocktail Party Dress', 99.99, 'photo-1487222477894-8943e31ef7b2', [
                'original_price' => 135.00, 'rating' => 4.6, 'category' => 'Party Wear',
                'description' => 'Statement cocktail dress with structured bodice and flared skirt. Turn heads at every event.',
            ]),
            $this->product('spring-floral-dress', 'Spring Floral Mini Dress', 54.99, 'photo-1595777457583-95e059d581b8', [
                'badge' => 'New', 'badge_variant' => 'new', 'rating' => 4.7, 'category' => 'New In',
                'description' => 'Fresh spring mini dress with vibrant floral print. Playful and youthful for everyday style.',
            ]),
            $this->product('knit-sweater-dress', 'Knit Sweater Dress', 72.00, 'photo-1434389677669-e08b4cac3105', [
                'badge' => 'New', 'badge_variant' => 'new', 'rating' => 4.5, 'category' => 'Casual',
                'description' => 'Cozy knit sweater dress with ribbed texture. Ideal for cooler days and layered looks.',
            ]),
            $this->product('denim-shirt-dress', 'Denim Shirt Dress', 64.99, 'photo-1542272604-787c3835535d', [
                'rating' => 4.8, 'category' => 'Casual',
                'description' => 'Classic denim shirt dress with button-front and belted waist. Versatile casual staple.',
            ]),
            $this->product('runway-gown', 'Runway Inspired Gown', 149.00, 'photo-1509631179647-0177331693ae', [
                'badge' => 'New', 'badge_variant' => 'new', 'rating' => 4.9, 'category' => 'New In',
                'description' => 'High-fashion runway-inspired gown with dramatic silhouette. For those who love to make an entrance.',
            ]),
        ];

        return collect($items)->keyBy('slug')->all();
    }

    private function product(string $slug, string $name, float $price, string $photoId, array $extra = []): array
    {
        return array_merge([
            'slug' => $slug,
            'name' => $name,
            'price' => $price,
            'original_price' => null,
            'image' => $this->img($photoId),
            'images' => [$this->imgLg($photoId), $this->img($photoId)],
            'badge' => null,
            'badge_variant' => 'default',
            'rating' => 4.5,
            'category' => 'Dresses',
            'description' => 'Premium quality fashion piece crafted for comfort and style.',
            'sizes' => ['XS', 'S', 'M', 'L', 'XL'],
            'colors' => ['Black', 'White', 'Rose'],
        ], $extra);
    }

    private function img(string $id): string
    {
        return sprintf(self::IMG, $id);
    }

    private function imgLg(string $id): string
    {
        return sprintf(self::IMG_LG, $id);
    }
}
