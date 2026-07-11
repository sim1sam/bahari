<?php

namespace App\Services;

use App\Models\AccountExpense;
use App\Models\BankInterTransfer;
use App\Models\CustomerPayment;
use App\Models\FinancialTransaction;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\PaymentBank;
use App\Models\PaymentTransaction;

class FinancialTransactionService
{
    public function calculateCharge(float $baseAmount, float $chargePercent): array
    {
        $chargeAmount = bank_charge_amount($baseAmount, $chargePercent);

        return [
            'base_amount' => round($baseAmount, 2),
            'bank_charge_percent' => round($chargePercent, 2),
            'bank_charge_amount' => $chargeAmount,
            'total_amount' => round($baseAmount + $chargeAmount, 2),
        ];
    }

    public function recordFromPaymentTransaction(PaymentTransaction $transaction, bool $pending = true): void
    {
        $saleAmount = (float) ($transaction->sale_amount ?? $transaction->order?->total ?? $transaction->amount);
        $chargeAmount = (float) ($transaction->bank_charge_amount ?? 0);
        $totalAmount = (float) $transaction->amount;
        $chargePercent = (float) ($transaction->bank_charge_percent ?? 0);

        if ($saleAmount <= 0 && $totalAmount > 0) {
            $saleAmount = max(0, $totalAmount - $chargeAmount);
        }

        $this->upsert(
            source: $transaction,
            attributes: [
                'transaction_date' => $transaction->created_at->toDateString(),
                'type' => $pending ? FinancialTransaction::TYPE_CHECKOUT_PENDING : FinancialTransaction::TYPE_PAYMENT_IN,
                'direction' => FinancialTransaction::DIRECTION_CREDIT,
                'order_id' => $transaction->order_id,
                'user_id' => $transaction->user_id,
                'payment_bank_id' => $transaction->payment_bank_id,
                'base_amount' => $saleAmount,
                'bank_charge_percent' => $chargePercent,
                'bank_charge_amount' => $chargeAmount,
                'total_amount' => $totalAmount,
                'reference' => $transaction->order?->number,
                'description' => ($pending ? 'Checkout payment pending' : 'Checkout payment approved')
                    .($transaction->bank_name ? ' — '.$transaction->bank_name : ''),
                'recorded_by' => $transaction->reviewed_by,
            ],
        );
    }

    public function confirmPaymentTransaction(PaymentTransaction $transaction): void
    {
        $this->recordFromPaymentTransaction($transaction, pending: false);
    }

    public function recordFromOrderPayment(OrderPayment $payment): void
    {
        if ($payment->payment_transaction_id) {
            return;
        }

        $baseAmount = (float) ($payment->sale_amount ?? $payment->amount);
        $chargeAmount = (float) ($payment->bank_charge_amount ?? 0);
        $totalAmount = (float) $payment->amount;

        $this->upsert(
            source: $payment,
            attributes: [
                'transaction_date' => $payment->created_at->toDateString(),
                'type' => FinancialTransaction::TYPE_PAYMENT_IN,
                'direction' => FinancialTransaction::DIRECTION_CREDIT,
                'order_id' => $payment->order_id,
                'user_id' => $payment->order?->user_id,
                'payment_bank_id' => $payment->payment_bank_id,
                'base_amount' => $baseAmount,
                'bank_charge_percent' => (float) ($payment->bank_charge_percent ?? 0),
                'bank_charge_amount' => $chargeAmount,
                'total_amount' => $totalAmount,
                'reference' => $payment->order?->number,
                'description' => 'Order payment — '.$payment->methodLabel()
                    .($payment->bank_name ? ' ('.$payment->bank_name.')' : ''),
                'recorded_by' => $payment->recorded_by,
            ],
        );
    }

    public function recordFromCustomerPayment(CustomerPayment $payment): void
    {
        if ($payment->order_id) {
            return;
        }

        $baseAmount = (float) ($payment->sale_amount ?? $payment->amount);
        $chargeAmount = (float) ($payment->bank_charge_amount ?? 0);
        $totalAmount = (float) $payment->amount;

        $this->upsert(
            source: $payment,
            attributes: [
                'transaction_date' => $payment->payment_date->toDateString(),
                'type' => FinancialTransaction::TYPE_ADVANCE_IN,
                'direction' => FinancialTransaction::DIRECTION_CREDIT,
                'order_id' => null,
                'user_id' => $payment->user_id,
                'payment_bank_id' => $payment->payment_bank_id,
                'base_amount' => $baseAmount,
                'bank_charge_percent' => (float) ($payment->bank_charge_percent ?? 0),
                'bank_charge_amount' => $chargeAmount,
                'total_amount' => $totalAmount,
                'reference' => 'ADV-'.$payment->id,
                'description' => 'Advance payment — '.($payment->user?->name ?? 'Customer'),
                'recorded_by' => $payment->recorded_by,
            ],
        );
    }

    public function recordFromExpense(AccountExpense $expense): void
    {
        $this->upsert(
            source: $expense,
            attributes: [
                'transaction_date' => $expense->expense_date->toDateString(),
                'type' => FinancialTransaction::TYPE_PAYMENT_OUT,
                'direction' => FinancialTransaction::DIRECTION_DEBIT,
                'order_id' => null,
                'user_id' => null,
                'payment_bank_id' => $expense->payment_bank_id,
                'account_head_id' => $expense->account_head_id,
                'base_amount' => (float) $expense->amount,
                'bank_charge_percent' => (float) $expense->bank_charge_percent,
                'bank_charge_amount' => (float) $expense->bank_charge_amount,
                'total_amount' => (float) $expense->total_deduction,
                'reference' => $expense->reference ?: 'EXP-'.$expense->id,
                'description' => $expense->title.' ('.$expense->accountHeadLabel().')',
                'recorded_by' => $expense->recorded_by,
            ],
        );
    }

    public function recordFromGatewayPayment(Order $order): void
    {
        if ((float) $order->amount_paid <= 0) {
            return;
        }

        $this->upsert(
            source: $order,
            attributes: [
                'transaction_date' => $order->updated_at->toDateString(),
                'type' => FinancialTransaction::TYPE_GATEWAY_IN,
                'direction' => FinancialTransaction::DIRECTION_CREDIT,
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'payment_bank_id' => null,
                'base_amount' => (float) $order->amount_paid,
                'bank_charge_percent' => 0,
                'bank_charge_amount' => 0,
                'total_amount' => (float) $order->amount_paid,
                'reference' => $order->number,
                'description' => 'Gateway payment — '.$order->paymentMethodLabel(),
                'recorded_by' => null,
            ],
        );
    }

    public function recordInterTransfer(BankInterTransfer $transfer): void
    {
        $amount = (float) $transfer->amount;
        $reference = 'TRF-'.$transfer->id;
        $description = 'Transfer to '.$transfer->toBank->displayName();
        $reverseDescription = 'Transfer from '.$transfer->fromBank->displayName();

        FinancialTransaction::query()->updateOrCreate(
            [
                'source_type' => BankInterTransfer::class,
                'source_id' => $transfer->id,
            ],
            [
                'transaction_date' => $transfer->transfer_date->toDateString(),
                'type' => FinancialTransaction::TYPE_INTER_TRANSFER_OUT,
                'direction' => FinancialTransaction::DIRECTION_DEBIT,
                'order_id' => null,
                'user_id' => null,
                'payment_bank_id' => $transfer->from_bank_id,
                'counterparty_bank_id' => $transfer->to_bank_id,
                'account_head_id' => null,
                'base_amount' => $amount,
                'bank_charge_percent' => 0,
                'bank_charge_amount' => 0,
                'total_amount' => $amount,
                'reference' => $reference,
                'description' => $description,
                'recorded_by' => $transfer->recorded_by,
            ],
        );

        FinancialTransaction::query()->updateOrCreate(
            [
                'source_type' => BankInterTransfer::class.':in',
                'source_id' => $transfer->id,
            ],
            [
                'transaction_date' => $transfer->transfer_date->toDateString(),
                'type' => FinancialTransaction::TYPE_INTER_TRANSFER_IN,
                'direction' => FinancialTransaction::DIRECTION_CREDIT,
                'order_id' => null,
                'user_id' => null,
                'payment_bank_id' => $transfer->to_bank_id,
                'counterparty_bank_id' => $transfer->from_bank_id,
                'account_head_id' => null,
                'base_amount' => $amount,
                'bank_charge_percent' => 0,
                'bank_charge_amount' => 0,
                'total_amount' => $amount,
                'reference' => $reference,
                'description' => $reverseDescription,
                'recorded_by' => $transfer->recorded_by,
            ],
        );
    }

    public function deleteForSource(object $source): void
    {
        FinancialTransaction::query()
            ->where('source_type', $source::class)
            ->where('source_id', $source->getKey())
            ->delete();
    }

    public function splitForBank(PaymentBank $bank, float $totalPaid): array
    {
        return $this->calculateCharge($totalPaid, (float) $bank->charge_percent);
    }

    public function splitFromTotal(float $saleAmount, float $totalPaid, ?PaymentBank $bank = null): array
    {
        $chargeAmount = max(0, round($totalPaid - $saleAmount, 2));
        $chargePercent = $saleAmount > 0 && $bank
            ? (float) $bank->charge_percent
            : ($saleAmount > 0 ? round($chargeAmount / $saleAmount * 100, 2) : 0);

        return [
            'base_amount' => round($saleAmount, 2),
            'bank_charge_percent' => $chargePercent,
            'bank_charge_amount' => $chargeAmount,
            'total_amount' => round($totalPaid, 2),
        ];
    }

    /** @param  array<string, mixed>  $attributes */
    private function upsert(object $source, array $attributes): FinancialTransaction
    {
        return FinancialTransaction::query()->updateOrCreate(
            [
                'source_type' => $source::class,
                'source_id' => $source->getKey(),
            ],
            $attributes,
        );
    }
}
