<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankInterTransfer;
use App\Models\PaymentBank;
use App\Services\BankBalanceService;
use App\Services\FinancialTransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BankInterTransferController extends Controller
{
    public function __construct(
        private FinancialTransactionService $financialTransactions,
        private BankBalanceService $bankBalances,
    ) {}

    public function index(): View
    {
        $banks = PaymentBank::query()->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.bank-inter-transfers.index', [
            'transfers' => BankInterTransfer::query()
                ->with(['fromBank', 'toBank', 'recorder'])
                ->latest('transfer_date')
                ->latest('id')
                ->paginate(20),
            'banks' => $banks,
            'bankBalances' => $this->bankBalances->balances($banks),
            'stats' => [
                'total' => BankInterTransfer::count(),
                'today' => BankInterTransfer::whereDate('transfer_date', now()->toDateString())->count(),
                'today_amount' => (float) BankInterTransfer::whereDate('transfer_date', now()->toDateString())->sum('amount'),
            ],
        ]);
    }

    public function create(BankBalanceService $balances): View
    {
        $banks = PaymentBank::query()->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.bank-inter-transfers.create', [
            'banks' => $banks,
            'bankBalances' => $balances->balances($banks),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'from_bank_id' => ['required', Rule::exists('payment_banks', 'id')],
            'to_bank_id' => ['required', Rule::exists('payment_banks', 'id'), 'different:from_bank_id'],
            'amount' => 'required|numeric|min:0.01',
            'transfer_date' => 'required|date',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($validated) {
            $transfer = BankInterTransfer::create([
                'from_bank_id' => $validated['from_bank_id'],
                'to_bank_id' => $validated['to_bank_id'],
                'amount' => round((float) $validated['amount'], 2),
                'transfer_date' => $validated['transfer_date'],
                'notes' => $validated['notes'] ?? null,
                'recorded_by' => Auth::id(),
            ]);

            $this->financialTransactions->recordInterTransfer($transfer->load(['fromBank', 'toBank']));
        });

        return redirect()
            ->route('admin.bank-inter-transfers.index')
            ->with('success', 'Bank transfer recorded.');
    }
}
