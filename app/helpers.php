<?php

use App\Support\Money;

if (! function_exists('money')) {
    function money(float|int|string|null $amount, ?int $decimals = null): string
    {
        return Money::format($amount, $decimals);
    }
}

if (! function_exists('money_or_free')) {
    function money_or_free(float|int|string|null $amount, ?int $decimals = null): string
    {
        return Money::formatOrFree($amount, $decimals);
    }
}

if (! function_exists('amount_in_words')) {
    function amount_in_words(float|int|string|null $amount, ?string $currencyName = null): string
    {
        return \App\Support\AmountInWords::format($amount, $currencyName);
    }
}

if (! function_exists('bank_charge_amount')) {
    function bank_charge_amount(float $baseAmount, float $chargePercent): float
    {
        if ($chargePercent <= 0 || $baseAmount <= 0) {
            return 0.0;
        }

        $rawCharge = round($baseAmount * $chargePercent / 100, 2);

        return floor($rawCharge / 5) * 5;
    }
}
