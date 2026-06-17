<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

class CartService
{
    private const SESSION_KEY = 'cart';

    private const COUPON_KEY = 'cart_coupon';

    public function __construct(
        private ProductCatalog $catalog,
        private CouponService $coupons,
    ) {}

    public function items(): array
    {
        return session(self::SESSION_KEY, []);
    }

    public function count(): int
    {
        return collect($this->items())->sum('quantity');
    }

    public function subtotal(): float
    {
        return collect($this->items())->sum(fn ($item) => $item['price'] * $item['quantity']);
    }

    public function shipping(): float
    {
        $threshold = (float) config('currency.free_shipping_threshold', 2000);
        $fee = (float) config('currency.shipping_fee', 120);

        return $this->subtotal() >= $threshold || $this->subtotal() == 0 ? 0 : $fee;
    }

    public function coupon(): ?array
    {
        $code = session(self::COUPON_KEY);

        if (! $code) {
            return null;
        }

        $coupon = $this->coupons->find($code, Auth::user());

        if (! $coupon) {
            session()->forget(self::COUPON_KEY);

            return null;
        }

        $discount = $this->discount();

        return array_merge($coupon, ['discount' => $discount]);
    }

    public function discount(): float
    {
        $code = session(self::COUPON_KEY);

        if (! $code) {
            return 0;
        }

        return $this->coupons->discount($code, $this->subtotal(), Auth::user());
    }

    public function total(): float
    {
        return max(0, $this->subtotal() - $this->discount()) + $this->shipping();
    }

    public function applyCoupon(string $code): ?string
    {
        if ($error = $this->coupons->validationError($code, Auth::user())) {
            return $error;
        }

        session([self::COUPON_KEY => strtoupper(trim($code))]);

        return null;
    }

    public function removeCoupon(): void
    {
        session()->forget(self::COUPON_KEY);
    }

    public function add(string $slug, int $quantity = 1, ?string $size = null, ?string $color = null): bool
    {
        $product = $this->catalog->find($slug);

        if (! $product) {
            return false;
        }

        $cart = $this->items();
        $key = $this->itemKey($slug, $size);

        if (isset($cart[$key])) {
            $cart[$key]['quantity'] += $quantity;
        } else {
            $cart[$key] = [
                'key' => $key,
                'slug' => $product['slug'],
                'name' => $product['name'],
                'price' => $product['price'],
                'image' => $product['image'],
                'size' => $size ?: ($product['sizes'][0] ?? null),
                'color' => $color ?: ($product['colors'][0] ?? null),
                'quantity' => max(1, $quantity),
            ];
        }

        session([self::SESSION_KEY => $cart]);

        return true;
    }

    public function update(string $key, int $quantity, ?string $size = null): bool
    {
        $cart = $this->items();

        if (! isset($cart[$key])) {
            return false;
        }

        if ($quantity < 1) {
            unset($cart[$key]);
            session([self::SESSION_KEY => $cart]);

            return true;
        }

        if ($size !== null && $size !== $cart[$key]['size']) {
            $item = $cart[$key];
            unset($cart[$key]);
            $newKey = $this->itemKey($item['slug'], $size);

            if (isset($cart[$newKey])) {
                $cart[$newKey]['quantity'] += $quantity;
            } else {
                $item['key'] = $newKey;
                $item['size'] = $size;
                $item['quantity'] = $quantity;
                $cart[$newKey] = $item;
            }
        } else {
            $cart[$key]['quantity'] = $quantity;
        }

        session([self::SESSION_KEY => $cart]);

        return true;
    }

    public function remove(string $key): bool
    {
        $cart = $this->items();

        if (! isset($cart[$key])) {
            return false;
        }

        unset($cart[$key]);
        session([self::SESSION_KEY => $cart]);

        return true;
    }

    public function clear(): void
    {
        session()->forget([self::SESSION_KEY, self::COUPON_KEY]);
    }

    private function itemKey(string $slug, ?string $size): string
    {
        return $slug.'|'.($size ?? '');
    }
}
