<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\CategoryCatalog;
use App\Services\ProductCatalog;
use App\Services\ShopPageService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function __construct(
        private ShopPageService $shop,
        private ProductCatalog $products,
        private CategoryCatalog $categories,
    ) {}

    public function index(Request $request): View
    {
        abort_unless($this->shop->isEnabled(), 404);

        $settings = $this->shop->settings();
        $sort = $request->query('sort');
        $filters = [
            'brands' => array_filter((array) $request->query('brands', [])),
            'sizes' => array_filter((array) $request->query('sizes', [])),
            'price' => $request->query('price'),
            'sale' => $request->boolean('sale'),
            'category' => $request->query('category'),
        ];

        $productList = $this->shop->products($sort, $filters);
        $filterOptions = $this->shop->filterOptions();
        $activeBrands = $this->shop->activeBrands();
        $categoryRows = $this->categoryRows($productList);

        $trackingImpressionGroups = collect($categoryRows)->map(function (array $row) {
            $categoryName = $row['category']['name'] ?? 'Collection';

            return [
                'list_name' => 'Shop — '.$categoryName,
                'products' => collect($row['products'])->values()->map(fn (array $p, int $i) => [
                    'product_id' => $p['slug'] ?? '',
                    'product_name' => $p['name'] ?? '',
                    'product_price' => $p['price'] ?? 0,
                    'product_type' => $p['category'] ?? $categoryName,
                    'product_brand' => $p['brand'] ?? config('app.name'),
                    'product_position' => $i + 1,
                ])->all(),
            ];
        })->filter(fn (array $group) => count($group['products']) > 0)->values()->all();

        $trackingImpressions = collect($productList)->values()->map(fn (array $p, int $i) => [
            'product_id' => $p['slug'] ?? '',
            'product_name' => $p['name'] ?? '',
            'product_price' => $p['price'] ?? 0,
            'product_type' => $p['category'] ?? '',
            'product_brand' => $p['brand'] ?? config('app.name'),
            'product_position' => $i + 1,
        ])->all();

        $pageExtra = array_filter([
            'shop_product_count' => count($productList),
            'shop_active_brands' => $activeBrands ?: null,
            'shop_sort' => $sort ?: null,
            'shop_filter_brands' => $filters['brands'] ?: null,
            'shop_filter_category' => $filters['category'] ?: null,
            'shop_filter_sale' => $filters['sale'] ? true : null,
        ], fn ($v) => $v !== null && $v !== [] && $v !== false);

        return view('pages.shop.index', [
            'settings' => $settings,
            'activeBrands' => $activeBrands,
            'categoryRows' => $categoryRows,
            'productCount' => count($productList),
            'sort' => $sort,
            'filters' => $filters,
            'filterOptions' => $filterOptions,
            'activeFilterCount' => collect($filters)->filter(function ($value, $key) {
                if ($key === 'sale') {
                    return (bool) $value;
                }

                return ! empty($value);
            })->count(),
            'trackingImpressions' => $trackingImpressions,
            'trackingImpressionGroups' => $trackingImpressionGroups,
            'trackingPageExtra' => $pageExtra,
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $productList
     * @return array<int, array{category: array{name: string, count: ?string, href: string, image: ?string, color: string}, products: array<int, array<string, mixed>>}>
     */
    private function categoryRows(array $productList): array
    {
        return collect($productList)
            ->groupBy(fn (array $product) => filled($product['category'] ?? null) ? (string) $product['category'] : 'Collection')
            ->map(function ($items, string $name) {
                $cards = $items
                    ->map(fn (array $product) => $this->products->toCard($product))
                    ->values()
                    ->all();

                $fallbackImage = collect($cards)
                    ->pluck('image')
                    ->first(fn ($image) => filled($image));

                return [
                    'category' => $this->categoryPayload($name, $items->count(), $fallbackImage),
                    'products' => $cards,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array{name: string, count: ?string, href: string, image: ?string, color: string}
     */
    private function categoryPayload(string $name, int $productCount, ?string $fallbackImage = null): array
    {
        $category = $this->resolveCategory($name);

        if (! $category) {
            return [
                'name' => $name,
                'count' => $productCount.' item'.($productCount === 1 ? '' : 's'),
                'href' => route('shop.index', ['category' => $name]).'#shop-collection',
                'image' => $fallbackImage,
                'color' => 'brand',
            ];
        }

        $card = $this->categories->toCard($category);

        // Prefer card_image, then hero image, then product fallback.
        $image = $category['card_image']
            ?? $category['image']
            ?? $card['image']
            ?? $fallbackImage;

        return [
            'name' => $card['name'],
            'count' => $productCount.' item'.($productCount === 1 ? '' : 's'),
            'href' => $card['href'],
            'image' => $image,
            'color' => $card['color'] ?? 'brand',
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function resolveCategory(string $name): ?array
    {
        $slug = $this->categories->slugForName($name);
        if ($slug) {
            return $this->categories->find($slug);
        }

        foreach ($this->categories->all() as $category) {
            if (strcasecmp((string) ($category['name'] ?? ''), $name) === 0) {
                return $category;
            }
        }

        return null;
    }
}
