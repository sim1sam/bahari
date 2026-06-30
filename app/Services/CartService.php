<?php

namespace App\Services;

use App\Support\ShippingZone;
use Illuminate\Support\Facades\Auth;

class CartService
{
    private const SESSION_KEY = 'cart';

    private const COUPON_KEY = 'cart_coupon';

    private const SHIPPING_ZONE_KEY = 'cart_shipping_zone';

    public function __construct(
        private ProductCatalog $catalog,
        private CouponService $coupons,
        private SiteSettingsService $settings,
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

    public function shippingZone(): string
    {
        $zone = session(self::SHIPPING_ZONE_KEY);

        return ShippingZone::isValid($zone) ? $zone : ShippingZone::INSIDE_DHAKA;
    }

    public function setShippingZone(string $zone): bool
    {
        if (! ShippingZone::isValid($zone)) {
            return false;
        }

        session([self::SHIPPING_ZONE_KEY => $zone]);

        return true;
    }

    public function shipping(): float
    {
        return $this->settings->calculateShipping($this->subtotal(), $this->shippingZone());
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

        if (($product['is_manual'] ?? false) && ! ($product['in_stock'] ?? true)) {
            return false;
        }

        $maxStock = ($product['is_manual'] ?? false)
            ? max(0, (int) ($product['stock'] ?? 0))
            : null;

        $cart = $this->items();
        $key = $this->itemKey($slug, $size);
        $currentQty = $cart[$key]['quantity'] ?? 0;
        $newQty = $currentQty + $quantity;

        if ($maxStock !== null && $newQty > $maxStock) {
            return false;
        }

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
        session()->forget([self::SESSION_KEY, self::COUPON_KEY, self::SHIPPING_ZONE_KEY]);
    }

    private function itemKey(string $slug, ?string $size): string
    {
        return $slug.'|'.($size ?? '');
    }
}
