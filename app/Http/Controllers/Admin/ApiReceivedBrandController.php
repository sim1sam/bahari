<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesHomepageImages;
use App\Http\Controllers\Controller;
use App\Models\ApiReceivedBrand;
use App\Services\ApiReceivedBrandService;
use App\Services\MediaStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ApiReceivedBrandController extends Controller
{
    use HandlesHomepageImages;

    public function __construct(private MediaStorageService $media) {}

    public function index(): View
    {
        $allBrands = ApiReceivedBrand::query()
            ->withCount('receivedItems')
            ->orderBy('name')
            ->get();

        return view('admin.api-brands.index', [
            'brands' => ApiReceivedBrand::query()
                ->withCount('receivedItems')
                ->orderBy('name')
                ->paginate(20),
            'stats' => [
                'total' => $allBrands->count(),
                'active' => $allBrands->where('is_active', true)->count(),
                'with_items' => $allBrands->where('received_items_count', '>', 0)->count(),
                'received_items' => (int) $allBrands->sum('received_items_count'),
                'with_images' => $allBrands->filter(fn (ApiReceivedBrand $brand) => filled($brand->image))->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.api-brands.form', ['brand' => new ApiReceivedBrand(['is_active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        ApiReceivedBrand::create($this->validateBrand($request));

        return redirect()
            ->route('admin.api-brands.index')
            ->with('success', 'Brand created.');
    }

    public function edit(ApiReceivedBrand $apiBrand): View
    {
        return view('admin.api-brands.form', ['brand' => $apiBrand]);
    }

    public function update(Request $request, ApiReceivedBrand $apiBrand): RedirectResponse
    {
        $apiBrand->update($this->validateBrand($request, $apiBrand));

        return redirect()
            ->route('admin.api-brands.index')
            ->with('success', 'Brand updated.');
    }

    public function destroy(ApiReceivedBrand $apiBrand): RedirectResponse
    {
        $apiBrand->receivedItems()->update(['api_received_brand_id' => null]);
        $this->media->delete($apiBrand->image);
        $apiBrand->delete();

        return redirect()
            ->route('admin.api-brands.index')
            ->with('success', 'Brand deleted.');
    }

    public function syncFromReceived(ApiReceivedBrandService $brands): RedirectResponse
    {
        $synced = $brands->syncFromReceivedItems();

        return redirect()
            ->route('admin.api-brands.index')
            ->with('success', "Synced {$synced} received item(s) to saved brands.");
    }

    /** @return array<string, mixed> */
    private function validateBrand(Request $request, ?ApiReceivedBrand $brand = null): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:api_received_brands,name,'.($brand?->id ?? 'NULL'),
            'slug' => 'nullable|string|max:100|unique:api_received_brands,slug,'.($brand?->id ?? 'NULL'),
            'notes' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'image' => [
                'nullable',
                'image',
                'mimes:png,jpg,jpeg,webp',
                'max:5120',
            ],
            'image_url' => 'nullable|url|max:500',
            'remove_image' => 'boolean',
        ]);

        $slug = trim((string) ($validated['slug'] ?? ''));
        $validated['slug'] = $slug !== '' ? $slug : Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);

        if ($validated['slug'] === '') {
            $validated['slug'] = 'brand-'.Str::lower(Str::random(6));
        }

        $validated['image'] = $this->resolveImage(
            $request,
            $this->media,
            'api-brands',
            $brand?->image,
        );

        unset($validated['image_url'], $validated['remove_image']);

        return $validated;
    }
}
