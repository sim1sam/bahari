<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerPayment;
use App\Models\Order;
use App\Models\PaymentBank;
use App\Models\Role;
use App\Models\User;
use App\Services\BankBalanceService;
use App\Services\CustomerLedgerService;
use App\Services\OrderTransferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CustomerPaymentController extends Controller
{
    public function __construct(
        private CustomerLedgerService $ledger,
        private OrderTransferService $transfer,
        private BankBalanceService $bankBalances,
    ) {}

    public function create(Request $request): View
    {
        $customers = User::customers()->orderBy('name')->get(['id', 'name', 'email']);
        $banks = PaymentBank::query()->orderBy('sort_order')->orderBy('name')->get();
        $balances = $this->bankBalances->balances($banks);
        $selectedCustomerId = $request->integer('customer_id') ?: null;
        $selectedOrderId = $request->integer('order_id') ?: null;
        $selectedBankId = $request->integer('payment_bank_id') ?: (int) old('payment_bank_id') ?: null;
        $orders = $selectedCustomerId
            ? $this->ordersForCustomerId($selectedCustomerId)
            : collect();
        $today = now()->toDateString();

        return view('admin.bank-payments.create', [
            'customers' => $customers,
            'banks' => $banks,
            'bankBalances' => $balances,
            'orders' => $orders,
            'selectedCustomerId' => $selectedCustomerId,
            'selectedOrderId' => $selectedOrderId,
            'selectedBankId' => $selectedBankId,
            'stats' => [
                'total' => CustomerPayment::count(),
                'today' => CustomerPayment::whereDate('payment_date', $today)->count(),
                'today_amount' => (float) CustomerPayment::whereDate('payment_date', $today)->sum('amount'),
                'banks' => $banks->where('is_active', true)->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'order_id' => 'nullable|exists:orders,id',
            'payment_bank_id' => 'required|exists:payment_banks,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string|max:500',
        ]);

        $customer = User::query()->findOrFail($validated['user_id']);
        $this->ensureCustomer($customer);

        $bank = PaymentBank::query()->findOrFail($validated['payment_bank_id']);
        $amount = round((float) $validated['amount'], 2);
        $order = null;

        if (! empty($validated['order_id'])) {
            $order = $this->ledger->ordersQueryForUser($customer)
                ->whereKey($validated['order_id'])
                ->firstOrFail();

            $due = $order->amountDue();

            if ($amount > $due) {
                return back()
                    ->withInput()
                    ->with('error', 'Payment amount cannot exceed balance due ('.money($due).').');
            }
        }

        $movedToProcessing = false;

        DB::transaction(function () use ($validated, $customer, $bank, $amount, $order, &$movedToProcessing) {
            CustomerPayment::create([
                'user_id' => $customer->id,
                'order_id' => $order?->id,
                'payment_bank_id' => $bank->id,
                'recorded_by' => Auth::id(),
                'amount' => $amount,
                'bank_name' => $bank->displayName(),
                'notes' => $validated['notes'] ?? null,
                'payment_date' => $validated['payment_date'],
            ]);

            if ($order) {
                \App\Models\OrderPayment::create([
                    'order_id' => $order->id,
                    'recorded_by' => Auth::id(),
                    'amount' => $amount,
                    'payment_method' => 'bank_transfer',
                    'bank_name' => $bank->displayName(),
                    'notes' => $validated['notes'] ?? null,
                ]);

                $movedToProcessing = $order->status === 'pending';
                $order->amount_paid = round((float) $order->amount_paid + $amount, 2);
                $order->recalculatePaymentStatus();

                if ($movedToProcessing) {
                    $order->status = 'processing';
                }

                $order->save();
            }
        });

        $message = 'Bank payment of '.money($amount).' recorded for '.$customer->name.'.';

        if ($order && $movedToProcessing) {
            $message .= $this->transfer->transfer($order->fresh())
                ? ' Order transferred to API site.'
                : ' Order transfer did not complete.';
        }

        return redirect()
            ->route('admin.bank-payments.create', ['customer_id' => $customer->id])
            ->with('success', $message);
    }

    public function bankPayments(PaymentBank $paymentBank): JsonResponse
    {
        $payments = CustomerPayment::query()
            ->with(['user:id,name,email', 'order:id,number'])
            ->where('payment_bank_id', $paymentBank->id)
            ->latest('payment_date')
            ->latest('id')
            ->limit(25)
            ->get()
            ->map(fn (CustomerPayment $payment) => [
                'id' => $payment->id,
                'customer_name' => $payment->user->name,
                'customer_email' => $payment->user->email,
                'order_number' => $payment->order?->number,
                'amount' => (float) $payment->amount,
                'payment_date' => $payment->payment_date->format('M d, Y'),
                'notes' => $payment->notes,
                'type' => $payment->order_id ? 'Order payment' : 'Advance',
            ]);

        return response()->json(['payments' => $payments]);
    }

    public function customerOrders(User $customer): JsonResponse
    {
        $this->ensureCustomer($customer);

        $orders = $this->ordersForCustomerId($customer->id)->map(fn (Order $order) => [
            'id' => $order->id,
            'number' => $order->number,
            'total' => (float) $order->total,
            'amount_paid' => (float) $order->amount_paid,
            'amount_due' => $order->amountDue(),
            'payment_status' => $order->paymentStatusLabel(),
            'created_at' => $order->created_at->format('M d, Y'),
        ]);

        return response()->json(['orders' => $orders]);
    }

    private function ordersForCustomerId(int $customerId)
    {
        $customer = User::query()->findOrFail($customerId);

        return $this->ledger->ordersQueryForUser($customer)
            ->whereColumn('total', '>', 'amount_paid')
            ->latest()
            ->get(['id', 'number', 'total', 'amount_paid', 'payment_status', 'status', 'created_at']);
    }

    private function ensureCustomer(User $customer): void
    {
        if (! $customer->hasRole(Role::SLUG_CUSTOMER)) {
            abort(404);
        }
    }
}
