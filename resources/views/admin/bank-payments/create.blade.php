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
                        <span class="bank-payment-section-icon bank-payment-section-icon--cyan">
                            <i class="fas fa-user"></i>
                        </span>
                        <div>
                            <h3 class="mb-0">Customer & Order</h3>
                            <p class="mb-0 text-muted">Choose who paid and optionally link an unpaid order.</p>
                        </div>
                        <span class="bank-payment-section-number">01</span>
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
                            <p class="mb-0 text-muted">Enter bank, amount, date, and any reference notes.</p>
                        </div>
                        <span class="bank-payment-section-number">02</span>
                    </div>
                    <div class="card-body bank-payment-form-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Payment Bank *</label>
                                    <select name="payment_bank_id" id="payment_bank_id" class="form-control @error('payment_bank_id') is-invalid @enderror" required>
                                        <option value="">Select bank account</option>
                                        @foreach ($banks as $bank)
                                            <option value="{{ $bank->id }}" @selected((int) old('payment_bank_id') === $bank->id)>
                                                {{ $bank->displayName() }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('payment_bank_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Payment Date *</label>
                                    <input type="date" name="payment_date" id="payment_date" class="form-control @error('payment_date') is-invalid @enderror" value="{{ old('payment_date', now()->toDateString()) }}" required>
                                    @error('payment_date')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
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
    }
</style>
@endpush

@push('scripts')
<script>
(function () {
    const customerSelect = document.getElementById('customer_id');
    const orderSelect = document.getElementById('order_id');
    const bankSelect = document.getElementById('payment_bank_id');
    const amountInput = document.getElementById('amount');
    const dateInput = document.getElementById('payment_date');
    const notesInput = document.getElementById('notes');
    const dueHint = document.getElementById('order-due-hint');
    const ordersUrlTemplate = @json(url('/admin/customers')) + '/';

    const previewCustomer = document.getElementById('preview-customer');
    const previewCustomerEmail = document.getElementById('preview-customer-email');
    const previewType = document.getElementById('preview-type');
    const previewOrder = document.getElementById('preview-order');
    const previewOrderStatus = document.getElementById('preview-order-status');
    const previewDue = document.getElementById('preview-due');
    const previewBank = document.getElementById('preview-bank');
    const previewDate = document.getElementById('preview-date');
    const previewAmount = document.getElementById('preview-amount');
    const previewNotesWrap = document.getElementById('preview-notes-wrap');
    const previewNotes = document.getElementById('preview-notes');

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

    function updatePreview() {
        const customer = selectedOption(customerSelect);
        const order = selectedOption(orderSelect);
        const bank = selectedOption(bankSelect);

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

        previewBank.textContent = bank && bank.value ? bank.textContent.trim() : '—';
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

    [bankSelect, amountInput, dateInput, notesInput].forEach(function (element) {
        element.addEventListener('input', updatePreview);
        element.addEventListener('change', updatePreview);
    });

    updatePreview();
})();
</script>
@endpush
