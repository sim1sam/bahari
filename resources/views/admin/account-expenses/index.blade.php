@extends('layouts.admin')

@section('title', 'Expenses')
@section('page_title', 'Expenses')

@section('content')
    <div class="settings-page">
        <section class="settings-hero">
            <div>
                <span class="settings-eyebrow">Account</span>
                <h2>Expenses</h2>
                <p>Record and manage operating expenses for your business.</p>
                <div class="settings-hero-meta">
                    <span class="settings-hero-chip"><i class="fas fa-receipt"></i> {{ $stats['total'] }} record{{ $stats['total'] === 1 ? '' : 's' }}</span>
                    <span class="settings-hero-chip"><i class="fas fa-coins"></i> {{ money($stats['amount']) }}</span>
                </div>
            </div>
            <div class="settings-hero-actions">
                <a href="{{ route('admin.account-expenses.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus mr-1"></i> Add Expense
                </a>
            </div>
        </section>

        <section class="row mb-3">
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="settings-stat settings-stat--teal">
                    <span class="settings-stat-icon"><i class="fas fa-receipt"></i></span>
                    <div>
                        <div class="settings-stat-value">{{ $stats['total'] }}</div>
                        <div class="settings-stat-label">Total Records</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="settings-stat settings-stat--amber">
                    <span class="settings-stat-icon"><i class="fas fa-coins"></i></span>
                    <div>
                        <div class="settings-stat-value">{{ money($stats['amount']) }}</div>
                        <div class="settings-stat-label">Total Amount</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="settings-stat settings-stat--blue">
                    <span class="settings-stat-icon"><i class="fas fa-percentage"></i></span>
                    <div>
                        <div class="settings-stat-value">{{ money($stats['charges']) }}</div>
                        <div class="settings-stat-label">Bank Charges</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="settings-stat settings-stat--green">
                    <span class="settings-stat-icon"><i class="fas fa-money-check-alt"></i></span>
                    <div>
                        <div class="settings-stat-value">{{ money($stats['amount'] + $stats['charges']) }}</div>
                        <div class="settings-stat-label">Total Deducted</div>
                    </div>
                </article>
            </div>
        </section>

        @include('admin.account.partials.nav')

        <div class="settings-card">
            <div class="settings-card-head">
                <div>
                    <h3>All Expenses</h3>
                    <p>Operating costs used in profit &amp; loss reports.</p>
                </div>
                <a href="{{ route('admin.account-expenses.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus mr-1"></i> Add Expense
                </a>
            </div>
            <div class="settings-card-body border-bottom">
                <form action="{{ route('admin.account-expenses.index') }}" method="GET" class="row align-items-end">
                    <div class="col-md-2">
                        <div class="settings-field mb-0">
                            <label for="date_from">From</label>
                            <input type="date" name="date_from" id="date_from" class="form-control" value="{{ $expenseFilters['date_from'] ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="settings-field mb-0">
                            <label for="date_to">To</label>
                            <input type="date" name="date_to" id="date_to" class="form-control" value="{{ $expenseFilters['date_to'] ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="settings-field mb-0">
                            <label for="account_head_id">Account Head</label>
                            <select name="account_head_id" id="account_head_id" class="form-control settings-textarea">
                                <option value="">All account heads</option>
                                @foreach ($accountHeads as $head)
                                    <option value="{{ $head->id }}" @selected((string) ($expenseFilters['account_head_id'] ?? '') === (string) $head->id)>{{ $head->displayName() }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="settings-field mb-0">
                            <label for="payment_bank_id">Payment Bank</label>
                            <select name="payment_bank_id" id="payment_bank_id" class="form-control settings-textarea">
                                <option value="">All banks</option>
                                @foreach ($paymentBanks as $bank)
                                    <option value="{{ $bank->id }}" @selected((string) ($expenseFilters['payment_bank_id'] ?? '') === (string) $bank->id)>
                                        {{ $bank->displayName() }} — {{ money($bankBalances[$bank->id] ?? 0) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search mr-1"></i> Filter</button>
                        @if (($expenseFilters['date_from'] ?? '') || ($expenseFilters['date_to'] ?? '') || ($expenseFilters['account_head_id'] ?? '') || ($expenseFilters['payment_bank_id'] ?? ''))
                            <a href="{{ route('admin.account-expenses.index') }}" class="btn btn-secondary">Clear</a>
                        @endif
                    </div>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 settings-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Account Head</th>
                            <th>Title</th>
                            <th>Bank</th>
                            <th>Reference</th>
                            <th class="text-right">Expense</th>
                            <th class="text-right">Charge</th>
                            <th class="text-right">Deducted</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($expenses as $expense)
                            <tr>
                                <td>{{ $expense->expense_date->format('M d, Y') }}</td>
                                <td>
                                    <span class="settings-status settings-status--hidden">{{ $expense->accountHeadLabel() }}</span>
                                </td>
                                <td>
                                    <strong>{{ $expense->title }}</strong>
                                    @if ($expense->product)
                                        <div class="text-muted small">
                                            <a href="{{ route('admin.products.edit', $expense->product) }}">View product</a>
                                        </div>
                                    @endif
                                    @if ($expense->notes)
                                        <div class="text-muted small">{{ Str::limit($expense->notes, 50) }}</div>
                                    @endif
                                </td>
                                <td>{{ $expense->paymentBank?->displayName() ?? '—' }}</td>
                                <td>{{ $expense->reference ?: '—' }}</td>
                                <td class="text-right font-weight-bold">{{ money($expense->amount) }}</td>
                                <td class="text-right">{{ money($expense->bank_charge_amount) }}</td>
                                <td class="text-right font-weight-bold">{{ money($expense->total_deduction) }}</td>
                                <td class="text-right">
                                    <div class="settings-actions">
                                        <a href="{{ route('admin.account-expenses.edit', $expense) }}" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                                        <form action="{{ route('admin.account-expenses.destroy', $expense) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this expense?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="settings-empty">
                                    <i class="fas fa-receipt"></i>
                                    <strong>No expenses recorded yet</strong>
                                    <p>Add your first expense to track operating costs.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($expenses->hasPages())
                <div class="settings-card-footer">{{ $expenses->links() }}</div>
            @endif
        </div>
    </div>
@endsection

@push('styles')
    @include('admin.settings.partials.page-styles')
@endpush
