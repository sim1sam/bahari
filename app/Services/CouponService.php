<?php

namespace App\Services;

class CouponService
{
    private const COUPONS = [
        'LUXE10' => ['type' => 'percent', 'value' => 10, 'label' => '10% off your order'],
        'LUXE20' => ['type' => 'percent', 'value' => 20, 'label' => '20% off your order'],
        'SAVE15' => ['type' => 'fixed', 'value' => 15, 'label' => '$15 off your order'],
        'FASHION' => ['type' => 'percent', 'value' => 15, 'label' => '15% off fashion items'],
    ];

    public function find(string $code): ?array
    {
        $code = strtoupper(trim($code));

        if (! isset(self::COUPONS[$code])) {
            return null;
        }

        return array_merge(self::COUPONS[$code], ['code' => $code]);
    }

    public function discount(string $code, float $subtotal): float
    {
        $coupon = $this->find($code);

        if (! $coupon || $subtotal <= 0) {
            return 0;
        }

        $amount = match ($coupon['type']) {
            'percent' => $subtotal * ($coupon['value'] / 100),
            'fixed' => $coupon['value'],
            default => 0,
        };

        return round(min($amount, $subtotal), 2);
    }
}
