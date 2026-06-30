<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountExpense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $query = AccountExpense::query()->with(['recorder', 'product'])->latest('expense_date');

        if ($request->filled('date_from')) {
            $query->whereDate('expense_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('expense_date', '<=', $request->date_to);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        return view('admin.reports.expenses.index', [
            'expenses' => $query->paginate(20)->withQueryString(),
            'categories' => AccountExpense::CATEGORIES,
            'expenseFilters' => $request->only(['date_from', 'date_to', 'category']),
        ]);
    }

    public function create(): View
    {
        return view('admin.reports.expenses.form', [
            'expense' => new AccountExpense(['expense_date' => now()->toDateString()]),
            'categories' => AccountExpense::CATEGORIES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        AccountExpense::create($this->validated($request));

        return redirect()
            ->route('admin.reports.expenses.index')
            ->with('success', 'Expense recorded.');
    }

    public function edit(AccountExpense $expense): View
    {
        return view('admin.reports.expenses.form', [
            'expense' => $expense,
            'categories' => AccountExpense::CATEGORIES,
        ]);
    }

    public function update(Request $request, AccountExpense $expense): RedirectResponse
    {
        $expense->update($this->validated($request));

        return redirect()
            ->route('admin.reports.expenses.index')
            ->with('success', 'Expense updated.');
    }

    public function destroy(AccountExpense $expense): RedirectResponse
    {
        $expense->delete();

        return redirect()
            ->route('admin.reports.expenses.index')
            ->with('success', 'Expense deleted.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'expense_date' => 'required|date',
            'category' => 'required|string|in:'.implode(',', array_keys(AccountExpense::CATEGORIES)),
            'title' => 'required|string|max:200',
            'notes' => 'nullable|string|max:2000',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'nullable|string|max:40',
            'reference' => 'nullable|string|max:100',
        ]);

        $validated['recorded_by'] = auth()->id();

        return $validated;
    }
}
