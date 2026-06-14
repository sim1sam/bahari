<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiReceivedItem;
use App\Models\Category;
use App\Services\ProductLogoService;
use App\Services\SiteSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApiContentController extends Controller
{
    public function __construct(private SiteSettingsService $settings) {}

    public function index(Request $request): View
    {
        $query = ApiReceivedItem::with('source')
            ->where('status', ApiReceivedItem::STATUS_PENDING)
            ->latest();

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        return view('admin.content.index', [
            'items' => $query->paginate(24)->withQueryString(),
            'date' => $request->query('date'),
            'pendingCount' => ApiReceivedItem::where('status', ApiReceivedItem::STATUS_PENDING)->count(),
            'logoUrl' => $this->settings->apiLogoUrl(),
        ]);
    }

    public function show(ApiReceivedItem $item): View|RedirectResponse
    {
        if (! $item->isPending()) {
            return redirect()->route('admin.processed.show', $item);
        }

        $item->load('source');

        return view('admin.content.show', [
            'item' => $item,
            'categories' => Category::where('is_active', true)->orderBy('sort_order')->get(),
            'logoUrl' => $this->settings->apiLogoUrl(),
        ]);
    }

    public function uploadLogo(Request $request, ProductLogoService $logoService): RedirectResponse
    {
        $request->validate(['logo' => 'required|image|max:2048']);

        $logoService->storeSiteLogo($request->file('logo'));
        $this->settings->clearCache();

        return back()->with('success', 'Logo uploaded. Select images and click Process.');
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

        return back()->with('success', 'Content updated.');
    }

    public function process(ApiReceivedItem $item, ProductLogoService $logoService): RedirectResponse
    {
        if (! $item->canProcess()) {
            return back()->with('error', 'This item cannot be processed.');
        }

        if (! $item->image) {
            return back()->with('error', 'No image to process.');
        }

        $processedPath = $logoService->applyLogoToReceivedItem($item->image);

        $item->update([
            'processed_image' => $processedPath,
            'status' => ApiReceivedItem::STATUS_PROCESSED,
        ]);

        return redirect()
            ->route('admin.processed.show', $item)
            ->with('success', 'Logo applied. Review in Processed and click Go Live.');
    }

    public function processBatch(Request $request, ProductLogoService $logoService): RedirectResponse
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*' => 'integer|exists:api_received_items,id',
        ]);

        $processed = 0;
        $errors = [];

        foreach ($validated['items'] as $id) {
            $item = ApiReceivedItem::find($id);
            if (! $item || ! $item->isPending() || ! $item->image) {
                $errors[] = $id;

                continue;
            }

            try {
                $processedPath = $logoService->applyLogoToReceivedItem($item->image);
                $item->update([
                    'processed_image' => $processedPath,
                    'status' => ApiReceivedItem::STATUS_PROCESSED,
                ]);
                $processed++;
            } catch (\Throwable) {
                $errors[] = $id;
            }
        }

        $message = "{$processed} item(s) processed.";
        if ($errors) {
            $message .= ' Some items failed — upload logo or open item to process individually.';
        }

        return redirect()
            ->route('admin.processed.index')
            ->with($processed ? 'success' : 'error', $message);
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

    /** @return array<int, string> */
    private function listFromString(string $value): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $value))));
    }
}
