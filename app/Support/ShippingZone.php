<?php

namespace App\Support;

class ShippingZone
{
    public const INSIDE_DHAKA = 'inside_dhaka';

    public const OUTSIDE_DHAKA = 'outside_dhaka';

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            self::INSIDE_DHAKA => 'Inside Dhaka',
            self::OUTSIDE_DHAKA => 'Outside Dhaka',
        ];
    }

    public static function label(string $zone): string
    {
        return self::labels()[$zone] ?? ucfirst(str_replace('_', ' ', $zone));
    }

    public static function isValid(?string $zone): bool
    {
        return in_array($zone, [self::INSIDE_DHAKA, self::OUTSIDE_DHAKA], true);
    }
}
