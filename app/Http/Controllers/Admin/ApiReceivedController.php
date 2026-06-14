<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiReceivedItem;
use App\Models\ApiSource;
use App\Models\Category;
use App\Models\SiteSetting;
use App\Services\ApiProductImportService;
use App\Services\ProductLogoService;
use App\Services\SiteSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApiReceivedController extends Controller
{
    public function __construct(private SiteSettingsService $settings) {}

    public function index(Request $request): View
    {
        $status = $request->query('status', 'pending');

        $query = ApiReceivedItem::with(['source', 'product'])->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $siteSettings = SiteSetting::current();

        return view('admin.api-received.index', [
            'sources' => ApiSource::latest()->get(),
            'items' => $query->paginate(20)->withQueryString(),
            'status' => $status,
            'date' => $request->query('date'),
            'pendingCount' => ApiReceivedItem::where('status', ApiReceivedItem::STATUS_PENDING)->count(),
            'processedCount' => ApiReceivedItem::where('status', ApiReceivedItem::STATUS_PROCESSED)->count(),
            'receiveUrl' => $this->settings->apiReceiveUrl(),
            'webhookBaseUrl' => $siteSettings->api_webhook_url,
            'isLocalWebhook' => $this->settings->apiWebhookUsesLocalHost(),
            'appUrl' => config('app.url'),
            'siteSettings' => $siteSettings,
            'logoUrl' => $this->settings->apiLogoUrl(),
        ]);
    }

    public function show(ApiReceivedItem $item): View
    {
        $item->load(['source', 'product', 'reviewer']);

        return view('admin.api-received.show', [
            'item' => $item,
            'categories' => Category::where('is_active', true)->orderBy('sort_order')->get(),
            'logoUrl' => $this->settings->apiLogoUrl(),
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'api_webhook_url' => 'nullable|url|max:500',
        ]);

        $settings = SiteSetting::current();
        $settings->api_webhook_url = $validated['api_webhook_url'] ?: null;
        $settings->save();
        $this->settings->clearCache();

        return back()->with('success', 'API settings saved.');
    }

    public function uploadLogo(Request $request, ProductLogoService $logoService): RedirectResponse
    {
        $request->validate([
            'logo' => 'required|image|max:2048',
        ]);

        $logoService->storeSiteLogo($request->file('logo'));
        $this->settings->clearCache();

        return back()->with('success', 'Site logo uploaded. Use Process on received items to apply it.');
    }

    public function updateItem(Request $request, ApiReceivedItem $item): RedirectResponse
    {
        if ($item->isImported()) {
            return back()->with('error', 'Published items cannot be edited here.');
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
            'status' => ApiReceivedItem::STATUS_PENDING,
            'processed_image' => null,
        ]);

        return back()->with('success', 'Item updated. Process again to apply logo.');
    }

    public function process(ApiReceivedItem $item, ProductLogoService $logoService): RedirectResponse
    {
        if (! $item->canProcess()) {
            return back()->with('error', 'This item cannot be processed.');
        }

        if (! $item->image) {
            return back()->with('error', 'No product image to process.');
        }

        $processedPath = $logoService->applyLogoToReceivedItem($item->image);

        $item->update([
            'processed_image' => $processedPath,
            'status' => ApiReceivedItem::STATUS_PROCESSED,
        ]);

        return back()->with('success', 'Logo applied. Review and publish to show on site.');
    }

    public function publish(ApiReceivedItem $item, ApiProductImportService $importer): RedirectResponse
    {
        if (! $item->canPublish()) {
            return back()->with('error', 'Process the item (apply logo) before publishing.');
        }

        $product = $item->product_id
            ? $importer->syncProduct($item, $item->product)
            : $importer->import($item);

        return redirect()
            ->route('admin.api-received.show', $item)
            ->with('success', 'Product published on site: '.$product->name);
    }

    public function storeSource(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'api_key' => 'required|string|max:64',
            'api_token' => 'required|string|max:128',
        ]);

        ApiSource::create([
            'name' => $validated['name'],
            'api_key' => $validated['api_key'],
            'api_token' => $validated['api_token'],
            'is_active' => true,
        ]);

        return back()->with('success', 'API source added.');
    }

    public function generateSource(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $credentials = ApiSource::generateCredentials();

        ApiSource::create([
            'name' => $validated['name'],
            'api_key' => $credentials['api_key'],
            'api_token' => $credentials['api_token'],
            'is_active' => true,
        ]);

        return back()->with([
            'success' => 'API credentials generated for "'.$validated['name'].'".',
            'generated_credentials' => $credentials,
        ]);
    }

    public function destroySource(ApiSource $source): RedirectResponse
    {
        $source->delete();

        return back()->with('success', 'API source removed.');
    }

    public function reject(Request $request, ApiReceivedItem $item): RedirectResponse
    {
        if ($item->isImported()) {
            return back()->with('error', 'Published items cannot be rejected.');
        }

        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:500',
        ]);

        $item->update([
            'status' => ApiReceivedItem::STATUS_REJECTED,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'admin_notes' => $validated['admin_notes'] ?? null,
        ]);

        return back()->with('success', 'Item rejected.');
    }

    /** @return array<int, string> */
    private function listFromString(string $value): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $value))));
    }
}
