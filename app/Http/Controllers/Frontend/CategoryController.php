<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\CategoryCatalog;
use App\Services\ProductCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(
        private CategoryCatalog $categories,
        private ProductCatalog $products,
    ) {}

    public function index(): View
    {
        return view('pages.categories.index', [
            'categories' => $this->categories->all(),
            'totalProducts' => count($this->products->all()),
        ]);
    }

    public function show(Request $request, string $slug): View|RedirectResponse
    {
        $category = $this->categories->find($slug);

        abort_unless($category, 404);

        $sort = $request->query('sort');
        $filterOptions = $this->categories->filterOptions($slug);
        $priceMaxLimit = (int) ($filterOptions['price_max'] ?? 1000);

        $filters = [
            'sizes' => array_filter((array) $request->query('sizes', [])),
            'min_price' => $request->filled('min_price') ? max(0, min($priceMaxLimit, (int) $request->query('min_price'))) : null,
            'max_price' => $request->filled('max_price') ? max(0, min($priceMaxLimit, (int) $request->query('max_price'))) : null,
            'price_limit_max' => $priceMaxLimit,
            'sale' => $request->boolean('sale'),
        ];

        if ($filters['min_price'] !== null && $filters['max_price'] !== null && $filters['min_price'] > $filters['max_price']) {
            [$filters['min_price'], $filters['max_price']] = [$filters['max_price'], $filters['min_price']];
        }

        $productList = $this->categories->products($slug, $sort, $filters);

        return view('pages.categories.show', [
            'category' => $category,
            'products' => collect($productList)->map(fn ($p) => $this->products->toCard($p))->all(),
            'sort' => $sort,
            'filters' => $filters,
            'filterOptions' => $filterOptions,
            'activeFilterCount' => collect($filters)->filter(function ($value, $key) use ($priceMaxLimit) {
                if ($key === 'sale') {
                    return (bool) $value;
                }

                if ($key === 'price_limit_max') {
                    return false;
                }

                if ($key === 'min_price') {
                    return $value !== null && (int) $value > 0;
                }

                if ($key === 'max_price') {
                    return $value !== null && (int) $value < $priceMaxLimit;
                }

                return ! empty($value);
            })->count(),
            'relatedCategories' => collect($this->categories->all())
                ->filter(fn ($c) => $c['slug'] !== $slug)
                ->take(4)
                ->map(fn ($c) => $this->categories->toCard($c))
                ->values()
                ->all(),
        ]);
    }
}
