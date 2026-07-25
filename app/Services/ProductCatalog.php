<?php

namespace App\Services;

use App\Models\Product;

class ProductCatalog
{
    /** @var array<string, array<string, mixed>>|null */
    private ?array $productsCache = null;

    private ?bool $hasStorefrontProducts = null;

    public function all(): array
    {
        return array_values($this->products());
    }

    public function find(string $slug): ?array
    {
        if ($this->productsCache !== null) {
            return $this->productsCache[$slug] ?? null;
        }

        if (! $this->usesStorefrontProducts()) {
            return null;
        }

        $product = $this->storefrontQuery()
            ->where('slug', $slug)
            ->first();

        return $product?->toCatalogArray();
    }

    public function featured(int $limit = 8): array
    {
        if (! $this->usesStorefrontProducts()) {
            return [];
        }

        return $this->storefrontQuery()
            ->where('is_featured', true)
            ->limit($limit)
            ->get()
            ->map(fn ($p) => $p->toCatalogArray(false))
            ->values()
            ->all();
    }

    public function newArrivals(int $limit = 20): array
    {
        if (! $this->usesStorefrontProducts()) {
            return [];
        }

        return $this->storefrontQuery()
            ->where('is_new_arrival', true)
            ->limit($limit)
            ->get()
            ->map(fn ($p) => $p->toCatalogArray(false))
            ->values()
            ->all();
    }

    public function related(string $slug, int $limit = 4): array
    {
        if (! $this->usesStorefrontProducts()) {
            return [];
        }

        $current = Product::query()->where('slug', $slug)->first(['id', 'category_id']);

        if (! $current) {
            return [];
        }

        $query = $this->storefrontQuery()->where('products.id', '!=', $current->id);

        if ($current->category_id) {
            $query->orderByRaw('CASE WHEN category_id = ? THEN 0 ELSE 1 END', [$current->category_id]);
        }

        return $query
            ->limit($limit)
            ->get()
            ->map(fn ($p) => $p->toCatalogArray(false))
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function search(string $query, ?int $limit = null): array
    {
        $query = trim($query);

        if ($query === '' || ! $this->usesStorefrontProducts()) {
            return [];
        }

        $term = '%'.addcslashes($query, '%_\\').'%';

        $builder = $this->storefrontQuery()
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('description', 'like', $term)
                    ->orWhere('short_description', 'like', $term)
                    ->orWhere('brand', 'like', $term)
                    ->orWhereHas('category', function ($category) use ($term) {
                        $category->where('name', 'like', $term);
                    });
            })
            ->orderByNewest();

        if ($limit !== null) {
            $builder->limit($limit);
        }

        return $builder
            ->get()
            ->map(fn ($product) => $this->toCard($product->toCatalogArray(false)))
            ->values()
            ->all();
    }

    public function toCard(array $product): array
    {
        return [
            'slug' => $product['slug'],
            'name' => $product['name'],
            'price' => $product['price'],
            'price_formatted' => money($product['price']),
            'original_price' => $product['original_price'] ?? null,
            'image' => $product['image'],
            'badge' => $product['badge'] ?? null,
            'badge_variant' => $product['badge_variant'] ?? 'default',
            'rating' => $product['rating'] ?? null,
            'brand' => $product['brand'] ?? null,
            'category' => $product['category'] ?? null,
            'href' => route('products.show', $product['slug']),
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function products(): array
    {
        if ($this->productsCache !== null) {
            return $this->productsCache;
        }

        if (! $this->usesStorefrontProducts()) {
            return $this->productsCache = [];
        }

        return $this->productsCache = $this->storefrontQuery()
            ->get()
            ->mapWithKeys(fn ($p) => [$p->slug => $p->toCatalogArray(false)])
            ->all();
    }

    private function storefrontQuery()
    {
        return Product::with(['category:id,name', 'apiReceivedItem:id,product_id,status,reviewed_at'])
            ->onStorefront()
            ->orderByNewest();
    }

    private function usesStorefrontProducts(): bool
    {
        if ($this->hasStorefrontProducts !== null) {
            return $this->hasStorefrontProducts;
        }

        try {
            return $this->hasStorefrontProducts = Product::onStorefront()->exists();
        } catch (\Throwable) {
            return $this->hasStorefrontProducts = false;
        }
    }
}
