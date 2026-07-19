<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Support\Facades\Auth;

class WishlistService
{
    private const SESSION_KEY = 'wishlist';

    private bool $syncedSession = false;

    /**
     * @return array<string, array<string, mixed>>
     */
    public function items(): array
    {
        if (! Auth::check()) {
            return [];
        }

        $this->syncSessionToDatabase();

        return Wishlist::query()
            ->where('user_id', Auth::id())
            ->with('product.category')
            ->latest()
            ->get()
            ->mapWithKeys(function (Wishlist $wishlist) {
                $item = $this->toItemArray($wishlist);

                return $item ? [$item['slug'] => $item] : [];
            })
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public function slugs(): array
    {
        if (! Auth::check()) {
            return [];
        }

        $this->syncSessionToDatabase();

        return Wishlist::query()
            ->where('user_id', Auth::id())
            ->whereHas('product')
            ->with('product:id,slug')
            ->get()
            ->pluck('product.slug')
            ->filter()
            ->values()
            ->all();
    }

    public function count(): int
    {
        if (! Auth::check()) {
            return 0;
        }

        $this->syncSessionToDatabase();

        return Wishlist::query()->where('user_id', Auth::id())->count();
    }

    public function has(string $slug): bool
    {
        if (! Auth::check()) {
            return false;
        }

        $this->syncSessionToDatabase();

        return Wishlist::query()
            ->where('user_id', Auth::id())
            ->whereHas('product', fn ($query) => $query->where('slug', $slug))
            ->exists();
    }

    public function toggle(string $slug): bool
    {
        if ($this->has($slug)) {
            $this->remove($slug);

            return false;
        }

        return $this->add($slug);
    }

    public function add(string $slug): bool
    {
        if (! Auth::check()) {
            return false;
        }

        $product = Product::query()->where('slug', $slug)->first();

        if (! $product) {
            return false;
        }

        Wishlist::query()->firstOrCreate([
            'user_id' => Auth::id(),
            'product_id' => $product->id,
        ]);

        $this->forgetSessionSlug($slug);

        return true;
    }

    public function remove(string $slug): void
    {
        if (! Auth::check()) {
            return;
        }

        Wishlist::query()
            ->where('user_id', Auth::id())
            ->whereHas('product', fn ($query) => $query->where('slug', $slug))
            ->delete();

        $this->forgetSessionSlug($slug);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function products(): array
    {
        return collect($this->items())
            ->sortByDesc('added_at')
            ->values()
            ->all();
    }

    private function syncSessionToDatabase(): void
    {
        if ($this->syncedSession || ! Auth::check()) {
            return;
        }

        $this->syncedSession = true;

        $sessionItems = session(self::SESSION_KEY, []);

        if ($sessionItems === []) {
            return;
        }

        $slugs = array_keys($sessionItems);
        $products = Product::query()->whereIn('slug', $slugs)->get()->keyBy('slug');

        foreach ($slugs as $slug) {
            $product = $products->get($slug);

            if (! $product) {
                continue;
            }

            Wishlist::query()->firstOrCreate([
                'user_id' => Auth::id(),
                'product_id' => $product->id,
            ]);
        }

        session()->forget(self::SESSION_KEY);
    }

    private function forgetSessionSlug(string $slug): void
    {
        $items = session(self::SESSION_KEY, []);

        if (! array_key_exists($slug, $items)) {
            return;
        }

        unset($items[$slug]);
        session([self::SESSION_KEY => $items]);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function toItemArray(Wishlist $wishlist): ?array
    {
        $product = $wishlist->product;

        if (! $product) {
            return null;
        }

        $catalog = $product->toCatalogArray();

        return [
            'slug' => $product->slug,
            'name' => $catalog['name'] ?? $product->name,
            'price' => (float) ($catalog['price'] ?? $product->price),
            'original_price' => $catalog['original_price'] ?? null,
            'image' => $catalog['image'] ?? null,
            'brand' => $catalog['brand'] ?? $product->brand,
            'category' => $catalog['category'] ?? null,
            'added_at' => $wishlist->created_at?->toIso8601String(),
        ];
    }
}
