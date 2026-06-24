<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        if (Category::query()->exists()) {
            return;
        }

        $img = fn (string $id) => "https://images.unsplash.com/{$id}?auto=format&fit=crop&w=600&h=750&q=80";
        $cardImg = fn (string $id) => "https://images.unsplash.com/{$id}?auto=format&fit=crop&w=400&h=300&q=80";
        $heroImg = fn (string $id) => "https://images.unsplash.com/{$id}?auto=format&fit=crop&w=1200&h=400&q=80";

        $categories = [
            ['slug' => 'dresses', 'name' => 'Dresses', 'color' => 'rose', 'photo' => 'photo-1595777457583-95e059d581b8', 'description' => 'From casual day dresses to elegant evening styles — find your perfect fit.', 'sort' => 1],
            ['slug' => 'tops', 'name' => 'Tops & Blouses', 'color' => 'brand', 'photo' => 'photo-1434389677669-e08b4cac3105', 'description' => 'Stylish tops and blouses for every occasion.', 'sort' => 2],
            ['slug' => 'skirts', 'name' => 'Skirts', 'color' => 'purple', 'photo' => 'photo-1539533018447-63fcce2678e3', 'description' => 'Flowing midi skirts and trendy mini styles.', 'sort' => 3],
            ['slug' => 'party-wear', 'name' => 'Party Wear', 'color' => 'amber', 'photo' => 'photo-1469334031218-e382a71b716b', 'description' => 'Glamorous gowns and cocktail dresses.', 'sort' => 4],
            ['slug' => 'casual', 'name' => 'Casual', 'color' => 'blue', 'photo' => 'photo-1496747611176-843222e1e57c', 'description' => 'Effortless everyday styles.', 'sort' => 5],
            ['slug' => 'new-in', 'name' => 'New In', 'color' => 'cyan', 'photo' => 'photo-1509631179647-0177331693ae', 'description' => 'Fresh arrivals and latest trends.', 'sort' => 6],
            ['slug' => 'sale', 'name' => 'Sale', 'color' => 'rose', 'photo' => 'photo-1595777457583-95e059d581b8', 'description' => 'Shop our best deals.', 'sort' => 7, 'is_sale' => true],
        ];

        $catIds = [];
        foreach ($categories as $c) {
            $cat = Category::create([
                'slug' => $c['slug'],
                'name' => $c['name'],
                'description' => $c['description'],
                'color' => $c['color'],
                'image' => $heroImg($c['photo']),
                'card_image' => $cardImg($c['photo']),
                'is_sale' => $c['is_sale'] ?? false,
                'sort_order' => $c['sort'],
            ]);
            $catIds[$c['slug']] = $cat->id;
        }

        $nameToSlug = [
            'Dresses' => 'dresses', 'Party Wear' => 'party-wear', 'Casual' => 'casual', 'New In' => 'new-in',
        ];

        $products = [
            ['floral-summer-dress', 'Floral Summer Midi Dress', 68.00, 'photo-1595777457583-95e059d581b8', ['original_price' => 89.00, 'badge' => 'Sale', 'badge_variant' => 'sale', 'rating' => 4.8, 'category' => 'Dresses', 'featured' => true, 'description' => 'A breezy floral midi dress perfect for summer outings.']],
            ['satin-evening-gown', 'Elegant Satin Evening Gown', 129.00, 'photo-1539533018447-63fcce2678e3', ['badge' => 'New', 'badge_variant' => 'new', 'rating' => 4.9, 'category' => 'Party Wear', 'featured' => true, 'description' => 'Luxurious satin gown with a flowing skirt.']],
            ['linen-wrap-dress', 'Linen Wrap Dress', 74.99, 'photo-1581044777550-4cfa60707c03', ['rating' => 4.6, 'category' => 'Dresses', 'featured' => true, 'description' => 'Effortlessly chic linen wrap dress.']],
            ['off-shoulder-dress', 'Chic Off-Shoulder Dress', 59.99, 'photo-1496747611176-843222e1e57c', ['original_price' => 79.99, 'badge' => 'Hot', 'badge_variant' => 'hot', 'rating' => 4.7, 'category' => 'Dresses', 'featured' => true, 'description' => 'Romantic off-shoulder dress with ruffle detailing.']],
            ['boho-maxi-dress', 'Boho Maxi Dress', 82.00, 'photo-1558618666-fcd25c85cd64', ['rating' => 4.5, 'category' => 'Casual', 'featured' => true, 'description' => 'Free-spirited boho maxi with tiered skirt.']],
            ['classic-sheath-dress', 'Classic Sheath Dress', 95.00, 'photo-1594633312681-425c7b97ccd1', ['badge' => 'Sale', 'badge_variant' => 'sale', 'rating' => 4.8, 'category' => 'Dresses', 'featured' => true, 'description' => 'Tailored sheath dress with timeless silhouette.']],
            ['silk-slip-dress', 'Silk Slip Dress', 110.00, 'photo-1469334031218-e382a71b716b', ['rating' => 4.7, 'category' => 'Party Wear', 'featured' => true, 'description' => 'Minimalist silk slip dress.']],
            ['cocktail-party-dress', 'Cocktail Party Dress', 99.99, 'photo-1487222477894-8943e31ef7b2', ['original_price' => 135.00, 'rating' => 4.6, 'category' => 'Party Wear', 'featured' => true, 'description' => 'Statement cocktail dress.']],
            ['spring-floral-dress', 'Spring Floral Mini Dress', 54.99, 'photo-1595777457583-95e059d581b8', ['badge' => 'New', 'badge_variant' => 'new', 'rating' => 4.7, 'category' => 'New In', 'new_arrival' => true, 'description' => 'Fresh spring mini dress.']],
            ['knit-sweater-dress', 'Knit Sweater Dress', 72.00, 'photo-1434389677669-e08b4cac3105', ['badge' => 'New', 'badge_variant' => 'new', 'rating' => 4.5, 'category' => 'Casual', 'new_arrival' => true, 'description' => 'Cozy knit sweater dress.']],
            ['denim-shirt-dress', 'Denim Shirt Dress', 64.99, 'photo-1542272604-787c3835535d', ['rating' => 4.8, 'category' => 'Casual', 'new_arrival' => true, 'description' => 'Classic denim shirt dress.']],
            ['runway-gown', 'Runway Inspired Gown', 149.00, 'photo-1509631179647-0177331693ae', ['badge' => 'New', 'badge_variant' => 'new', 'rating' => 4.9, 'category' => 'New In', 'new_arrival' => true, 'description' => 'High-fashion runway-inspired gown.']],
        ];

        foreach ($products as [$slug, $name, $price, $photo, $extra]) {
            $image = $img($photo);
            Product::create([
                'category_id' => $catIds[$nameToSlug[$extra['category']] ?? 'dresses'] ?? null,
                'slug' => $slug,
                'name' => $name,
                'price' => $price,
                'original_price' => $extra['original_price'] ?? null,
                'image' => $image,
                'images' => [$image],
                'badge' => $extra['badge'] ?? null,
                'badge_variant' => $extra['badge_variant'] ?? 'default',
                'rating' => $extra['rating'] ?? 4.5,
                'description' => $extra['description'] ?? '',
                'sizes' => ['XS', 'S', 'M', 'L', 'XL'],
                'colors' => ['Black', 'White', 'Rose'],
                'is_featured' => $extra['featured'] ?? false,
                'is_new_arrival' => $extra['new_arrival'] ?? false,
            ]);
        }
    }
}
