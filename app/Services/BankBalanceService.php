<?php

namespace App\Services;

use App\Models\AccountExpense;
use App\Models\CustomerPayment;
use App\Models\FinancialTransaction;
use App\Models\OrderPayment;
use App\Models\PaymentBank;

class BankBalanceService
{
    public function balance(PaymentBank $bank, ?string $asOf = null): float
    {
        $opening = (float) $bank->opening_balance;

        $credits = (float) $this->bankTransactionsQuery($bank, $asOf)
            ->where('direction', FinancialTransaction::DIRECTION_CREDIT)
            ->sum('total_amount');

        $debits = (float) $this->bankTransactionsQuery($bank, $asOf)
            ->where('direction', FinancialTransaction::DIRECTION_DEBIT)
            ->sum('total_amount');

        if ($this->hasFinancialTransactions()) {
            return round($opening + $credits - $debits, 2);
        }

        return $this->legacyBalance($bank);
    }

    /** @return array<int, float> */
    public function balances(iterable $banks, ?string $asOf = null): array
    {
        $balances = [];

        foreach ($banks as $bank) {
            $balances[$bank->id] = $this->balance($bank, $asOf);
        }

        return $balances;
    }

    /** @return array<string, float> */
    public function breakdown(PaymentBank $bank, ?string $asOf = null): array
    {
        $query = $this->bankTransactionsQuery($bank, $asOf);

        return [
            'opening_balance' => (float) $bank->opening_balance,
            'payments_in' => (float) (clone $query)
                ->where('direction', FinancialTransaction::DIRECTION_CREDIT)
                ->whereIn('type', [
                    FinancialTransaction::TYPE_PAYMENT_IN,
                    FinancialTransaction::TYPE_ADVANCE_IN,
                    FinancialTransaction::TYPE_GATEWAY_IN,
                ])
                ->sum('total_amount'),
            'transfers_in' => (float) (clone $query)
                ->where('type', FinancialTransaction::TYPE_INTER_TRANSFER_IN)
                ->sum('total_amount'),
            'expenses_out' => (float) (clone $query)
                ->where('type', FinancialTransaction::TYPE_PAYMENT_OUT)
                ->sum('total_amount'),
            'transfers_out' => (float) (clone $query)
                ->where('type', FinancialTransaction::TYPE_INTER_TRANSFER_OUT)
                ->sum('total_amount'),
            'current_balance' => $this->balance($bank, $asOf),
        ];
    }

    private function bankTransactionsQuery(PaymentBank $bank, ?string $asOf = null)
    {
        $query = FinancialTransaction::query()
            ->where('payment_bank_id', $bank->id)
            ->whereNotIn('type', [FinancialTransaction::TYPE_CHECKOUT_PENDING]);

        if ($asOf) {
            $query->whereDate('transaction_date', '<=', $asOf);
        }

        return $query;
    }

    private function hasFinancialTransactions(): bool
    {
        return FinancialTransaction::query()->exists();
    }

    private function legacyBalance(PaymentBank $bank): float
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

        return round((float) $bank->opening_balance + $orderPayments + $advancePayments - $outgoing, 2);
    }
}
