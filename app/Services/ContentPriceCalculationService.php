<?php

namespace App\Services;

use App\Models\CostCalculationSetting;
use App\Models\ShippingCharge;

class ContentPriceCalculationService
{
    /**
     * Same formula as CRM content-processing / cost-calculator.
     *
     * @return array{retail: float, wholesale: float, reselling: float, procurement: float, service_charge: float}
     */
    public function calculate(?int $categoryId, float $priceInr): array
    {
        if (! $categoryId || $priceInr <= 0) {
            return $this->emptyPrices();
        }

        $category = ShippingCharge::find($categoryId);
        if (! $category) {
            return $this->emptyPrices();
        }

        $conversionRate = $this->resolveConversionRate();
        $shippingCharge = (float) ($category->retail_rate ?? 0);
        $itemName = $category->item_name ?? '';

        if ($priceInr < 200) {
            $cmRR = 0.5;
            $cmRS = 0.3;
            $cmWS = 0.2;
        } else {
            $cmRR = 0.3;
            $cmRS = 0.2;
            $cmWS = 0.12;
        }

        $acrRR = $conversionRate + ($conversionRate * $cmRR);
        $acrRS = $conversionRate + ($conversionRate * $cmRS);
        $acrWS = $conversionRate + ($conversionRate * $cmWS);

        $icRR = $this->roundToNearest50(($priceInr * $acrRR) + $shippingCharge + 25);
        $icRS = $this->roundToNearest50(($priceInr * $acrRS) + $shippingCharge + 25);
        $icWS = $this->roundToNearest50(($priceInr * $acrWS) + $shippingCharge + 25);

        $factorP = ShippingCharge::getProductFactor($itemName, $priceInr);
        $finalRR = $this->applyComparativeAdjustment($icRR, $icRS, $factorP);
        $procurementCost = $this->roundToNearest5($icWS * 0.98);

        return [
            'retail' => $finalRR,
            'wholesale' => $icWS,
            'reselling' => $icRS,
            'procurement' => $procurementCost,
            'service_charge' => $finalRR - $procurementCost,
        ];
    }

    public function formatRetailPrice(float $price): string
    {
        return 'BDT '.number_format($price, 0, '.', ',').'/-';
    }

    public function formatBdtPrice(float $price): string
    {
        return $this->formatRetailPrice($price);
    }

    public function resolveConversionRate(): float
    {
        return CostCalculationSetting::resolveConversionRate();
    }

    /** @return array{retail: float, wholesale: float, reselling: float, procurement: float, service_charge: float} */
    private function emptyPrices(): array
    {
        return [
            'retail' => 0.0,
            'wholesale' => 0.0,
            'reselling' => 0.0,
            'procurement' => 0.0,
            'service_charge' => 0.0,
        ];
    }

    private function roundToNearest5(float $value): float
    {
        return round($value / 5) * 5;
    }

    private function roundToNearest50(float $value): float
    {
        return round($value / 50) * 50;
    }

    private function applyComparativeAdjustment(float $ic, float $icLowerTier, float $factorP): float
    {
        $factorA = ($ic - $icLowerTier) >= 300 ? 0 : 300;

        return $this->roundToNearest50($ic + ($factorA * $factorP));
    }
}
