<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiReceivedItem;
use App\Models\Category;
use App\Models\Product;
use App\Services\ApiProductImportService;
use App\Services\ApiReceivedBrandService;
use App\Services\ApiReceivedCategoryService;
use App\Services\ApiReceivedMetadataService;
use App\Services\ApiReceivedPriceService;
use App\Services\MediaStorageService;
use App\Services\ProcessedImageDownloadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ApiProcessedController extends Controller
{
    private const PER_PAGE_OPTIONS = [20, 50, 100];

    public function index(Request $request): View
    {
        $query = $this->processedIndexQuery($request);
        $perPage = $this->perPage($request);

        return view('admin.processed.index', [
            'items' => $query->paginate($perPage)->withQueryString(),
            'dateFrom' => $request->query('date_from'),
            'dateTo' => $request->query('date_to'),
            'brand' => $request->query('brand'),
            'perPage' => $perPage,
            'brands' => app(ApiReceivedBrandService::class)->activeBrandNames(),
            'processedCount' => ApiReceivedItem::where('status', ApiReceivedItem::STATUS_PROCESSED)
                ->whereNull('product_id')
                ->count(),
            'liveCount' => ApiReceivedItem::where('status', ApiReceivedItem::STATUS_IMPORTED)->count(),
            'categories' => Category::where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function liveIndex(Request $request): View
    {
        $query = ApiReceivedItem::queryForLists()
            ->with(['source', 'product'])
            ->where('status', ApiReceivedItem::STATUS_IMPORTED)
            ->latest('reviewed_at');

        if ($request->filled('date_from')) {
            $query->whereDate('reviewed_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('reviewed_at', '<=', $request->date_to);
        }

        return view('admin.processed.live', [
            'items' => $query->paginate(20)->withQueryString(),
            'dateFrom' => $request->query('date_from'),
            'dateTo' => $request->query('date_to'),
        ]);
    }

    public function show(ApiReceivedItem $item, ApiReceivedPriceService $prices, ApiReceivedMetadataService $metadata): View|RedirectResponse
    {
        if ((float) $item->price <= 0) {
            try {
                $prices->applyToItem($item);
                $item->refresh();
            } catch (\Throwable) {
                // Price sync should not block viewing the item.
            }
        }

        if (ApiReceivedItem::hasBrandVendorColumns()) {
            try {
                $metadata->syncItem($item);
                $item->refresh();
            } catch (\Throwable) {
                // Metadata sync should not block viewing the item.
            }
        }

        if ($item->isImported()) {
            $item->load(['source', 'product']);

            return view('admin.processed.show', [
                'item' => $item,
                'categories' => Category::where('is_active', true)->orderBy('sort_order')->get(),
                'isLive' => true,
            ]);
        }

        if (! $item->isProcessed()) {
            return redirect()->route('admin.content.show', $item);
        }

        $item->load('source');

        return view('admin.processed.show', [
            'item' => $item,
            'categories' => Category::where('is_active', true)->orderBy('sort_order')->get(),
            'isLive' => false,
        ]);
    }

    public function update(Request $request, ApiReceivedItem $item): RedirectResponse
    {
        if (! $item->isProcessed()) {
            return back()->with('error', 'Only processed items can be edited here.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'slug' => 'nullable|string|max:100',
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:5000',
            'category_name' => 'nullable|string|max:100',
            'brand' => 'nullable|string|max:100',
            'vendor' => 'nullable|string|max:100',
            'sizes' => 'nullable|string|max:255',
            'colors' => 'nullable|string|max:255',
            'badge' => 'nullable|string|max:30',
            'badge_variant' => 'nullable|string|max:30',
            'rating' => 'nullable|numeric|min:0|max:5',
        ]);

        $item->update(ApiReceivedItem::withoutMissingBrandVendorColumns([
            'title' => $validated['title'],
            'sku' => $validated['sku'] ?? null,
            'slug' => $validated['slug'] ?? null,
            'price' => $validated['price'],
            'original_price' => filled($validated['original_price'] ?? null) ? $validated['original_price'] : null,
            'purchase_price' => filled($validated['purchase_price'] ?? null) ? $validated['purchase_price'] : null,
            'description' => $validated['description'] ?? null,
            'category_name' => $validated['category_name'] ?? null,
            'brand' => filled($validated['brand'] ?? null) ? $validated['brand'] : null,
            'vendor' => filled($validated['vendor'] ?? null) ? $validated['vendor'] : null,
            'sizes' => $this->listFromString($validated['sizes'] ?? ''),
            'colors' => $this->listFromString($validated['colors'] ?? ''),
            'badge' => filled($validated['badge'] ?? null) ? $validated['badge'] : null,
            'badge_variant' => filled($validated['badge_variant'] ?? null) ? $validated['badge_variant'] : null,
            'rating' => filled($validated['rating'] ?? null) ? $validated['rating'] : null,
        ]));

        $item->loadMissing('product');

        if ($item->product_id && $item->product) {
            $item->product->update([
                'name' => $validated['title'],
                'price' => $validated['price'],
                'original_price' => filled($validated['original_price'] ?? null) ? $validated['original_price'] : null,
                'purchase_price' => filled($validated['purchase_price'] ?? null) ? $validated['purchase_price'] : $item->product->purchase_price,
            ]);
        }

        if (filled($validated['brand'] ?? null)) {
            app(ApiReceivedBrandService::class)->attachToItem($item->fresh(), $validated['brand']);
        }

        if (filled($validated['category_name'] ?? null)) {
            app(ApiReceivedCategoryService::class)->attachToItem($item->fresh(), $validated['category_name']);
        }

        return back()->with('success', 'Product information updated.');
    }

    public function live(Request $request, ApiReceivedItem $item, ApiProductImportService $importer, ApiReceivedPriceService $prices): RedirectResponse
    {
        if (! $item->canPublish()) {
            return back()->with('error', 'This item is not ready to go live.');
        }

        $prices->applyToItem($item);
        $item->refresh();

        if ((float) $item->price <= 0) {
            return back()->with('error', 'Price is 0. Enter price in the form or sync from API (price_bdt) before Go Live. The price on the image is part of the photo only.');
        }

        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $categoryId = filled($validated['category_id'] ?? null) ? (int) $validated['category_id'] : null;

        if ($categoryId === null && ! filled($item->category_name)) {
            return back()->with('error', 'No category from API. Set ecommerce_category_name in the payload or select a category manually.');
        }

        $product = $item->product_id
            ? $importer->syncProduct($item, $item->product, $categoryId)
            : $importer->import($item, $categoryId);

        return redirect()
            ->route('admin.processed.live')
            ->with('success', 'Product is now live under '.$product->category?->name.'.');
    }

    public function liveBatch(Request $request, ApiProductImportService $importer, ApiReceivedPriceService $prices): RedirectResponse
    {
        $validated = $request->validate([
            'select_all' => 'sometimes|boolean',
            'filter_brand' => 'nullable|string|max:100',
            'filter_date_from' => 'nullable|date',
            'filter_date_to' => 'nullable|date',
            'items' => 'required_without:select_all|array|min:1',
            'items.*' => 'integer|exists:api_received_items,id',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $items = $this->resolveBatchItems($request);
        $this->extendBatchTimeLimit($request);
        $published = 0;
        $skippedZeroPrice = 0;
        $skippedNoCategory = 0;
        $overrideCategoryId = filled($validated['category_id'] ?? null) ? (int) $validated['category_id'] : null;

        foreach ($items as $item) {
            if (! $item->canPublish()) {
                continue;
            }

            $prices->applyToItem($item);
            $item->refresh();

            if ((float) $item->price <= 0) {
                $skippedZeroPrice++;

                continue;
            }

            if ($overrideCategoryId === null && ! filled($item->category_name)) {
                $skippedNoCategory++;

                continue;
            }

            if ($item->product_id) {
                $importer->syncProduct($item, $item->product, $overrideCategoryId);
            } else {
                $importer->import($item, $overrideCategoryId);
            }
            $published++;
        }

        $message = "{$published} product(s) are now live.";
        if ($overrideCategoryId) {
            $message = "{$published} product(s) are now live under ".(Category::find($overrideCategoryId)?->name ?? 'selected category').'.';
        }

        if ($skippedZeroPrice > 0) {
            $message .= " {$skippedZeroPrice} item(s) skipped because price is 0. Send price_bdt or enter price manually.";
        }
        if ($skippedNoCategory > 0) {
            $message .= " {$skippedNoCategory} item(s) skipped — no ecommerce_category_name and no override category selected.";
        }

        return redirect()
            ->route('admin.processed.live')
            ->with($published > 0 ? 'success' : 'warning', $message);
    }

    public function destroyLive(ApiReceivedItem $item, ApiProductImportService $importer): RedirectResponse
    {
        if (! $item->isImported() || ! $item->product) {
            return back()->with('error', 'Only live storefront products can be removed here.');
        }

        $importer->unpublish($item->product);

        return redirect()
            ->route('admin.processed.live')
            ->with('success', 'Product removed from storefront. It is back in Processed and can be published again.');
    }

    public function destroy(ApiReceivedItem $item, MediaStorageService $media): RedirectResponse
    {
        if (! $item->isProcessed()) {
            return back()->with('error', 'Only processed items awaiting go live can be deleted.');
        }

        $this->deleteProcessedItem($item, $media);

        return redirect()
            ->route('admin.processed.index')
            ->with('success', 'Processed item deleted.');
    }

    public function destroyBatch(Request $request, MediaStorageService $media): RedirectResponse
    {
        $request->validate([
            'select_all' => 'sometimes|boolean',
            'filter_brand' => 'nullable|string|max:100',
            'filter_date_from' => 'nullable|date',
            'filter_date_to' => 'nullable|date',
            'items' => 'required_without:select_all|array|min:1',
            'items.*' => 'integer|exists:api_received_items,id',
        ]);

        $deleted = 0;
        $this->extendBatchTimeLimit($request);

        foreach ($this->resolveBatchItems($request) as $item) {
            if (! $item->isProcessed()) {
                continue;
            }

            $this->deleteProcessedItem($item, $media);
            $deleted++;
        }

        return redirect()
            ->route('admin.processed.index')
            ->with('success', "{$deleted} processed item(s) deleted.");
    }

    public function purgeManualProducts(): RedirectResponse
    {
        $deleted = Product::query()
            ->where('is_manual', false)
            ->whereDoesntHave('apiReceivedItem')
            ->delete();

        return redirect()
            ->route('admin.processed.index')
            ->with('success', "{$deleted} old product(s) removed. Only API processed products will show on the storefront.");
    }

    public function downloadImage(ApiReceivedItem $item, ProcessedImageDownloadService $downloader): BinaryFileResponse|RedirectResponse
    {
        if (! $item->isProcessed() && ! $item->isImported()) {
            abort(404);
        }

        $resolved = $downloader->resolveDownloadablePath($item);

        if (! $resolved) {
            return back()->with('error', 'Image file not found for this item.');
        }

        return response()
            ->download($resolved['path'], $downloader->downloadFilename($item))
            ->deleteFileAfterSend($resolved['temporary']);
    }

    public function downloadImages(Request $request, ProcessedImageDownloadService $downloader): BinaryFileResponse|RedirectResponse
    {
        $validated = $request->validate([
            'select_all' => 'sometimes|boolean',
            'filter_brand' => 'nullable|string|max:100',
            'filter_date_from' => 'nullable|date',
            'filter_date_to' => 'nullable|date',
            'items' => 'required_without:select_all|array|min:1',
            'items.*' => 'integer|exists:api_received_items,id',
            'layout' => 'required|in:flat,brand',
        ]);

        $items = $this->resolveBatchItems($request);
        $this->extendBatchTimeLimit($request);

        if ($items->isEmpty()) {
            return back()->with('error', 'No processed items found for download.');
        }

        $layout = $validated['layout'];
        if ($request->boolean('select_all') || filled($validated['filter_brand'] ?? null)) {
            $layout = 'brand';
        }

        if ($items->count() === 1 && $layout === 'flat') {
            return $this->downloadImage($items->first(), $downloader);
        }

        try {
            $zipPath = $downloader->createZip($items, $layout);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage() ?: 'Could not prepare download archive.');
        }

        $suffix = $layout === 'brand' ? 'by-brand' : 'selected';

        return response()
            ->download($zipPath, 'processed-images-'.$suffix.'-'.now()->format('Y-m-d-His').'.zip')
            ->deleteFileAfterSend(true);
    }

    public function downloadFiltered(Request $request, ProcessedImageDownloadService $downloader): BinaryFileResponse|RedirectResponse
    {
        $request->merge(['select_all' => true]);
        $request->merge([
            'filter_brand' => $request->query('brand', $request->input('brand')),
            'filter_date_from' => $request->query('date_from', $request->input('date_from', $request->input('date'))),
            'filter_date_to' => $request->query('date_to', $request->input('date_to', $request->input('date'))),
        ]);

        if (! $request->filled('filter_brand') && ! $request->filled('filter_date_from') && ! $request->filled('filter_date_to')) {
            return back()->with('error', 'Apply a brand or date range filter first, or use Select all with batch download.');
        }

        $request->merge(['layout' => 'brand']);

        return $this->downloadImages($request, $downloader);
    }

    private function processedIndexQuery(Request $request)
    {
        $query = ApiReceivedItem::queryForLists()
            ->with(['source', 'product'])
            ->where('status', ApiReceivedItem::STATUS_PROCESSED)
            ->whereNull('product_id')
            ->latest('updated_at');

        $this->applyDateRangeFilter($query, $request->query('date_from'), $request->query('date_to'));

        if ($request->filled('brand')) {
            $this->applyBrandFilter($query, $request->string('brand')->toString());
        }

        return $query;
    }

    private function perPage(Request $request): int
    {
        $perPage = (int) $request->query('per_page', 20);

        return in_array($perPage, self::PER_PAGE_OPTIONS, true) ? $perPage : 20;
    }

    /** @return Collection<int, ApiReceivedItem> */
    private function resolveBatchItems(Request $request): Collection
    {
        if ($request->boolean('select_all')) {
            return $this->processedBatchQuery($request)->get();
        }

        $ids = $request->input('items', []);

        return ApiReceivedItem::query()
            ->whereIn('id', $ids)
            ->where('status', ApiReceivedItem::STATUS_PROCESSED)
            ->whereNull('product_id')
            ->get();
    }

    private function processedBatchQuery(Request $request)
    {
        $query = ApiReceivedItem::query()
            ->where('status', ApiReceivedItem::STATUS_PROCESSED)
            ->whereNull('product_id');

        if ($request->filled('filter_brand')) {
            $this->applyBrandFilter($query, $request->string('filter_brand')->toString());
        }

        $this->applyDateRangeFilter(
            $query,
            $request->input('filter_date_from', $request->input('filter_date')),
            $request->input('filter_date_to', $request->input('filter_date'))
        );

        return $query;
    }

    private function applyDateRangeFilter($query, mixed $from, mixed $to): void
    {
        if (filled($from)) {
            $query->whereDate('updated_at', '>=', $from);
        }

        if (filled($to)) {
            $query->whereDate('updated_at', '<=', $to);
        }
    }

    private function extendBatchTimeLimit(Request $request): void
    {
        if ($request->boolean('select_all')) {
            @set_time_limit(300);
        }
    }

    private function applyBrandFilter($query, string $brand): void
    {
        $query->where(function ($brandQuery) use ($brand) {
            if (ApiReceivedItem::hasBrandVendorColumns()) {
                $brandQuery->where('brand', $brand);
            }

            $brandQuery->orWhere('payload->brand_name', $brand)
                ->orWhere('payload->brand', $brand);
        });
    }

    private function deleteProcessedItem(ApiReceivedItem $item, MediaStorageService $media): void
    {
        $media->delete($item->processed_image);
        $item->delete();
    }

    /** @return array<int, string> */
    private function listFromString(string $value): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $value))));
    }
}
