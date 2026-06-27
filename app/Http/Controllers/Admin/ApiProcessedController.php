<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiReceivedItem;
use App\Models\Category;
use App\Models\Product;
use App\Services\ApiProductImportService;
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
    public function index(Request $request): View
    {
        $query = ApiReceivedItem::queryForLists()
            ->with(['source', 'product'])
            ->where('status', ApiReceivedItem::STATUS_PROCESSED)
            ->latest();

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        if ($request->filled('brand')) {
            $this->applyBrandFilter($query, $request->string('brand')->toString());
        }

        return view('admin.processed.index', [
            'items' => $query->paginate(20)->withQueryString(),
            'date' => $request->query('date'),
            'brand' => $request->query('brand'),
            'brands' => $this->processedBrands(),
            'processedCount' => ApiReceivedItem::where('status', ApiReceivedItem::STATUS_PROCESSED)->count(),
            'liveCount' => ApiReceivedItem::where('status', ApiReceivedItem::STATUS_IMPORTED)->count(),
            'categories' => Category::where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function liveIndex(Request $request): View
    {
        $query = ApiReceivedItem::queryForLists()
            ->with(['source', 'product'])
            ->where('status', ApiReceivedItem::STATUS_IMPORTED)
            ->latest();

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        return view('admin.processed.live', [
            'items' => $query->paginate(20)->withQueryString(),
            'date' => $request->query('date'),
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
            ]);
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
            'category_id' => 'required|exists:categories,id',
        ]);

        $product = $item->product_id
            ? $importer->syncProduct($item, $item->product, (int) $validated['category_id'])
            : $importer->import($item, (int) $validated['category_id']);

        return redirect()
            ->route('admin.processed.show', $item)
            ->with('success', 'Product is now live under '.$product->category?->name.'.');
    }

    public function liveBatch(Request $request, ApiProductImportService $importer, ApiReceivedPriceService $prices): RedirectResponse
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*' => 'integer|exists:api_received_items,id',
            'category_id' => 'required|exists:categories,id',
        ]);

        $published = 0;
        $skippedZeroPrice = 0;
        $categoryId = (int) $validated['category_id'];

        foreach ($validated['items'] as $id) {
            $item = ApiReceivedItem::find($id);
            if (! $item || ! $item->canPublish()) {
                continue;
            }

            $prices->applyToItem($item);
            $item->refresh();

            if ((float) $item->price <= 0) {
                $skippedZeroPrice++;

                continue;
            }

            if ($item->product_id) {
                $importer->syncProduct($item, $item->product, $categoryId);
            } else {
                $importer->import($item, $categoryId);
            }
            $published++;
        }

        $categoryName = Category::find($categoryId)?->name ?? 'selected category';
        $message = "{$published} product(s) are now live under {$categoryName}.";

        if ($skippedZeroPrice > 0) {
            $message .= " {$skippedZeroPrice} item(s) skipped because price is 0. Send price_bdt or enter price manually.";
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
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*' => 'integer|exists:api_received_items,id',
        ]);

        $deleted = 0;

        foreach ($validated['items'] as $id) {
            $item = ApiReceivedItem::find($id);

            if (! $item || ! $item->isProcessed()) {
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
            ->whereDoesntHave('apiReceivedItem', function ($query) {
                $query->where('status', ApiReceivedItem::STATUS_IMPORTED);
            })
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
            'items' => 'required|array|min:1',
            'items.*' => 'integer|exists:api_received_items,id',
            'layout' => 'required|in:flat,brand',
        ]);

        $items = ApiReceivedItem::query()
            ->whereIn('id', $validated['items'])
            ->where('status', ApiReceivedItem::STATUS_PROCESSED)
            ->get();

        if ($items->isEmpty()) {
            return back()->with('error', 'No processed items found for download.');
        }

        if ($items->count() === 1 && $validated['layout'] === 'flat') {
            return $this->downloadImage($items->first(), $downloader);
        }

        try {
            $zipPath = $downloader->createZip($items, $validated['layout']);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage() ?: 'Could not prepare download archive.');
        }

        $suffix = $validated['layout'] === 'brand' ? 'by-brand' : 'selected';

        return response()
            ->download($zipPath, 'processed-images-'.$suffix.'-'.now()->format('Y-m-d-His').'.zip')
            ->deleteFileAfterSend(true);
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

    /** @return Collection<int, string> */
    private function processedBrands(): Collection
    {
        return ApiReceivedItem::query()
            ->where('status', ApiReceivedItem::STATUS_PROCESSED)
            ->get(['id', 'brand', 'payload'])
            ->map(fn (ApiReceivedItem $item) => $item->brand)
            ->filter()
            ->unique()
            ->sort()
            ->values();
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
