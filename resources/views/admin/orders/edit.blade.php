@extends('layouts.admin')

@section('title', 'Edit Order '.$order->number)
@section('page_title', 'Edit Order '.$order->number)

@section('content')
    <form action="{{ route('admin.orders.update', $order) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-md-8">
                {{-- Customer & shipping --}}
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Customer & Shipping</h3></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Customer Name *</label>
                                    <input type="text" name="customer_name" class="form-control @error('customer_name') is-invalid @enderror" value="{{ old('customer_name', $order->customer_name) }}" required>
                                    @error('customer_name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email *</label>
                                    <input type="email" name="customer_email" class="form-control @error('customer_email') is-invalid @enderror" value="{{ old('customer_email', $order->customer_email) }}" required>
                                    @error('customer_email')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Phone</label>
                                    <input type="text" name="customer_phone" class="form-control" value="{{ old('customer_phone', $order->customer_phone) }}">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Address</label>
                                    <input type="text" name="address" class="form-control" value="{{ old('address', $order->address) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>City</label>
                                    <input type="text" name="city" class="form-control" value="{{ old('city', $order->city) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>ZIP</label>
                                    <input type="text" name="zip" class="form-control" value="{{ old('zip', $order->zip) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Order items --}}
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Order Items</h3>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="add-item-row">+ Add Item</button>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table mb-0" id="items-table">
                            <thead>
                                <tr>
                                    <th style="width:18%">Product</th>
                                    <th style="width:12%">Slug</th>
                                    <th style="width:15%">Link</th>
                                    <th style="width:15%">Image URL</th>
                                    <th style="width:8%">Size</th>
                                    <th style="width:8%">Color</th>
                                    <th style="width:7%">Qty</th>
                                    <th style="width:9%">Price</th>
                                    <th style="width:5%"></th>
                                </tr>
                            </thead>
                            <tbody id="items-body">
                                @foreach ($order->items as $item)
                                    <tr data-existing="1">
                                        <td>
                                            <input type="text" name="items[{{ $item->id }}][product_name]" class="form-control form-control-sm item-name" value="{{ old('items.'.$item->id.'.product_name', $item->product_name) }}" required>
                                        </td>
                                        <td>
                                            <input type="text" name="items[{{ $item->id }}][product_slug]" class="form-control form-control-sm item-slug" value="{{ old('items.'.$item->id.'.product_slug', $item->product_slug) }}" data-manual="1">
                                        </td>
                                        <td>
                                            <input type="text" name="items[{{ $item->id }}][product_link]" class="form-control form-control-sm" value="{{ old('items.'.$item->id.'.product_link', $item->product_link) }}">
                                        </td>
                                        <td>
                                            <input type="text" name="items[{{ $item->id }}][image]" class="form-control form-control-sm" value="{{ old('items.'.$item->id.'.image', $item->image) }}">
                                        </td>
                                        <td>
                                            <input type="text" name="items[{{ $item->id }}][size]" class="form-control form-control-sm" value="{{ old('items.'.$item->id.'.size', $item->size) }}">
                                        </td>
                                        <td>
                                            <input type="text" name="items[{{ $item->id }}][color]" class="form-control form-control-sm" value="{{ old('items.'.$item->id.'.color', $item->color) }}">
                                        </td>
                                        <td>
                                            <input type="number" name="items[{{ $item->id }}][quantity]" class="form-control form-control-sm item-qty" min="1" value="{{ old('items.'.$item->id.'.quantity', $item->quantity) }}" required>
                                        </td>
                                        <td>
                                            <input type="number" name="items[{{ $item->id }}][price]" class="form-control form-control-sm item-price" min="0" step="0.01" value="{{ old('items.'.$item->id.'.price', $item->price) }}" required>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" name="delete_items[]" value="{{ $item->id }}" title="Remove item">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Payment history --}}
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Payment History</h3>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="add-payment-row">+ Add Payment</button>
                    </div>
                    <div class="card-body p-0">
                        @if ($order->payments->isEmpty())
                            <p class="text-muted p-3 mb-0" id="no-payments-msg">No payments recorded. Add one below or set amount paid in the sidebar.</p>
                        @endif
                        <table class="table mb-0 {{ $order->payments->isEmpty() ? 'd-none' : '' }}" id="payments-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Bank</th>
                                    <th>Notes</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="payments-body">
                                @foreach ($order->payments as $payment)
                                    <tr data-existing="1">
                                        <td class="align-middle text-muted small">{{ $payment->created_at->format('M d, Y H:i') }}</td>
                                        <td>
                                            <input type="number" name="payments[{{ $payment->id }}][amount]" class="form-control form-control-sm" min="0" step="0.01" value="{{ old('payments.'.$payment->id.'.amount', $payment->amount) }}" required>
                                        </td>
                                        <td>
                                            <select name="payments[{{ $payment->id }}][payment_method]" class="form-control form-control-sm payment-method-select">
                                                @foreach (['cod' => 'COD', 'cash' => 'Cash', 'bank_transfer' => 'Bank Transfer'] as $val => $label)
                                                    <option value="{{ $val }}" @selected(old('payments.'.$payment->id.'.payment_method', $payment->payment_method) === $val)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="payments[{{ $payment->id }}][bank_name]" class="form-control form-control-sm">
                                                <option value="">—</option>
                                                @foreach ($banks as $key => $label)
                                                    <option value="{{ $key }}" @selected(old('payments.'.$payment->id.'.bank_name', $payment->bank_name) === $label || old('payments.'.$payment->id.'.bank_name') === $key)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="payments[{{ $payment->id }}][notes]" class="form-control form-control-sm" value="{{ old('payments.'.$payment->id.'.notes', $payment->notes) }}">
                                        </td>
                                        <td class="text-center align-middle">
                                            <input type="checkbox" name="delete_payments[]" value="{{ $payment->id }}" title="Remove payment">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                {{-- Order totals --}}
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Totals</h3></div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Subtotal (BDT)</label>
                            <input type="number" name="subtotal" id="subtotal" class="form-control" min="0" step="0.01" value="{{ old('subtotal', $order->subtotal) }}" required>
                        </div>
                        <div class="form-group">
                            <label>Discount (BDT)</label>
                            <input type="number" name="discount" id="discount" class="form-control" min="0" step="0.01" value="{{ old('discount', $order->discount) }}" required>
                        </div>
                        <div class="form-group">
                            <label>Delivery Zone</label>
                            <select name="shipping_zone" id="shipping_zone" class="form-control" required>
                                @foreach ($shippingZones as $value => $label)
                                    <option value="{{ $value }}" @selected(old('shipping_zone', $order->shipping_zone ?? 'inside_dhaka') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Shipping (BDT)</label>
                            <input type="number" name="shipping" id="shipping" class="form-control" min="0" step="0.01" value="{{ old('shipping', $order->shipping) }}" required>
                            <small class="text-muted">Auto-calculated from items. Free above {{ money($freeShippingThreshold) }}.</small>
                        </div>
                        <div class="form-group">
                            <label>Coupon Code</label>
                            <input type="text" name="coupon_code" class="form-control" value="{{ old('coupon_code', $order->coupon_code) }}">
                        </div>
                        <div class="form-group">
                            <label>Total (BDT)</label>
                            <input type="number" name="total" id="total" class="form-control" min="0" step="0.01" value="{{ old('total', $order->total) }}" required>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary btn-block mb-2" id="calc-from-items">Recalculate from items</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary btn-block" id="calc-total">Recalculate total</button>
                    </div>
                </div>

                {{-- Payment info --}}
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Payment Info</h3></div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Payment Method</label>
                            <select name="payment_method" class="form-control">
                                @foreach (['card' => 'Card', 'cod' => 'COD', 'bank_transfer' => 'Bank Transfer', 'order_code' => 'Order Code'] as $val => $label)
                                    <option value="{{ $val }}" @selected(old('payment_method', $order->payment_method) === $val)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Reference Code</label>
                            <input type="text" name="reference_code" class="form-control" value="{{ old('reference_code', $order->reference_code) }}">
                        </div>
                        <div class="form-group">
                            <label>Customer Bank</label>
                            <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name', $order->bank_name) }}">
                        </div>
                        <div class="form-group" id="manual-payment-fields" @if($order->payments->isNotEmpty()) style="display:none" @endif>
                            <label>Amount Paid (BDT)</label>
                            <input type="number" name="amount_paid" class="form-control" min="0" step="0.01" value="{{ old('amount_paid', $order->amount_paid) }}">
                            <small class="text-muted">Ignored when payment history exists (sum of payments is used).</small>
                        </div>
                        <div class="form-group" id="manual-payment-status" @if($order->payments->isNotEmpty()) style="display:none" @endif>
                            <label>Payment Status</label>
                            <select name="payment_status" class="form-control">
                                @foreach (['pending' => 'Pending', 'paid' => 'Paid', 'partial' => 'Partial', 'due' => 'Due'] as $val => $label)
                                    <option value="{{ $val }}" @selected(old('payment_status', $order->payment_status) === $val)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if ($order->isCustom())
                            <div class="form-group">
                                <label>Customer Notes</label>
                                <textarea name="notes" class="form-control" rows="3">{{ old('notes', $order->notes) }}</textarea>
                            </div>
                            <div class="form-group">
                                <label>Payment Screenshot</label>
                                @if ($order->paymentScreenshotUrl())
                                    <div class="mb-2">
                                        <a href="{{ $order->paymentScreenshotUrl() }}" target="_blank" rel="noopener">
                                            <img src="{{ $order->paymentScreenshotUrl() }}" alt="Payment" class="img-fluid rounded border" style="max-height:80px">
                                        </a>
                                        <div class="custom-control custom-checkbox mt-1">
                                            <input type="checkbox" class="custom-control-input" name="remove_payment_screenshot" value="1" id="remove_screenshot">
                                            <label class="custom-control-label" for="remove_screenshot">Remove screenshot</label>
                                        </div>
                                    </div>
                                @endif
                                <input type="file" name="payment_screenshot" class="form-control-file" accept="image/*">
                            </div>
                        @else
                            <div class="form-group">
                                <label>Notes</label>
                                <textarea name="notes" class="form-control" rows="2">{{ old('notes', $order->notes) }}</textarea>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Status --}}
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Order Status</h3></div>
                    <div class="card-body">
                        <div class="form-group mb-0">
                            <select name="status" class="form-control">
                                @foreach (['pending','processing','shipped','completed','cancelled'] as $status)
                                    <option value="{{ $status }}" @selected(old('status', $order->status) === $status)>{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-block">Save Changes</button>
                        <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-default btn-block">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- Hidden templates --}}
    <template id="item-row-template">
        <tr>
            <td><input type="text" name="new_items[__INDEX__][product_name]" class="form-control form-control-sm item-name" required></td>
            <td><input type="text" name="new_items[__INDEX__][product_slug]" class="form-control form-control-sm item-slug" placeholder="Auto from name" readonly></td>
            <td><input type="text" name="new_items[__INDEX__][product_link]" class="form-control form-control-sm"></td>
            <td><input type="text" name="new_items[__INDEX__][image]" class="form-control form-control-sm"></td>
            <td><input type="text" name="new_items[__INDEX__][size]" class="form-control form-control-sm"></td>
            <td><input type="text" name="new_items[__INDEX__][color]" class="form-control form-control-sm"></td>
            <td><input type="number" name="new_items[__INDEX__][quantity]" class="form-control form-control-sm item-qty" min="1" value="1" required></td>
            <td><input type="number" name="new_items[__INDEX__][price]" class="form-control form-control-sm item-price" min="0" step="0.01" value="0" required></td>
            <td class="text-center"><button type="button" class="btn btn-xs btn-danger remove-row">&times;</button></td>
        </tr>
    </template>

    <template id="payment-row-template">
        <tr>
            <td class="align-middle text-muted small">New</td>
            <td><input type="number" name="new_payments[__INDEX__][amount]" class="form-control form-control-sm" min="0.01" step="0.01" required></td>
            <td>
                <select name="new_payments[__INDEX__][payment_method]" class="form-control form-control-sm">
                    <option value="cod">COD</option>
                    <option value="cash">Cash</option>
                    <option value="bank_transfer">Bank Transfer</option>
                </select>
            </td>
            <td>
                <select name="new_payments[__INDEX__][bank_name]" class="form-control form-control-sm">
                    <option value="">—</option>
                    @foreach ($banks as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </td>
            <td><input type="text" name="new_payments[__INDEX__][notes]" class="form-control form-control-sm"></td>
            <td class="text-center align-middle"><button type="button" class="btn btn-xs btn-danger remove-row">&times;</button></td>
        </tr>
    </template>
@endsection

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
        var html = tpl.innerHTML.replace(/__INDEX__/g, templateId === 'item-row-template' ? itemIndex++ : paymentIndex++);
        var tbody = document.getElementById(bodyId);
        tbody.insertAdjacentHTML('beforeend', html);
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

    function sumItems() {
        var total = 0;
        document.querySelectorAll('#items-body tr:not([style*="display: none"])').forEach(function (row) {
            if (row.querySelector('input[name^="delete_items"]')?.checked) return;
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
        document.getElementById('total').value = Math.max(0, sub - disc + ship).toFixed(2);
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

    document.getElementById('items-body').addEventListener('input', function (e) {
        if (!e.target.classList.contains('item-slug')) return;
        e.target.dataset.manual = '1';
        e.target.readOnly = false;
    });

    toggleManualPaymentFields();
})();
</script>
@endpush
