@extends('layouts.admin')

@section('title', 'Record Bank Payment')
@section('page_title', 'Record Bank Payment')

@section('content')
    <form action="{{ route('admin.bank-payments.store') }}" method="POST" id="bank-payment-form">
        @csrf

        <div class="row">
            <div class="col-lg-8">
                <div class="card card-primary card-outline">
                    <div class="card-header"><h3 class="card-title mb-0">Payment Details</h3></div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Customer *</label>
                            <select name="user_id" id="customer_id" class="form-control @error('user_id') is-invalid @enderror" required>
                                <option value="">Select customer</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}" @selected((int) old('user_id', $selectedCustomerId) === $customer->id)>
                                        {{ $customer->name }} ({{ $customer->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-group">
                            <label>Order <span class="text-muted">(optional)</span></label>
                            <select name="order_id" id="order_id" class="form-control @error('order_id') is-invalid @enderror">
                                <option value="">No order — record as advance payment</option>
                                @foreach ($orders as $order)
                                    <option
                                        value="{{ $order->id }}"
                                        data-due="{{ $order->amountDue() }}"
                                        @selected((int) old('order_id', $selectedOrderId) === $order->id)
                                    >
                                        {{ $order->number }} — Due {{ money($order->amountDue()) }} ({{ $order->paymentStatusLabel() }})
                                    </option>
                                @endforeach
                            </select>
                            @error('order_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            <small class="text-muted d-block mt-1">Select a customer first to load their unpaid orders.</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Payment Bank *</label>
                                    <select name="payment_bank_id" class="form-control @error('payment_bank_id') is-invalid @enderror" required>
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
                                    <input type="date" name="payment_date" class="form-control @error('payment_date') is-invalid @enderror" value="{{ old('payment_date', now()->toDateString()) }}" required>
                                    @error('payment_date')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Amount (BDT) *</label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text">৳</span></div>
                                <input type="number" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror" min="0.01" step="0.01" value="{{ old('amount') }}" required>
                            </div>
                            @error('amount')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                            <small id="order-due-hint" class="text-muted d-none"></small>
                        </div>

                        <div class="form-group mb-0">
                            <label>Notes</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Transaction reference, remarks...">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Payment</button>
                        <a href="{{ route('admin.customer-ledgers.index') }}" class="btn btn-default">Customer Ledgers</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
(function () {
    const customerSelect = document.getElementById('customer_id');
    const orderSelect = document.getElementById('order_id');
    const amountInput = document.getElementById('amount');
    const dueHint = document.getElementById('order-due-hint');
    const ordersUrlTemplate = @json(url('/admin/customers')) + '/';

    function updateDueHint() {
        const selected = orderSelect.options[orderSelect.selectedIndex];
        const due = selected ? selected.getAttribute('data-due') : null;

        if (due && due !== '') {
            dueHint.textContent = 'Balance due for selected order: ৳' + parseFloat(due).toFixed(2);
            dueHint.classList.remove('d-none');
        } else {
            dueHint.classList.add('d-none');
            dueHint.textContent = '';
        }
    }

    function populateOrders(orders, selectedOrderId) {
        orderSelect.innerHTML = '<option value="">No order — record as advance payment</option>';

        orders.forEach(function (order) {
            const option = document.createElement('option');
            option.value = order.id;
            option.setAttribute('data-due', order.amount_due);
            option.textContent = order.number + ' — Due ৳' + Number(order.amount_due).toFixed(2) + ' (' + order.payment_status + ')';
            if (String(selectedOrderId) === String(order.id)) {
                option.selected = true;
            }
            orderSelect.appendChild(option);
        });

        updateDueHint();
    }

    customerSelect.addEventListener('change', function () {
        const customerId = this.value;

        if (!customerId) {
            populateOrders([], null);
            return;
        }

        const url = ordersUrlTemplate + customerId + '/orders-for-payment';

        fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (response) { return response.json(); })
            .then(function (data) { populateOrders(data.orders || [], null); })
            .catch(function () { populateOrders([], null); });
    });

    orderSelect.addEventListener('change', function () {
        updateDueHint();
        const selected = orderSelect.options[orderSelect.selectedIndex];
        const due = selected ? selected.getAttribute('data-due') : null;
        if (due && due !== '' && !amountInput.value) {
            amountInput.value = parseFloat(due).toFixed(2);
        }
    });

    updateDueHint();
})();
</script>
@endpush
