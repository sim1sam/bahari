<?php

namespace App\Services;

use App\Models\ApiReceivedItem;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ApiProductImportService
{
    public function __construct(
        private MediaStorageService $media,
        private ApiReceivedPriceService $prices,
        private ApiReceivedCategoryService $categories,
    ) {}

    public function import(ApiReceivedItem $item, ?int $categoryId = null): Product
    {
        if ($item->product_id && $item->product) {
            return $this->syncProduct($item, $item->product, $categoryId);
        }

        $slug = $this->uniqueSlug($item->slug ?: $item->sku ?: $item->title);
        $imagePath = $this->publishProcessedImage($item);
        $pricing = $this->prices->resolve($item);

        $product = Product::create([
            'category_id' => $this->resolveCategoryId($categoryId, $item->category_name),
            'slug' => $slug,
            'name' => $item->title,
            'brand' => filled($item->brand) ? trim((string) $item->brand) : null,
            'price' => $pricing['price'],
            'original_price' => $pricing['original_price'],
            'purchase_price' => $pricing['purchase_price'],
            'image' => $imagePath,
            'images' => $imagePath ? [$imagePath] : [],
            'description' => $item->description ?: 'Imported via API.',
            'sizes' => $item->sizes ?: [],
            'colors' => $item->colors ?: [],
            'stock' => 100,
            'badge' => $item->badge ?: 'New',
            'badge_variant' => $item->badge_variant ?: 'new',
            'rating' => $item->rating ?? 4.5,
            'is_active' => true,
            'is_new_arrival' => true,
            'is_featured' => true,
        ]);

        $item->update([
            'status' => ApiReceivedItem::STATUS_IMPORTED,
            'product_id' => $product->id,
            'price' => $pricing['price'],
            'original_price' => $pricing['original_price'],
            'purchase_price' => $pricing['purchase_price'],
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return $product;
    }

    public function syncProduct(ApiReceivedItem $item, Product $product, ?int $categoryId = null): Product
    {
        $imagePath = $this->publishProcessedImage($item);
        $pricing = $this->prices->resolve($item);

        $product->update([
            'name' => $item->title,
            'brand' => filled($item->brand) ? trim((string) $item->brand) : $product->brand,
            'price' => $pricing['price'],
            'original_price' => $pricing['original_price'],
            'purchase_price' => $pricing['purchase_price'] ?? $product->purchase_price,
            'image' => $imagePath ?: $product->image,
            'images' => $imagePath ? [$imagePath] : $product->images,
            'description' => $item->description ?: $product->description,
            'sizes' => $item->sizes ?: [],
            'colors' => $item->colors ?: [],
            'stock' => (int) $product->stock > 0 ? (int) $product->stock : 100,
            'badge' => $item->badge ?: $product->badge,
            'badge_variant' => $item->badge_variant ?: $product->badge_variant,
            'rating' => $item->rating ?? $product->rating,
            'category_id' => $this->resolveCategoryId($categoryId, $item->category_name) ?: $product->category_id,
            'is_active' => true,
            'is_new_arrival' => true,
        ]);

        $item->update([
            'status' => ApiReceivedItem::STATUS_IMPORTED,
            'product_id' => $product->id,
            'price' => $pricing['price'],
            'original_price' => $pricing['original_price'],
            'purchase_price' => $pricing['purchase_price'],
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return $product->fresh();
    }

    public function unpublish(Product $product): void
    {
        $item = $product->apiReceivedItem;

        if ($item) {
            $this->preserveProcessedImageFromProduct($item, $product);
        }

        $this->deleteProductsDirectoryMedia($product);

        $product->delete();

        if ($item?->isImported() || ($item && $item->product_id)) {
            $item->refresh();
            $item->update([
                'status' => ApiReceivedItem::STATUS_PROCESSED,
                'product_id' => null,
                'reviewed_by' => null,
                'reviewed_at' => null,
            ]);
        }
    }

    public function publishProcessedImage(ApiReceivedItem|string|null $itemOrPath): ?string
    {
        // Store relative disk path only — Product::imageUrl() resolves via /media or /storage at request time.
        if ($itemOrPath instanceof ApiReceivedItem) {
            return $this->copyImageToProducts($this->resolvePublishSource($itemOrPath));
        }

        return $this->copyImageToProducts($itemOrPath);
    }

    private function resolvePublishSource(ApiReceivedItem $item): ?string
    {
        $item->loadMissing('source');

        foreach ($this->candidateImagePaths($item) as $path) {
            $stored = $this->media->storedPath($path);

            if ($stored && Storage::disk('public')->exists($stored)) {
                return $stored;
            }

            if ($this->media->isExternal($path)) {
                return $path;
            }
        }

        try {
            if (app(ApiReceivedImageService::class)->repairItem($item)) {
                $item->refresh();

                foreach ($this->candidateImagePaths($item) as $path) {
                    $stored = $this->media->storedPath($path);

                    if ($stored && Storage::disk('public')->exists($stored)) {
                        return $stored;
                    }

                    if ($this->media->isExternal($path)) {
                        return $path;
                    }
                }
            }
        } catch (\Throwable) {
            // Fall through to payload URL candidates.
        }

        $payload = $item->payloadData();
        $baseUrl = $item->source?->base_url;

        foreach (['image_url', 'image', 'thumbnail', 'photo'] as $key) {
            $candidate = $payload[$key] ?? null;

            if (! is_string($candidate) || trim($candidate) === '') {
                continue;
            }

            if ($this->media->isExternal($candidate)) {
                return $candidate;
            }

            if (filled($baseUrl) && str_starts_with($candidate, '/')) {
                return rtrim($baseUrl, '/').$candidate;
            }
        }

        return null;
    }

    /** @return array<int, string> */
    private function candidateImagePaths(ApiReceivedItem $item): array
    {
        return array_values(array_unique(array_filter([
            $item->processed_image,
            $item->image,
            ...($item->images ?? []),
        ], fn ($path) => filled($path))));
    }

    private function preserveProcessedImageFromProduct(ApiReceivedItem $item, Product $product): void
    {
        $processed = $this->media->storedPath($item->processed_image);

        if ($processed && Storage::disk('public')->exists($processed) && ! str_starts_with($processed, 'products/')) {
            return;
        }

        $source = null;

        foreach (array_filter([$product->image, ...($product->images ?? []), $item->image, ...($item->images ?? [])]) as $path) {
            $stored = $this->media->storedPath($path);

            if ($stored && Storage::disk('public')->exists($stored)) {
                $source = $stored;
                break;
            }
        }

        if (! $source) {
            return;
        }

        $extension = strtolower(pathinfo($source, PATHINFO_EXTENSION)) ?: 'jpg';
        $extension = in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)
            ? ($extension === 'jpeg' ? 'jpg' : $extension)
            : 'jpg';

        Storage::disk('public')->makeDirectory('api-received/processed');
        $destination = 'api-received/processed/'.Str::uuid().'.'.$extension;
        Storage::disk('public')->put($destination, Storage::disk('public')->get($source));

        $gallery = array_values(array_unique(array_filter([
            $destination,
            ...array_filter($item->images ?? [], fn ($path) => ! str_starts_with((string) $this->media->storedPath($path), 'products/')),
        ])));

        $item->update([
            'processed_image' => $destination,
            'image' => $destination,
            'images' => $gallery ?: [$destination],
        ]);
    }

    private function deleteProductsDirectoryMedia(Product $product): void
    {
        $paths = collect($product->images ?? [])
            ->push($product->image)
            ->map(fn ($path) => $this->media->storedPath($path))
            ->filter(fn ($path) => $path && str_starts_with($path, 'products/'))
            ->unique()
            ->all();

        foreach ($paths as $path) {
            $this->media->delete($path);
        }
    }

    private function copyImageToProducts(?string $image): ?string
    {
        if (! $image) {
            return null;
        }

        $stored = $this->media->storedPath($image);

        if ($stored && Storage::disk('public')->exists($stored)) {
            // Always copy into a fresh products/ file so unpublish can delete
            // product media without destroying the processed source image.
            $extension = strtolower(pathinfo($stored, PATHINFO_EXTENSION)) ?: 'jpg';
            $extension = in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)
                ? ($extension === 'jpeg' ? 'jpg' : $extension)
                : 'jpg';
            $destination = 'products/'.Str::uuid().'.'.$extension;

            Storage::disk('public')->makeDirectory('products');
            Storage::disk('public')->put(
                $destination,
                Storage::disk('public')->get($stored)
            );

            return $destination;
        }

        if ($this->media->isExternal($image)) {
            try {
                return $this->media->storeFromUrl($image, 'products');
            } catch (\Throwable) {
                return null;
            }
        }

        return null;
    }

    private function resolveCategoryId(?int $categoryId, ?string $name): ?int
    {
        return $this->categories->resolveCategoryId($categoryId, $name);
    }

    private function defaultCategoryId(): ?int
    {
        return $this->categories->defaultCategoryId();
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
