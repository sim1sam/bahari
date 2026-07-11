<?php

namespace App\Services;

use App\Models\AccountExpense;
use App\Models\CustomerPayment;
use App\Models\OrderPayment;
use App\Models\PaymentBank;

class BankBalanceService
{
    public function balance(PaymentBank $bank): float
    {
        $orderPayments = (float) OrderPayment::query()
            ->where('payment_method', 'bank_transfer')
            ->whereIn('bank_name', array_unique([$bank->name, $bank->displayName()]))
            ->sum('amount');

        $advancePayments = (float) CustomerPayment::query()
            ->where('payment_bank_id', $bank->id)
            ->whereNull('order_id')
            ->sum('amount');

        $outgoing = (float) AccountExpense::query()
            ->where('payment_bank_id', $bank->id)
            ->sum('total_deduction');

        return round($orderPayments + $advancePayments - $outgoing, 2);
    }

    /** @return array<int, float> */
    public function balances(iterable $banks): array
    {
        $balances = [];

        foreach ($banks as $bank) {
            $balances[$bank->id] = $this->balance($bank);
        }

        return $balances;
    }
}
