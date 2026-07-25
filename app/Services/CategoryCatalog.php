<?php

namespace App\Services;

use App\Models\Category;

class CategoryCatalog
{
    private const IMG = 'https://images.unsplash.com/%s?auto=format&fit=crop&w=1200&h=400&q=80';

    private const CARD_IMG = 'https://images.unsplash.com/%s?auto=format&fit=crop&w=400&h=300&q=80';

    /** @var array<string, array<string, mixed>>|null */
    private ?array $categoriesCache = null;

    /** @var array<int, array{label: string, href: string, active: bool}>|null */
    private ?array $navigationCache = null;

    public function __construct(private ProductCatalog $products) {}

    public function all(): array
    {
        return array_values($this->categories());
    }

    /** Featured categories for the homepage Shop by Category row (max 6). */
    public function featuredForHome(int $limit = 6): array
    {
        return collect($this->all())
            ->filter(fn (array $category) => ($category['is_featured'] ?? false) === true)
            ->take($limit)
            ->map(fn (array $category) => $this->toCard($category))
            ->values()
            ->all();
    }

    public function find(string $slug): ?array
    {
        return $this->categories()[$slug] ?? null;
    }

    public function products(string $slug, ?string $sort = null, array $filters = []): array
    {
        $category = $this->find($slug);

        if (! $category) {
            return [];
        }

        $items = $this->baseProducts($category);
        $items = $this->applyFilters($items, $filters);

        return $this->sort($items, $sort);
    }

    public function filterOptions(string $slug): array
    {
        $items = $this->baseProducts($this->find($slug) ?? []);
        $prices = collect($items)->pluck('price')->filter(fn ($price) => is_numeric($price))->map(fn ($price) => (float) $price);
        $highest = (int) ceil($prices->max() ?: 0);

        $sizes = collect($items)->pluck('sizes')->flatten()->unique()->sort()->values()->all();

        return [
            'sizes' => $sizes,
            'has_sale' => collect($items)->contains(fn ($p) => ($p['badge'] ?? null) === 'Sale' || ($p['original_price'] ?? null) !== null),
            'price_min' => 0,
            'price_max' => max(100, $highest),
        ];
    }

    private function baseProducts(array $category): array
    {
        if (empty($category)) {
            return [];
        }

        return match ($category['filter'] ?? 'category') {
            'sale' => array_values(array_filter(
                $this->products->all(),
                fn ($p) => ($p['badge'] ?? null) === 'Sale' || ($p['original_price'] ?? null) !== null,
            )),
            default => array_values(array_filter(
                $this->products->all(),
                fn ($p) => in_array($p['category'], $category['product_categories'], true),
            )),
        };
    }

    private function applyFilters(array $items, array $filters): array
    {
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

        $hasMin = array_key_exists('min_price', $filters) && $filters['min_price'] !== null;
        $hasMax = array_key_exists('max_price', $filters) && $filters['max_price'] !== null;

        if ($hasMin || $hasMax) {
            $minPrice = $hasMin ? (float) $filters['min_price'] : 0;
            $maxPrice = $hasMax ? (float) $filters['max_price'] : PHP_FLOAT_MAX;
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
                    'under_60' => $p['price'] < 60,
                    '60_100' => $p['price'] >= 60 && $p['price'] <= 100,
                    'over_100' => $p['price'] > 100,
                    default => true,
                };
            }));
        }

        return $items;
    }

    public function slugForName(string $name): ?string
    {
        foreach ($this->categories() as $slug => $category) {
            if (strcasecmp((string) ($category['name'] ?? ''), $name) === 0) {
                return $slug;
            }

            if (in_array($name, $category['product_categories'] ?? [], true)) {
                return $slug;
            }

            foreach ($category['product_categories'] ?? [] as $alias) {
                if (strcasecmp((string) $alias, $name) === 0) {
                    return $slug;
                }
            }
        }

        return null;
    }

    public function toCard(array $category): array
    {
        return [
            'name' => $category['name'],
            'count' => $category['count_label'],
            'href' => route('categories.show', $category['slug']),
            'image' => $category['card_image'],
            'color' => $category['color'],
        ];
    }

    /**
     * @return array<int, array{label: string, href: string, active: bool}>
     */
    public function navigationLinks(): array
    {
        if ($this->navigationCache !== null) {
            return $this->navigationCache;
        }

        $links = [
            [
                'label' => 'Home',
                'href' => route('home'),
                'active' => request()->routeIs('home'),
            ],
        ];

        $shopEnabled = false;

        try {
            $shopEnabled = app(ShopPageService::class)->isEnabled();
        } catch (\Throwable) {
            $shopEnabled = false;
        }

        if ($shopEnabled) {
            $links[] = [
                'label' => 'Shop',
                'href' => route('shop.index'),
                'active' => request()->routeIs('shop.*'),
            ];
        }

        // Lightweight nav — no product counts (was loading the full catalog per category).
        foreach ($this->navigationCategories() as $category) {
            $links[] = [
                'label' => $category['name'],
                'href' => route('categories.show', $category['slug']),
                'active' => request()->routeIs('categories.show') && request()->route('slug') === $category['slug'],
            ];
        }

        return $this->navigationCache = $links;
    }

    /**
     * @return array<int, array{slug: string, name: string}>
     */
    private function navigationCategories(): array
    {
        if ($this->usesDatabase()) {
            return Category::query()
                ->where('is_active', true)
                ->where('is_sale', false)
                ->orderBy('sort_order')
                ->get(['slug', 'name'])
                ->reject(function (Category $cat) {
                    $slug = strtolower((string) $cat->slug);
                    $name = strtolower(trim((string) $cat->name));

                    return in_array($slug, ['sale', 'new', 'new-in', 'new-arrivals'], true)
                        || in_array($name, ['sale', 'new', 'new in', 'new arrivals'], true);
                })
                ->map(fn (Category $cat) => [
                    'slug' => $cat->slug,
                    'name' => $cat->name,
                ])
                ->values()
                ->all();
        }

        return collect($this->categories())
            ->reject(function (array $category) {
                $slug = strtolower((string) ($category['slug'] ?? ''));
                $name = strtolower(trim((string) ($category['name'] ?? '')));

                return ($category['filter'] ?? null) === 'sale'
                    || in_array($slug, ['sale', 'new', 'new-in', 'new-arrivals'], true)
                    || in_array($name, ['sale', 'new', 'new in', 'new arrivals'], true);
            })
            ->map(fn (array $category) => [
                'slug' => $category['slug'],
                'name' => $category['name'],
            ])
            ->values()
            ->all();
    }

    private function categories(): array
    {
        if ($this->categoriesCache !== null) {
            return $this->categoriesCache;
        }

        if ($this->usesDatabase()) {
            return $this->categoriesCache = Category::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->withCount(['products as storefront_products_count' => function ($query) {
                    $query->onStorefront();
                }])
                ->get()
                ->mapWithKeys(function (Category $cat) {
                    $data = $cat->toCatalogArray();
                    $count = ($data['filter'] ?? '') === 'sale'
                        ? $this->saleProductCount()
                        : (int) $cat->storefront_products_count;

                    return [$cat->slug => array_merge($data, [
                        'count' => $count,
                        'count_label' => $count > 0 ? $count.' items' : 'Coming soon',
                    ])];
                })->all();
        }

        $items = [
            $this->category('dresses', 'Dresses', 'rose', 'photo-1595777457583-95e059d581b8', [
                'description' => 'From casual day dresses to elegant evening styles — find your perfect fit.',
                'product_categories' => ['Dresses'],
            ]),
            $this->category('tops', 'Tops & Blouses', 'brand', 'photo-1434389677669-e08b4cac3105', [
                'description' => 'Stylish tops and blouses for every occasion, from office chic to weekend casual.',
                'product_categories' => ['Tops & Blouses'],
            ]),
            $this->category('skirts', 'Skirts', 'purple', 'photo-1539533018447-63fcce2678e3', [
                'description' => 'Flowing midi skirts, pleated classics, and trendy mini styles.',
                'product_categories' => ['Skirts'],
            ]),
            $this->category('party-wear', 'Party Wear', 'amber', 'photo-1469334031218-e382a71b716b', [
                'description' => 'Glamorous gowns and cocktail dresses for your next special event.',
                'product_categories' => ['Party Wear'],
            ]),
            $this->category('casual', 'Casual', 'blue', 'photo-1496747611176-843222e1e57c', [
                'description' => 'Effortless everyday styles made for comfort without compromising on style.',
                'product_categories' => ['Casual'],
            ]),
            $this->category('new-in', 'New In', 'cyan', 'photo-1509631179647-0177331693ae', [
                'description' => 'Fresh arrivals and the latest trends just landed in store.',
                'product_categories' => ['New In'],
            ]),
            $this->category('sale', 'Sale', 'rose', 'photo-1595777457583-95e059d581b8', [
                'description' => 'Shop our best deals — premium fashion at unbeatable prices.',
                'filter' => 'sale',
                'product_categories' => [],
            ]),
        ];

        return $this->categoriesCache = collect($items)->keyBy('slug')->all();
    }

    private function category(string $slug, string $name, string $color, string $photoId, array $extra = []): array
    {
        $data = array_merge([
            'slug' => $slug,
            'name' => $name,
            'description' => 'Explore our curated collection.',
            'color' => $color,
            'image' => $this->img($photoId),
            'card_image' => $this->cardImg($photoId),
            'filter' => 'category',
            'product_categories' => [],
        ], $extra);

        $count = $this->countFor($data);

        return array_merge($data, [
            'count' => $count,
            'count_label' => $count > 0 ? $count.' items' : 'Coming soon',
        ]);
    }

    private function countFor(array $category): int
    {
        if (($category['filter'] ?? 'category') === 'sale') {
            return $this->saleProductCount();
        }

        return count(array_filter(
            $this->products->all(),
            fn ($p) => in_array($p['category'], $category['product_categories'], true),
        ));
    }

    private function saleProductCount(): int
    {
        static $count = null;

        if ($count !== null) {
            return $count;
        }

        return $count = count(array_filter(
            $this->products->all(),
            fn ($p) => ($p['badge'] ?? null) === 'Sale' || ($p['original_price'] ?? null) !== null,
        ));
    }

    private function sort(array $items, ?string $sort): array
    {
        return match ($sort) {
            'price_asc' => collect($items)->sortBy('price')->values()->all(),
            'price_desc' => collect($items)->sortByDesc('price')->values()->all(),
            'name' => collect($items)->sortBy('name')->values()->all(),
            default => collect($items)->sortByDesc('published_at')->values()->all(),
        };
    }

    private function img(string $id): string
    {
        return sprintf(self::IMG, $id);
    }

    private function cardImg(string $id): string
    {
        return sprintf(self::CARD_IMG, $id);
    }

    private function usesDatabase(): bool
    {
        try {
            return Category::count('*') > 0;
        } catch (\Throwable) {
            return false;
        }
    }
}
