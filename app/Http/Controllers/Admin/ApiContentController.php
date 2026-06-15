<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiReceivedItem;
use App\Models\Category;
use App\Models\SiteSetting;
use App\Services\ProductLogoService;
use App\Services\ApiReceivedImageService;
use App\Services\ApiReceivedPriceService;
use App\Services\SiteSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        return view('admin.content.index', [
            'items' => $query->paginate(24)->withQueryString(),
            'dateFrom' => $request->query('date_from'),
            'dateTo' => $request->query('date_to'),
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

    public function repairImages(ApiReceivedImageService $images, ApiReceivedPriceService $prices): RedirectResponse
    {
        $fixed = 0;
        $failed = 0;
        $pricesSynced = 0;

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
        }

        $message = "{$fixed} image(s) re-downloaded.";
        if ($pricesSynced > 0) {
            $message .= " {$pricesSynced} price(s) synced from API payload.";
        }
        if ($failed > 0) {
            $message .= " {$failed} item(s) still missing images — set the sender Site URL in API Settings.";
        }

        return back()->with($fixed > 0 || $pricesSynced > 0 ? 'success' : 'warning', $message);
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
            'sku' => $validated['sku'] ?? null,
            'slug' => $validated['slug'] ?? null,
            'price' => $validated['price'],
            'original_price' => filled($validated['original_price'] ?? null) ? $validated['original_price'] : null,
            'description' => $validated['description'] ?? null,
            'category_name' => $validated['category_name'] ?? null,
            'sizes' => $this->listFromString($validated['sizes'] ?? ''),
            'colors' => $this->listFromString($validated['colors'] ?? ''),
            'badge' => filled($validated['badge'] ?? null) ? $validated['badge'] : null,
            'badge_variant' => filled($validated['badge_variant'] ?? null) ? $validated['badge_variant'] : null,
            'rating' => filled($validated['rating'] ?? null) ? $validated['rating'] : null,
        ]);

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
            return back()->with('error', 'Could not load product image. Set sender Site URL in API Settings, then click Re-download Images.');
        }

        $images->persistLocalImage($item, $imagePath);

        try {
            $processedPath = $logoService->applyLogoToReceivedItem($imagePath);
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first() ?: 'Failed to apply logo.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage() ?: 'Failed to apply logo to this image.');
        }

        $item->update([
            'processed_image' => $processedPath,
            'status' => ApiReceivedItem::STATUS_PROCESSED,
        ]);

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
            'items' => 'required|array|min:1',
            'items.*' => 'integer|exists:api_received_items,id',
        ]);

        @set_time_limit(300);

        $processed = 0;
        $missingImage = 0;
        $failed = 0;

        foreach ($validated['items'] as $id) {
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

                $images->persistLocalImage($item, $imagePath);

                $processedPath = $logoService->applyLogoToReceivedItem($imagePath);
                $item->update([
                    'processed_image' => $processedPath,
                    'status' => ApiReceivedItem::STATUS_PROCESSED,
                ]);
                $processed++;
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
            $message .= " {$missingImage} item(s) have no downloadable image — set sender Site URL in API Settings and click Re-download Images.";
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

    /** @return array<int, string> */
    private function listFromString(string $value): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $value))));
    }
}
