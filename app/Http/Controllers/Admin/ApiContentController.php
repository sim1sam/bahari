<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiReceivedItem;
use App\Models\Category;
use App\Models\SiteSetting;
use App\Services\ApiReceivedBrandService;
use App\Services\ApiReceivedCategoryService;
use App\Services\ProductLogoService;
use App\Services\ApiReceivedImageService;
use App\Services\ApiReceivedMetadataService;
use App\Services\ApiReceivedPriceService;
use App\Services\MediaStorageService;
use App\Services\SiteSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ApiContentController extends Controller
{
    public function __construct(private SiteSettingsService $settings) {}

    public function index(Request $request): View
    {
        $query = ApiReceivedItem::with('source')
            ->where('status', ApiReceivedItem::STATUS_PENDING)
            ->latest();

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('brand')) {
            $this->applyBrandFilter($query, $request->string('brand')->toString());
        }

        $items = $query->paginate(24)->withQueryString();

        return view('admin.content.index', [
            'items' => $items,
            'dateFrom' => $request->query('date_from'),
            'dateTo' => $request->query('date_to'),
            'brand' => $request->query('brand'),
            'brands' => app(ApiReceivedBrandService::class)->activeBrandNames(),
            'pendingCount' => ApiReceivedItem::where('status', ApiReceivedItem::STATUS_PENDING)->count(),
            'logoUrl' => $this->settings->apiLogoUrl(),
            'logoScale' => Schema::hasColumn((new SiteSetting)->getTable(), 'api_logo_scale')
                ? (SiteSetting::current()->api_logo_scale ?: 28)
                : 28,
            'stats' => [
                'pending' => ApiReceivedItem::where('status', ApiReceivedItem::STATUS_PENDING)->count(),
                'processed' => ApiReceivedItem::where('status', ApiReceivedItem::STATUS_PROCESSED)->count(),
                'imported' => ApiReceivedItem::where('status', ApiReceivedItem::STATUS_IMPORTED)->count(),
                'brands' => count(app(ApiReceivedBrandService::class)->activeBrandNames()),
            ],
        ]);
    }

    public function show(ApiReceivedItem $item, ApiReceivedMetadataService $metadata): View|RedirectResponse
    {
        if (! $item->isPending()) {
            return redirect()->route('admin.processed.show', $item);
        }

        if (ApiReceivedItem::hasBrandVendorColumns()) {
            try {
                $metadata->syncItem($item);
                $item->refresh();
            } catch (\Throwable) {
                // Metadata sync should not block viewing the item.
            }
        }

        $item->load('source');

        return view('admin.content.show', [
            'item' => $item,
            'categories' => Category::where('is_active', true)->orderBy('sort_order')->get(),
            'logoUrl' => $this->settings->apiLogoUrl(),
            'logoScale' => Schema::hasColumn((new SiteSetting)->getTable(), 'api_logo_scale')
                ? (SiteSetting::current()->api_logo_scale ?: 28)
                : 28,
        ]);
    }

    public function repairImages(ApiReceivedImageService $images, ApiReceivedPriceService $prices, ApiReceivedMetadataService $metadata, ApiReceivedBrandService $brands, ApiReceivedCategoryService $categories): RedirectResponse
    {
        $fixed = 0;
        $failed = 0;
        $pricesSynced = 0;
        $metadataSynced = 0;

        $items = ApiReceivedItem::with(['source', 'product'])
            ->where('status', ApiReceivedItem::STATUS_PENDING)
            ->get();

        foreach ($items as $item) {
            if ($images->repairItem($item)) {
                $fixed++;
            } else {
                $failed++;
            }
        }

        $priceItems = ApiReceivedItem::with('product')
            ->whereIn('status', [
                ApiReceivedItem::STATUS_PENDING,
                ApiReceivedItem::STATUS_PROCESSED,
                ApiReceivedItem::STATUS_IMPORTED,
            ])
            ->get();

        foreach ($priceItems as $item) {
            if ($prices->syncItem($item)) {
                $pricesSynced++;
            }

            if ($metadata->syncItem($item)) {
                $metadataSynced++;
            }
        }

        $message = "{$fixed} image(s) re-downloaded.";
        if ($pricesSynced > 0) {
            $message .= " {$pricesSynced} price(s) synced from API payload.";
        }
        if ($metadataSynced > 0) {
            $message .= " {$metadataSynced} brand/vendor field(s) synced from API payload.";
        }

        $brandsSynced = $brands->syncFromReceivedItems();
        if ($brandsSynced > 0) {
            $message .= " {$brandsSynced} brand link(s) saved.";
        }

        $categoriesSynced = $categories->syncFromReceivedItems();
        if ($categoriesSynced > 0) {
            $message .= " {$categoriesSynced} category link(s) saved to Categories.";
        }
        if ($failed > 0) {
            $message .= " {$failed} item(s) still missing images — set the sender Site URL in Content API Settings.";
        }

        return back()->with($fixed > 0 || $pricesSynced > 0 || $metadataSynced > 0 ? 'success' : 'warning', $message);
    }

    public function update(Request $request, ApiReceivedItem $item): RedirectResponse
    {
        if (! $item->isPending()) {
            return back()->with('error', 'Only pending content can be edited here.');
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

        if (filled($validated['brand'] ?? null)) {
            app(ApiReceivedBrandService::class)->attachToItem($item->fresh(), $validated['brand']);
        }

        if (filled($validated['category_name'] ?? null)) {
            app(ApiReceivedCategoryService::class)->attachToItem($item->fresh(), $validated['category_name']);
        }

        return back()->with('success', 'Content updated.');
    }

    public function process(ApiReceivedItem $item, ProductLogoService $logoService, ApiReceivedImageService $images, ApiReceivedPriceService $prices): RedirectResponse
    {
        if (! $item->canProcess()) {
            return back()->with('error', 'This item cannot be processed.');
        }

        if (! function_exists('imagecreatetruecolor')) {
            return back()->with('error', 'PHP GD extension is required for logo processing. Enable GD on the server.');
        }

        try {
            $prices->applyToItem($item);
            $item->refresh();
        } catch (\Throwable) {
            // Price sync should not block image processing.
        }

        if (! SiteSetting::current()->api_logo) {
            return back()->with('error', 'Upload a logo on the Content page before processing.');
        }

        $item->load('source');
        $imagePath = $images->resolveProcessableImagePath($item);

        if (! $imagePath) {
            return back()->with('error', 'Could not load product image. Set sender Site URL in Content API Settings, then click Re-download Images.');
        }

        try {
            $storedPath = $images->persistLocalImage($item, $imagePath);
            $processedPath = $logoService->applyLogoToReceivedItem($storedPath);
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first() ?: 'Failed to apply logo.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage() ?: 'Failed to apply logo to this image.');
        }

        $images->recordProcessedImage($item, $processedPath, $storedPath);

        return redirect()
            ->route('admin.processed.show', $item)
            ->with('success', 'Logo applied. Review in Processed and click Go Live.');
    }

    public function processBatch(Request $request, ProductLogoService $logoService, ApiReceivedImageService $images, ApiReceivedPriceService $prices): RedirectResponse
    {
        if (! function_exists('imagecreatetruecolor')) {
            return redirect()
                ->route('admin.content.index')
                ->with('error', 'PHP GD extension is required for logo processing. Enable GD on the server.');
        }

        if (! SiteSetting::current()->api_logo) {
            return redirect()
                ->route('admin.content.index')
                ->with('error', 'Upload a logo on the Content page before processing selected images.');
        }

        $validated = $request->validate([
            'select_all' => 'sometimes|boolean',
            'filter_brand' => 'nullable|string|max:100',
            'filter_date_from' => 'nullable|date',
            'filter_date_to' => 'nullable|date',
            'items' => 'required_without:select_all|array|min:1',
            'items.*' => 'integer|exists:api_received_items,id',
        ]);

        @set_time_limit(300);

        $itemIds = $request->boolean('select_all')
            ? $this->pendingBatchQuery($request)->pluck('id')
            : collect($validated['items'] ?? []);

        $processed = 0;
        $missingImage = 0;
        $failed = 0;

        foreach ($itemIds as $id) {
            try {
                $item = ApiReceivedItem::with('source')->find($id);

                if (! $item || ! $item->isPending()) {
                    $failed++;

                    continue;
                }

                try {
                    $prices->applyToItem($item);
                    $item->refresh();
                } catch (\Throwable) {
                    // Price sync should not block image processing.
                }

                $imagePath = $images->resolveProcessableImagePath($item);

                if (! $imagePath) {
                    $missingImage++;

                    continue;
                }

                try {
                    $storedPath = $images->persistLocalImage($item, $imagePath);
                    $processedPath = $logoService->applyLogoToReceivedItem($storedPath);
                    $images->recordProcessedImage($item, $processedPath, $storedPath);
                    $processed++;
                } catch (\Throwable) {
                    $failed++;
                }
            } catch (\Throwable) {
                $failed++;
            }
        }

        if ($processed > 0) {
            $message = "{$processed} item(s) processed.";
            if ($missingImage > 0 || $failed > 0) {
                $message .= " {$missingImage} missing image(s), {$failed} failed.";
            }

            return redirect()
                ->route('admin.processed.index')
                ->with('success', $message);
        }

        $message = 'No items were processed.';
        if ($missingImage > 0) {
            $message .= " {$missingImage} item(s) have no downloadable image — set sender Site URL in Content API Settings and click Re-download Images.";
        }
        if ($failed > 0) {
            $message .= " {$failed} item(s) failed during logo processing.";
        }

        return redirect()
            ->route('admin.content.index')
            ->with('error', $message);
    }

    public function reject(Request $request, ApiReceivedItem $item): RedirectResponse
    {
        if (! $item->isPending()) {
            return back()->with('error', 'Only pending content can be rejected.');
        }

        $validated = $request->validate(['admin_notes' => 'nullable|string|max:500']);

        $item->update([
            'status' => ApiReceivedItem::STATUS_REJECTED,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'admin_notes' => $validated['admin_notes'] ?? null,
        ]);

        return redirect()->route('admin.content.index')->with('success', 'Item rejected.');
    }

    public function destroy(ApiReceivedItem $item, MediaStorageService $media): RedirectResponse
    {
        if (! $item->isPending()) {
            return back()->with('error', 'Only pending received content can be deleted.');
        }

        $this->deletePendingItem($item, $media);

        return redirect()
            ->route('admin.content.index')
            ->with('success', 'Received item deleted.');
    }

    public function reimport(
        ApiReceivedItem $item,
        ApiReceivedImageService $images,
        ApiReceivedPriceService $prices,
        ApiReceivedMetadataService $metadata,
        MediaStorageService $media
    ): RedirectResponse {
        if ($item->isImported()) {
            return back()->with('error', 'Remove this product from Live first, then use Re-import.');
        }

        if (! $item->isPending() && ! $item->isProcessed()) {
            return back()->with('error', 'Only pending or processed items can be re-imported.');
        }

        $item->load('source');

        $originalImage = null;
        foreach ($item->images ?? [] as $path) {
            if (filled($path) && $path !== $item->processed_image) {
                $originalImage = $path;
                break;
            }
        }

        if ($item->processed_image) {
            $media->delete($item->processed_image);
        }

        $attributes = [
            'processed_image' => null,
            'status' => ApiReceivedItem::STATUS_PENDING,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'product_id' => null,
        ];

        if ($originalImage) {
            $attributes['image'] = $originalImage;
            $attributes['images'] = array_values(array_unique(array_filter([$originalImage])));
        }

        if (ApiReceivedItem::hasProcessedImageBlobColumn()) {
            $attributes['processed_image_blob'] = null;
        }

        $item->update($attributes);
        $item->refresh();

        $repaired = $images->repairItem($item);
        $item->refresh();

        try {
            $prices->syncItem($item);
        } catch (\Throwable) {
            // Price sync should not block re-import.
        }

        try {
            $metadata->syncItem($item);
        } catch (\Throwable) {
            // Metadata sync should not block re-import.
        }

        $message = $repaired
            ? 'Item re-imported. Image refreshed from API payload — process again when ready.'
            : 'Item reset to Import list. Image could not be re-downloaded — check sender Site URL, then try Re-import again.';

        return redirect()
            ->route('admin.content.index')
            ->with($repaired ? 'success' : 'warning', $message);
    }

    public function destroyBatch(Request $request, MediaStorageService $media): RedirectResponse
    {
        $validated = $request->validate([
            'select_all' => 'sometimes|boolean',
            'filter_brand' => 'nullable|string|max:100',
            'filter_date_from' => 'nullable|date',
            'filter_date_to' => 'nullable|date',
            'items' => 'required_without:select_all|array|min:1',
            'items.*' => 'integer|exists:api_received_items,id',
        ]);

        $itemIds = $request->boolean('select_all')
            ? $this->pendingBatchQuery($request)->pluck('id')
            : collect($validated['items'] ?? []);

        $deleted = 0;

        foreach ($itemIds as $id) {
            $item = ApiReceivedItem::query()->find($id);

            if (! $item || ! $item->isPending()) {
                continue;
            }

            $this->deletePendingItem($item, $media);
            $deleted++;
        }

        return redirect()
            ->route('admin.content.index')
            ->with('success', "{$deleted} received item(s) deleted.");
    }

    private function pendingBatchQuery(Request $request)
    {
        $query = ApiReceivedItem::query()
            ->where('status', ApiReceivedItem::STATUS_PENDING);

        if ($request->filled('filter_brand')) {
            $this->applyBrandFilter($query, $request->string('filter_brand')->toString());
        }

        if ($request->filled('filter_date_from')) {
            $query->whereDate('created_at', '>=', $request->filter_date_from);
        }

        if ($request->filled('filter_date_to')) {
            $query->whereDate('created_at', '<=', $request->filter_date_to);
        }

        return $query;
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

    /** @return array<int, string> */
    private function listFromString(string $value): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $value))));
    }

    private function deletePendingItem(ApiReceivedItem $item, MediaStorageService $media): void
    {
        $paths = array_values(array_unique(array_filter([
            $item->image,
            $item->processed_image,
            ...($item->images ?? []),
        ])));

        foreach ($paths as $path) {
            $media->delete($path);
        }

        $item->delete();
    }
}
