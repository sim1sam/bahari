<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\ApiProductImportService;
use App\Services\MediaStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private MediaStorageService $media,
        private ApiProductImportService $importer,
    ) {}

    public function index(): View
    {
        return view('admin.products.index', [
            'products' => Product::query()
                ->with(['category', 'apiReceivedItem'])
                ->where(function ($query) {
                    $query->liveFromApi()->orWhere('is_manual', true);
                })
                ->latest()
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.products.form', [
            'product' => new Product(['is_active' => true, 'is_manual' => true, 'stock' => 0]),
            'categories' => Category::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $product = Product::create($this->validateProduct($request));
        $product->update($this->syncImages($request, $product));

        return redirect()->route('admin.products.index')->with('success', 'Product created and published on storefront.');
    }

    public function edit(Product $product): View
    {
        abort_unless($product->isManualProduct() || $product->isLiveFromApi(), 404);

        return view('admin.products.form', [
            'product' => $product,
            'categories' => Category::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'isApiProduct' => $product->isLiveFromApi() && ! $product->isManualProduct(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->isManualProduct() || $product->isLiveFromApi(), 404);

        if ($product->isLiveFromApi() && ! $product->isManualProduct()) {
            $product->update($this->validateApiProduct($request, $product));

            return redirect()->route('admin.products.index')->with('success', 'API product updated.');
        }

        $product->update($this->validateProduct($request, $product));
        $product->update($this->syncImages($request, $product));

        return redirect()->route('admin.products.index')->with('success', 'Product updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->isLiveFromApi() && ! $product->isManualProduct()) {
            $this->importer->unpublish($product);
        } else {
            $this->deleteProductMedia($product);
            $product->delete();
        }

        return redirect()->route('admin.products.index')->with('success', 'Product removed from storefront.');
    }

    public function destroyBatch(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'products' => 'required|array|min:1',
            'products.*' => 'integer|exists:products,id',
        ]);

        $deleted = 0;

        foreach ($validated['products'] as $id) {
            $product = Product::query()->find($id);

            if (! $product) {
                continue;
            }

            if ($product->isLiveFromApi() && ! $product->isManualProduct()) {
                $this->importer->unpublish($product);
            } else {
                $this->deleteProductMedia($product);
                $product->delete();
            }

            $deleted++;
        }

        return redirect()
            ->route('admin.products.index')
            ->with('success', "{$deleted} product(s) removed from storefront.");
    }

    private function validateProduct(Request $request, ?Product $product = null): array
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'slug' => 'nullable|string|max:100|unique:products,slug,'.($product?->id ?? 'NULL'),
            'name' => 'required|string|max:200',
            'brand' => 'nullable|string|max:120',
            'purchase_price' => 'nullable|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0|gte:price',
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'sizes' => 'nullable|string',
            'colors' => 'nullable|string',
            'stock' => 'required|integer|min:0',
            'badge' => 'nullable|string|max:30',
            'badge_variant' => 'nullable|string|max:30',
            'rating' => 'nullable|numeric|min:0|max:5',
            'is_featured' => 'boolean',
            'is_new_arrival' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['sizes'] = $this->toArray($validated['sizes'] ?? '');
        $validated['colors'] = $this->toArray($validated['colors'] ?? '');
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_new_arrival'] = $request->boolean('is_new_arrival');
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_manual'] = true;
        $validated['badge_variant'] = $validated['badge_variant'] ?: 'default';

        if (empty($validated['slug']) && ! empty($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        if (empty($validated['slug'])) {
            $validated['slug'] = 'product-'.Str::random(8);
        }

        if (empty($validated['badge']) && ! empty($validated['original_price']) && $validated['original_price'] > $validated['price']) {
            $validated['badge'] = 'Sale';
            $validated['badge_variant'] = 'sale';
        }

        return $validated;
    }

    private function validateApiProduct(Request $request, Product $product): array
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'is_featured' => 'boolean',
            'is_new_arrival' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_new_arrival'] = $request->boolean('is_new_arrival');
        $validated['is_active'] = $request->boolean('is_active', true);

        return $validated;
    }

    private function syncImages(Request $request, Product $product): array
    {
        $thumbnail = $this->resolveImageField(
            $request,
            'thumbnail',
            'thumbnail_url',
            'remove_thumbnail',
            $product->image
        );

        $gallery = collect($product->images ?? [])
            ->map(fn ($path) => $this->media->storedPath($path))
            ->filter()
            ->values()
            ->all();

        foreach ((array) $request->input('remove_gallery', []) as $path) {
            $stored = $this->media->storedPath($path);

            if ($stored && in_array($stored, $gallery, true)) {
                $this->media->delete($stored);
                $gallery = array_values(array_filter($gallery, fn ($item) => $item !== $stored));
            }
        }

        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $index => $file) {
                if ($file && $file->isValid()) {
                    $gallery[] = $this->media->storeUpload($file, 'products', field: "gallery.{$index}");
                }
            }
        }

        foreach ((array) $request->input('gallery_urls', []) as $index => $url) {
            if (filled($url)) {
                $gallery[] = $this->media->storeFromUrl($url, 'products', field: "gallery_urls.{$index}");
            }
        }

        $gallery = array_values(array_unique(array_filter($gallery)));

        if ($thumbnail && ! in_array($thumbnail, $gallery, true)) {
            array_unshift($gallery, $thumbnail);
        }

        if (! $thumbnail && $gallery !== []) {
            $thumbnail = $gallery[0];
        }

        return [
            'image' => $thumbnail,
            'images' => $gallery,
        ];
    }

    private function resolveImageField(
        Request $request,
        string $fileKey,
        string $urlKey,
        string $removeKey,
        ?string $current
    ): ?string {
        if ($request->boolean($removeKey)) {
            $this->media->delete($current);

            return null;
        }

        $file = $request->file($fileKey);

        if ($file && $file->getError() !== UPLOAD_ERR_NO_FILE) {
            return $this->media->storeUpload($file, 'products', $current, $fileKey);
        }

        if ($request->filled($urlKey)) {
            return $this->media->storeFromUrl($request->input($urlKey), 'products', $current, $urlKey);
        }

        return $this->media->storedPath($current);
    }

    private function deleteProductMedia(Product $product): void
    {
        $paths = collect($product->images ?? [])
            ->push($product->image)
            ->map(fn ($path) => $this->media->storedPath($path))
            ->filter()
            ->unique()
            ->all();

        foreach ($paths as $path) {
            $this->media->delete($path);
        }
    }

    private function toArray(string $value): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $value))));
    }
}
