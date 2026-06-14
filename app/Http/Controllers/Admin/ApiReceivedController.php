<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiReceivedItem;
use App\Models\ApiSource;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Services\MediaStorageService;
use App\Services\SiteSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ApiReceivedController extends Controller
{
    public function __construct(private SiteSettingsService $settings) {}

    public function index(Request $request): View
    {
        $status = $request->query('status', 'pending');

        $query = ApiReceivedItem::with('source')->latest();

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
            'receiveUrl' => $this->settings->apiReceiveUrl(),
            'webhookBaseUrl' => $siteSettings->api_webhook_url,
            'isLocalWebhook' => $this->settings->apiWebhookUsesLocalHost(),
            'appUrl' => config('app.url'),
        ]);
    }

    public function updateWebhook(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'api_webhook_url' => 'nullable|url|max:500',
        ]);

        $settings = SiteSetting::current();
        $settings->api_webhook_url = $validated['api_webhook_url'] ?: null;
        $settings->save();
        $this->settings->clearCache();

        return back()->with('success', 'Public webhook URL saved. Use this URL on the sender site (kolkata2dhaka).');
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

        return back()->with('success', 'API source added. Use the receive URL on the sending site.');
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

    public function approve(ApiReceivedItem $item, MediaStorageService $media): RedirectResponse
    {
        if (! $item->isPending()) {
            return back()->with('error', 'This item has already been processed.');
        }

        DB::transaction(function () use ($item, $media) {
            $slug = $this->uniqueSlug($item->sku ?: $item->title);

            $image = $item->image;
            if ($image && str_starts_with($image, 'http')) {
                try {
                    $image = $media->storeFromUrl($image, 'products');
                } catch (\Throwable) {
                    // keep URL as-is
                }
            } elseif ($image && ! str_starts_with($image, 'http')) {
                $image = $media->url($image);
            }

            $product = Product::create([
                'slug' => $slug,
                'name' => $item->title,
                'price' => $item->price,
                'image' => $image,
                'images' => $image ? [$image] : [],
                'description' => $item->description,
                'is_active' => true,
            ]);

            $item->update([
                'status' => ApiReceivedItem::STATUS_IMPORTED,
                'product_id' => $product->id,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);
        });

        return back()->with('success', 'Item imported as product.');
    }

    public function reject(Request $request, ApiReceivedItem $item): RedirectResponse
    {
        if (! $item->isPending()) {
            return back()->with('error', 'This item has already been processed.');
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

    private function uniqueSlug(string $base): string
    {
        $slug = Str::slug($base) ?: 'product';
        $original = $slug;
        $counter = 1;

        while (Product::where('slug', $slug)->exists()) {
            $slug = $original.'-'.$counter++;
        }

        return $slug;
    }
}
