<?php

namespace App\Services;

use App\Models\AccountExpense;
use App\Models\Product;
use App\Support\Money;

class ProductPurchaseExpenseService
{
    public function recordForNewProduct(Product $product): ?AccountExpense
    {
        if (! $product->is_manual) {
            return null;
        }

        return $this->recordPurchase($product, (int) $product->stock, (float) $product->purchase_price);
    }

    public function recordStockIncrease(Product $product, int $previousStock, float $previousPurchasePrice): ?AccountExpense
    {
        if (! $product->is_manual) {
            return null;
        }

        $addedUnits = (int) $product->stock - $previousStock;

        if ($addedUnits <= 0) {
            return null;
        }

        $unitCost = (float) ($product->purchase_price ?: $previousPurchasePrice);

        return $this->recordPurchase($product, $addedUnits, $unitCost);
    }

    private function recordPurchase(Product $product, int $quantity, float $unitCost): ?AccountExpense
    {
        if ($quantity <= 0 || $unitCost <= 0) {
            return null;
        }

        $amount = round($quantity * $unitCost, 2);

        if ($amount <= 0) {
            return null;
        }

        return AccountExpense::create([
            'expense_date' => now()->toDateString(),
            'category' => 'inventory',
            'title' => 'Product purchase: '.$product->name,
            'notes' => $quantity.' unit(s) × '.Money::format($unitCost).' purchase price',
            'amount' => $amount,
            'payment_method' => null,
            'reference' => 'STOCK-'.$product->id.'-'.now()->format('YmdHis'),
            'product_id' => $product->id,
            'recorded_by' => auth()->id(),
        ]);
    }
}
