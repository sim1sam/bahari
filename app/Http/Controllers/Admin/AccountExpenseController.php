<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountExpense;
use App\Models\AccountHead;
use App\Models\PaymentBank;
use App\Services\BankBalanceService;
use App\Services\FinancialTransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AccountExpenseController extends Controller
{
    public function __construct(private FinancialTransactionService $financialTransactions) {}

    public function index(Request $request, BankBalanceService $balances): View
    {
        $query = $this->filteredQuery($request);
        $statsQuery = $this->filteredQuery($request);
        $accountHeads = $this->expenseAccountHeads();
        $paymentBanks = $this->paymentBanks();

        return view('admin.account-expenses.index', [
            'expenses' => $query->paginate(20)->withQueryString(),
            'accountHeads' => $accountHeads,
            'paymentBanks' => $paymentBanks,
            'bankBalances' => $balances->balances($paymentBanks),
            'expenseFilters' => $request->only(['date_from', 'date_to', 'account_head_id', 'payment_bank_id']),
            'stats' => [
                'total' => (clone $statsQuery)->count(),
                'amount' => (float) (clone $statsQuery)->sum('amount'),
                'charges' => (float) (clone $statsQuery)->sum('bank_charge_amount'),
                'inventory' => (float) (clone $statsQuery)->where(function ($q) {
                    $q->whereNotNull('product_id')
                        ->orWhereHas('accountHead', fn ($head) => $head->where('code', 'INVENTORY'));
                })->sum('amount'),
                'operating' => (float) (clone $statsQuery)->where(function ($q) {
                    $q->whereNull('product_id')
                        ->whereDoesntHave('accountHead', fn ($head) => $head->where('code', 'INVENTORY'));
                })->sum('amount'),
            ],
        ]);
    }

    public function create(BankBalanceService $balances): View
    {
        $paymentBanks = $this->paymentBanks();

        return view('admin.account-expenses.form', [
            'expense' => new AccountExpense(['expense_date' => now()->toDateString()]),
            'accountHeads' => $this->expenseAccountHeads(),
            'paymentBanks' => $paymentBanks,
            'bankBalances' => $balances->balances($paymentBanks),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);
        $validated['recorded_by'] = Auth::id();
        $expense = AccountExpense::create($validated);
        $this->financialTransactions->recordFromExpense($expense);

        return redirect()
            ->route('admin.account-expenses.index')
            ->with('success', 'Expense recorded.');
    }

    public function edit(AccountExpense $expense, BankBalanceService $balances): View
    {
        $paymentBanks = $this->paymentBanks($expense->payment_bank_id);
        $bankBalances = $balances->balances($paymentBanks);

        if ($expense->payment_bank_id) {
            $bankBalances[$expense->payment_bank_id] = round(
                ($bankBalances[$expense->payment_bank_id] ?? 0) + (float) $expense->total_deduction,
                2
            );
        }

        return view('admin.account-expenses.form', [
            'expense' => $expense->load(['accountHead', 'paymentBank']),
            'accountHeads' => $this->expenseAccountHeads($expense->account_head_id),
            'paymentBanks' => $paymentBanks,
            'bankBalances' => $bankBalances,
        ]);
    }

    public function update(Request $request, AccountExpense $expense): RedirectResponse
    {
        $expense->update($this->validated($request, $expense));
        $this->financialTransactions->recordFromExpense($expense->fresh());

        return redirect()
            ->route('admin.account-expenses.index')
            ->with('success', 'Expense updated.');
    }

    public function destroy(AccountExpense $expense): RedirectResponse
    {
        $this->financialTransactions->deleteForSource($expense);
        AccountExpense::query()->whereKey($expense->getKey())->delete();

        return redirect()
            ->route('admin.account-expenses.index')
            ->with('success', 'Expense deleted.');
    }

    private function filteredQuery(Request $request)
    {
        $query = AccountExpense::query()
            ->with(['recorder', 'product', 'accountHead', 'paymentBank'])
            ->latest('expense_date');

        if ($request->filled('date_from')) {
            $query->whereDate('expense_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('expense_date', '<=', $request->date_to);
        }

        if ($request->filled('account_head_id')) {
            $query->where('account_head_id', $request->account_head_id);
        }

        if ($request->filled('payment_bank_id')) {
            $query->where('payment_bank_id', $request->payment_bank_id);
        }

        return $query;
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, AccountHead> */
    private function expenseAccountHeads(?int $includeHeadId = null)
    {
        return AccountHead::query()
            ->with('accountHeadType')
            ->whereHas('accountHeadType', fn ($query) => $query->where('slug', 'expense'))
            ->where(function ($query) use ($includeHeadId) {
                $query->where('is_active', true);

                if ($includeHeadId) {
                    $query->orWhere('id', $includeHeadId);
                }
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, PaymentBank> */
    private function paymentBanks(?int $includeBankId = null)
    {
        return PaymentBank::query()
            ->where(function ($query) use ($includeBankId) {
                $query->where('is_active', true);

                if ($includeBankId) {
                    $query->orWhere('id', $includeBankId);
                }
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?AccountExpense $expense = null): array
    {
        $validated = $request->validate([
            'expense_date' => 'required|date',
            'account_head_id' => [
                'required',
                Rule::exists('account_heads', 'id'),
            ],
            'title' => 'required|string|max:200',
            'notes' => 'nullable|string|max:2000',
            'amount' => 'required|numeric|min:0.01',
            'payment_bank_id' => ['required', Rule::exists('payment_banks', 'id')],
            'reference' => 'nullable|string|max:100',
        ]);

        $bank = PaymentBank::query()->findOrFail($validated['payment_bank_id']);
        $sameBank = $expense && (int) $expense->payment_bank_id === (int) $bank->id;
        $chargePercent = $sameBank
            ? (float) $expense->bank_charge_percent
            : (float) $bank->charge_percent;
        $amount = round((float) $validated['amount'], 2);
        $chargeSplit = $this->financialTransactions->calculateCharge($amount, $chargePercent);

        $validated['bank_charge_percent'] = $chargePercent;
        $validated['bank_charge_amount'] = $chargeSplit['bank_charge_amount'];
        $validated['total_deduction'] = $chargeSplit['total_amount'];
        $validated['payment_method'] = $bank->displayName();

        return $validated;
    }
}
