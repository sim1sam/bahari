<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingCharge extends Model
{
    protected $fillable = [
        'item_name',
        'sort_order',
        'unit',
        'approx_weight',
        'actual_rate',
        'retail_rate',
        'reselling_rate',
        'wholesale_rate',
        'only_shipping_rate',
        'product_factor',
        'show_in_cost_calculation',
    ];

    protected function casts(): array
    {
        return [
            'approx_weight' => 'decimal:2',
            'actual_rate' => 'decimal:2',
            'retail_rate' => 'decimal:2',
            'reselling_rate' => 'decimal:2',
            'wholesale_rate' => 'decimal:2',
            'only_shipping_rate' => 'decimal:2',
            'product_factor' => 'decimal:6',
            'show_in_cost_calculation' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeForCostCalculation($query)
    {
        return $query->where('show_in_cost_calculation', true);
    }

    /** @return array<int, string> */
    public static function units(): array
    {
        return ['PC', 'KG'];
    }

    public static function getProductFactor(string $itemName, float $priceInr = 0): float
    {
        $itemNameLower = strtolower(trim($itemName));

        if (preg_match('/\b(kurti|top|shirt|t-shirt|tshirt)\b/i', $itemNameLower)) {
            return -0.166666;
        }

        if (preg_match('/\b(saree|sari)\b/i', $itemNameLower)) {
            if ($priceInr < 1999) {
                return 1.0;
            }
        }

        return 0.0;
    }
}
