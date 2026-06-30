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
