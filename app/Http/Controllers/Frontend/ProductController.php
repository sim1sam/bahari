<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\CategoryCatalog;
use App\Services\ProductCatalog;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private ProductCatalog $catalog,
        private CategoryCatalog $categories,
    ) {}

    public function show(string $slug): View
    {
        $product = $this->catalog->find($slug);

        abort_unless($product, 404);

        return view('pages.products.show', [
            'product' => $product,
            'categorySlug' => $this->categories->slugForName($product['category']),
            'related' => collect($this->catalog->related($slug))
                ->map(fn ($p) => $this->catalog->toCard($p))
                ->all(),
        ]);
    }
}
