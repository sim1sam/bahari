<?php

namespace App\Support;

class AmountInWords
{
    private const ONES = [
        '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
        'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen',
        'Seventeen', 'Eighteen', 'Nineteen',
    ];

    private const TENS = [
        '', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety',
    ];

    public static function format(float|int|string|null $amount, ?string $currencyName = null): string
    {
        $amount = round((float) ($amount ?? 0), 2);
        $currencyName = $currencyName ?: config('currency.name', 'Taka');

        $whole = (int) floor($amount);
        $fraction = (int) round(($amount - $whole) * 100);

        $words = self::convertWhole($whole);

        if ($fraction > 0) {
            $words .= ' and '.self::convertWhole($fraction).' Paisa';
        }

        $words = trim($words) ?: 'Zero';

        return $words.' '.$currencyName.' Only';
    }

    private static function convertWhole(int $number): string
    {
        if ($number === 0) {
            return '';
        }

        if ($number < 20) {
            return self::ONES[$number];
        }

        if ($number < 100) {
            return trim(self::TENS[intdiv($number, 10)].' '.self::ONES[$number % 10]);
        }

        if ($number < 1000) {
            return trim(self::ONES[intdiv($number, 100)].' Hundred '.self::convertWhole($number % 100));
        }

        if ($number < 100000) {
            return trim(self::convertWhole(intdiv($number, 1000)).' Thousand '.self::convertWhole($number % 1000));
        }

        if ($number < 10000000) {
            return trim(self::convertWhole(intdiv($number, 100000)).' Lakh '.self::convertWhole($number % 100000));
        }

        return trim(self::convertWhole(intdiv($number, 10000000)).' Crore '.self::convertWhole($number % 10000000));
    }
}
