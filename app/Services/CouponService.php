<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\User;

class CouponService
{
    public function find(string $code, ?User $user = null): ?array
    {
        $coupon = $this->coupon($code);

        if (! $coupon || $this->validationErrorForCoupon($coupon, $user)) {
            return null;
        }

        return $coupon->toCartPayload();
    }

    public function discount(string $code, float $subtotal, ?User $user = null): float
    {
        $coupon = $this->coupon($code);

        if (! $coupon || $subtotal <= 0 || $this->validationErrorForCoupon($coupon, $user)) {
            return 0;
        }

        return $coupon->discountAmount($subtotal);
    }

    public function validationError(string $code, ?User $user = null): ?string
    {
        $coupon = $this->coupon($code);

        if (! $coupon) {
            return 'Invalid coupon code.';
        }

        return $this->validationErrorForCoupon($coupon, $user);
    }

    private function coupon(string $code): ?Coupon
    {
        return Coupon::query()
            ->with('customers:id')
            ->where('code', strtoupper(trim($code)))
            ->first();
    }

    private function validationErrorForCoupon(Coupon $coupon, ?User $user = null): ?string
    {
        $now = now();

        if (! $coupon->is_active) {
            return 'This coupon is not active.';
        }

        if ($coupon->starts_at && $coupon->starts_at->isFuture()) {
            return 'This coupon is not active yet.';
        }

        if ($coupon->ends_at && $coupon->ends_at->lt($now)) {
            return 'This coupon has expired.';
        }

        if (! $coupon->isPublic()) {
            if (! $user || ! $coupon->customers->contains('id', $user->id)) {
                return 'This coupon is not available for your account.';
            }
        }

        if ($coupon->max_uses && $coupon->totalUses() >= $coupon->max_uses) {
            return 'This coupon has reached its usage limit.';
        }

        if ($coupon->per_customer_limit && $coupon->usesByCustomer($user) >= $coupon->per_customer_limit) {
            return 'You have already used this coupon.';
        }

        return null;
    }
}
