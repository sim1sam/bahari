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
        $filters = [
            'sizes' => array_filter((array) $request->query('sizes', [])),
            'colors' => array_filter((array) $request->query('colors', [])),
            'price' => $request->query('price'),
            'sale' => $request->boolean('sale'),
        ];
        $productList = $this->categories->products($slug, $sort, $filters);
        $filterOptions = $this->categories->filterOptions($slug);

        return view('pages.categories.show', [
            'category' => $category,
            'products' => collect($productList)->map(fn ($p) => $this->products->toCard($p))->all(),
            'sort' => $sort,
            'filters' => $filters,
            'filterOptions' => $filterOptions,
            'activeFilterCount' => collect($filters)->filter(fn ($v, $k) => $k === 'sale' ? $v : ! empty($v))->count(),
            'relatedCategories' => collect($this->categories->all())
                ->filter(fn ($c) => $c['slug'] !== $slug)
                ->take(4)
                ->map(fn ($c) => $this->categories->toCard($c))
                ->values()
                ->all(),
        ]);
    }
}
