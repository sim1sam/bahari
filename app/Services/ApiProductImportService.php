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
        $imagePath = $this->publishProcessedImage($item->processed_image ?: $item->image);
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
        $imagePath = $this->publishProcessedImage($item->processed_image ?: $item->image);
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

        $product->delete();

        if ($item?->isImported()) {
            $item->update([
                'status' => ApiReceivedItem::STATUS_PROCESSED,
                'product_id' => null,
                'reviewed_by' => null,
                'reviewed_at' => null,
            ]);
        }
    }

    public function publishProcessedImage(?string $image): ?string
    {
        // Store relative disk path only — Product::imageUrl() resolves via /media or /storage at request time.
        return $this->copyImageToProducts($image);
    }

    private function copyImageToProducts(?string $image): ?string
    {
        if (! $image) {
            return null;
        }

        $stored = $this->media->storedPath($image);

        if ($stored && Storage::disk('public')->exists($stored)) {
            if (str_starts_with($stored, 'products/')) {
                return $stored;
            }

            $extension = strtolower(pathinfo($stored, PATHINFO_EXTENSION)) ?: 'jpg';
            $extension = in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)
                ? ($extension === 'jpeg' ? 'jpg' : $extension)
                : 'jpg';
            $destination = 'products/'.Str::uuid().'.'.$extension;

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
