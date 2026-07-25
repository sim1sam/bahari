<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\ApiProductImportService;
use App\Services\MediaStorageService;
use App\Services\ProductPurchaseExpenseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private MediaStorageService $media,
        private ApiProductImportService $importer,
        private ProductPurchaseExpenseService $purchaseExpenses,
    ) {}

    public function index(Request $request): View
    {
        $perPageRaw = $request->query('per_page', 20);
        $showAll = $perPageRaw === 'all' || $perPageRaw === 'All';
        $perPage = $showAll ? null : (int) $perPageRaw;
        if (! $showAll && ! in_array($perPage, [20, 50, 100], true)) {
            $perPage = 20;
        }

        $listQuery = Product::query()
            ->where(function ($query) {
                $query->liveFromApi()->orWhere('is_manual', true);
            });

        $this->applyProductListFilters($listQuery, $request);

        $filteredQuery = (clone $listQuery)
            ->with(['category', 'apiReceivedItem'])
            ->latest();

        if ($showAll) {
            $total = (clone $listQuery)->count();
            $products = $filteredQuery->paginate(max($total, 1))->withQueryString();
        } else {
            $products = $filteredQuery->paginate($perPage)->withQueryString();
        }

        return view('admin.products.index', [
            'products' => $products,
            'brands' => $this->brandOptions(),
            'categories' => Category::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'brand' => $request->query('brand'),
            'categoryId' => $request->query('category_id'),
            'source' => $request->query('source'),
            'dateFrom' => $request->query('date_from'),
            'dateTo' => $request->query('date_to'),
            'q' => $request->query('q'),
            'perPage' => $showAll ? 'all' : $perPage,
            'stats' => [
                'total' => Product::query()->where(function ($query) {
                    $query->liveFromApi()->orWhere('is_manual', true);
                })->count(),
                'live' => Product::query()->where(function ($query) {
                    $query->liveFromApi()->orWhere('is_manual', true);
                })->where('is_active', true)->count(),
                'manual' => Product::query()->where(function ($query) {
                    $query->liveFromApi()->orWhere('is_manual', true);
                })->where('is_manual', true)->count(),
                'api' => Product::query()->where(function ($query) {
                    $query->liveFromApi()->orWhere('is_manual', true);
                })->where('is_manual', false)->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.products.form', [
            'product' => new Product(['is_active' => true, 'is_manual' => true, 'stock' => 0]),
            'categories' => Category::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'brands' => $this->brandOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            $purchaseExpense = null;
            $validated = $this->validateProduct($request);
            $product = Product::create($validated);
            $product->update($this->syncImages($request, $product));
            $product->refresh();
            $purchaseExpense = $this->purchaseExpenses->recordForNewProduct($product);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', 'Product could not be created: '.$e->getMessage());
        }

        $message = 'Product created and published on storefront.';
        if ($purchaseExpense ?? null) {
            $message .= ' Purchase expense of '.money($purchaseExpense->amount).' recorded.';
        }

        return redirect()->route('admin.products.index')->with('success', $message);
    }

    public function edit(Product $product): View
    {
        abort_unless($product->isManualProduct() || $product->isLiveFromApi(), 404);

        return view('admin.products.form', [
            'product' => $product,
            'categories' => Category::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'brands' => $this->brandOptions($product->brand),
            'isApiProduct' => $product->isLiveFromApi() && ! $product->isManualProduct(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->isManualProduct() || $product->isLiveFromApi(), 404);

        if ($product->isLiveFromApi() && ! $product->isManualProduct()) {
            $product->update($this->validateApiProduct($request, $product));

            return redirect()->route('admin.products.index')->with('success', 'API product updated.');
        }

        try {
            $purchaseExpense = null;
            $previousStock = (int) $product->stock;
            $previousPurchasePrice = (float) $product->purchase_price;

            $product->update($this->validateProduct($request, $product));
            $product->update($this->syncImages($request, $product));
            $product->refresh();

            $purchaseExpense = $this->purchaseExpenses->recordStockIncrease(
                $product,
                $previousStock,
                $previousPurchasePrice
            );
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', 'Product could not be updated: '.$e->getMessage());
        }

        $message = 'Product updated.';
        if ($purchaseExpense ?? null) {
            $message .= ' Purchase expense of '.money($purchaseExpense->amount).' recorded for added stock.';
        }

        return redirect()->route('admin.products.index')->with('success', $message);
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->isLiveFromApi() && ! $product->isManualProduct()) {
            $this->importer->unpublish($product);
        } else {
            $this->deleteProductMedia($product);
            $product->delete();
        }

        return redirect()->route('admin.products.index')->with('success', 'Product removed from storefront.');
    }

    public function destroyBatch(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'select_all' => 'sometimes|boolean',
            'filter_brand' => 'nullable|string|max:120',
            'filter_category_id' => 'nullable|integer|exists:categories,id',
            'filter_source' => 'nullable|in:api,manual',
            'filter_date_from' => 'nullable|date',
            'filter_date_to' => 'nullable|date',
            'filter_q' => 'nullable|string|max:200',
            'products' => 'required_without:select_all|array|min:1',
            'products.*' => 'integer|exists:products,id',
        ]);

        if ($request->boolean('select_all')) {
            $filterRequest = Request::create('/', 'GET', [
                'brand' => $validated['filter_brand'] ?? null,
                'category_id' => $validated['filter_category_id'] ?? null,
                'source' => $validated['filter_source'] ?? null,
                'date_from' => $validated['filter_date_from'] ?? null,
                'date_to' => $validated['filter_date_to'] ?? null,
                'q' => $validated['filter_q'] ?? null,
            ]);

            $productIds = $this->applyProductListFilters(
                Product::query()->where(function ($query) {
                    $query->liveFromApi()->orWhere('is_manual', true);
                }),
                $filterRequest
            )->pluck('id');
        } else {
            $productIds = collect($validated['products'] ?? []);
        }

        $deleted = 0;

        foreach ($productIds as $id) {
            $product = Product::query()->find($id);

            if (! $product) {
                continue;
            }

            if ($product->isLiveFromApi() && ! $product->isManualProduct()) {
                $this->importer->unpublish($product);
            } else {
                $this->deleteProductMedia($product);
                $product->delete();
            }

            $deleted++;
        }

        return redirect()
            ->route('admin.products.index')
            ->with('success', "{$deleted} product(s) removed from storefront.");
    }

    private function applyProductListFilters($query, Request $request)
    {
        if ($request->filled('q')) {
            $term = '%'.$request->string('q')->toString().'%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('slug', 'like', $term)
                    ->orWhere('brand', 'like', $term);
            });
        }

        if ($request->filled('brand')) {
            $query->where('brand', $request->string('brand')->toString());
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', (int) $request->input('category_id'));
        }

        if ($request->query('source', $request->input('source')) === 'api') {
            $query->where('is_manual', false);
        } elseif ($request->query('source', $request->input('source')) === 'manual') {
            $query->where('is_manual', true);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        return $query;
    }

    private function validateProduct(Request $request, ?Product $product = null): array
    {
        $request->merge([
            'brand' => $this->resolveBrandInput($request),
        ]);

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'slug' => 'nullable|string|max:100',
            'name' => 'required|string|max:200',
            'brand' => 'nullable|string|max:120',
            'brand_select' => 'nullable|string|max:120',
            'brand_custom' => 'nullable|string|max:120',
            'purchase_price' => 'nullable|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0|gte:price',
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'sizes' => 'nullable|string',
            'colors' => 'nullable|string',
            'stock' => 'required|integer|min:0',
            'badge' => 'nullable|string|max:30',
            'badge_variant' => 'nullable|string|max:30',
            'rating' => 'nullable|numeric|min:0|max:5',
            'thumbnail' => 'nullable|image|max:5120',
            'gallery' => 'nullable|array',
            'gallery.*' => 'nullable|image|max:5120',
            'thumbnail_url' => 'nullable|url|max:2048',
            'gallery_urls' => 'nullable|array',
            'gallery_urls.*' => 'nullable|url|max:2048',
            'is_featured' => 'boolean',
            'is_new_arrival' => 'boolean',
            'is_active' => 'boolean',
        ], [
            'category_id.required' => 'Please select a category.',
            'category_id.exists' => 'The selected category is invalid.',
            'name.required' => 'Product name is required.',
            'price.required' => 'Sale price is required.',
            'price.numeric' => 'Sale price must be a valid number.',
            'original_price.gte' => 'Original / discount price must be equal to or higher than the sale price.',
            'stock.required' => 'Stock quantity is required.',
            'thumbnail.image' => 'Thumbnail must be an image file (jpg, png, webp, etc.).',
            'thumbnail.max' => 'Thumbnail must be smaller than 5 MB.',
            'gallery.*.image' => 'Each gallery file must be an image.',
            'gallery.*.max' => 'Each gallery image must be smaller than 5 MB.',
            'thumbnail_url.url' => 'Thumbnail URL must be a valid link.',
            'gallery_urls.*.url' => 'Each gallery URL must be a valid link.',
        ]);

        $validated['sizes'] = $this->toArray($validated['sizes'] ?? '');
        $validated['colors'] = $this->toArray($validated['colors'] ?? '');
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_new_arrival'] = $request->boolean('is_new_arrival');
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_manual'] = true;
        $validated['badge_variant'] = $validated['badge_variant'] ?: 'default';
        $validated['brand'] = filled($validated['brand'] ?? null) ? trim((string) $validated['brand']) : null;

        if (empty($validated['slug']) && ! empty($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        if (empty($validated['slug'])) {
            $validated['slug'] = 'product-'.Str::random(8);
        }

        $validated['slug'] = $this->ensureUniqueSlug($validated['slug'], $product?->id);

        if (empty($validated['badge']) && ! empty($validated['original_price']) && $validated['original_price'] > $validated['price']) {
            $validated['badge'] = 'Sale';
            $validated['badge_variant'] = 'sale';
        }

        unset(
            $validated['thumbnail'],
            $validated['gallery'],
            $validated['thumbnail_url'],
            $validated['gallery_urls'],
            $validated['brand_select'],
            $validated['brand_custom'],
        );

        return $validated;
    }

    private function ensureUniqueSlug(string $slug, ?int $exceptId = null): string
    {
        $base = Str::slug($slug) ?: 'product';
        $candidate = $base;
        $suffix = 1;

        while (Product::query()
            ->when($exceptId, fn ($query) => $query->where('id', '!=', $exceptId))
            ->where('slug', $candidate)
            ->exists()) {
            $candidate = $base.'-'.$suffix;
            $suffix++;
        }

        return $candidate;
    }

    private function validateApiProduct(Request $request, Product $product): array
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'brand' => 'nullable|string|max:120',
            'purchase_price' => 'nullable|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'is_featured' => 'boolean',
            'is_new_arrival' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_new_arrival'] = $request->boolean('is_new_arrival');
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['brand'] = filled($validated['brand'] ?? null) ? trim((string) $validated['brand']) : null;
        $validated['purchase_price'] = array_key_exists('purchase_price', $validated) && $validated['purchase_price'] !== null
            ? round((float) $validated['purchase_price'], 2)
            : null;

        return $validated;
    }

    /**
     * @return \Illuminate\Support\Collection<int, string>
     */
    private function brandOptions(?string $current = null)
    {
        $fromProducts = Product::query()
            ->whereNotNull('brand')
            ->where('brand', '!=', '')
            ->distinct()
            ->orderBy('brand')
            ->pluck('brand');

        $fromApiBrands = \App\Models\ApiReceivedBrand::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name');

        return $fromProducts
            ->merge($fromApiBrands)
            ->when(filled($current), fn ($c) => $c->push($current))
            ->map(fn ($brand) => trim((string) $brand))
            ->filter()
            ->unique()
            ->sort()
            ->values();
    }

    private function resolveBrandInput(Request $request): ?string
    {
        $select = trim((string) $request->input('brand_select', ''));

        if ($select === '__custom__') {
            $custom = trim((string) $request->input('brand_custom', ''));

            return $custom !== '' ? $custom : null;
        }

        if ($select !== '') {
            return $select;
        }

        $brand = trim((string) $request->input('brand', ''));

        return $brand !== '' ? $brand : null;
    }

    private function syncImages(Request $request, Product $product): array
    {
        $thumbnail = $this->resolveImageField(
            $request,
            'thumbnail',
            'thumbnail_url',
            'remove_thumbnail',
            $product->image
        );

        $gallery = collect($product->images ?? [])
            ->map(fn ($path) => $this->media->storedPath($path))
            ->filter()
            ->values()
            ->all();

        foreach ((array) $request->input('remove_gallery', []) as $path) {
            $stored = $this->media->storedPath($path);

            if ($stored && in_array($stored, $gallery, true)) {
                $this->media->delete($stored);
                $gallery = array_values(array_filter($gallery, fn ($item) => $item !== $stored));
            }
        }

        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $index => $file) {
                if ($file && $file->isValid()) {
                    $gallery[] = $this->media->storeUpload($file, 'products', field: "gallery.{$index}");
                }
            }
        }

        foreach ((array) $request->input('gallery_urls', []) as $index => $url) {
            if (filled($url)) {
                $gallery[] = $this->media->storeFromUrl($url, 'products', field: "gallery_urls.{$index}");
            }
        }

        $gallery = array_values(array_unique(array_filter($gallery)));

        if ($thumbnail && ! in_array($thumbnail, $gallery, true)) {
            array_unshift($gallery, $thumbnail);
        }

        if (! $thumbnail && $gallery !== []) {
            $thumbnail = $gallery[0];
        }

        return [
            'image' => $thumbnail,
            'images' => $gallery,
        ];
    }

    private function resolveImageField(
        Request $request,
        string $fileKey,
        string $urlKey,
        string $removeKey,
        ?string $current
    ): ?string {
        if ($request->boolean($removeKey)) {
            $this->media->delete($current);

            return null;
        }

        $file = $request->file($fileKey);

        if ($file && $file->getError() !== UPLOAD_ERR_NO_FILE) {
            return $this->media->storeUpload($file, 'products', $current, $fileKey);
        }

        if ($request->filled($urlKey)) {
            return $this->media->storeFromUrl($request->input($urlKey), 'products', $current, $urlKey);
        }

        return $this->media->storedPath($current);
    }

    private function deleteProductMedia(Product $product): void
    {
        $paths = collect($product->images ?? [])
            ->push($product->image)
            ->map(fn ($path) => $this->media->storedPath($path))
            ->filter()
            ->unique()
            ->all();

        foreach ($paths as $path) {
            $this->media->delete($path);
        }
    }

    private function toArray(string $value): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $value))));
    }
}
