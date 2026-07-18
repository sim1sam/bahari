<?php

namespace App\Services;

use App\Models\AccountExpense;
use App\Models\FinancialTransaction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderPayment;
use App\Models\Product;
use App\Support\FinancialReportFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FinancialReportService
{
    public function __construct(private BankBalanceService $bankBalances) {}

    /** @return array<string, mixed> */
    public function overview(FinancialReportFilters $filters): array
    {
        $profitLoss = $this->profitLoss($filters);

        return [
            'total_revenue' => $profitLoss['total_revenue'],
            'gross_profit' => $profitLoss['gross_profit'],
            'net_profit' => $profitLoss['net_profit'],
            'cash_collected' => $this->cashCollected($filters),
            'bank_charges_collected' => $this->bankChargesCollected($filters),
            'accounts_receivable' => $this->accountsReceivable($filters),
            'inventory_value' => $this->inventoryValue(),
            'total_expenses' => $profitLoss['operating_expenses'],
            'order_count' => $this->ordersQuery($filters)->count(),
        ];
    }

    /** @return array<string, mixed> */
    public function profitLoss(FinancialReportFilters $filters): array
    {
        if ($filters->basis === 'cash') {
            return $this->profitLossCash($filters);
        }

        $orders = $this->ordersQuery($filters);
        $orderIds = (clone $orders)->pluck('id');

        $grossSales = (float) (clone $orders)->sum('subtotal');
        $discounts = (float) (clone $orders)->sum('discount');
        $shipping = (float) (clone $orders)->sum('shipping');
        $netSales = $grossSales - $discounts;
        $totalRevenue = $netSales + $shipping;
        $cogs = $this->cogsForOrderIds($orderIds);
        $productPurchases = $this->inventoryPurchasesSum($filters);
        $grossProfit = $netSales - $cogs;
        $operatingExpenses = $this->operatingExpensesSum($filters);
        $netProfit = $grossProfit + $shipping - $operatingExpenses;

        $itemsWithoutCost = $this->itemsMissingCostCount($orderIds);

        return [
            'basis' => 'accrual',
            'gross_sales' => $grossSales,
            'discounts' => $discounts,
            'net_sales' => $netSales,
            'shipping_income' => $shipping,
            'total_revenue' => $totalRevenue,
            'cogs' => $cogs,
            'product_purchases' => $productPurchases,
            'gross_profit' => $grossProfit,
            'operating_expenses' => $operatingExpenses,
            'net_profit' => $netProfit,
            'order_count' => $orderIds->count(),
            'items_missing_cost' => $itemsWithoutCost,
            'expense_breakdown' => $this->expenseBreakdown($filters),
            'coupon_breakdown' => $this->couponBreakdown($filters),
        ];
    }

    /** @return array<string, mixed> */
    private function profitLossCash(FinancialReportFilters $filters): array
    {
        $cashCollected = $this->cashCollected($filters);
        $operatingExpenses = $this->expensesSum($filters);
        $netProfit = $cashCollected - $operatingExpenses;

        return [
            'basis' => 'cash',
            'gross_sales' => $cashCollected,
            'discounts' => 0.0,
            'net_sales' => $cashCollected,
            'shipping_income' => 0.0,
            'total_revenue' => $cashCollected,
            'cogs' => 0.0,
            'gross_profit' => $cashCollected,
            'operating_expenses' => $operatingExpenses,
            'net_profit' => $netProfit,
            'order_count' => $this->cashCollectionCount($filters),
            'items_missing_cost' => 0,
            'expense_breakdown' => $this->expenseBreakdown($filters),
            'coupon_breakdown' => [],
            'cash_note' => 'Cash basis uses payments received in the selected period. COGS is not allocated on cash basis.',
        ];
    }

    /** @return array<string, mixed> */
    public function balanceSheet(FinancialReportFilters $filters): array
    {
        $asOf = $filters->dateTo ?: now()->toDateString();
        $asOfFilters = new FinancialReportFilters(
            dateFrom: null,
            dateTo: $asOf,
            basis: 'accrual',
            status: 'completed',
            excludeCancelled: true,
        );

        $lifetimeFilters = new FinancialReportFilters(
            dateFrom: null,
            dateTo: $asOf,
            basis: 'accrual',
            status: 'completed',
            excludeCancelled: true,
        );

        $cash = $this->bankBalancesReport($asOf)['total_balance'];
        $accountsReceivable = $this->accountsReceivableUpTo($asOf);
        $inventory = $this->inventoryValue();
        $totalAssets = $cash + $accountsReceivable + $inventory;

        $profitLoss = $this->profitLoss($lifetimeFilters);
        $retainedEarnings = $profitLoss['net_profit'];
        $totalEquity = $retainedEarnings;
        $totalLiabilitiesEquity = $totalEquity;

        return [
            'as_of' => $asOf,
            'cash' => $cash,
            'accounts_receivable' => $accountsReceivable,
            'inventory' => $inventory,
            'total_assets' => $totalAssets,
            'accounts_payable' => 0.0,
            'total_liabilities' => 0.0,
            'retained_earnings' => $retainedEarnings,
            'total_equity' => $totalEquity,
            'total_liabilities_equity' => $totalLiabilitiesEquity,
            'is_balanced' => abs($totalAssets - $totalLiabilitiesEquity) < 0.01,
            'variance' => $totalAssets - $totalLiabilitiesEquity,
            'inventory_units' => (int) Product::query()->sum('stock'),
            'products_with_cost' => (int) Product::query()->whereNotNull('purchase_price')->where('purchase_price', '>', 0)->count(),
        ];
    }

    /** @return Collection<int, array<string, mixed>> */
    public function ledger(FinancialReportFilters $filters): Collection
    {
        if ($this->usesFinancialTransactions()) {
            return $this->ledgerFromFinancialTransactions($filters);
        }

        return $this->legacyLedger($filters);
    }

    /** @return Collection<int, array<string, mixed>> */
    private function ledgerFromFinancialTransactions(FinancialReportFilters $filters): Collection
    {
        $query = $this->financialTransactionsQuery($filters, includeInterTransfers: true)
            ->with(['paymentBank', 'accountHead', 'order'])
            ->orderBy('transaction_date')
            ->orderBy('id');

        if ($filters->basis === 'accrual') {
            $query->whereNotIn('type', [
                FinancialTransaction::TYPE_PAYMENT_IN,
                FinancialTransaction::TYPE_ADVANCE_IN,
                FinancialTransaction::TYPE_GATEWAY_IN,
                FinancialTransaction::TYPE_CHECKOUT_PENDING,
            ]);
        }

        $balance = 0.0;

        return $query->get()->map(function (FinancialTransaction $transaction) use (&$balance) {
            $debit = $transaction->direction === FinancialTransaction::DIRECTION_DEBIT
                ? (float) $transaction->total_amount
                : 0.0;
            $credit = $transaction->direction === FinancialTransaction::DIRECTION_CREDIT
                ? (float) $transaction->total_amount
                : 0.0;

            $balance += $credit - $debit;

            return [
                'date' => $transaction->transaction_date->toDateString(),
                'datetime' => $transaction->transaction_date,
                'type' => $transaction->type,
                'type_label' => $transaction->typeLabel(),
                'reference' => $transaction->reference ?: 'TXN-'.$transaction->id,
                'description' => $transaction->description,
                'base_amount' => (float) $transaction->base_amount,
                'bank_charge_amount' => (float) $transaction->bank_charge_amount,
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $balance,
                'order_id' => $transaction->order_id,
            ];
        });
    }

    /** @return Collection<int, array<string, mixed>> */
    private function legacyLedger(FinancialReportFilters $filters): Collection
    {
        $entries = collect();

        if ($filters->basis === 'accrual') {
            $this->ordersQuery($filters)
                ->with('items')
                ->orderBy($filters->status === 'completed' ? 'completed_at' : 'created_at')
                ->chunk(100, function ($orders) use (&$entries) {
                    foreach ($orders as $order) {
                        $saleAt = $order->completed_at ?? $order->created_at;
                        $date = $saleAt->toDateString();
                        $cogs = $this->cogsForOrderIds(collect([$order->id]));

                        $entries->push([
                            'date' => $date,
                            'datetime' => $saleAt,
                            'type' => 'sale',
                            'reference' => $order->number,
                            'description' => 'Order sale — '.$order->customer_name,
                            'debit' => 0.0,
                            'credit' => (float) $order->subtotal,
                            'order_id' => $order->id,
                        ]);

                        if ((float) $order->discount > 0) {
                            $entries->push([
                                'date' => $date,
                                'datetime' => $saleAt,
                                'type' => 'discount',
                                'reference' => $order->number,
                                'description' => 'Coupon / discount'.($order->coupon_code ? ' ('.$order->coupon_code.')' : ''),
                                'debit' => (float) $order->discount,
                                'credit' => 0.0,
                                'order_id' => $order->id,
                            ]);
                        }

                        if ((float) $order->shipping > 0) {
                            $entries->push([
                                'date' => $date,
                                'datetime' => $saleAt,
                                'type' => 'shipping',
                                'reference' => $order->number,
                                'description' => 'Shipping income',
                                'debit' => 0.0,
                                'credit' => (float) $order->shipping,
                                'order_id' => $order->id,
                            ]);
                        }

                        if ($cogs > 0) {
                            $entries->push([
                                'date' => $date,
                                'datetime' => $saleAt,
                                'type' => 'cogs',
                                'reference' => $order->number,
                                'description' => 'Cost of goods sold',
                                'debit' => $cogs,
                                'credit' => 0.0,
                                'order_id' => $order->id,
                            ]);
                        }
                    }
                });
        }

        $this->cashCollectionQuery($filters)
            ->with('order')
            ->orderBy('created_at')
            ->chunk(100, function ($payments) use (&$entries) {
                foreach ($payments as $payment) {
                    $entries->push([
                        'date' => $payment->created_at->toDateString(),
                        'datetime' => $payment->created_at,
                        'type' => 'payment',
                        'reference' => $payment->order?->number ?? '#'.$payment->order_id,
                        'description' => 'Payment received — '.$payment->methodLabel(),
                        'debit' => 0.0,
                        'credit' => (float) $payment->amount,
                        'order_id' => $payment->order_id,
                    ]);
                }
            });

        if ($filters->basis === 'cash') {
            $this->gatewayPaymentsQuery($filters)
                ->orderBy('updated_at')
                ->chunk(100, function ($orders) use (&$entries) {
                    foreach ($orders as $order) {
                        $entries->push([
                            'date' => $order->updated_at->toDateString(),
                            'datetime' => $order->updated_at,
                            'type' => 'payment',
                            'reference' => $order->number,
                            'description' => 'Gateway payment — '.$order->paymentMethodLabel(),
                            'debit' => 0.0,
                            'credit' => (float) $order->amount_paid,
                            'order_id' => $order->id,
                        ]);
                    }
                });
        }

        $this->expensesQuery($filters)
            ->orderBy('expense_date')
            ->chunk(100, function ($expenses) use (&$entries) {
                foreach ($expenses as $expense) {
                    $entries->push([
                        'date' => $expense->expense_date->toDateString(),
                        'datetime' => $expense->expense_date->startOfDay(),
                        'type' => 'expense',
                        'reference' => $expense->reference ?: 'EXP-'.$expense->id,
                        'description' => $expense->title.' ('.$expense->accountHeadLabel().')',
                        'debit' => (float) $expense->total_deduction,
                        'credit' => 0.0,
                        'order_id' => null,
                    ]);
                }
            });

        $sorted = $entries->sortBy('datetime')->values();

        $balance = 0.0;

        return $sorted->map(function (array $entry) use (&$balance) {
            $balance += $entry['credit'] - $entry['debit'];
            $entry['balance'] = $balance;

            return $entry;
        });
    }

    public function ordersQuery(FinancialReportFilters $filters): Builder
    {
        $query = Order::query();

        $dateColumn = $filters->status === 'completed' ? 'completed_at' : 'created_at';

        if ($filters->dateFrom) {
            $query->whereDate($dateColumn, '>=', $filters->dateFrom);
        }

        if ($filters->dateTo) {
            $query->whereDate($dateColumn, '<=', $filters->dateTo);
        }

        if ($filters->status === 'completed') {
            $query->where('status', 'completed')->whereNotNull('completed_at');
        } elseif ($filters->status) {
            $query->where('status', $filters->status);
        } elseif ($filters->excludeCancelled) {
            $query->where('status', '!=', 'cancelled');
        }

        if ($filters->paymentStatus) {
            $query->where('payment_status', $filters->paymentStatus);
        }

        if ($filters->paymentMethod) {
            $query->where('payment_method', $filters->paymentMethod);
        }

        if ($filters->orderType) {
            $query->where('order_type', $filters->orderType);
        }

        return $query;
    }

    private function cashCollectionQuery(FinancialReportFilters $filters): Builder
    {
        $query = OrderPayment::query();

        if ($filters->dateFrom) {
            $query->whereDate('created_at', '>=', $filters->dateFrom);
        }

        if ($filters->dateTo) {
            $query->whereDate('created_at', '<=', $filters->dateTo);
        }

        if ($filters->paymentMethod) {
            $query->where('payment_method', $filters->paymentMethod);
        }

        $query->whereHas('order', function (Builder $orderQuery) use ($filters) {
            if ($filters->excludeCancelled && ! $filters->status) {
                $orderQuery->where('status', '!=', 'cancelled');
            }

            if ($filters->status) {
                $orderQuery->where('status', $filters->status);
            }

            if ($filters->paymentStatus) {
                $orderQuery->where('payment_status', $filters->paymentStatus);
            }

            if ($filters->orderType) {
                $orderQuery->where('order_type', $filters->orderType);
            }
        });

        return $query;
    }

    private function gatewayPaymentsQuery(FinancialReportFilters $filters): Builder
    {
        $query = Order::query()
            ->where('payment_method', 'sslcommerz')
            ->where('payment_status', 'paid')
            ->where('amount_paid', '>', 0)
            ->whereDoesntHave('payments');

        if ($filters->dateFrom) {
            $query->whereDate('updated_at', '>=', $filters->dateFrom);
        }

        if ($filters->dateTo) {
            $query->whereDate('updated_at', '<=', $filters->dateTo);
        }

        if ($filters->excludeCancelled && ! $filters->status) {
            $query->where('status', '!=', 'cancelled');
        }

        if ($filters->status) {
            $query->where('status', $filters->status);
        }

        if ($filters->orderType) {
            $query->where('order_type', $filters->orderType);
        }

        return $query;
    }

    public function cashCollected(FinancialReportFilters $filters): float
    {
        if ($this->usesFinancialTransactions()) {
            return (float) $this->financialTransactionsQuery($filters)
                ->where('direction', FinancialTransaction::DIRECTION_CREDIT)
                ->whereIn('type', [
                    FinancialTransaction::TYPE_PAYMENT_IN,
                    FinancialTransaction::TYPE_ADVANCE_IN,
                    FinancialTransaction::TYPE_GATEWAY_IN,
                ])
                ->sum('total_amount');
        }

        $fromPayments = (float) $this->cashCollectionQuery($filters)->sum('amount');
        $fromGateway = (float) $this->gatewayPaymentsQuery($filters)->sum('amount_paid');

        return $fromPayments + $fromGateway;
    }

    public function bankChargesCollected(FinancialReportFilters $filters): float
    {
        if (! $this->usesFinancialTransactions()) {
            return 0.0;
        }

        return (float) $this->financialTransactionsQuery($filters)
            ->where('direction', FinancialTransaction::DIRECTION_CREDIT)
            ->whereIn('type', [
                FinancialTransaction::TYPE_PAYMENT_IN,
                FinancialTransaction::TYPE_ADVANCE_IN,
            ])
            ->sum('bank_charge_amount');
    }

    /** @return array<string, mixed> */
    public function bankBalancesReport(?string $asOf = null): array
    {
        $banks = \App\Models\PaymentBank::query()->orderBy('sort_order')->orderBy('name')->get();

        return [
            'as_of' => $asOf ?: now()->toDateString(),
            'banks' => $banks->map(fn ($bank) => array_merge(
                ['id' => $bank->id, 'name' => $bank->displayName()],
                $this->bankBalances->breakdown($bank, $asOf),
            ))->all(),
            'total_balance' => array_sum($this->bankBalances->balances($banks, $asOf)),
        ];
    }

    /**
     * Order-wise sales report: sales price, procurement cost, and service charge.
     *
     * @return array{rows: Collection<int, array<string, mixed>>, totals: array<string, float>, order_count: int, items_missing_cost: int}
     */
    public function salesReport(FinancialReportFilters $filters): array
    {
        $orders = $this->ordersQuery($filters)
            ->orderByDesc($filters->status === 'completed' ? 'completed_at' : 'created_at')
            ->orderByDesc('id')
            ->get(['id', 'number', 'customer_name', 'status', 'payment_status', 'subtotal', 'discount', 'shipping', 'total', 'created_at', 'completed_at']);

        $orderIds = $orders->pluck('id');
        $procurementByOrder = $this->procurementCostByOrderIds($orderIds);

        $rows = $orders->map(function (Order $order) use ($procurementByOrder) {
            $salesPrice = (float) $order->subtotal;
            $procurementCost = (float) ($procurementByOrder[$order->id] ?? 0);
            $serviceCharge = $salesPrice - $procurementCost;
            $reportDate = $order->completed_at ?? $order->created_at;

            return [
                'order_id' => $order->id,
                'date' => $reportDate?->toDateString(),
                'number' => $order->number,
                'customer_name' => $order->customer_name,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'sales_price' => $salesPrice,
                'discount' => (float) $order->discount,
                'shipping' => (float) $order->shipping,
                'order_total' => (float) $order->total,
                'procurement_cost' => $procurementCost,
                'service_charge' => $serviceCharge,
            ];
        });

        return [
            'rows' => $rows,
            'totals' => [
                'sales_price' => (float) $rows->sum('sales_price'),
                'discount' => (float) $rows->sum('discount'),
                'shipping' => (float) $rows->sum('shipping'),
                'order_total' => (float) $rows->sum('order_total'),
                'procurement_cost' => (float) $rows->sum('procurement_cost'),
                'service_charge' => (float) $rows->sum('service_charge'),
            ],
            'order_count' => $rows->count(),
            'items_missing_cost' => $this->itemsMissingCostCount($orderIds),
        ];
    }

    /**
     * @param  Collection<int, int>  $orderIds
     * @return array<int, float>
     */
    private function procurementCostByOrderIds(Collection $orderIds): array
    {
        if ($orderIds->isEmpty()) {
            return [];
        }

        return OrderItem::query()
            ->whereIn('order_id', $orderIds)
            ->leftJoin('products', 'products.slug', '=', 'order_items.product_slug')
            ->groupBy('order_items.order_id')
            ->selectRaw('order_items.order_id, SUM(order_items.quantity * COALESCE(products.purchase_price, 0)) as procurement_cost')
            ->pluck('procurement_cost', 'order_id')
            ->map(fn ($value) => (float) $value)
            ->all();
    }

    private function cashCollectedUpTo(string $asOf): float
    {
        $payments = (float) OrderPayment::query()
            ->whereDate('created_at', '<=', $asOf)
            ->whereHas('order', fn (Builder $q) => $q->where('status', '!=', 'cancelled'))
            ->sum('amount');

        $gateway = (float) Order::query()
            ->where('payment_method', 'sslcommerz')
            ->where('payment_status', 'paid')
            ->where('amount_paid', '>', 0)
            ->whereDoesntHave('payments')
            ->whereDate('updated_at', '<=', $asOf)
            ->where('status', '!=', 'cancelled')
            ->sum('amount_paid');

        return $payments + $gateway;
    }

    public function accountsReceivable(FinancialReportFilters $filters): float
    {
        return (float) $this->ordersQuery($filters)
            ->whereIn('payment_status', ['due', 'partial', 'pending'])
            ->get()
            ->sum(fn (Order $order) => $order->amountDue());
    }

    private function accountsReceivableUpTo(string $asOf): float
    {
        return (float) Order::query()
            ->whereDate('created_at', '<=', $asOf)
            ->where('status', '!=', 'cancelled')
            ->whereIn('payment_status', ['due', 'partial', 'pending'])
            ->get()
            ->sum(fn (Order $order) => $order->amountDue());
    }

    public function inventoryValue(): float
    {
        return (float) Product::query()
            ->whereNotNull('purchase_price')
            ->where('purchase_price', '>', 0)
            ->selectRaw('SUM(stock * purchase_price) as value')
            ->value('value');
    }

    /** @param Collection<int, int> $orderIds */
    private function cogsForOrderIds(Collection $orderIds): float
    {
        if ($orderIds->isEmpty()) {
            return 0.0;
        }

        return (float) OrderItem::query()
            ->whereIn('order_id', $orderIds)
            ->leftJoin('products', 'products.slug', '=', 'order_items.product_slug')
            ->selectRaw('SUM(order_items.quantity * COALESCE(products.purchase_price, 0)) as total')
            ->value('total');
    }

    /** @param Collection<int, int> $orderIds */
    private function itemsMissingCostCount(Collection $orderIds): int
    {
        if ($orderIds->isEmpty()) {
            return 0;
        }

        return (int) OrderItem::query()
            ->whereIn('order_id', $orderIds)
            ->leftJoin('products', 'products.slug', '=', 'order_items.product_slug')
            ->where(function (Builder $query) {
                $query->whereNull('products.purchase_price')
                    ->orWhere('products.purchase_price', '<=', 0);
            })
            ->count();
    }

    private function expensesQuery(FinancialReportFilters $filters): Builder
    {
        $query = AccountExpense::query()->with('accountHead');

        if ($filters->dateFrom) {
            $query->whereDate('expense_date', '>=', $filters->dateFrom);
        }

        if ($filters->dateTo) {
            $query->whereDate('expense_date', '<=', $filters->dateTo);
        }

        if ($filters->paymentBankId) {
            $query->where('payment_bank_id', $filters->paymentBankId);
        }

        if ($filters->accountHeadId) {
            $query->where('account_head_id', $filters->accountHeadId);
        }

        return $query;
    }

    private function financialTransactionsQuery(FinancialReportFilters $filters, bool $includeInterTransfers = false): Builder
    {
        $excluded = [FinancialTransaction::TYPE_CHECKOUT_PENDING];

        if (! $includeInterTransfers) {
            $excluded = array_merge($excluded, FinancialTransaction::PNL_EXCLUDED_TYPES);
            $excluded = array_values(array_unique(array_filter($excluded, fn ($type) => $type !== FinancialTransaction::TYPE_CHECKOUT_PENDING)));
        }

        $query = FinancialTransaction::query()->whereNotIn('type', $excluded);

        if ($filters->dateFrom) {
            $query->whereDate('transaction_date', '>=', $filters->dateFrom);
        }

        if ($filters->dateTo) {
            $query->whereDate('transaction_date', '<=', $filters->dateTo);
        }

        if ($filters->paymentBankId) {
            $query->where('payment_bank_id', $filters->paymentBankId);
        }

        if ($filters->accountHeadId) {
            $query->where('account_head_id', $filters->accountHeadId);
        }

        return $query;
    }

    private function usesFinancialTransactions(): bool
    {
        return FinancialTransaction::query()->exists();
    }

    private function expensesSum(FinancialReportFilters $filters): float
    {
        return (float) $this->expensesQuery($filters)->sum('total_deduction');
    }

    private function inventoryPurchasesSum(FinancialReportFilters $filters): float
    {
        return (float) $this->expensesQuery($filters)
            ->where(function (Builder $query) {
                $query->whereNotNull('product_id')
                    ->orWhereHas('accountHead', fn (Builder $head) => $head->where('code', 'INVENTORY'));
            })
            ->sum('total_deduction');
    }

    private function operatingExpensesSum(FinancialReportFilters $filters): float
    {
        return (float) $this->expensesQuery($filters)
            ->where(function (Builder $query) {
                $query->whereNull('product_id')
                    ->whereDoesntHave('accountHead', fn (Builder $head) => $head->where('code', 'INVENTORY'));
            })
            ->sum('total_deduction');
    }

    /** @return array<int, array{category: string, label: string, total: float}> */
    private function expenseBreakdown(FinancialReportFilters $filters): array
    {
        return $this->expensesQuery($filters)
            ->with('accountHead')
            ->get()
            ->groupBy('account_head_id')
            ->map(function ($rows, $headId) {
                $head = $rows->first()->accountHead;

                return [
                    'category' => (string) $headId,
                    'label' => $head?->displayName() ?? 'Unknown',
                    'total' => (float) $rows->sum('total_deduction'),
                ];
            })
            ->sortByDesc('total')
            ->values()
            ->all();
    }

    /** @return array<int, array{coupon: string, orders: int, total: float}> */
    private function couponBreakdown(FinancialReportFilters $filters): array
    {
        return $this->ordersQuery($filters)
            ->whereNotNull('coupon_code')
            ->where('coupon_code', '!=', '')
            ->select('coupon_code', DB::raw('COUNT(*) as orders'), DB::raw('SUM(discount) as total'))
            ->groupBy('coupon_code')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'coupon' => $row->coupon_code,
                'orders' => (int) $row->orders,
                'total' => (float) $row->total,
            ])
            ->all();
    }

    private function cashCollectionCount(FinancialReportFilters $filters): int
    {
        return $this->cashCollectionQuery($filters)->count()
            + $this->gatewayPaymentsQuery($filters)->count();
    }

    public function defaultDateFrom(): string
    {
        return Carbon::now()->startOfMonth()->toDateString();
    }

    public function defaultDateTo(): string
    {
        return Carbon::now()->toDateString();
    }
}
