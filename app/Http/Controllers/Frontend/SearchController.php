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

        return view('pages.search.index', [
            'query' => $query,
            'products' => $products,
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
