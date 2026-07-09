<?php

namespace App\Services;

use App\Models\CustomerPayment;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class CustomerLedgerService
{
    public function ordersQueryForUser(User $user): Builder
    {
        return Order::query()
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('customer_email', $user->email);
            });
    }

    /** @return array<int, array{date: string, datetime: \Illuminate\Support\Carbon, type: string, reference: string, order_id: int|null, description: string, debit: float, credit: float, balance?: float}> */
    public function entriesForUser(User $user): array
    {
        $entries = [];
        $orders = $this->ordersQueryForUser($user)->orderBy('created_at')->get();
        $orderIds = $orders->pluck('id');

        foreach ($orders as $order) {
            $entries[] = [
                'date' => $order->created_at->format('Y-m-d'),
                'datetime' => $order->created_at,
                'type' => 'Order',
                'reference' => $order->number,
                'order_id' => $order->id,
                'description' => 'Order charge',
                'debit' => (float) $order->total,
                'credit' => 0.0,
            ];
        }

        foreach (OrderPayment::query()->whereIn('order_id', $orderIds)->with('order')->orderBy('created_at')->get() as $payment) {
            $entries[] = [
                'date' => $payment->created_at->format('Y-m-d'),
                'datetime' => $payment->created_at,
                'type' => 'Payment',
                'reference' => $payment->order?->number ?? '—',
                'order_id' => $payment->order_id,
                'description' => trim($payment->methodLabel().($payment->bank_name ? ' · '.$payment->bank_name : '')),
                'debit' => 0.0,
                'credit' => (float) $payment->amount,
            ];
        }

        foreach ($orders as $order) {
            if ($order->payment_method !== 'sslcommerz' || $order->payment_status !== 'paid') {
                continue;
            }

            $recorded = (float) $order->payments()->sum('amount');

            if ($recorded >= (float) $order->total) {
                continue;
            }

            $entries[] = [
                'date' => $order->updated_at->format('Y-m-d'),
                'datetime' => $order->updated_at,
                'type' => 'SSLCommerz',
                'reference' => $order->number,
                'order_id' => $order->id,
                'description' => 'Online payment',
                'debit' => 0.0,
                'credit' => (float) $order->total - $recorded,
            ];
        }

        foreach (CustomerPayment::query()->where('user_id', $user->id)->whereNull('order_id')->orderBy('payment_date')->orderBy('created_at')->get() as $payment) {
            $entries[] = [
                'date' => $payment->payment_date->format('Y-m-d'),
                'datetime' => $payment->created_at,
                'type' => 'Bank Payment',
                'reference' => 'ADV-'.$payment->id,
                'order_id' => null,
                'description' => trim('Advance payment'.($payment->bank_name ? ' · '.$payment->bank_name : '')),
                'debit' => 0.0,
                'credit' => (float) $payment->amount,
            ];
        }

        usort($entries, fn (array $left, array $right) => $left['datetime'] <=> $right['datetime']);

        $balance = 0.0;

        foreach ($entries as &$entry) {
            $balance += $entry['debit'] - $entry['credit'];
            $entry['balance'] = round($balance, 2);
        }
        unset($entry);

        return $entries;
    }

    public function balanceForUser(User $user): float
    {
        $entries = $this->entriesForUser($user);

        if ($entries === []) {
            return 0.0;
        }

        return (float) end($entries)['balance'];
    }

    /** @return array{total_orders: float, total_paid: float, balance: float} */
    public function totalsForUser(User $user): array
    {
        $orders = $this->ordersQueryForUser($user)->get();
        $totalOrders = (float) $orders->sum('total');
        $totalPaid = (float) $orders->sum('amount_paid');

        foreach (CustomerPayment::query()->where('user_id', $user->id)->whereNull('order_id')->get() as $payment) {
            $totalPaid += (float) $payment->amount;
        }

        return [
            'total_orders' => round($totalOrders, 2),
            'total_paid' => round($totalPaid, 2),
            'balance' => round(max(0, $totalOrders - $totalPaid), 2),
        ];
    }

    /** @return \Illuminate\Support\Collection<int, array{user: User, total_orders: float, total_paid: float, balance: float}> */
    public function summariesForCustomers()
    {
        return User::customers()
            ->withCount('orders')
            ->orderBy('name')
            ->get()
            ->map(function (User $customer) {
                $totals = $this->totalsForUser($customer);

                return [
                    'user' => $customer,
                    'total_orders' => $totals['total_orders'],
                    'total_paid' => $totals['total_paid'],
                    'balance' => $totals['balance'],
                ];
            });
    }
}
