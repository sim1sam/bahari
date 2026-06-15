<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiReceivedItem;
use App\Models\Category;
use App\Models\Product;
use App\Services\ApiProductImportService;
use App\Services\MediaStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApiProcessedController extends Controller
{
    public function index(Request $request): View
    {
        $query = ApiReceivedItem::with(['source', 'product'])
            ->where('status', ApiReceivedItem::STATUS_PROCESSED)
            ->latest();

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        return view('admin.processed.index', [
            'items' => $query->paginate(20)->withQueryString(),
            'date' => $request->query('date'),
            'processedCount' => ApiReceivedItem::where('status', ApiReceivedItem::STATUS_PROCESSED)->count(),
            'liveCount' => ApiReceivedItem::where('status', ApiReceivedItem::STATUS_IMPORTED)->count(),
            'categories' => Category::where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function liveIndex(Request $request): View
    {
        $query = ApiReceivedItem::with(['source', 'product'])
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

    public function show(ApiReceivedItem $item): View|RedirectResponse
    {
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
            'sizes' => 'nullable|string|max:255',
            'colors' => 'nullable|string|max:255',
            'badge' => 'nullable|string|max:30',
            'badge_variant' => 'nullable|string|max:30',
            'rating' => 'nullable|numeric|min:0|max:5',
        ]);

        $item->update([
            'title' => $validated['title'],
            'sku' => $validated['sku'],
            'slug' => $validated['slug'],
            'price' => $validated['price'],
            'original_price' => $validated['original_price'],
            'description' => $validated['description'],
            'category_name' => $validated['category_name'],
            'sizes' => $this->listFromString($validated['sizes'] ?? ''),
            'colors' => $this->listFromString($validated['colors'] ?? ''),
            'badge' => $validated['badge'],
            'badge_variant' => $validated['badge_variant'],
            'rating' => $validated['rating'],
        ]);

        return back()->with('success', 'Product information updated.');
    }

    public function live(Request $request, ApiReceivedItem $item, ApiProductImportService $importer): RedirectResponse
    {
        if (! $item->canPublish()) {
            return back()->with('error', 'This item is not ready to go live.');
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

    public function liveBatch(Request $request, ApiProductImportService $importer): RedirectResponse
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*' => 'integer|exists:api_received_items,id',
            'category_id' => 'required|exists:categories,id',
        ]);

        $published = 0;
        $categoryId = (int) $validated['category_id'];

        foreach ($validated['items'] as $id) {
            $item = ApiReceivedItem::find($id);
            if (! $item || ! $item->canPublish()) {
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

        return redirect()
            ->route('admin.processed.live')
            ->with('success', "{$published} product(s) are now live under {$categoryName}.");
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
