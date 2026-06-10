<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\CategoryCatalog;
use App\Services\HomepageContentService;
use App\Services\ProductCatalog;

class HomeController extends Controller
{
    public function __construct(
        private HomepageContentService $homepage,
        private ProductCatalog $catalog,
        private CategoryCatalog $categories,
    ) {}

    public function index()
    {
        return view('pages.home.index', [
            'heroSlides' => $this->homepage->sliders(),
            'banners' => $this->homepage->banners(),
            'features' => $this->homepage->features(),
            'categories' => collect($this->categories->all())
                ->take(6)
                ->map(fn ($c) => $this->categories->toCard($c))
                ->all(),
            'featuredProducts' => $this->productCards(fn () => $this->catalog->featured(), 8),
            'newArrivals' => $this->productCards(fn () => $this->catalog->newArrivals(), 4),
        ]);
    }

    /** @return array<int, array<string, mixed>> */
    private function productCards(callable $fetcher, int $limit): array
    {
        $items = $fetcher();

        if (empty($items)) {
            $items = array_slice(array_values($this->catalog->all()), 0, $limit);
        }

        return collect($items)
            ->take($limit)
            ->map(fn ($p) => $this->catalog->toCard($p))
            ->values()
            ->all();
    }
}
