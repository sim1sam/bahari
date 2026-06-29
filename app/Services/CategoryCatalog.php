<?php

namespace App\Services;

use App\Models\Category;

class CategoryCatalog
{
    private const IMG = 'https://images.unsplash.com/%s?auto=format&fit=crop&w=1200&h=400&q=80';

    private const CARD_IMG = 'https://images.unsplash.com/%s?auto=format&fit=crop&w=400&h=300&q=80';

    public function __construct(private ProductCatalog $products) {}

    public function all(): array
    {
        return array_values($this->categories());
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

        $sizes = collect($items)->pluck('sizes')->flatten()->unique()->sort()->values()->all();
        $colors = collect($items)->pluck('colors')->flatten()->unique()->sort()->values()->all();

        return [
            'sizes' => $sizes,
            'colors' => $colors,
            'has_sale' => collect($items)->contains(fn ($p) => ($p['badge'] ?? null) === 'Sale' || ($p['original_price'] ?? null) !== null),
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

        if (! empty($filters['colors'])) {
            $colors = $filters['colors'];
            $items = array_values(array_filter(
                $items,
                fn ($p) => ! empty(array_intersect($p['colors'] ?? [], $colors)),
            ));
        }

        if (! empty($filters['price'])) {
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
            if (in_array($name, $category['product_categories'], true)) {
                return $slug;
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
        $links = [
            [
                'label' => 'Home',
                'href' => route('home'),
                'active' => request()->routeIs('home'),
            ],
        ];

        foreach ($this->all() as $category) {
            $links[] = [
                'label' => $category['name'],
                'href' => route('categories.show', $category['slug']),
                'active' => request()->routeIs('categories.show') && request()->route('slug') === $category['slug'],
            ];
        }

        return $links;
    }

    private function categories(): array
    {
        if ($this->usesDatabase()) {
            return Category::where('is_active', true)->orderBy('sort_order')->get()
                ->mapWithKeys(function (Category $cat) {
                    $data = $cat->toCatalogArray();
                    $count = $this->countFor($data);

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

        return collect($items)->keyBy('slug')->all();
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
            return count(array_filter(
                $this->products->all(),
                fn ($p) => ($p['badge'] ?? null) === 'Sale' || ($p['original_price'] ?? null) !== null,
            ));
        }

        return count(array_filter(
            $this->products->all(),
            fn ($p) => in_array($p['category'], $category['product_categories'], true),
        ));
    }

    private function sort(array $items, ?string $sort): array
    {
        return match ($sort) {
            'price_asc' => collect($items)->sortBy('price')->values()->all(),
            'price_desc' => collect($items)->sortByDesc('price')->values()->all(),
            'name' => collect($items)->sortBy('name')->values()->all(),
            default => $items,
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
            return Category::count() > 0;
        } catch (\Throwable) {
            return false;
        }
    }
}
