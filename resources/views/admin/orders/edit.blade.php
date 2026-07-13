@extends('layouts.admin')

@section('title', 'Edit Order '.$order->number)
@section('page_title', 'Edit Order '.$order->number)

@section('content')
    @php
        $statusStyles = [
            'pending' => ['bg' => '#fff7ed', 'text' => '#c2410c'],
            'processing' => ['bg' => '#eff6ff', 'text' => '#1d4ed8'],
            'shipped' => ['bg' => '#f5f3ff', 'text' => '#6d28d9'],
            'completed' => ['bg' => '#ecfdf5', 'text' => '#047857'],
            'cancelled' => ['bg' => '#fef2f2', 'text' => '#b91c1c'],
        ];
        $statusStyle = $statusStyles[$order->status] ?? $statusStyles['pending'];
    @endphp

    <form action="{{ route('admin.orders.update', $order) }}" method="POST" enctype="multipart/form-data" class="order-form-page">
        @csrf
        @method('PUT')

        <div class="order-form-hero">
            <div>
                <span class="order-form-eyebrow">Order management</span>
                <h2>Edit {{ $order->number }}</h2>
                <p>Update customer details, line items, payments, and order status.</p>
                <div class="order-form-hero-meta">
                    <span class="order-form-hero-chip" style="background:{{ $statusStyle['bg'] }};color:{{ $statusStyle['text'] }};border-color:transparent">
                        <i class="fas fa-circle" style="font-size:0.45rem"></i> {{ $order->statusLabel() }}
                    </span>
                    <span class="order-form-hero-chip">
                        <i class="fas fa-wallet"></i> {{ $order->paymentStatusLabel() }}
                    </span>
                    <span class="order-form-hero-chip">
                        <i class="fas fa-box"></i> {{ $order->items->count() }} {{ Str::plural('item', $order->items->count()) }}
                    </span>
                    <span class="order-form-hero-chip">
                        <i class="fas fa-tag"></i> {{ money($order->total) }}
                    </span>
                </div>
            </div>
            <div class="order-form-hero-actions">
                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-light">
                    <i class="fas fa-eye mr-1"></i> View
                </a>
                <a href="{{ route('admin.orders.invoice', $order) }}" class="btn btn-light" target="_blank" rel="noopener">
                    <i class="fas fa-file-invoice mr-1"></i> Invoice
                </a>
                <button type="submit" class="btn btn-info">
                    <i class="fas fa-save mr-1"></i> Save Changes
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
                <div class="card order-form-card mb-3">
                    <div class="card-header order-form-card-header">
                        <span class="order-section-icon order-section-icon--cyan"><i class="fas fa-user"></i></span>
                        <div>
                            <h3 class="card-title">Customer & Shipping</h3>
                            <p>Contact and delivery address for this order.</p>
                        </div>
                        <span class="order-section-number">01</span>
                    </div>
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
                                <div class="form-group mb-md-0">
                                    <label>City</label>
                                    <input type="text" name="city" class="form-control" value="{{ old('city', $order->city) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label>ZIP</label>
                                    <input type="text" name="zip" class="form-control" value="{{ old('zip', $order->zip) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card order-form-card mb-3">
                    <div class="card-header order-form-card-header">
                        <span class="order-section-icon order-section-icon--violet"><i class="fas fa-box-open"></i></span>
                        <div>
                            <h3 class="card-title">Order Items</h3>
                            <p>Edit products, quantities, and prices.</p>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary ml-auto" id="add-item-row">
                            <i class="fas fa-plus mr-1"></i> Add Item
                        </button>
                        <span class="order-section-number">02</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive order-items-scroll">
                            <table class="table mb-0 order-form-table order-mobile-stack-table" id="items-table">
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
                                <tbody id="items-body">
                                    @foreach ($order->items as $item)
                                        <tr data-existing="1">
                                            <td class="order-mobile-lead" data-label="Product">
                                                <input type="text" name="items[{{ $item->id }}][product_name]" class="form-control form-control-sm item-name" value="{{ old('items.'.$item->id.'.product_name', $item->product_name) }}" required>
                                            </td>
                                            <td data-label="Slug">
                                                <input type="text" name="items[{{ $item->id }}][product_slug]" class="form-control form-control-sm item-slug" value="{{ old('items.'.$item->id.'.product_slug', $item->product_slug) }}" data-manual="1">
                                            </td>
                                            <td data-label="Link">
                                                <input type="text" name="items[{{ $item->id }}][product_link]" class="form-control form-control-sm" value="{{ old('items.'.$item->id.'.product_link', $item->product_link) }}" placeholder="https://...">
                                            </td>
                                            <td data-label="Image">
                                                <div class="order-item-image-cell">
                                                    @if ($item->imageUrl())
                                                        <a href="{{ $item->imageUrl() }}" target="_blank" rel="noopener" class="order-item-image-preview">
                                                            <img src="{{ $item->imageUrl() }}" alt="{{ $item->product_name }}">
                                                        </a>
                                                    @else
                                                        <span class="order-item-image-preview order-item-image-preview--empty">
                                                            <i class="fas fa-image"></i>
                                                        </span>
                                                    @endif
                                                    <label class="order-item-upload">
                                                        <i class="fas fa-cloud-upload-alt"></i>
                                                        <span>{{ $item->imageUrl() ? 'Replace' : 'Upload' }}</span>
                                                        <input type="file" name="items[{{ $item->id }}][image]" accept="image/*">
                                                    </label>
                                                </div>
                                            </td>
                                            <td data-label="Size">
                                                <input type="text" name="items[{{ $item->id }}][size]" class="form-control form-control-sm" value="{{ old('items.'.$item->id.'.size', $item->size) }}">
                                            </td>
                                            <td data-label="Color">
                                                <input type="text" name="items[{{ $item->id }}][color]" class="form-control form-control-sm" value="{{ old('items.'.$item->id.'.color', $item->color) }}">
                                            </td>
                                            <td data-label="Qty">
                                                <input type="number" name="items[{{ $item->id }}][quantity]" class="form-control form-control-sm item-qty" min="1" value="{{ old('items.'.$item->id.'.quantity', $item->quantity) }}" required>
                                            </td>
                                            <td data-label="Price">
                                                <input type="number" name="items[{{ $item->id }}][price]" class="form-control form-control-sm item-price" min="0" step="0.01" value="{{ old('items.'.$item->id.'.price', $item->price) }}" required>
                                            </td>
                                            <td class="text-center order-mobile-actions" data-label="">
                                                <label class="order-row-remove" title="Remove item">
                                                    <input type="checkbox" name="delete_items[]" value="{{ $item->id }}">
                                                    <span><i class="fas fa-trash"></i></span>
                                                </label>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card order-form-card">
                    <div class="card-header order-form-card-header">
                        <span class="order-section-icon order-section-icon--emerald"><i class="fas fa-wallet"></i></span>
                        <div>
                            <h3 class="card-title">Payment History</h3>
                            <p>Record or update payments for this order.</p>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary ml-auto" id="add-payment-row">
                            <i class="fas fa-plus mr-1"></i> Add Payment
                        </button>
                        <span class="order-section-number">03</span>
                    </div>
                    <div class="card-body p-0">
                        @if ($order->payments->isEmpty())
                            <div class="order-empty-state" id="no-payments-msg">
                                <span><i class="fas fa-receipt"></i></span>
                                <div>
                                    <strong>No payments recorded</strong>
                                    <p>Add a payment here, or set amount paid in Payment Details.</p>
                                </div>
                            </div>
                        @endif
                        <div class="table-responsive order-payments-scroll">
                            <table class="table mb-0 order-form-table order-mobile-stack-table {{ $order->payments->isEmpty() ? 'd-none' : '' }}" id="payments-table">
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
                                            <td data-label="Date"><span class="order-payment-date">{{ $payment->created_at->format('M d, Y H:i') }}</span></td>
                                            <td data-label="Amount">
                                                <input type="number" name="payments[{{ $payment->id }}][amount]" class="form-control form-control-sm" min="0" step="0.01" value="{{ old('payments.'.$payment->id.'.amount', $payment->amount) }}" required>
                                            </td>
                                            <td data-label="Method">
                                                <select name="payments[{{ $payment->id }}][payment_method]" class="form-control form-control-sm">
                                                    @foreach (['cod' => 'COD', 'cash' => 'Cash', 'bank_transfer' => 'Bank Transfer'] as $val => $label)
                                                        <option value="{{ $val }}" @selected(old('payments.'.$payment->id.'.payment_method', $payment->payment_method) === $val)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td data-label="Bank">
                                                <select name="payments[{{ $payment->id }}][bank_name]" class="form-control form-control-sm">
                                                    <option value="">—</option>
                                                    @foreach ($banks as $key => $label)
                                                        <option value="{{ $key }}" @selected(old('payments.'.$payment->id.'.bank_name', $payment->bank_name) === $label || old('payments.'.$payment->id.'.bank_name') === $key)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td data-label="Notes">
                                                <input type="text" name="payments[{{ $payment->id }}][notes]" class="form-control form-control-sm" value="{{ old('payments.'.$payment->id.'.notes', $payment->notes) }}" placeholder="Optional note">
                                            </td>
                                            <td class="text-center align-middle order-mobile-actions" data-label="">
                                                <label class="order-row-remove" title="Remove payment">
                                                    <input type="checkbox" name="delete_payments[]" value="{{ $payment->id }}">
                                                    <span><i class="fas fa-trash"></i></span>
                                                </label>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="order-form-sidebar">
                    <div class="card order-form-card order-total-card mb-3">
                        <div class="card-header order-form-card-header">
                            <span class="order-section-icon order-section-icon--amber"><i class="fas fa-calculator"></i></span>
                            <div>
                                <h3 class="card-title">Order Summary</h3>
                                <p>Pricing and delivery zone.</p>
                            </div>
                        </div>
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
                            <div class="order-total-preview">
                                <span>Order total</span>
                                <strong id="total-preview">{{ money(old('total', $order->total)) }}</strong>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-info btn-block mb-2" id="calc-from-items">
                                <i class="fas fa-sync-alt mr-1"></i> Calculate from items
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary btn-block" id="calc-total">
                                Recalculate total
                            </button>
                        </div>
                    </div>

                    <div class="card order-form-card mb-3">
                        <div class="card-header order-form-card-header">
                            <span class="order-section-icon order-section-icon--emerald"><i class="fas fa-credit-card"></i></span>
                            <div>
                                <h3 class="card-title">Payment Details</h3>
                                <p>Method, reference, and payment status.</p>
                            </div>
                        </div>
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
                                <small class="text-muted">Ignored when payment history exists.</small>
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
                                <div class="form-group mb-0">
                                    <label>Payment Screenshot</label>
                                    @if ($order->paymentScreenshotUrl())
                                        <div class="order-screenshot-preview">
                                            <a href="{{ $order->paymentScreenshotUrl() }}" target="_blank" rel="noopener">
                                                <img src="{{ $order->paymentScreenshotUrl() }}" alt="Payment screenshot">
                                            </a>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" name="remove_payment_screenshot" value="1" id="remove_screenshot">
                                                <label class="custom-control-label" for="remove_screenshot">Remove screenshot</label>
                                            </div>
                                        </div>
                                    @endif
                                    <input type="file" name="payment_screenshot" class="form-control-file" accept="image/*">
                                </div>
                            @else
                                <div class="form-group mb-0">
                                    <label>Notes</label>
                                    <textarea name="notes" class="form-control" rows="2">{{ old('notes', $order->notes) }}</textarea>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="card order-form-card">
                        <div class="card-header order-form-card-header">
                            <span class="order-section-icon order-section-icon--blue"><i class="fas fa-clipboard-check"></i></span>
                            <div>
                                <h3 class="card-title">Order Status</h3>
                                <p>Update the workflow state.</p>
                            </div>
                        </div>
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
                            <button type="submit" class="btn btn-info btn-block">
                                <i class="fas fa-save mr-1"></i> Save Changes
                            </button>
                            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-default btn-block">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <template id="item-row-template">
        <tr>
            <td class="order-mobile-lead" data-label="Product"><input type="text" name="new_items[__INDEX__][product_name]" class="form-control form-control-sm item-name" placeholder="Product name" required></td>
            <td data-label="Slug"><input type="text" name="new_items[__INDEX__][product_slug]" class="form-control form-control-sm item-slug" placeholder="Auto generated" readonly></td>
            <td data-label="Link"><input type="text" name="new_items[__INDEX__][product_link]" class="form-control form-control-sm" placeholder="https://..."></td>
            <td data-label="Image">
                <div class="order-item-image-cell">
                    <span class="order-item-image-preview order-item-image-preview--empty">
                        <i class="fas fa-image"></i>
                    </span>
                    <label class="order-item-upload">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <span>Upload</span>
                        <input type="file" name="new_items[__INDEX__][image]" accept="image/*">
                    </label>
                </div>
            </td>
            <td data-label="Size"><input type="text" name="new_items[__INDEX__][size]" class="form-control form-control-sm" placeholder="Size"></td>
            <td data-label="Color"><input type="text" name="new_items[__INDEX__][color]" class="form-control form-control-sm" placeholder="Color"></td>
            <td data-label="Qty"><input type="number" name="new_items[__INDEX__][quantity]" class="form-control form-control-sm item-qty" min="1" value="1" required></td>
            <td data-label="Price"><input type="number" name="new_items[__INDEX__][price]" class="form-control form-control-sm item-price" min="0" step="0.01" value="0" required></td>
            <td class="text-center order-mobile-actions" data-label=""><button type="button" class="btn btn-xs btn-outline-danger remove-row" title="Remove item"><i class="fas fa-trash"></i></button></td>
        </tr>
    </template>

    <template id="payment-row-template">
        <tr>
            <td data-label="Date"><span class="order-payment-date">New</span></td>
            <td data-label="Amount"><input type="number" name="new_payments[__INDEX__][amount]" class="form-control form-control-sm" min="0.01" step="0.01" required></td>
            <td data-label="Method">
                <select name="new_payments[__INDEX__][payment_method]" class="form-control form-control-sm">
                    <option value="cod">COD</option>
                    <option value="cash">Cash</option>
                    <option value="bank_transfer">Bank Transfer</option>
                </select>
            </td>
            <td data-label="Bank">
                <select name="new_payments[__INDEX__][bank_name]" class="form-control form-control-sm">
                    <option value="">—</option>
                    @foreach ($banks as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </td>
            <td data-label="Notes"><input type="text" name="new_payments[__INDEX__][notes]" class="form-control form-control-sm" placeholder="Optional note"></td>
            <td class="text-center align-middle order-mobile-actions" data-label=""><button type="button" class="btn btn-xs btn-outline-danger remove-row" title="Remove payment"><i class="fas fa-trash"></i></button></td>
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
        var html = tpl.innerHTML.replace(/__INDEX__/g, templateId === 'item-row-template' ? itemIndex++ : paymentIndex++);
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
        var btn = e.target.closest('.remove-row');
        if (btn) {
            btn.closest('tr').remove();
            toggleManualPaymentFields();
        }
    });

    function sumItems() {
        var total = 0;
        document.querySelectorAll('#items-body tr').forEach(function (row) {
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

    document.getElementById('items-body').addEventListener('change', function (e) {
        if (e.target.type === 'file') {
            var label = e.target.closest('.order-item-upload');
            var text = label ? label.querySelector('span') : null;
            var preview = e.target.closest('.order-item-image-cell')?.querySelector('.order-item-image-preview');
            var file = e.target.files && e.target.files[0];

            if (label && text) {
                if (file) {
                    text.textContent = file.name.length > 12 ? file.name.slice(0, 10) + '…' : file.name;
                    label.classList.add('has-file');
                } else {
                    text.textContent = preview && preview.querySelector('img') ? 'Replace' : 'Upload';
                    label.classList.remove('has-file');
                }
            }

            if (file && file.type.startsWith('image/') && preview) {
                var reader = new FileReader();
                reader.onload = function (event) {
                    if (preview.tagName === 'A') {
                        var img = preview.querySelector('img');
                        if (img) img.src = event.target.result;
                    } else {
                        preview.classList.remove('order-item-image-preview--empty');
                        preview.innerHTML = '<img src="' + event.target.result + '" alt="Preview">';
                    }
                };
                reader.readAsDataURL(file);
            }

            return;
        }

        if (e.target.name !== 'delete_items[]') return;
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
    updateTotalPreview(parseFloat(document.getElementById('total').value) || 0);
})();
</script>
@endpush
