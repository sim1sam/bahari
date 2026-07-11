@extends('layouts.admin')

@section('title', $expense->exists ? 'Edit Expense' : 'Add Expense')
@section('page_title', $expense->exists ? 'Edit Expense' : 'Add Expense')

@section('content')
    <div class="settings-page">
        <a href="{{ route('admin.account-expenses.index') }}" class="settings-back-link">
            <i class="fas fa-arrow-left"></i> Back to Expenses
        </a>

        <section class="settings-hero">
            <div>
                <span class="settings-eyebrow">Account</span>
                <h2>{{ $expense->exists ? 'Edit Expense' : 'Add Expense' }}</h2>
                <p>{{ $expense->exists ? 'Update expense details and amount.' : 'Record a new operating expense.' }}</p>
                @if ($expense->exists)
                    <div class="settings-hero-meta">
                        <span class="settings-hero-chip"><i class="fas fa-calendar"></i> {{ $expense->expense_date->format('M d, Y') }}</span>
                        <span class="settings-hero-chip"><i class="fas fa-coins"></i> {{ money($expense->amount) }}</span>
                    </div>
                @endif
            </div>
        </section>

        @include('admin.account.partials.nav')

        <form action="{{ $expense->exists ? route('admin.account-expenses.update', $expense) : route('admin.account-expenses.store') }}" method="POST">
            @csrf
            @if ($expense->exists) @method('PUT') @endif

            <div class="settings-card">
                <div class="settings-card-body">
                    <section class="settings-form-panel mb-0">
                        <div class="settings-form-panel-head">
                            <span class="settings-form-panel-icon settings-form-panel-icon--brand"><i class="fas fa-receipt"></i></span>
                            <div>
                                <h4>Expense Details</h4>
                                <p>Date, account head, amount, and payment information.</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="settings-field">
                                    <label for="expense_date">Date *</label>
                                    <div class="settings-input-wrap">
                                        <span class="settings-input-icon"><i class="fas fa-calendar"></i></span>
                                        <input type="date" name="expense_date" id="expense_date" class="form-control @error('expense_date') is-invalid @enderror" value="{{ old('expense_date', $expense->expense_date?->format('Y-m-d')) }}" required>
                                    </div>
                                    @error('expense_date')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="settings-field">
                                    <label for="account_head_id">Account Head *</label>
                                    <select name="account_head_id" id="account_head_id" class="form-control settings-textarea @error('account_head_id') is-invalid @enderror" required>
                                        <option value="" disabled @selected(! old('account_head_id', $expense->account_head_id))>Select account head</option>
                                        @foreach ($accountHeads as $head)
                                            <option value="{{ $head->id }}" @selected((string) old('account_head_id', $expense->account_head_id) === (string) $head->id)>
                                                {{ $head->displayName() }}@unless ($head->is_active) (inactive)@endunless
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('account_head_id')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                    @if ($accountHeads->isEmpty())
                                        <p class="text-muted small mt-2 mb-0">
                                            No expense account heads yet.
                                            <a href="{{ route('admin.account-heads.create') }}">Create one first</a>.
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="settings-field">
                                    <label for="amount">Amount *</label>
                                    <div class="settings-input-wrap">
                                        <span class="settings-input-icon"><i class="fas fa-coins"></i></span>
                                        <input type="number" step="0.01" min="0.01" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount', $expense->amount) }}" required>
                                    </div>
                                    @error('amount')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="settings-field">
                                    <label for="title">Title *</label>
                                    <div class="settings-input-wrap">
                                        <span class="settings-input-icon"><i class="fas fa-tag"></i></span>
                                        <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $expense->title) }}" required>
                                    </div>
                                    @error('title')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="settings-field">
                                    <label for="reference">Reference</label>
                                    <div class="settings-input-wrap">
                                        <span class="settings-input-icon"><i class="fas fa-hashtag"></i></span>
                                        <input type="text" name="reference" id="reference" class="form-control @error('reference') is-invalid @enderror" value="{{ old('reference', $expense->reference) }}" placeholder="Invoice #, receipt #">
                                    </div>
                                    @error('reference')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="settings-field">
                                    <label for="payment_bank_id">Payment Bank *</label>
                                    <select name="payment_bank_id" id="payment_bank_id" class="form-control settings-textarea @error('payment_bank_id') is-invalid @enderror" required>
                                        <option value="" disabled @selected(! old('payment_bank_id', $expense->payment_bank_id))>Select payment bank</option>
                                        @foreach ($paymentBanks as $bank)
                                            <option
                                                value="{{ $bank->id }}"
                                                data-charge="{{ $expense->exists && (int) $expense->payment_bank_id === (int) $bank->id ? (float) $expense->bank_charge_percent : (float) $bank->charge_percent }}"
                                                data-balance="{{ $bankBalances[$bank->id] ?? 0 }}"
                                                @selected((string) old('payment_bank_id', $expense->payment_bank_id) === (string) $bank->id)
                                            >
                                                {{ $bank->displayName() }}@unless ($bank->is_active) (inactive)@endunless
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('payment_bank_id')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                    @if ($paymentBanks->isEmpty())
                                        <p class="text-muted small mt-2 mb-0">
                                            No payment banks yet.
                                            <a href="{{ route('admin.payment-banks.index') }}">Create one first</a>.
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="expense-bank-summary">
                                    <div>
                                        <span>Current Bank Balance</span>
                                        <strong id="bank_balance">—</strong>
                                    </div>
                                    <div>
                                        <span>Bank Charge</span>
                                        <strong><span id="charge_percent">0.00</span>% = <span id="charge_amount">0.00</span></strong>
                                    </div>
                                    <div>
                                        <span>Total Deduction</span>
                                        <strong id="total_deduction">0.00</strong>
                                    </div>
                                    <div>
                                        <span>Balance After Expense</span>
                                        <strong id="balance_after">—</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="settings-field mb-0">
                                    <label for="notes">Notes</label>
                                    <textarea name="notes" id="notes" class="form-control settings-textarea" rows="3" placeholder="Optional notes about this expense">{{ old('notes', $expense->notes) }}</textarea>
                                    @error('notes')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
                <div class="settings-card-footer d-flex align-items-center flex-wrap gap-2">
                    <button type="submit" class="btn btn-info btn-lg">
                        <i class="fas fa-save mr-1"></i> {{ $expense->exists ? 'Update Expense' : 'Save Expense' }}
                    </button>
                    <a href="{{ route('admin.account-expenses.index') }}" class="btn btn-secondary btn-lg">Cancel</a>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('styles')
    @include('admin.settings.partials.page-styles')
    <style>
        .expense-bank-summary {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.75rem;
            margin-bottom: 1rem;
            padding: 1rem;
            border: 1px solid #cffafe;
            border-radius: 0.85rem;
            background: #ecfeff;
        }

        .expense-bank-summary span {
            display: block;
            color: #64748b;
            font-size: 0.75rem;
        }

        .expense-bank-summary strong {
            display: block;
            margin-top: 0.2rem;
            color: #0f172a;
            font-size: 0.95rem;
        }

        @media (max-width: 767.98px) {
            .expense-bank-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
    </style>
@endpush

@push('scripts')
<script>
    (function () {
        var bank = document.getElementById('payment_bank_id');
        var amount = document.getElementById('amount');
        if (!bank || !amount) return;

        function money(value) {
            return Number(value || 0).toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function roundChargeToPreviousFive(charge) {
            if (charge <= 0) {
                return 0;
            }

            var rounded = Math.round(charge * 100) / 100;

            return Math.floor(rounded / 5) * 5;
        }

        function calculate() {
            var option = bank.options[bank.selectedIndex];
            var chargePercent = Number(option && option.dataset.charge || 0);
            var balance = Number(option && option.dataset.balance || 0);
            var expenseAmount = Number(amount.value || 0);
            var rawCharge = Math.round((expenseAmount * chargePercent / 100) * 100) / 100;
            var chargeAmount = roundChargeToPreviousFive(rawCharge);
            var total = expenseAmount + chargeAmount;

            document.getElementById('bank_balance').textContent = option && option.value ? money(balance) : '—';
            document.getElementById('charge_percent').textContent = chargePercent.toFixed(2);
            document.getElementById('charge_amount').textContent = money(chargeAmount);
            document.getElementById('total_deduction').textContent = money(total);
            document.getElementById('balance_after').textContent = option && option.value ? money(balance - total) : '—';
        }

        bank.addEventListener('change', calculate);
        amount.addEventListener('input', calculate);
        calculate();
    })();
</script>
@endpush
