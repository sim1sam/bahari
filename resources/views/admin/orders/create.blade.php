@extends('layouts.admin')

@section('title', 'Create Order')
@section('page_title', 'Create Order')

@section('content')
    <form action="{{ route('admin.orders.store') }}" method="POST" enctype="multipart/form-data" class="order-create-form">
        @csrf

        <div class="order-create-hero">
            <div>
                <span class="order-create-eyebrow">Order management</span>
                <h2>Create a new order</h2>
                <p>Enter customer information, add products, review totals, and record payment details.</p>
            </div>
            <div class="order-create-hero-actions">
                <a href="{{ route('admin.orders.index') }}" class="btn btn-light">
                    <i class="fas fa-arrow-left mr-1"></i> All Orders
                </a>
                <button type="submit" class="btn btn-info">
                    <i class="fas fa-check mr-1"></i> Create Order
                </button>
            </div>
        </div>

        @if ($errors->any())
            <div class="order-form-alert">
                <i class="fas fa-exclamation-circle"></i>
                <div>
                    <strong>Please check the form.</strong>
                    <span>{{ $errors->count() }} {{ Str::plural('field', $errors->count()) }} need your attention.</span>
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-xl-8">
                <div class="card order-form-card">
                    <div class="card-header order-form-card-header">
                        <span class="order-section-icon order-section-icon--cyan"><i class="fas fa-user"></i></span>
                        <div>
                            <h3 class="card-title">Customer & Shipping</h3>
                            <p>Select an existing customer or enter details manually.</p>
                        </div>
                        <span class="order-section-number">01</span>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Link to Customer (optional)</label>
                            <select id="customer-select" name="user_id" class="form-control">
                                <option value="">— Walk-in / manual entry —</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}" data-name="{{ $customer->name }}" data-email="{{ $customer->email }}" @selected(old('user_id') == $customer->id)>
                                        {{ $customer->name }} ({{ $customer->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Customer Name *</label>
                                    <input type="text" name="customer_name" id="customer_name" class="form-control @error('customer_name') is-invalid @enderror" value="{{ old('customer_name') }}" required>
                                    @error('customer_name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email *</label>
                                    <input type="email" name="customer_email" id="customer_email" class="form-control @error('customer_email') is-invalid @enderror" value="{{ old('customer_email') }}" required>
                                    @error('customer_email')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Phone</label>
                                    <input type="text" name="customer_phone" class="form-control" value="{{ old('customer_phone') }}">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Address</label>
                                    <input type="text" name="address" class="form-control" value="{{ old('address') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>City</label>
                                    <input type="text" name="city" class="form-control" value="{{ old('city') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>ZIP</label>
                                    <input type="text" name="zip" class="form-control" value="{{ old('zip') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card order-form-card">
                    <div class="card-header order-form-card-header">
                        <span class="order-section-icon order-section-icon--violet"><i class="fas fa-box-open"></i></span>
                        <div>
                            <h3 class="card-title">Order Items</h3>
                            <p>Add every product included in this order.</p>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary ml-auto" id="add-item-row">
                            <i class="fas fa-plus mr-1"></i> Add Item
                        </button>
                        <span class="order-section-number">02</span>
                    </div>
                    <div class="card-body p-0">
                        @error('items')<div class="text-danger px-3 pt-2">{{ $message }}</div>@enderror
                        <div class="table-responsive order-items-scroll">
                        <table class="table mb-0 order-form-table" id="items-table">
                            <thead>
                                <tr>
                                    <th style="width:18%">Product *</th>
                                    <th style="width:12%">Slug</th>
                                    <th style="width:15%">Link</th>
                                    <th style="width:14%">Image</th>
                                    <th style="width:8%">Size</th>
                                    <th style="width:8%">Color</th>
                                    <th style="width:7%">Qty</th>
                                    <th style="width:9%">Price</th>
                                    <th style="width:5%"></th>
                                </tr>
                            </thead>
                            <tbody id="items-body"></tbody>
                        </table>
                        </div>
                    </div>
                </div>

                <div class="card order-form-card">
                    <div class="card-header order-form-card-header">
                        <span class="order-section-icon order-section-icon--emerald"><i class="fas fa-wallet"></i></span>
                        <div>
                            <h3 class="card-title">Payment History</h3>
                            <p>Optionally record one or more payments now.</p>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary ml-auto" id="add-payment-row">
                            <i class="fas fa-plus mr-1"></i> Add Payment
                        </button>
                        <span class="order-section-number">03</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="order-empty-state" id="no-payments-msg">
                            <span><i class="fas fa-receipt"></i></span>
                            <div>
                                <strong>No payment rows added</strong>
                                <p>Add a payment here, or set the amount paid in Payment Details.</p>
                            </div>
                        </div>
                        <div class="table-responsive">
                        <table class="table mb-0 d-none order-form-table" id="payments-table">
                            <thead>
                                <tr>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Bank</th>
                                    <th>Notes</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="payments-body"></tbody>
                        </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="order-create-sidebar">
                <div class="card order-form-card order-total-card">
                    <div class="card-header order-form-card-header">
                        <span class="order-section-icon order-section-icon--amber"><i class="fas fa-calculator"></i></span>
                        <div>
                            <h3 class="card-title">Order Summary</h3>
                            <p>Review pricing and delivery.</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Subtotal (BDT)</label>
                            <input type="number" name="subtotal" id="subtotal" class="form-control" min="0" step="0.01" value="{{ old('subtotal', 0) }}" required>
                        </div>
                        <div class="form-group">
                            <label>Discount (BDT)</label>
                            <input type="number" name="discount" id="discount" class="form-control" min="0" step="0.01" value="{{ old('discount', 0) }}" required>
                        </div>
                        <div class="form-group">
                            <label>Delivery Zone</label>
                            <select name="shipping_zone" id="shipping_zone" class="form-control" required>
                                @foreach ($shippingZones as $value => $label)
                                    <option value="{{ $value }}" @selected(old('shipping_zone', 'inside_dhaka') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Shipping (BDT)</label>
                            <input type="number" name="shipping" id="shipping" class="form-control" min="0" step="0.01" value="{{ old('shipping', 0) }}" required>
                            <small class="text-muted">Auto-calculated from items. Free above {{ money($freeShippingThreshold) }}.</small>
                        </div>
                        <div class="form-group">
                            <label>Coupon Code</label>
                            <input type="text" name="coupon_code" class="form-control" value="{{ old('coupon_code') }}">
                        </div>
                        <div class="form-group">
                            <label>Total (BDT)</label>
                            <input type="number" name="total" id="total" class="form-control" min="0" step="0.01" value="{{ old('total', 0) }}" required>
                        </div>
                        <div class="order-total-preview">
                            <span>Order total</span>
                            <strong id="total-preview">{{ money(old('total', 0)) }}</strong>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-info btn-block mb-2" id="calc-from-items">
                            <i class="fas fa-sync-alt mr-1"></i> Calculate from items
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary btn-block" id="calc-total">
                            Recalculate total
                        </button>
                    </div>
                </div>

                <div class="card order-form-card">
                    <div class="card-header order-form-card-header">
                        <span class="order-section-icon order-section-icon--emerald"><i class="fas fa-credit-card"></i></span>
                        <div>
                            <h3 class="card-title">Payment Details</h3>
                            <p>Method and payment status.</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Payment Method</label>
                            <select name="payment_method" class="form-control">
                                @foreach (['card' => 'Card', 'cod' => 'COD', 'bank_transfer' => 'Bank Transfer', 'order_code' => 'Order Code'] as $val => $label)
                                    <option value="{{ $val }}" @selected(old('payment_method', 'cod') === $val)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Reference Code</label>
                            <input type="text" name="reference_code" class="form-control" value="{{ old('reference_code') }}">
                        </div>
                        <div class="form-group">
                            <label>Customer Bank</label>
                            <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name') }}">
                        </div>
                        <div class="form-group" id="manual-payment-fields">
                            <label>Amount Paid (BDT)</label>
                            <input type="number" name="amount_paid" class="form-control" min="0" step="0.01" value="{{ old('amount_paid', 0) }}">
                            <small class="text-muted">Ignored when payment history exists (sum of payments is used).</small>
                        </div>
                        <div class="form-group" id="manual-payment-status">
                            <label>Payment Status</label>
                            <select name="payment_status" class="form-control">
                                @foreach (['pending' => 'Pending', 'paid' => 'Paid', 'partial' => 'Partial', 'due' => 'Due'] as $val => $label)
                                    <option value="{{ $val }}" @selected(old('payment_status', 'pending') === $val)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Order Type</label>
                            <select name="order_type" class="form-control">
                                <option value="standard" @selected(old('order_type', 'standard') === 'standard')>Standard</option>
                                <option value="custom" @selected(old('order_type') === 'custom')>Custom</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Notes</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                        </div>
                        <div class="form-group">
                            <label>Payment Screenshot</label>
                            <input type="file" name="payment_screenshot" class="form-control-file" accept="image/*">
                        </div>
                    </div>
                </div>

                <div class="card order-form-card">
                    <div class="card-header order-form-card-header">
                        <span class="order-section-icon order-section-icon--blue"><i class="fas fa-clipboard-check"></i></span>
                        <div>
                            <h3 class="card-title">Order Status</h3>
                            <p>Choose the initial workflow state.</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-0">
                            <select name="status" class="form-control">
                                @foreach (['pending','processing','shipped','completed','cancelled'] as $status)
                                    <option value="{{ $status }}" @selected(old('status', 'pending') === $status)>{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-info btn-block">
                            <i class="fas fa-check-circle mr-1"></i> Create Order
                        </button>
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-default btn-block">Cancel</a>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </form>

    <template id="item-row-template">
        <tr>
            <td><input type="text" name="items[__INDEX__][product_name]" class="form-control form-control-sm item-name" placeholder="Product name" required></td>
            <td><input type="text" name="items[__INDEX__][product_slug]" class="form-control form-control-sm item-slug" placeholder="Auto generated" readonly></td>
            <td><input type="text" name="items[__INDEX__][product_link]" class="form-control form-control-sm" placeholder="https://..."></td>
            <td>
                <label class="order-item-upload">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <span>Upload</span>
                    <input type="file" name="items[__INDEX__][image]" accept="image/*">
                </label>
            </td>
            <td><input type="text" name="items[__INDEX__][size]" class="form-control form-control-sm" placeholder="Size"></td>
            <td><input type="text" name="items[__INDEX__][color]" class="form-control form-control-sm" placeholder="Color"></td>
            <td><input type="number" name="items[__INDEX__][quantity]" class="form-control form-control-sm item-qty" min="1" value="1" required></td>
            <td><input type="number" name="items[__INDEX__][price]" class="form-control form-control-sm item-price" min="0" step="0.01" value="0" required></td>
            <td class="text-center"><button type="button" class="btn btn-xs btn-outline-danger remove-row" title="Remove item"><i class="fas fa-trash"></i></button></td>
        </tr>
    </template>

    <template id="payment-row-template">
        <tr>
            <td><input type="number" name="payments[__INDEX__][amount]" class="form-control form-control-sm" min="0.01" step="0.01" required></td>
            <td>
                <select name="payments[__INDEX__][payment_method]" class="form-control form-control-sm">
                    <option value="cod">COD</option>
                    <option value="cash">Cash</option>
                    <option value="bank_transfer">Bank Transfer</option>
                </select>
            </td>
            <td>
                <select name="payments[__INDEX__][bank_name]" class="form-control form-control-sm">
                    <option value="">—</option>
                    @foreach ($banks as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </td>
            <td><input type="text" name="payments[__INDEX__][notes]" class="form-control form-control-sm" placeholder="Optional note"></td>
            <td class="text-center align-middle"><button type="button" class="btn btn-xs btn-outline-danger remove-row" title="Remove payment"><i class="fas fa-trash"></i></button></td>
        </tr>
    </template>
@endsection

@push('styles')
    @include('admin.orders.partials.form-styles')
@endpush

@push('scripts')
<script>
(function () {
    var itemIndex = 0;
    var paymentIndex = 0;
    var shippingFeeInside = {{ json_encode((float) $shippingFeeInside) }};
    var shippingFeeOutside = {{ json_encode((float) $shippingFeeOutside) }};
    var freeShippingThreshold = {{ json_encode((float) $freeShippingThreshold) }};

    function zoneShippingFee() {
        var zone = document.getElementById('shipping_zone').value;
        return zone === 'outside_dhaka' ? shippingFeeOutside : shippingFeeInside;
    }

    function calcShipping(subtotal) {
        if (subtotal <= 0 || subtotal >= freeShippingThreshold) {
            return 0;
        }

        return zoneShippingFee();
    }

    function addRow(templateId, bodyId, tableId, noMsgId) {
        var tpl = document.getElementById(templateId);
        var index = templateId === 'item-row-template' ? itemIndex++ : paymentIndex++;
        var html = tpl.innerHTML.replace(/__INDEX__/g, index);
        document.getElementById(bodyId).insertAdjacentHTML('beforeend', html);
        if (tableId) {
            document.getElementById(tableId).classList.remove('d-none');
        }
        if (noMsgId) {
            var msg = document.getElementById(noMsgId);
            if (msg) msg.classList.add('d-none');
        }
        if (templateId === 'payment-row-template') {
            toggleManualPaymentFields();
        }
    }

    document.getElementById('add-item-row').addEventListener('click', function () {
        addRow('item-row-template', 'items-body');
    });

    document.getElementById('add-payment-row').addEventListener('click', function () {
        addRow('payment-row-template', 'payments-body', 'payments-table', 'no-payments-msg');
    });

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-row')) {
            e.target.closest('tr').remove();
            toggleManualPaymentFields();
        }
    });

    document.getElementById('customer-select').addEventListener('change', function () {
        var option = this.options[this.selectedIndex];
        if (!option.value) return;
        document.getElementById('customer_name').value = option.dataset.name || '';
        document.getElementById('customer_email').value = option.dataset.email || '';
    });

    function sumItems() {
        var total = 0;
        document.querySelectorAll('#items-body tr').forEach(function (row) {
            var qty = parseFloat(row.querySelector('.item-qty')?.value) || 0;
            var price = parseFloat(row.querySelector('.item-price')?.value) || 0;
            total += qty * price;
        });
        return total;
    }

    document.getElementById('calc-from-items').addEventListener('click', function () {
        var subtotal = sumItems();
        document.getElementById('subtotal').value = subtotal.toFixed(2);
        document.getElementById('shipping').value = calcShipping(subtotal).toFixed(2);
        recalcTotal();
    });

    function recalcTotal() {
        var sub = parseFloat(document.getElementById('subtotal').value) || 0;
        var disc = parseFloat(document.getElementById('discount').value) || 0;
        var ship = parseFloat(document.getElementById('shipping').value) || 0;
        var total = Math.max(0, sub - disc + ship);
        document.getElementById('total').value = total.toFixed(2);
        updateTotalPreview(total);
    }

    function updateTotalPreview(total) {
        var preview = document.getElementById('total-preview');
        if (!preview) return;
        preview.textContent = '৳' + Number(total || 0).toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    document.getElementById('calc-total').addEventListener('click', function () {
        var sub = parseFloat(document.getElementById('subtotal').value) || 0;
        document.getElementById('shipping').value = calcShipping(sub).toFixed(2);
        recalcTotal();
    });

    document.getElementById('shipping_zone').addEventListener('change', function () {
        var sub = parseFloat(document.getElementById('subtotal').value) || 0;
        document.getElementById('shipping').value = calcShipping(sub).toFixed(2);
        recalcTotal();
    });

    document.getElementById('total').addEventListener('input', function () {
        updateTotalPreview(parseFloat(this.value) || 0);
    });

    document.getElementById('items-body').addEventListener('input', function (e) {
        if (!e.target.classList.contains('item-qty') && !e.target.classList.contains('item-price')) {
            return;
        }

        var subtotal = sumItems();
        document.getElementById('subtotal').value = subtotal.toFixed(2);
        document.getElementById('shipping').value = calcShipping(subtotal).toFixed(2);
        recalcTotal();
    });

    function toggleManualPaymentFields() {
        var hasPayments = document.querySelectorAll('#payments-body tr').length > 0;
        var manualFields = document.getElementById('manual-payment-fields');
        var manualStatus = document.getElementById('manual-payment-status');
        if (manualFields) manualFields.style.display = hasPayments ? 'none' : 'block';
        if (manualStatus) manualStatus.style.display = hasPayments ? 'none' : 'block';
    }

    function slugify(text) {
        return text.toString().toLowerCase().trim()
            .replace(/[^\w\s-]/g, '')
            .replace(/[\s_-]+/g, '-')
            .replace(/^-+|-+$/g, '') || 'custom';
    }

    document.getElementById('items-body').addEventListener('input', function (e) {
        if (!e.target.classList.contains('item-name')) return;
        var slugInput = e.target.closest('tr').querySelector('.item-slug');
        if (slugInput && slugInput.dataset.manual !== '1') {
            slugInput.value = slugify(e.target.value);
        }
    });

    document.getElementById('items-body').addEventListener('focusin', function (e) {
        if (!e.target.classList.contains('item-slug')) return;
        e.target.readOnly = false;
    });

    document.getElementById('items-body').addEventListener('change', function (e) {
        if (e.target.type !== 'file') return;
        var label = e.target.closest('.order-item-upload');
        var text = label ? label.querySelector('span') : null;
        if (!label || !text) return;

        if (e.target.files && e.target.files[0]) {
            text.textContent = e.target.files[0].name;
            label.classList.add('has-file');
        } else {
            text.textContent = 'Upload';
            label.classList.remove('has-file');
        }
    });

    document.getElementById('items-body').addEventListener('input', function (e) {
        if (!e.target.classList.contains('item-slug')) return;
        e.target.dataset.manual = '1';
        e.target.readOnly = false;
    });

    addRow('item-row-template', 'items-body');
    toggleManualPaymentFields();
    updateTotalPreview(parseFloat(document.getElementById('total').value) || 0);
})();
</script>
@endpush
