<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShopPageSetting;
use App\Services\ShopPageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopPageProductController extends Controller
{
    public function __construct(private ShopPageService $shop) {}

    public function edit(Request $request): View
    {
        $settings = ShopPageSetting::current();
        $search = trim((string) $request->query('q', ''));
        $dateFrom = trim((string) $request->query('date_from', ''));
        $dateTo = trim((string) $request->query('date_to', ''));

        $products = $this->shop->productOptions(
            $search !== '' ? $search : null,
            $dateFrom !== '' ? $dateFrom : null,
            $dateTo !== '' ? $dateTo : null,
        );
        $selected = $settings->featuredProductIds();

        return view('admin.shop-page.products', [
            'settings' => $settings,
            'products' => $products,
            'selected' => $selected,
            'search' => $search,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'hasFilters' => $search !== '' || $dateFrom !== '' || $dateTo !== '',
            'selectedCount' => count($selected),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'featured_product_ids' => 'nullable|array',
            'featured_product_ids.*' => 'integer|exists:products,id',
            'q' => 'nullable|string|max:150',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $settings = ShopPageSetting::current();
        $incoming = array_values(array_unique(array_map(
            'intval',
            $validated['featured_product_ids'] ?? []
        )));

        $search = trim((string) ($validated['q'] ?? ''));
        $dateFrom = trim((string) ($validated['date_from'] ?? ''));
        $dateTo = trim((string) ($validated['date_to'] ?? ''));
        $hasFilters = $search !== '' || $dateFrom !== '' || $dateTo !== '';

        if ($hasFilters) {
            $listedIds = collect($this->shop->productOptions(
                $search !== '' ? $search : null,
                $dateFrom !== '' ? $dateFrom : null,
                $dateTo !== '' ? $dateTo : null,
            ))->pluck('id')->all();

            $kept = array_values(array_diff($settings->featuredProductIds(), $listedIds));
            $incoming = array_values(array_unique(array_merge($kept, $incoming)));
        }

        $settings->update([
            'featured_product_ids' => $incoming,
        ]);

        $this->shop->clearCache();

        return redirect()
            ->route('admin.shop-page.products.edit', array_filter([
                'q' => $search !== '' ? $search : null,
                'date_from' => $dateFrom !== '' ? $dateFrom : null,
                'date_to' => $dateTo !== '' ? $dateTo : null,
            ]))
            ->with('success', 'Shop products updated.');
    }
}
