<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\CategoryCatalog;
use App\Services\MetaConversionsApiService;
use App\Services\ProductCatalog;
use App\Support\TrackingPayload;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private ProductCatalog $catalog,
        private CategoryCatalog $categories,
        private MetaConversionsApiService $metaCapi,
    ) {}

    public function show(string $slug): View
    {
        $product = $this->catalog->find($slug);

        abort_unless($product, 404);

        $eventId = $this->metaCapi->newEventId();
        $trackingProduct = TrackingPayload::fromProduct($product);

        $this->metaCapi->send(
            'ViewContent',
            $eventId,
            [
                'content_ids' => $trackingProduct['meta_content_ids'],
                'content_type' => 'product',
                'contents' => $trackingProduct['meta_contents'],
                'content_name' => $trackingProduct['product_name'],
                'content_category' => $trackingProduct['product_type'],
                'currency' => $trackingProduct['currency'],
                'value' => $trackingProduct['total_value'],
            ],
            Auth::check()
                ? $this->metaCapi->userDataFromCustomer([
                    'email' => Auth::user()->email,
                    'name' => Auth::user()->name,
                ], Auth::id())
                : $this->metaCapi->userDataFromCustomer([]),
            route('products.show', $slug),
        );

        return view('pages.products.show', [
            'product' => $product,
            'categorySlug' => $this->categories->slugForName($product['category']),
            'related' => collect($this->catalog->related($slug))
                ->map(fn ($p) => $this->catalog->toCard($p))
                ->all(),
            'trackingEventId' => $eventId,
            'trackingProduct' => $trackingProduct,
        ]);
    }

    public function newArrivals(): View
    {
        $products = collect($this->catalog->newArrivals())
            ->map(fn ($p) => $this->catalog->toCard($p))
            ->values();

        return view('pages.products.new-arrivals', [
            'products' => $products->all(),
            'trackingImpressions' => $products->map(fn ($p, $i) => [
                'product_id' => $p['slug'] ?? '',
                'product_name' => $p['name'] ?? '',
                'product_price' => $p['price'] ?? 0,
                'product_type' => $p['category'] ?? '',
                'product_brand' => $p['brand'] ?? config('app.name'),
                'product_position' => $i + 1,
            ])->all(),
        ]);
    }
}
