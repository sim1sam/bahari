<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiReceivedItem;
use App\Models\Category;
use App\Services\ApiProductImportService;
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

    public function live(ApiReceivedItem $item, ApiProductImportService $importer): RedirectResponse
    {
        if (! $item->canPublish()) {
            return back()->with('error', 'This item is not ready to go live.');
        }

        $product = $item->product_id
            ? $importer->syncProduct($item, $item->product)
            : $importer->import($item);

        return redirect()
            ->route('admin.processed.show', $item)
            ->with('success', 'Product is now live on the storefront: '.$product->name);
    }

    public function liveBatch(Request $request, ApiProductImportService $importer): RedirectResponse
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*' => 'integer|exists:api_received_items,id',
        ]);

        $published = 0;

        foreach ($validated['items'] as $id) {
            $item = ApiReceivedItem::find($id);
            if (! $item || ! $item->canPublish()) {
                continue;
            }

            if ($item->product_id) {
                $importer->syncProduct($item, $item->product);
            } else {
                $importer->import($item);
            }
            $published++;
        }

        return redirect()
            ->route('admin.processed.live')
            ->with('success', "{$published} product(s) are now live on the storefront.");
    }

    /** @return array<int, string> */
    private function listFromString(string $value): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $value))));
    }
}
