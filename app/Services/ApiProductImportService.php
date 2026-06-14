<?php

namespace App\Services;

use App\Models\ApiReceivedItem;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;

class ApiProductImportService
{
    public function __construct(private MediaStorageService $media) {}

    public function import(ApiReceivedItem $item): Product
    {
        if ($item->product_id && $item->product) {
            return $this->syncProduct($item, $item->product);
        }

        $slug = $this->uniqueSlug($item->slug ?: $item->sku ?: $item->title);
        $imageUrl = $this->resolveImageForProduct($item->processed_image ?: $item->image);
        $images = $this->resolveImagesForProduct($item, $imageUrl);

        $product = Product::create([
            'category_id' => $this->resolveCategoryId($item->category_name),
            'slug' => $slug,
            'name' => $item->title,
            'price' => $item->price,
            'original_price' => $item->original_price,
            'image' => $imageUrl,
            'images' => $images,
            'description' => $item->description ?: 'Imported via API.',
            'sizes' => $item->sizes ?: ['XS', 'S', 'M', 'L', 'XL'],
            'colors' => $item->colors ?: ['Black', 'White', 'Rose'],
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
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return $product;
    }

    public function syncProduct(ApiReceivedItem $item, Product $product): Product
    {
        $imageUrl = $this->resolveImageForProduct($item->processed_image ?: $item->image);

        $product->update([
            'name' => $item->title,
            'price' => $item->price,
            'original_price' => $item->original_price,
            'image' => $imageUrl ?: $product->image,
            'images' => $this->resolveImagesForProduct($item, $imageUrl) ?: $product->images,
            'description' => $item->description ?: $product->description,
            'sizes' => $item->sizes ?: $product->sizes,
            'colors' => $item->colors ?: $product->colors,
            'badge' => $item->badge ?: $product->badge,
            'badge_variant' => $item->badge_variant ?: $product->badge_variant,
            'rating' => $item->rating ?? $product->rating,
            'category_id' => $this->resolveCategoryId($item->category_name) ?: $product->category_id,
            'is_active' => true,
            'is_new_arrival' => true,
        ]);

        $item->update([
            'status' => ApiReceivedItem::STATUS_IMPORTED,
            'product_id' => $product->id,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return $product->fresh();
    }

    public function resolveImageForProduct(?string $image): ?string
    {
        if (! $image) {
            return null;
        }

        if ($this->media->isExternal($image)) {
            try {
                $path = $this->media->storeFromUrl($image, 'products');

                return $this->media->url($path);
            } catch (\Throwable) {
                return $image;
            }
        }

        return $this->media->url($image) ?? $image;
    }

    /** @return array<int, string|null> */
    private function resolveImagesForProduct(ApiReceivedItem $item, ?string $primary): array
    {
        $images = collect($item->images ?? [])
            ->map(fn ($img) => $this->resolveImageForProduct($img))
            ->filter()
            ->values()
            ->all();

        if ($primary && ! in_array($primary, $images, true)) {
            array_unshift($images, $primary);
        }

        return $images ?: ($primary ? [$primary] : []);
    }

    private function resolveCategoryId(?string $name): ?int
    {
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
