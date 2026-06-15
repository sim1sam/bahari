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
        if (! $this->usesStorefrontProducts()) {
            return [];
        }

        return $this->storefrontQuery()
            ->where('is_featured', true)
            ->get()
            ->map(fn ($p) => $p->toCatalogArray())
            ->values()
            ->all();
    }

    public function newArrivals(): array
    {
        if (! $this->usesStorefrontProducts()) {
            return [];
        }

        return $this->storefrontQuery()
            ->where('is_new_arrival', true)
            ->get()
            ->map(fn ($p) => $p->toCatalogArray())
            ->values()
            ->all();
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
        if (! $this->usesStorefrontProducts()) {
            return [];
        }

        return $this->storefrontQuery()
            ->get()
            ->mapWithKeys(fn ($p) => [$p->slug => $p->toCatalogArray()])
            ->all();
    }

    private function storefrontQuery()
    {
        return Product::with('category')
            ->where('is_active', true)
            ->liveFromApi();
    }

    private function usesStorefrontProducts(): bool
    {
        try {
            return Product::liveFromApi()->where('is_active', true)->exists();
        } catch (\Throwable) {
            return false;
        }
    }
}
