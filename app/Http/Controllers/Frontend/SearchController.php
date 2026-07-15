<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\ProductCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __construct(
        private ProductCatalog $products,
    ) {}

    public function index(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));
        $products = $query !== '' ? $this->products->search($query) : [];

        $impressions = collect($products)->values()->map(fn ($p, $i) => [
            'product_id' => $p['slug'] ?? '',
            'product_name' => $p['name'] ?? '',
            'product_price' => $p['price'] ?? 0,
            'product_type' => $p['category'] ?? '',
            'product_brand' => $p['brand'] ?? config('app.name'),
            'product_position' => $i + 1,
        ])->all();

        return view('pages.search.index', [
            'query' => $query,
            'products' => $products,
            'trackingImpressions' => $impressions,
        ]);
    }

    public function suggest(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));

        if (mb_strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        return response()->json([
            'results' => $this->products->search($query, 8),
        ]);
    }
}
