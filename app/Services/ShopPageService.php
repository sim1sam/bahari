<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ShopPageBrandSchedule;
use App\Models\ShopPageSetting;
use Illuminate\Support\Facades\Cache;

class ShopPageService
{
    private const CACHE_KEY = 'shop_page_settings';

    public function settings(): ShopPageSetting
    {
        $data = Cache::remember(self::CACHE_KEY, 3600, fn () => ShopPageSetting::current()->toArray());

        $settings = new ShopPageSetting;
        $settings->forceFill($data);
        $settings->exists = true;

        return $settings;
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function isEnabled(): bool
    {
        return (bool) $this->settings()->is_enabled;
    }

    /**
     * Brands currently scheduled to appear on the shop page.
     *
     * @return array<int, string>
     */
    public function activeBrands(): array
    {
        return ShopPageBrandSchedule::currentlyActive()
            ->ordered()
            ->pluck('brand')
            ->map(fn ($brand) => trim((string) $brand))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Products visible on the shop page (newest first), before request filters.
     *
     * Shows products when:
     * - an active brand schedule matches, and/or
     * - admin pinned products are selected.
     * If neither is set, optionally shows all (show_all_when_empty).
     *
     * @return array<int, array<string, mixed>>
     */
    public function baseProducts(): array
    {
        if (! $this->isEnabled() || ! $this->usesStorefrontProducts()) {
            return [];
        }

        $settings = $this->settings();
        $activeBrands = $this->activeBrands();
        $featuredIds = $settings->featuredProductIds();
        $hasSelection = $activeBrands !== [] || $featuredIds !== [];

        if (! $hasSelection && ! $settings->show_all_when_empty) {
            return [];
        }

        $query = Product::with(['category', 'apiReceivedItem'])->onStorefront();

        if ($hasSelection) {
            $query->where(function ($builder) use ($activeBrands, $featuredIds) {
                if ($activeBrands !== []) {
                    $builder->where(function ($brandQuery) use ($activeBrands) {
                        foreach ($activeBrands as $index => $brand) {
                            $method = $index === 0 ? 'whereRaw' : 'orWhereRaw';
                            $brandQuery->{$method}("LOWER(TRIM(COALESCE(brand, ''))) = ?", [mb_strtolower(trim($brand))]);
                        }
                    });
                }

                if ($featuredIds !== []) {
                    $method = $activeBrands !== [] ? 'orWhereIn' : 'whereIn';
                    $builder->{$method}('id', $featuredIds);
                }
            });
        }

        return $query->orderByNewest()
            ->get()
            ->map(fn (Product $product) => $product->toCatalogArray() + ['id' => $product->id])
            ->values()
            ->all();
    }

    /**
     * @param  array{brands?: array<int, string>, sizes?: array<int, string>, colors?: array<int, string>, price?: ?string, sale?: bool, category?: ?string}  $filters
     * @return array<int, array<string, mixed>>
     */
    public function products(?string $sort = null, array $filters = []): array
    {
        $items = $this->applyFilters($this->baseProducts(), $filters);

        return $this->sort($items, $sort);
    }

    /**
     * @return array{brands: array<int, string>, sizes: array<int, string>, colors: array<int, string>, categories: array<int, string>, has_sale: bool}
     */
    public function filterOptions(): array
    {
        $items = collect($this->baseProducts());
        $prices = $items->pluck('price')->filter(fn ($price) => is_numeric($price))->map(fn ($price) => (float) $price);
        $highest = (int) ceil($prices->max() ?: 0);

        return [
            'brands' => $items->pluck('brand')->filter()->unique()->sort()->values()->all(),
            'sizes' => $items->pluck('sizes')->flatten()->unique()->sort()->values()->all(),
            'colors' => $items->pluck('colors')->flatten()->unique()->sort()->values()->all(),
            'categories' => $items->pluck('category')->filter()->unique()->sort()->values()->all(),
            'has_sale' => $items->contains(fn ($p) => ($p['badge'] ?? null) === 'Sale' || ($p['original_price'] ?? null) !== null),
            'price_min' => 0,
            'price_max' => max(100, $highest),
        ];
    }

    /**
     * Live storefront brands with product counts (for admin brand picker).
     *
     * @return array<int, array{brand: string, product_count: int}>
     */
    public function availableBrandsWithCounts(): array
    {
        if (! $this->usesStorefrontProducts()) {
            return [];
        }

        return Product::onStorefront()
            ->whereNotNull('brand')
            ->where('brand', '!=', '')
            ->selectRaw('brand, COUNT(*) as product_count')
            ->groupBy('brand')
            ->orderBy('brand')
            ->get()
            ->map(fn ($row) => [
                'brand' => (string) $row->brand,
                'product_count' => (int) $row->product_count,
            ])
            ->values()
            ->all();
    }

    /**
     * Distinct brand names from storefront products (for admin schedule picker).
     *
     * @return array<int, string>
     */
    public function availableBrands(): array
    {
        return collect($this->availableBrandsWithCounts())->pluck('brand')->all();
    }

    /**
     * @return array<int, array{id: int, name: string, brand: ?string, image: ?string, price: float, price_formatted: string, published_at: ?string}>
     */
    public function productOptions(?string $search = null, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        if (! $this->usesStorefrontProducts()) {
            return [];
        }

        $query = Product::onStorefront()->with(['category', 'apiReceivedItem'])->orderByNewest();

        if (filled($search)) {
            $term = '%'.addcslashes($search, '%_\\').'%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('brand', 'like', $term)
                    ->orWhere('slug', 'like', $term);
            });
        }

        if (filled($dateFrom) || filled($dateTo)) {
            $imported = \App\Models\ApiReceivedItem::STATUS_IMPORTED;
            $publishedExpr = 'COALESCE(
                (SELECT reviewed_at FROM api_received_items WHERE api_received_items.product_id = products.id AND api_received_items.status = ? LIMIT 1),
                products.created_at
            )';

            if (filled($dateFrom) && filled($dateTo)) {
                $query->whereRaw("DATE({$publishedExpr}) BETWEEN ? AND ?", [$imported, $dateFrom, $dateTo]);
            } elseif (filled($dateFrom)) {
                $query->whereRaw("DATE({$publishedExpr}) >= ?", [$imported, $dateFrom]);
            } else {
                $query->whereRaw("DATE({$publishedExpr}) <= ?", [$imported, $dateTo]);
            }
        }

        return $query
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'brand' => $product->brand,
                'image' => $product->imageUrl(),
                'price' => (float) $product->price,
                'price_formatted' => money((float) $product->price),
                'published_at' => $product->publishedAt()?->format('Y-m-d'),
            ])
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @param  array{brands?: array<int, string>, sizes?: array<int, string>, colors?: array<int, string>, price?: ?string, sale?: bool, category?: ?string}  $filters
     * @return array<int, array<string, mixed>>
     */
    private function applyFilters(array $items, array $filters): array
    {
        if (! empty($filters['brands'])) {
            $brands = $filters['brands'];
            $items = array_values(array_filter(
                $items,
                fn ($p) => in_array($p['brand'] ?? null, $brands, true),
            ));
        }

        if (! empty($filters['category'])) {
            $category = $filters['category'];
            $items = array_values(array_filter(
                $items,
                fn ($p) => ($p['category'] ?? null) === $category,
            ));
        }

        if (! empty($filters['sale'])) {
            $items = array_values(array_filter(
                $items,
                fn ($p) => ($p['badge'] ?? null) === 'Sale' || ($p['original_price'] ?? null) !== null,
            ));
        }

        if (! empty($filters['sizes'])) {
            $sizes = $filters['sizes'];
            $items = array_values(array_filter(
                $items,
                fn ($p) => ! empty(array_intersect($p['sizes'] ?? [], $sizes)),
            ));
        }

        if (! empty($filters['colors'])) {
            $colors = $filters['colors'];
            $items = array_values(array_filter(
                $items,
                fn ($p) => ! empty(array_intersect($p['colors'] ?? [], $colors)),
            ));
        }

        if (isset($filters['min_price']) || isset($filters['max_price'])) {
            $minPrice = array_key_exists('min_price', $filters) && $filters['min_price'] !== null
                ? (float) $filters['min_price']
                : 0;
            $maxPrice = array_key_exists('max_price', $filters) && $filters['max_price'] !== null
                ? (float) $filters['max_price']
                : PHP_FLOAT_MAX;
            $limitMax = (float) ($filters['price_limit_max'] ?? $maxPrice);

            if ($minPrice > 0 || $maxPrice < $limitMax) {
                $items = array_values(array_filter(
                    $items,
                    fn ($p) => ($p['price'] ?? 0) >= $minPrice && ($p['price'] ?? 0) <= $maxPrice,
                ));
            }
        } elseif (! empty($filters['price'])) {
            $items = array_values(array_filter($items, function ($p) use ($filters) {
                return match ($filters['price']) {
                    'under_1000' => $p['price'] < 1000,
                    '1000_3000' => $p['price'] >= 1000 && $p['price'] <= 3000,
                    'over_3000' => $p['price'] > 3000,
                    default => true,
                };
            }));
        }

        return $items;
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    private function sort(array $items, ?string $sort): array
    {
        return match ($sort) {
            'price_asc' => collect($items)->sortBy('price')->values()->all(),
            'price_desc' => collect($items)->sortByDesc('price')->values()->all(),
            'name' => collect($items)->sortBy('name')->values()->all(),
            default => collect($items)->sortByDesc('published_at')->values()->all(),
        };
    }

    private function usesStorefrontProducts(): bool
    {
        try {
            return Product::onStorefront()->exists();
        } catch (\Throwable) {
            return false;
        }
    }
}
