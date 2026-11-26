<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CostCalculationSetting extends Model
{
    protected $fillable = [
        'conversion_rate',
        'default_price_inr',
    ];

    protected function casts(): array
    {
        return [
            'conversion_rate' => 'decimal:2',
            'default_price_inr' => 'decimal:2',
        ];
    }

    public static function getSetting(): self
    {
        return self::query()->firstOrCreate([], [
            'conversion_rate' => 1.45,
        ]);
    }

    public static function resolveConversionRate(): float
    {
        $rate = (float) (static::getSetting()->conversion_rate ?? 0);

        return $rate > 0 ? round($rate, 2) : 1.45;
    }
}
