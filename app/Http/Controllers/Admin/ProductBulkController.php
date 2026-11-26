<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiReceivedBrand;
use App\Models\Category;
use App\Models\CostCalculationSetting;
use App\Models\Product;
use App\Models\ShippingCharge;
use App\Services\ContentPriceCalculationService;
use App\Services\MediaStorageService;
use App\Services\ProductLogoService;
use App\Services\ProductPurchaseExpenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class ProductBulkController extends Controller
{
    public function __construct(
        private ContentPriceCalculationService $priceCalculation,
        private ProductLogoService $logoService,
        private MediaStorageService $media,
        private ProductPurchaseExpenseService $purchaseExpenses,
    ) {}

    public function create(): View
    {
        return view('admin.products.bulk', [
            'categories' => Category::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name']),
            'shippingCharges' => ShippingCharge::query()
                ->forCostCalculation()
                ->orderBy('sort_order')
                ->orderBy('item_name')
                ->get(),
            'brands' => $this->brandOptions(),
            'conversionRate' => CostCalculationSetting::resolveConversionRate(),
        ]);
    }

    public function calculatePrices(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|integer|exists:shipping_charges,id',
            'inr_price' => 'required|numeric|min:0',
        ]);

        $prices = $this->priceCalculation->calculate(
            (int) $validated['category_id'],
            (float) $validated['inr_price']
        );

        return response()->json([
            'retail' => $prices['retail'],
            'wholesale' => $prices['wholesale'],
            'reselling' => $prices['reselling'],
            'conversion_rate' => $this->priceCalculation->resolveConversionRate(),
            'retail_formatted' => $this->priceCalculation->formatBdtPrice($prices['retail']),
            'wholesale_formatted' => $this->priceCalculation->formatBdtPrice($prices['wholesale']),
            'reselling_formatted' => $this->priceCalculation->formatBdtPrice($prices['reselling']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $samePrice = $request->boolean('same_price');

        $rules = [
            'brand' => 'nullable|string|max:120',
            'brand_select' => 'nullable|string|max:120',
            'brand_custom' => 'nullable|string|max:120',
            'ecommerce_category_id' => 'required|exists:categories,id',
            'sizes' => 'nullable|string|max:2000',
            'hashtags' => 'nullable|string|max:2000',
            'stock' => 'nullable|integer|min:0',
            'images' => 'required|array|min:1',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'same_price' => 'nullable',
            'go_live' => 'nullable',
            'process_indices' => 'nullable|array',
            'process_indices.*' => 'integer|min:0',
        ];

        if ($samePrice) {
            $rules['common_title'] = 'nullable|string|max:255';
            $rules['common_category_id'] = 'required|exists:shipping_charges,id';
            $rules['common_inr_price'] = 'required|integer|min:1';
        } else {
            $rules['items'] = 'required|array|min:1';
            $rules['items.*.title'] = 'nullable|string|max:255';
            $rules['items.*.category_id'] = 'required|exists:shipping_charges,id';
            $rules['items.*.inr_price'] = 'required|integer|min:1';
        }

        $validated = $request->validate($rules);
        $images = $request->file('images') ?? [];
        $processIndices = $validated['process_indices'] ?? null;

        if (is_array($processIndices) && count($processIndices) > 0) {
            $indices = array_values(array_unique(array_map('intval', $processIndices)));
        } elseif ($samePrice) {
            $indices = array_keys($images);
        } else {
            $indices = array_keys($validated['items'] ?? []);
        }

        $sizesRaw = trim((string) ($validated['sizes'] ?? ''));
        $hashtagsRaw = trim((string) ($validated['hashtags'] ?? ''));
        $sizes = $this->parseSizes($sizesRaw !== '' ? $sizesRaw : null);
        $stock = (int) ($validated['stock'] ?? 100);
        $goLive = $request->boolean('go_live');
        $brand = $this->resolveBrandInput($request);
        $created = [];

        DB::beginTransaction();
        try {
            foreach ($indices as $index) {
                $imageFile = $images[$index] ?? null;
                if (! $imageFile) {
                    continue;
                }

                if ($samePrice) {
                    $title = trim((string) ($validated['common_title'] ?? ''));
                    $shippingChargeId = (int) $validated['common_category_id'];
                    $inrPrice = (int) $validated['common_inr_price'];
                } else {
                    $item = $validated['items'][$index] ?? [];
                    $title = trim((string) ($item['title'] ?? ''));
                    $shippingChargeId = (int) ($item['category_id'] ?? 0);
                    $inrPrice = (int) ($item['inr_price'] ?? 0);
                }

                if ($shippingChargeId <= 0 || $inrPrice <= 0) {
                    continue;
                }

                $prices = $this->priceCalculation->calculate($shippingChargeId, $inrPrice);
                $retail = (float) $prices['retail'];
                if ($retail <= 0) {
                    continue;
                }

                $originalPath = $this->media->storeUpload($imageFile, 'products/originals');
                $processedPath = $this->logoService->applyLogoToReceivedItem(
                    $originalPath,
                    null,
                    $retail,
                    $brand !== '' ? $brand : null,
                    $hashtagsRaw !== '' ? $hashtagsRaw : null,
                    $sizesRaw !== '' ? $sizesRaw : null,
                );

                $shipping = ShippingCharge::find($shippingChargeId);
                $name = $this->buildProductName($brand, $title, $shipping?->item_name, $index);
                $slug = $this->uniqueSlug($name);

                $product = Product::create([
                    'category_id' => $validated['ecommerce_category_id'],
                    'slug' => $slug,
                    'name' => $name,
                    'brand' => $brand !== '' ? $brand : null,
                    'price' => $retail,
                    'purchase_price' => $prices['wholesale'] > 0 ? $prices['wholesale'] : null,
                    'image' => $processedPath,
                    'images' => [$processedPath],
                    'sizes' => $sizes,
                    'stock' => $stock > 0 ? $stock : 100,
                    'is_active' => $goLive,
                    'is_manual' => true,
                    'is_new_arrival' => true,
                    'short_description' => $shipping?->item_name
                        ? 'INR '.$inrPrice.' · '.$shipping->item_name
                        : 'INR '.$inrPrice,
                ]);

                $this->purchaseExpenses->recordForNewProduct($product);
                $created[] = $product;
            }

            if ($created === []) {
                DB::rollBack();

                return back()
                    ->with('error', 'No products created. Check images, shipping category, and INR price.')
                    ->withInput();
            }

            DB::commit();

            return redirect()
                ->route('admin.products.bulk.create')
                ->with('success', count($created).' product(s) added successfully.')
                ->with('bulk_products', collect($created)->map(fn (Product $p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'price' => (float) $p->price,
                    'image' => $p->imageUrl(),
                ])->all());
        } catch (Throwable $e) {
            DB::rollBack();

            return back()
                ->with('error', 'Bulk add failed: '.$e->getMessage())
                ->withInput();
        }
    }

    private function resolveBrandInput(Request $request): string
    {
        $select = trim((string) $request->input('brand_select', ''));

        if ($select === '__custom__') {
            return trim((string) $request->input('brand_custom', ''));
        }

        if ($select !== '') {
            return $select;
        }

        return trim((string) $request->input('brand', ''));
    }

    /** @return \Illuminate\Support\Collection<int, string> */
    private function brandOptions(): \Illuminate\Support\Collection
    {
        $fromProducts = Product::query()
            ->whereNotNull('brand')
            ->where('brand', '!=', '')
            ->distinct()
            ->orderBy('brand')
            ->pluck('brand');

        $fromApiBrands = ApiReceivedBrand::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name');

        return $fromProducts
            ->merge($fromApiBrands)
            ->map(fn ($brand) => trim((string) $brand))
            ->filter()
            ->unique()
            ->sort()
            ->values();
    }

    private function buildProductName(string $brand, string $title, ?string $shippingName, int $index): string
    {
        $parts = array_filter([
            $brand !== '' ? $brand : null,
            $title !== '' ? $title : null,
            ($title === '' && $shippingName) ? $shippingName : null,
        ]);

        $base = trim(implode(' ', $parts));

        if ($base === '') {
            $base = 'Product '.($index + 1);
        }

        return $base;
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'product';
        $slug = $base;
        $i = 1;

        while (Product::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }

    /** @return array<int, string> */
    private function parseSizes(?string $raw): array
    {
        if (! filled($raw)) {
            return [];
        }

        return collect(preg_split('/[\r\n,]+/', $raw) ?: [])
            ->map(fn ($s) => trim((string) $s))
            ->filter()
            ->values()
            ->all();
    }
}
