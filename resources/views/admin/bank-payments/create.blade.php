@extends('layouts.admin')

@section('title', 'Make Payment')
@section('page_title', 'Make Payment')

@section('content')
    <form action="{{ route('admin.bank-payments.store') }}" method="POST" id="bank-payment-form" class="bank-payment-page">
        @csrf

        <section class="bank-payment-hero">
            <div>
                <span class="bank-payment-eyebrow">Account</span>
                <h2>Make Payment</h2>
                <p>Log customer bank transfers, link them to orders, and update ledger balances.</p>
            </div>
            <div class="bank-payment-hero-actions">
                <a href="{{ route('admin.payment-banks.index') }}" class="btn btn-light">
                    <i class="fas fa-university mr-1"></i> Payment Banks
                </a>
                <a href="{{ route('admin.customer-ledgers.index') }}" class="btn btn-light">
                    <i class="fas fa-book mr-1"></i> Customer Ledgers
                </a>
            </div>
        </section>

        @include('admin.account.partials.nav')

        <section class="row bank-payment-stats">
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="bank-payment-stat bank-payment-stat--total">
                    <span class="bank-payment-stat-icon"><i class="fas fa-receipt"></i></span>
                    <div>
                        <div class="bank-payment-stat-value">{{ number_format($stats['total']) }}</div>
                        <div class="bank-payment-stat-label">Total Recorded</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="bank-payment-stat bank-payment-stat--today">
                    <span class="bank-payment-stat-icon"><i class="fas fa-calendar-day"></i></span>
                    <div>
                        <div class="bank-payment-stat-value">{{ number_format($stats['today']) }}</div>
                        <div class="bank-payment-stat-label">Today</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="bank-payment-stat bank-payment-stat--amount">
                    <span class="bank-payment-stat-icon"><i class="fas fa-coins"></i></span>
                    <div>
                        <div class="bank-payment-stat-value">{{ money($stats['today_amount'], 0) }}</div>
                        <div class="bank-payment-stat-label">Collected Today</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="bank-payment-stat bank-payment-stat--banks">
                    <span class="bank-payment-stat-icon"><i class="fas fa-university"></i></span>
                    <div>
                        <div class="bank-payment-stat-value">{{ number_format($stats['banks']) }}</div>
                        <div class="bank-payment-stat-label">Active Banks</div>
                    </div>
                </article>
            </div>
        </section>

        @if ($errors->any())
            <div class="bank-payment-alert">
                <i class="fas fa-exclamation-circle"></i>
                <div>
                    <strong>Please check the form.</strong>
                    <span>{{ $errors->count() }} {{ Str::plural('field', $errors->count()) }} need your attention.</span>
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-xl-8">
                <div class="card bank-payment-card">
                    <div class="bank-payment-card-head">
                        <span class="bank-payment-section-icon bank-payment-section-icon--amber">
                            <i class="fas fa-university"></i>
                        </span>
                        <div>
                            <h3 class="mb-0">Select Payment Bank</h3>
                            <p class="mb-0 text-muted">Choose the bank account where the customer paid.</p>
                        </div>
                        <span class="bank-payment-section-number">01</span>
                    </div>
                    <div class="card-body bank-payment-form-body">
                        <input type="hidden" name="payment_bank_id" id="payment_bank_id" value="{{ old('payment_bank_id', $selectedBankId) }}">
                        @error('payment_bank_id')<div class="text-danger small mb-3">{{ $message }}</div>@enderror

                        @if ($banks->isEmpty())
                            <div class="bank-payment-empty">
                                <i class="fas fa-university"></i>
                                <strong>No payment banks yet</strong>
                                <p class="mb-0">Create a bank account before recording payments.</p>
                                <a href="{{ route('admin.payment-banks.index') }}" class="btn btn-info btn-sm mt-2">Add Payment Bank</a>
                            </div>
                        @else
                            <div class="bank-payment-bank-grid">
                                @foreach ($banks as $bank)
                                    <button
                                        type="button"
                                        class="bank-payment-bank-btn {{ (int) old('payment_bank_id', $selectedBankId) === $bank->id ? 'is-selected' : '' }}"
                                        data-bank-id="{{ $bank->id }}"
                                        data-name="{{ $bank->name }}"
                                        data-account-name="{{ $bank->account_name }}"
                                        data-account-number="{{ $bank->account_number }}"
                                        data-branch="{{ $bank->branch }}"
                                        data-instructions="{{ $bank->instructions }}"
                                        data-charge="{{ (float) $bank->charge_percent }}"
                                        data-balance="{{ $bankBalances[$bank->id] ?? 0 }}"
                                        data-active="{{ $bank->is_active ? '1' : '0' }}"
                                        data-image="{{ $bank->imageUrl() }}"
                                        data-display="{{ $bank->displayName() }}"
                                    >
                                        <span class="bank-payment-bank-btn-thumb">
                                            @if ($bank->imageUrl())
                                                <img src="{{ $bank->imageUrl() }}" alt="{{ $bank->name }}">
                                            @else
                                                <i class="fas fa-university"></i>
                                            @endif
                                        </span>
                                        <span class="bank-payment-bank-btn-body">
                                            <span class="bank-payment-bank-btn-title">{{ $bank->name }}</span>
                                            @if ($bank->account_number)
                                                <span class="bank-payment-bank-btn-meta">{{ $bank->account_number }}</span>
                                            @endif
                                            <span class="bank-payment-bank-btn-balance">Bal {{ money($bankBalances[$bank->id] ?? 0) }}</span>
                                        </span>
                                        <span class="bank-payment-bank-btn-select">Select</span>
                                    </button>
                                @endforeach
                            </div>

                            <div id="bank-details-panel" class="bank-payment-bank-details d-none">
                                <div class="bank-payment-bank-details-head">
                                    <div class="bank-payment-bank-details-title">
                                        <span id="bank-details-thumb" class="bank-payment-bank-details-thumb"></span>
                                        <div>
                                            <strong id="bank-details-name">—</strong>
                                            <span id="bank-details-status" class="bank-payment-bank-status"></span>
                                        </div>
                                    </div>
                                    <div class="bank-payment-bank-details-balance">
                                        <small>Current Balance</small>
                                        <strong id="bank-details-balance">—</strong>
                                    </div>
                                </div>
                                <div class="bank-payment-bank-details-grid">
                                    <div>
                                        <small>Account Name</small>
                                        <strong id="bank-details-account-name">—</strong>
                                    </div>
                                    <div>
                                        <small>Account Number</small>
                                        <strong id="bank-details-account-number">—</strong>
                                    </div>
                                    <div>
                                        <small>Branch / Type</small>
                                        <strong id="bank-details-branch">—</strong>
                                    </div>
                                    <div>
                                        <small>Charge</small>
                                        <strong id="bank-details-charge">—</strong>
                                    </div>
                                </div>
                                <div id="bank-details-instructions-wrap" class="bank-payment-bank-instructions d-none">
                                    <small>Instructions</small>
                                    <p id="bank-details-instructions" class="mb-0"></p>
                                </div>
                            </div>

                            <div id="bank-payments-panel" class="bank-payment-bank-payments d-none">
                                <div class="bank-payment-bank-payments-head">
                                    <h4 class="mb-0">Recent Customer Payments</h4>
                                    <span id="bank-payments-count" class="bank-payment-bank-payments-count"></span>
                                </div>
                                <div id="bank-payments-loading" class="bank-payment-bank-payments-loading d-none">
                                    <i class="fas fa-spinner fa-spin"></i> Loading payments...
                                </div>
                                <div id="bank-payments-empty" class="bank-payment-bank-payments-empty d-none">
                                    No customer payments recorded for this bank yet.
                                </div>
                                <div class="table-responsive">
                                    <table class="table bank-payment-bank-table mb-0 d-none" id="bank-payments-table">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Customer</th>
                                                <th>Type</th>
                                                <th>Order</th>
                                                <th class="text-right">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody id="bank-payments-body"></tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card bank-payment-card">
                    <div class="bank-payment-card-head">
                        <span class="bank-payment-section-icon bank-payment-section-icon--cyan">
                            <i class="fas fa-user"></i>
                        </span>
                        <div>
                            <h3 class="mb-0">Customer & Order</h3>
                            <p class="mb-0 text-muted">Choose who paid and optionally link an unpaid order.</p>
                        </div>
                        <span class="bank-payment-section-number">02</span>
                    </div>
                    <div class="card-body bank-payment-form-body">
                        <div class="form-group">
                            <label>Customer *</label>
                            <select name="user_id" id="customer_id" class="form-control @error('user_id') is-invalid @enderror" required>
                                <option value="">Select customer</option>
                                @foreach ($customers as $customer)
                                    <option
                                        value="{{ $customer->id }}"
                                        data-name="{{ $customer->name }}"
                                        data-email="{{ $customer->email }}"
                                        @selected((int) old('user_id', $selectedCustomerId) === $customer->id)
                                    >
                                        {{ $customer->name }} ({{ $customer->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-group mb-0">
                            <label>Order <span class="text-muted">(optional)</span></label>
                            <select name="order_id" id="order_id" class="form-control @error('order_id') is-invalid @enderror">
                                <option value="">No order — record as advance payment</option>
                                @foreach ($orders as $order)
                                    <option
                                        value="{{ $order->id }}"
                                        data-due="{{ $order->amountDue() }}"
                                        data-number="{{ $order->number }}"
                                        data-status="{{ $order->paymentStatusLabel() }}"
                                        @selected((int) old('order_id', $selectedOrderId) === $order->id)
                                    >
                                        {{ $order->number }} — Due {{ money($order->amountDue()) }} ({{ $order->paymentStatusLabel() }})
                                    </option>
                                @endforeach
                            </select>
                            @error('order_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            <small class="text-muted d-block mt-2">Select a customer first to load their unpaid orders.</small>
                        </div>
                    </div>
                </div>

                <div class="card bank-payment-card">
                    <div class="bank-payment-card-head">
                        <span class="bank-payment-section-icon bank-payment-section-icon--emerald">
                            <i class="fas fa-money-check-alt"></i>
                        </span>
                        <div>
                            <h3 class="mb-0">Payment Details</h3>
                            <p class="mb-0 text-muted">Enter amount, date, and any reference notes.</p>
                        </div>
                        <span class="bank-payment-section-number">03</span>
                    </div>
                    <div class="card-body bank-payment-form-body">
                        <div class="form-group">
                            <label>Payment Date *</label>
                            <input type="date" name="payment_date" id="payment_date" class="form-control @error('payment_date') is-invalid @enderror" value="{{ old('payment_date', now()->toDateString()) }}" required>
                            @error('payment_date')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-group">
                            <label>Amount (BDT) *</label>
                            <div class="bank-payment-amount-wrap">
                                <span class="bank-payment-currency">৳</span>
                                <input type="number" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror" min="0.01" step="0.01" value="{{ old('amount') }}" placeholder="0.00" required>
                            </div>
                            @error('amount')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                            <small id="order-due-hint" class="bank-payment-due-hint d-none"></small>
                        </div>

                        <div class="form-group mb-0">
                            <label>Notes</label>
                            <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Transaction reference, remarks...">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                    <div class="bank-payment-card-footer d-xl-none">
                        <button type="submit" class="btn btn-info btn-block">
                            <i class="fas fa-save mr-1"></i> Save Payment
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="bank-payment-summary-wrap">
                    <div class="card bank-payment-card bank-payment-summary">
                        <div class="bank-payment-card-head">
                            <span class="bank-payment-section-icon bank-payment-section-icon--violet">
                                <i class="fas fa-clipboard-list"></i>
                            </span>
                            <div>
                                <h3 class="mb-0">Payment Preview</h3>
                                <p class="mb-0 text-muted">Review before saving.</p>
                            </div>
                        </div>
                        <div class="card-body bank-payment-summary-body">
                            <div class="bank-payment-preview-item">
                                <small>Customer</small>
                                <strong id="preview-customer">—</strong>
                                <span id="preview-customer-email" class="text-muted"></span>
                            </div>
                            <div class="bank-payment-preview-item">
                                <small>Payment Type</small>
                                <strong id="preview-type">Advance payment</strong>
                            </div>
                            <div class="bank-payment-preview-item">
                                <small>Linked Order</small>
                                <strong id="preview-order">No order selected</strong>
                                <span id="preview-order-status" class="text-muted"></span>
                            </div>
                            <div class="bank-payment-preview-item">
                                <small>Balance Due</small>
                                <strong id="preview-due">—</strong>
                            </div>
                            <div class="bank-payment-preview-item">
                                <small>Payment Bank</small>
                                <strong id="preview-bank">—</strong>
                                <span id="preview-bank-balance" class="text-muted"></span>
                            </div>
                            <div class="bank-payment-preview-item">
                                <small>Payment Date</small>
                                <strong id="preview-date">{{ old('payment_date', now()->format('M d, Y')) }}</strong>
                            </div>
                            <div class="bank-payment-preview-amount">
                                <small>Amount to Record</small>
                                <div id="preview-amount">৳0.00</div>
                            </div>
                            <div class="bank-payment-preview-notes d-none" id="preview-notes-wrap">
                                <small>Notes</small>
                                <p id="preview-notes" class="mb-0"></p>
                            </div>
                        </div>
                        <div class="bank-payment-card-footer">
                            <button type="submit" class="btn btn-info btn-block">
                                <i class="fas fa-save mr-1"></i> Save Payment
                            </button>
                            <a href="{{ route('admin.customer-ledgers.index') }}" class="btn btn-default btn-block">
                                View Customer Ledgers
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('styles')
<style>
    .bank-payment-page {
        --bp-ink: #0f172a;
        --bp-muted: #64748b;
        --bp-border: #e2e8f0;
    }

    .bank-payment-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.25rem;
        padding: 1.35rem 1.5rem;
        border-radius: 1.1rem;
        color: #fff;
        background:
            radial-gradient(circle at 88% 15%, rgba(103, 232, 249, 0.25), transparent 35%),
            linear-gradient(135deg, #0f3d47 0%, #0e7490 55%, #0891b2 100%);
        box-shadow: 0 14px 32px rgba(8, 145, 178, 0.18);
    }

    .bank-payment-eyebrow {
        display: block;
        margin-bottom: 0.2rem;
        color: #a5f3fc;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.09em;
        text-transform: uppercase;
    }

    .bank-payment-hero h2 {
        margin: 0;
        font-size: 1.55rem;
        font-weight: 700;
    }

    .bank-payment-hero p {
        margin: 0.4rem 0 0;
        color: rgba(255, 255, 255, 0.82);
    }

    .bank-payment-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.55rem;
        flex-shrink: 0;
    }

    .bank-payment-stat {
        display: flex;
        align-items: center;
        gap: 0.9rem;
        height: 100%;
        padding: 1rem 1.1rem;
        border: 1px solid var(--bp-border);
        border-radius: 0.95rem;
        background: #fff;
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.04);
    }

    .bank-payment-stat-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.6rem;
        height: 2.6rem;
        border-radius: 0.75rem;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .bank-payment-stat--total .bank-payment-stat-icon { background: #ecfeff; color: #0891b2; }
    .bank-payment-stat--today .bank-payment-stat-icon { background: #f5f3ff; color: #7c3aed; }
    .bank-payment-stat--amount .bank-payment-stat-icon { background: #ecfdf5; color: #059669; }
    .bank-payment-stat--banks .bank-payment-stat-icon { background: #fff7ed; color: #d97706; }

    .bank-payment-stat-value {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--bp-ink);
        line-height: 1.1;
    }

    .bank-payment-stat-label {
        margin-top: 0.15rem;
        color: var(--bp-muted);
        font-size: 0.82rem;
        font-weight: 500;
    }

    .bank-payment-alert {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        margin-bottom: 1.25rem;
        padding: 0.85rem 1rem;
        border: 1px solid #fecaca;
        border-radius: 0.85rem;
        background: #fef2f2;
        color: #991b1b;
    }

    .bank-payment-alert > i {
        font-size: 1.2rem;
    }

    .bank-payment-alert span {
        display: block;
        color: #b91c1c;
        font-size: 0.83rem;
    }

    .bank-payment-card {
        border: 1px solid var(--bp-border);
        border-radius: 1rem;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
        overflow: hidden;
        margin-bottom: 1rem;
    }

    .bank-payment-card-head {
        position: relative;
        display: flex;
        align-items: center;
        gap: 0.85rem;
        padding: 1rem 1.15rem;
        border-bottom: 1px solid #eef2f7;
        background: #fff;
    }

    .bank-payment-card-head h3 {
        font-size: 1rem;
        font-weight: 700;
        color: var(--bp-ink);
    }

    .bank-payment-card-head p {
        font-size: 0.8rem;
    }

    .bank-payment-section-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.4rem;
        height: 2.4rem;
        border-radius: 0.75rem;
        font-size: 0.95rem;
        flex-shrink: 0;
    }

    .bank-payment-section-icon--cyan { background: #ecfeff; color: #0891b2; }
    .bank-payment-section-icon--emerald { background: #ecfdf5; color: #059669; }
    .bank-payment-section-icon--violet { background: #f5f3ff; color: #7c3aed; }
    .bank-payment-section-icon--amber { background: #fff7ed; color: #d97706; }

    .bank-payment-bank-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .bank-payment-bank-btn {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        width: 100%;
        padding: 0.85rem;
        border: 1px solid #dbe3ed;
        border-radius: 0.85rem;
        background: #fff;
        text-align: left;
        cursor: pointer;
        transition: border-color 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
    }

    .bank-payment-bank-btn:hover {
        border-color: #67e8f9;
        background: #f8feff;
    }

    .bank-payment-bank-btn.is-selected {
        border-color: #0891b2;
        background: linear-gradient(135deg, #ecfeff 0%, #f0fdfa 100%);
        box-shadow: 0 0 0 3px rgba(34, 211, 238, 0.12);
    }

    .bank-payment-bank-btn-thumb {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.8rem;
        height: 2.8rem;
        border-radius: 0.7rem;
        background: #ecfeff;
        color: #0891b2;
        flex-shrink: 0;
        overflow: hidden;
    }

    .bank-payment-bank-btn-thumb img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .bank-payment-bank-btn-body {
        display: flex;
        flex-direction: column;
        min-width: 0;
        flex: 1;
    }

    .bank-payment-bank-btn-title {
        color: var(--bp-ink);
        font-size: 0.88rem;
        font-weight: 700;
        line-height: 1.2;
    }

    .bank-payment-bank-btn-meta,
    .bank-payment-bank-btn-balance {
        color: var(--bp-muted);
        font-size: 0.74rem;
        line-height: 1.3;
    }

    .bank-payment-bank-btn-balance {
        margin-top: 0.1rem;
        color: #0f766e;
        font-weight: 600;
    }

    .bank-payment-bank-btn-select {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.2rem 0.55rem;
        border-radius: 999px;
        background: #f1f5f9;
        color: #64748b;
        font-size: 0.62rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        flex-shrink: 0;
    }

    .bank-payment-bank-btn.is-selected .bank-payment-bank-btn-select {
        background: #0891b2;
        color: #fff;
    }

    .bank-payment-bank-details {
        margin-bottom: 1rem;
        padding: 1rem;
        border: 1px solid #a5f3fc;
        border-radius: 0.9rem;
        background: linear-gradient(135deg, #ecfeff 0%, #f8fafc 100%);
    }

    .bank-payment-bank-details-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 0.85rem;
    }

    .bank-payment-bank-details-title {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        min-width: 0;
    }

    .bank-payment-bank-details-title strong {
        display: block;
        color: var(--bp-ink);
        font-size: 1rem;
    }

    .bank-payment-bank-details-thumb {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.8rem;
        height: 2.8rem;
        border-radius: 0.7rem;
        background: #fff;
        border: 1px solid #cffafe;
        overflow: hidden;
        flex-shrink: 0;
    }

    .bank-payment-bank-details-thumb img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .bank-payment-bank-status {
        display: inline-block;
        margin-top: 0.15rem;
        padding: 0.12rem 0.45rem;
        border-radius: 999px;
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .bank-payment-bank-status--active {
        background: #dcfce7;
        color: #166534;
    }

    .bank-payment-bank-status--inactive {
        background: #f1f5f9;
        color: #64748b;
    }

    .bank-payment-bank-details-balance {
        text-align: right;
        flex-shrink: 0;
    }

    .bank-payment-bank-details-balance small {
        display: block;
        color: var(--bp-muted);
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .bank-payment-bank-details-balance strong {
        display: block;
        margin-top: 0.1rem;
        color: #0f766e;
        font-size: 1.1rem;
    }

    .bank-payment-bank-details-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.75rem;
    }

    .bank-payment-bank-details-grid small {
        display: block;
        color: var(--bp-muted);
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .bank-payment-bank-details-grid strong {
        display: block;
        margin-top: 0.15rem;
        color: var(--bp-ink);
        font-size: 0.86rem;
        word-break: break-word;
    }

    .bank-payment-bank-instructions {
        margin-top: 0.85rem;
        padding-top: 0.85rem;
        border-top: 1px solid #cffafe;
    }

    .bank-payment-bank-instructions small {
        display: block;
        color: var(--bp-muted);
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .bank-payment-bank-instructions p {
        margin-top: 0.35rem;
        color: #0e7490;
        font-size: 0.84rem;
        line-height: 1.45;
    }

    .bank-payment-bank-payments {
        border: 1px solid var(--bp-border);
        border-radius: 0.85rem;
        overflow: hidden;
        background: #fff;
    }

    .bank-payment-bank-payments-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.8rem 1rem;
        border-bottom: 1px solid #eef2f7;
        background: #f8fafc;
    }

    .bank-payment-bank-payments-head h4 {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--bp-ink);
    }

    .bank-payment-bank-payments-count {
        color: var(--bp-muted);
        font-size: 0.75rem;
        font-weight: 600;
    }

    .bank-payment-bank-payments-loading,
    .bank-payment-bank-payments-empty {
        padding: 1rem;
        color: var(--bp-muted);
        font-size: 0.84rem;
        text-align: center;
    }

    .bank-payment-bank-table {
        font-size: 0.82rem;
    }

    .bank-payment-bank-table thead th {
        border-top: 0;
        border-bottom: 1px solid #eef2f7;
        background: #fff;
        color: var(--bp-muted);
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        white-space: nowrap;
    }

    .bank-payment-bank-table tbody td {
        vertical-align: middle;
        border-color: #eef2f7;
        color: #334155;
    }

    .bank-payment-bank-table .bank-payment-customer-email {
        display: block;
        color: var(--bp-muted);
        font-size: 0.72rem;
    }

    .bank-payment-empty {
        padding: 2rem 1rem;
        text-align: center;
        color: var(--bp-muted);
    }

    .bank-payment-empty i {
        display: block;
        margin-bottom: 0.65rem;
        font-size: 1.6rem;
        color: #cbd5e1;
    }

    .bank-payment-empty strong {
        display: block;
        color: var(--bp-ink);
        font-size: 0.95rem;
    }

    .bank-payment-empty p {
        margin-top: 0.35rem;
        font-size: 0.84rem;
    }

    .bank-payment-section-number {
        position: absolute;
        top: 0.65rem;
        right: 0.85rem;
        color: #e2e8f0;
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.08em;
    }

    .bank-payment-form-body {
        padding: 1.1rem 1.15rem;
    }

    .bank-payment-page label {
        margin-bottom: 0.35rem;
        color: #334155;
        font-size: 0.82rem;
        font-weight: 600;
    }

    .bank-payment-page .form-control {
        min-height: 2.55rem;
        border-color: #dbe3ed;
        border-radius: 0.55rem;
        color: var(--bp-ink);
        box-shadow: none;
    }

    .bank-payment-page .form-control:focus {
        border-color: #22d3ee;
        box-shadow: 0 0 0 3px rgba(34, 211, 238, 0.12);
    }

    .bank-payment-amount-wrap {
        position: relative;
    }

    .bank-payment-currency {
        position: absolute;
        left: 0.85rem;
        top: 50%;
        transform: translateY(-50%);
        color: #0891b2;
        font-weight: 700;
        z-index: 2;
    }

    .bank-payment-amount-wrap .form-control {
        padding-left: 2rem;
        font-size: 1.05rem;
        font-weight: 700;
    }

    .bank-payment-due-hint {
        display: block;
        margin-top: 0.45rem;
        padding: 0.45rem 0.65rem;
        border-radius: 0.55rem;
        background: #fff7ed;
        color: #c2410c;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .bank-payment-card-footer {
        padding: 0.9rem 1.15rem;
        border-top: 1px solid #eef2f7;
        background: #f8fafc;
    }

    .bank-payment-summary-wrap {
        position: sticky;
        top: 1rem;
    }

    .bank-payment-summary-body {
        padding: 1rem 1.15rem;
    }

    .bank-payment-preview-item {
        padding: 0.7rem 0;
        border-bottom: 1px solid #eef2f7;
    }

    .bank-payment-preview-item:last-of-type {
        border-bottom: 0;
    }

    .bank-payment-preview-item small {
        display: block;
        color: var(--bp-muted);
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .bank-payment-preview-item strong {
        display: block;
        margin-top: 0.15rem;
        color: var(--bp-ink);
        font-size: 0.92rem;
    }

    .bank-payment-preview-item span {
        display: block;
        margin-top: 0.1rem;
        font-size: 0.78rem;
    }

    .bank-payment-preview-amount {
        margin-top: 0.5rem;
        padding: 0.9rem;
        border-radius: 0.85rem;
        background: linear-gradient(135deg, #ecfeff 0%, #f0fdfa 100%);
        border: 1px solid #a5f3fc;
        text-align: center;
    }

    .bank-payment-preview-amount small {
        display: block;
        color: #0e7490;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .bank-payment-preview-amount div {
        margin-top: 0.25rem;
        color: #0f766e;
        font-size: 1.65rem;
        font-weight: 800;
        line-height: 1.1;
    }

    .bank-payment-preview-notes {
        margin-top: 0.75rem;
        padding: 0.75rem;
        border-radius: 0.75rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
    }

    .bank-payment-preview-notes small {
        display: block;
        color: var(--bp-muted);
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .bank-payment-preview-notes p {
        margin-top: 0.35rem;
        color: #475569;
        font-size: 0.84rem;
        line-height: 1.45;
    }

    @media (max-width: 1199.98px) {
        .bank-payment-summary-wrap {
            position: static;
        }
    }

    @media (max-width: 767.98px) {
        .bank-payment-hero {
            align-items: flex-start;
            flex-direction: column;
            padding: 1.1rem;
        }

        .bank-payment-hero h2 {
            font-size: 1.3rem;
        }

        .bank-payment-hero-actions {
            width: 100%;
        }

        .bank-payment-hero-actions .btn {
            flex: 1;
        }

        .bank-payment-bank-grid,
        .bank-payment-bank-details-grid {
            grid-template-columns: 1fr;
        }

        .bank-payment-bank-details-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .bank-payment-bank-details-balance {
            text-align: left;
        }
    }
</style>
@endpush

@push('scripts')
<script>
(function () {
    const customerSelect = document.getElementById('customer_id');
    const orderSelect = document.getElementById('order_id');
    const bankInput = document.getElementById('payment_bank_id');
    const amountInput = document.getElementById('amount');
    const dateInput = document.getElementById('payment_date');
    const notesInput = document.getElementById('notes');
    const dueHint = document.getElementById('order-due-hint');
    const ordersUrlTemplate = @json(url('/admin/customers')) + '/';
    const bankPaymentsUrlTemplate = @json(url('/admin/payment-banks')) + '/';
    const bankButtons = Array.from(document.querySelectorAll('.bank-payment-bank-btn'));

    const bankDetailsPanel = document.getElementById('bank-details-panel');
    const bankPaymentsPanel = document.getElementById('bank-payments-panel');
    const bankPaymentsLoading = document.getElementById('bank-payments-loading');
    const bankPaymentsEmpty = document.getElementById('bank-payments-empty');
    const bankPaymentsTable = document.getElementById('bank-payments-table');
    const bankPaymentsBody = document.getElementById('bank-payments-body');
    const bankPaymentsCount = document.getElementById('bank-payments-count');

    const previewCustomer = document.getElementById('preview-customer');
    const previewCustomerEmail = document.getElementById('preview-customer-email');
    const previewType = document.getElementById('preview-type');
    const previewOrder = document.getElementById('preview-order');
    const previewOrderStatus = document.getElementById('preview-order-status');
    const previewDue = document.getElementById('preview-due');
    const previewBank = document.getElementById('preview-bank');
    const previewBankBalance = document.getElementById('preview-bank-balance');
    const previewDate = document.getElementById('preview-date');
    const previewAmount = document.getElementById('preview-amount');
    const previewNotesWrap = document.getElementById('preview-notes-wrap');
    const previewNotes = document.getElementById('preview-notes');

    let selectedBankButton = null;

    function formatMoney(value) {
        const amount = parseFloat(value);
        if (isNaN(amount)) {
            return '৳0.00';
        }

        return '৳' + amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function formatDate(value) {
        if (!value) {
            return '—';
        }

        const date = new Date(value + 'T00:00:00');
        if (isNaN(date.getTime())) {
            return value;
        }

        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }

    function selectedOption(select) {
        return select.options[select.selectedIndex] || null;
    }

    function textOrDash(value) {
        return value && String(value).trim() !== '' ? String(value).trim() : '—';
    }

    function setThumb(element, imageUrl, fallbackIcon) {
        element.innerHTML = '';

        if (imageUrl) {
            const img = document.createElement('img');
            img.src = imageUrl;
            img.alt = '';
            element.appendChild(img);
            return;
        }

        const icon = document.createElement('i');
        icon.className = fallbackIcon;
        element.appendChild(icon);
    }

    function updateDueHint() {
        const selected = selectedOption(orderSelect);
        const due = selected ? selected.getAttribute('data-due') : null;

        if (due && due !== '') {
            dueHint.textContent = 'Balance due for selected order: ' + formatMoney(due);
            dueHint.classList.remove('d-none');
            previewDue.textContent = formatMoney(due);
        } else {
            dueHint.classList.add('d-none');
            dueHint.textContent = '';
            previewDue.textContent = '—';
        }
    }

    function renderBankPayments(payments) {
        bankPaymentsBody.innerHTML = '';

        if (!payments.length) {
            bankPaymentsTable.classList.add('d-none');
            bankPaymentsEmpty.classList.remove('d-none');
            bankPaymentsCount.textContent = '0 payments';
            return;
        }

        bankPaymentsEmpty.classList.add('d-none');
        bankPaymentsTable.classList.remove('d-none');
        bankPaymentsCount.textContent = payments.length + ' payment' + (payments.length === 1 ? '' : 's');

        payments.forEach(function (payment) {
            const row = document.createElement('tr');
            row.innerHTML =
                '<td>' + payment.payment_date + '</td>' +
                '<td><strong>' + payment.customer_name + '</strong><span class="bank-payment-customer-email">' + payment.customer_email + '</span></td>' +
                '<td>' + payment.type + '</td>' +
                '<td>' + (payment.order_number || '—') + '</td>' +
                '<td class="text-right"><strong>' + formatMoney(payment.amount) + '</strong></td>';
            bankPaymentsBody.appendChild(row);
        });
    }

    function loadBankPayments(bankId) {
        if (!bankPaymentsPanel) {
            return;
        }

        bankPaymentsPanel.classList.remove('d-none');
        bankPaymentsLoading.classList.remove('d-none');
        bankPaymentsEmpty.classList.add('d-none');
        bankPaymentsTable.classList.add('d-none');
        bankPaymentsBody.innerHTML = '';
        bankPaymentsCount.textContent = '';

        fetch(bankPaymentsUrlTemplate + bankId + '/customer-payments', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (response) { return response.json(); })
            .then(function (data) { renderBankPayments(data.payments || []); })
            .catch(function () {
                bankPaymentsTable.classList.add('d-none');
                bankPaymentsEmpty.classList.remove('d-none');
                bankPaymentsEmpty.textContent = 'Could not load payments for this bank.';
                bankPaymentsCount.textContent = '';
            })
            .finally(function () {
                bankPaymentsLoading.classList.add('d-none');
            });
    }

    function showBankDetails(button) {
        if (!bankDetailsPanel) {
            return;
        }

        const imageUrl = button.getAttribute('data-image') || '';
        const isActive = button.getAttribute('data-active') === '1';

        setThumb(document.getElementById('bank-details-thumb'), imageUrl, 'fas fa-university');
        document.getElementById('bank-details-name').textContent = button.getAttribute('data-name') || '—';
        document.getElementById('bank-details-balance').textContent = formatMoney(button.getAttribute('data-balance'));
        document.getElementById('bank-details-account-name').textContent = textOrDash(button.getAttribute('data-account-name'));
        document.getElementById('bank-details-account-number').textContent = textOrDash(button.getAttribute('data-account-number'));
        document.getElementById('bank-details-branch').textContent = textOrDash(button.getAttribute('data-branch'));
        document.getElementById('bank-details-charge').textContent = parseFloat(button.getAttribute('data-charge') || 0).toFixed(2) + '%';

        const status = document.getElementById('bank-details-status');
        status.textContent = isActive ? 'Active' : 'Inactive';
        status.className = 'bank-payment-bank-status ' + (isActive ? 'bank-payment-bank-status--active' : 'bank-payment-bank-status--inactive');

        const instructions = button.getAttribute('data-instructions') || '';
        const instructionsWrap = document.getElementById('bank-details-instructions-wrap');
        const instructionsEl = document.getElementById('bank-details-instructions');

        if (instructions.trim()) {
            instructionsEl.textContent = instructions.trim();
            instructionsWrap.classList.remove('d-none');
        } else {
            instructionsEl.textContent = '';
            instructionsWrap.classList.add('d-none');
        }

        bankDetailsPanel.classList.remove('d-none');
        loadBankPayments(button.getAttribute('data-bank-id'));
    }

    function selectBank(button) {
        bankButtons.forEach(function (item) {
            item.classList.remove('is-selected');
        });

        button.classList.add('is-selected');
        selectedBankButton = button;
        bankInput.value = button.getAttribute('data-bank-id');
        showBankDetails(button);
        updatePreview();
    }

    function updatePreview() {
        const customer = selectedOption(customerSelect);
        const order = selectedOption(orderSelect);

        if (customer && customer.value) {
            previewCustomer.textContent = customer.getAttribute('data-name') || customer.textContent;
            previewCustomerEmail.textContent = customer.getAttribute('data-email') || '';
        } else {
            previewCustomer.textContent = '—';
            previewCustomerEmail.textContent = '';
        }

        if (order && order.value) {
            previewType.textContent = 'Order payment';
            previewOrder.textContent = order.getAttribute('data-number') || order.textContent;
            previewOrderStatus.textContent = order.getAttribute('data-status') || '';
        } else {
            previewType.textContent = 'Advance payment';
            previewOrder.textContent = 'No order selected';
            previewOrderStatus.textContent = '';
        }

        if (selectedBankButton) {
            previewBank.textContent = selectedBankButton.getAttribute('data-display') || selectedBankButton.getAttribute('data-name') || '—';
            previewBankBalance.textContent = 'Balance: ' + formatMoney(selectedBankButton.getAttribute('data-balance'));
        } else {
            previewBank.textContent = '—';
            previewBankBalance.textContent = '';
        }

        previewDate.textContent = formatDate(dateInput.value);
        previewAmount.textContent = formatMoney(amountInput.value);

        if (notesInput.value.trim()) {
            previewNotes.textContent = notesInput.value.trim();
            previewNotesWrap.classList.remove('d-none');
        } else {
            previewNotes.textContent = '';
            previewNotesWrap.classList.add('d-none');
        }

        updateDueHint();
    }

    function populateOrders(orders, selectedOrderId) {
        orderSelect.innerHTML = '<option value="">No order — record as advance payment</option>';

        orders.forEach(function (order) {
            const option = document.createElement('option');
            option.value = order.id;
            option.setAttribute('data-due', order.amount_due);
            option.setAttribute('data-number', order.number);
            option.setAttribute('data-status', order.payment_status);
            option.textContent = order.number + ' — Due ৳' + Number(order.amount_due).toFixed(2) + ' (' + order.payment_status + ')';

            if (String(selectedOrderId) === String(order.id)) {
                option.selected = true;
            }

            orderSelect.appendChild(option);
        });

        updatePreview();
    }

    bankButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            selectBank(button);
        });
    });

    customerSelect.addEventListener('change', function () {
        const customerId = this.value;

        if (!customerId) {
            populateOrders([], null);
            updatePreview();
            return;
        }

        const url = ordersUrlTemplate + customerId + '/orders-for-payment';

        fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (response) { return response.json(); })
            .then(function (data) { populateOrders(data.orders || [], null); })
            .catch(function () { populateOrders([], null); });

        updatePreview();
    });

    orderSelect.addEventListener('change', function () {
        const selected = selectedOption(orderSelect);
        const due = selected ? selected.getAttribute('data-due') : null;

        if (due && due !== '' && !amountInput.value) {
            amountInput.value = parseFloat(due).toFixed(2);
        }

        updatePreview();
    });

    [amountInput, dateInput, notesInput].forEach(function (element) {
        element.addEventListener('input', updatePreview);
        element.addEventListener('change', updatePreview);
    });

    document.getElementById('bank-payment-form').addEventListener('submit', function (event) {
        if (!bankInput.value) {
            event.preventDefault();
            alert('Please select a payment bank first.');
        }
    });

    if (bankInput.value) {
        const initialButton = bankButtons.find(function (button) {
            return String(button.getAttribute('data-bank-id')) === String(bankInput.value);
        });

        if (initialButton) {
            selectBank(initialButton);
        }
    }

    updatePreview();
})();
</script>
@endpush
