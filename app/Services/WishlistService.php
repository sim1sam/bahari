<?php

namespace App\Services;

class WishlistService
{
    private const SESSION_KEY = 'wishlist';

    public function __construct(
        private ProductCatalog $catalog,
    ) {}

    /**
     * @return array<string, array<string, mixed>>
     */
    public function items(): array
    {
        return session(self::SESSION_KEY, []);
    }

    /**
     * @return array<int, string>
     */
    public function slugs(): array
    {
        return array_keys($this->items());
    }

    public function count(): int
    {
        return count($this->items());
    }

    public function has(string $slug): bool
    {
        return array_key_exists($slug, $this->items());
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
        $product = $this->catalog->find($slug);

        if (! $product) {
            return false;
        }

        $items = $this->items();
        $items[$slug] = [
            'slug' => $slug,
            'name' => $product['name'] ?? $slug,
            'price' => (float) ($product['price'] ?? 0),
            'original_price' => $product['original_price'] ?? null,
            'image' => $product['image'] ?? null,
            'brand' => $product['brand'] ?? null,
            'category' => $product['category'] ?? null,
            'added_at' => now()->toIso8601String(),
        ];

        session([self::SESSION_KEY => $items]);

        return true;
    }

    public function remove(string $slug): void
    {
        $items = $this->items();
        unset($items[$slug]);
        session([self::SESSION_KEY => $items]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function products(): array
    {
        return collect($this->items())
            ->map(function (array $item) {
                $live = $this->catalog->find($item['slug']);

                if (! $live) {
                    return $item;
                }

                return array_merge($item, [
                    'name' => $live['name'] ?? $item['name'],
                    'price' => (float) ($live['price'] ?? $item['price']),
                    'original_price' => $live['original_price'] ?? $item['original_price'] ?? null,
                    'image' => $live['image'] ?? $item['image'] ?? null,
                    'brand' => $live['brand'] ?? $item['brand'] ?? null,
                    'category' => $live['category'] ?? $item['category'] ?? null,
                ]);
            })
            ->sortByDesc('added_at')
            ->values()
            ->all();
    }
}
