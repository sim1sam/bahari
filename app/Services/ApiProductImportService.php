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
    ) {}

    public function import(ApiReceivedItem $item, ?int $categoryId = null): Product
    {
        if ($item->product_id && $item->product) {
            return $this->syncProduct($item, $item->product, $categoryId);
        }

        $slug = $this->uniqueSlug($item->slug ?: $item->sku ?: $item->title);
        $imageUrl = $this->publishProcessedImage($item->processed_image ?: $item->image);
        $pricing = $this->prices->resolve($item);

        $product = Product::create([
            'category_id' => $this->resolveCategoryId($categoryId, $item->category_name),
            'slug' => $slug,
            'name' => $item->title,
            'price' => $pricing['price'],
            'original_price' => $pricing['original_price'],
            'purchase_price' => $pricing['purchase_price'],
            'image' => $imageUrl,
            'images' => $imageUrl ? [$imageUrl] : [],
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
        $imageUrl = $this->publishProcessedImage($item->processed_image ?: $item->image);
        $pricing = $this->prices->resolve($item);

        $product->update([
            'name' => $item->title,
            'price' => $pricing['price'],
            'original_price' => $pricing['original_price'],
            'purchase_price' => $pricing['purchase_price'] ?? $product->purchase_price,
            'image' => $imageUrl ?: $product->image,
            'images' => $imageUrl ? [$imageUrl] : $product->images,
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
        $path = $this->copyImageToProducts($image);

        if (! $path) {
            return null;
        }

        $relative = $this->media->url($path);

        return $relative ? url($relative) : null;
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
        if ($categoryId) {
            return Category::query()
                ->where('is_active', true)
                ->where('id', $categoryId)
                ->value('id');
        }

        if (! $name) {
            return $this->defaultCategoryId();
        }

        $category = Category::query()
            ->where('is_active', true)
            ->where(function ($q) use ($name) {
                $q->where('name', $name)->orWhere('slug', Str::slug($name));
            })
            ->first();

        return $category?->id ?? $this->defaultCategoryId();
    }

    private function defaultCategoryId(): ?int
    {
        return Category::query()
            ->where('is_active', true)
            ->where('slug', 'new-in')
            ->value('id')
            ?? Category::query()->where('is_active', true)->orderBy('sort_order')->value('id');
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
