<?php

namespace App\Support;

class Money
{
    public static function symbol(): string
    {
        return config('currency.symbol', '৳');
    }

    public static function code(): string
    {
        return config('currency.code', 'BDT');
    }

    public static function format(float|int|string|null $amount, ?int $decimals = null): string
    {
        $amount = (float) ($amount ?? 0);
        $decimals = $decimals ?? (int) config('currency.decimals', 2);

        return self::symbol().number_format($amount, $decimals);
    }

    public static function formatOrFree(float|int|string|null $amount, ?int $decimals = null): string
    {
        $amount = (float) ($amount ?? 0);

        return $amount > 0 ? self::format($amount, $decimals) : 'Free';
    }
}
