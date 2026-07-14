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
                            <p>Select a customer to load their delivery address.</p>
                        </div>
                        <span class="order-section-number">01</span>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label>Customer *</label>
                            <div class="customer-picker" id="customer-picker">
                                <input type="hidden" name="user_id" id="customer-select" value="{{ old('user_id') }}" required>
                                <div class="customer-picker-control">
                                    <i class="fas fa-search customer-picker-icon"></i>
                                    <input
                                        type="text"
                                        id="customer-search"
                                        class="form-control customer-picker-input"
                                        placeholder="Search by name, mobile, or email"
                                        autocomplete="off"
                                        value=""
                                    >
                                    <button type="button" class="customer-picker-clear" id="customer-clear" title="Clear">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <div class="customer-picker-dropdown d-none" id="customer-dropdown"></div>
                            </div>
                            <small class="text-muted">Type to search by name, mobile number, or email.</small>
                        </div>

                        <input type="hidden" name="customer_name" id="customer_name" value="{{ old('customer_name') }}">
                        <input type="hidden" name="customer_email" id="customer_email" value="{{ old('customer_email') }}">
                        <input type="hidden" name="city" id="customer_city" value="{{ old('city') }}">
                        <input type="hidden" name="zip" id="customer_zip" value="{{ old('zip') }}">
                        @error('customer_name')<div class="text-danger mb-2">{{ $message }}</div>@enderror
                        @error('user_id')<div class="text-danger mb-2">{{ $message }}</div>@enderror

                        @php
                            $customerPickerData = $customers->map(function ($customer) {
                                $defaultAddress = $customer->addresses->firstWhere('is_default', true) ?? $customer->addresses->first();
                                $phones = $customer->addresses->pluck('phone')->filter()->unique()->values();
                                $primaryPhone = $defaultAddress?->phone ?: ($phones->first() ?? '');

                                return [
                                    'id' => $customer->id,
                                    'name' => $customer->name,
                                    'email' => $customer->email,
                                    'phone' => $primaryPhone,
                                    'address' => $defaultAddress?->address_line ?? '',
                                    'city' => $defaultAddress?->city ?? '',
                                    'zip' => $defaultAddress?->zip ?? '',
                                    'search' => strtolower(trim(implode(' ', array_filter([
                                        $customer->name,
                                        $customer->email,
                                        $phones->implode(' '),
                                    ])))),
                                ];
                            })->values();
                        @endphp
                        <script type="application/json" id="customer-picker-data">@json($customerPickerData)</script>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div>
                                <strong class="d-block" style="font-size:0.9rem;color:#334155">Delivery Address</strong>
                                <small class="text-muted" id="delivery-hint">Select a customer to auto-fill, or add manually.</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="toggle-delivery-address">
                                <i class="fas fa-plus mr-1"></i> Add Address
                            </button>
                        </div>

                        <div id="delivery-address-panel" class="order-delivery-panel {{ old('address') || old('customer_phone') ? '' : 'd-none' }}">
                            <div class="form-group">
                                <label>Phone</label>
                                <input type="text" name="customer_phone" id="customer_phone" class="form-control" value="{{ old('customer_phone') }}" placeholder="01XXXXXXXXX">
                            </div>
                            <div class="form-group mb-0">
                                <label>Address</label>
                                <textarea name="address" id="customer_address" class="form-control" rows="3" placeholder="Full delivery address">{{ old('address') }}</textarea>
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
                    <div class="card-body">
                        @error('items')<div class="text-danger mb-2">{{ $message }}</div>@enderror
                        <div id="items-body" class="order-item-cards"></div>
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
                        <div class="table-responsive order-payments-scroll">
                        <table class="table mb-0 d-none order-form-table order-mobile-stack-table" id="payments-table">
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
                            <input type="number" name="shipping" id="shipping" class="form-control" min="0" step="0.01" value="{{ old('shipping', $shippingFeeInside) }}" required>
                            <small class="text-muted">
                                Inside {{ money($shippingFeeInside) }} · Outside {{ money($shippingFeeOutside) }}.
                                Free above {{ money($freeShippingThreshold) }}. Updates automatically.
                            </small>
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
        <div class="order-item-card">
            <div class="order-item-card-header">
                <span class="order-item-card-title">Product Item</span>
                <button type="button" class="btn btn-xs btn-outline-danger remove-row" title="Remove item"><i class="fas fa-trash"></i></button>
            </div>
            <div class="form-group">
                <label>Product Name <span class="text-danger">*</span></label>
                <input type="text" name="items[__INDEX__][product_name]" class="form-control item-name" placeholder="Product name" required>
            </div>
            <div class="form-group">
                <label>Product URL</label>
                <input type="text" name="items[__INDEX__][product_link]" class="form-control" placeholder="https://...">
            </div>
            <div class="row align-items-end">
                <div class="col-4">
                    <div class="form-group mb-0">
                        <label>Size</label>
                        <input type="text" name="items[__INDEX__][size]" class="form-control" placeholder="Size">
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group mb-0">
                        <label>Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="items[__INDEX__][quantity]" class="form-control item-qty" min="1" value="1" required>
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group mb-0">
                        <label>Price <span class="text-danger">*</span></label>
                        <input type="number" name="items[__INDEX__][price]" class="form-control item-price" min="0" step="0.01" value="0" required>
                    </div>
                </div>
            </div>
            <div class="row mt-2 align-items-end order-item-meta-row">
                <div class="col-12 col-sm-7">
                    <div class="form-group mb-0">
                        <label>Image</label>
                        <div class="order-item-image-block">
                            <span class="order-item-image-preview order-item-image-preview--empty">
                                <i class="fas fa-image"></i>
                            </span>
                            <div class="order-item-image-fields">
                                <label class="order-item-upload">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span>Upload</span>
                                    <input type="file" name="items[__INDEX__][image]" accept="image/*">
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-5">
                    <div class="form-group mb-0">
                        <label>Amount</label>
                        <input type="text" class="form-control item-amount" value="0" readonly tabindex="-1">
                    </div>
                </div>
            </div>
        </div>
    </template>

    <template id="payment-row-template">
        <tr>
            <td data-label="Amount"><input type="number" name="payments[__INDEX__][amount]" class="form-control form-control-sm" min="0.01" step="0.01" required></td>
            <td data-label="Method">
                <select name="payments[__INDEX__][payment_method]" class="form-control form-control-sm">
                    <option value="cod">COD</option>
                    <option value="cash">Cash</option>
                    <option value="bank_transfer">Bank Transfer</option>
                </select>
            </td>
            <td data-label="Bank">
                <select name="payments[__INDEX__][bank_name]" class="form-control form-control-sm">
                    <option value="">—</option>
                    @foreach ($banks as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </td>
            <td data-label="Notes"><input type="text" name="payments[__INDEX__][notes]" class="form-control form-control-sm" placeholder="Optional note"></td>
            <td class="text-center align-middle order-mobile-actions" data-label=""><button type="button" class="btn btn-xs btn-outline-danger remove-row" title="Remove payment"><i class="fas fa-trash"></i></button></td>
        </tr>
    </template>
@endsection

@push('styles')
    @include('admin.orders.partials.form-styles')
    <style>
        .customer-picker { position: relative; }
        .customer-picker-control { position: relative; display: flex; align-items: center; }
        .customer-picker-icon {
            position: absolute; left: 0.85rem; z-index: 2; color: #94a3b8;
            font-size: 0.85rem; pointer-events: none;
        }
        .customer-picker-input {
            padding-left: 2.35rem !important;
            padding-right: 2.4rem !important;
            text-align: left !important;
        }
        .customer-picker-input::placeholder {
            color: #94a3b8; text-align: left; opacity: 1;
        }
        .customer-picker-clear {
            position: absolute; right: 0.55rem; z-index: 2;
            width: 1.6rem; height: 1.6rem; border: 0; border-radius: 999px;
            background: #e2e8f0; color: #64748b;
            display: none; align-items: center; justify-content: center;
            cursor: pointer; padding: 0; line-height: 1;
        }
        .customer-picker-clear.is-visible { display: inline-flex; }
        .customer-picker-clear:hover { background: #cbd5e1; color: #0f172a; }
        .customer-picker-dropdown {
            position: absolute; top: calc(100% + 0.35rem); left: 0; right: 0; z-index: 30;
            max-height: 16rem; overflow-y: auto; border: 1px solid #dbe3ed;
            border-radius: 0.75rem; background: #fff;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.12); text-align: left;
        }
        .customer-picker-option {
            display: block; width: 100%; border: 0; background: transparent;
            text-align: left; padding: 0.7rem 0.9rem; cursor: pointer;
            border-bottom: 1px solid #f1f5f9;
        }
        .customer-picker-option:last-child { border-bottom: 0; }
        .customer-picker-option:hover,
        .customer-picker-option.is-active { background: #ecfeff; }
        .customer-picker-option-name {
            display: block; font-size: 0.92rem; font-weight: 600;
            color: #0f172a; text-align: left; margin-bottom: 0.15rem;
        }
        .customer-picker-option-meta {
            display: flex; flex-wrap: wrap; gap: 0.35rem 0.85rem;
            font-size: 0.78rem; color: #64748b; text-align: left;
        }
        .customer-picker-option-meta span {
            display: inline-flex; align-items: center; gap: 0.3rem;
        }
        .customer-picker-empty {
            padding: 0.9rem; color: #94a3b8; font-size: 0.85rem; text-align: left;
        }
        .customer-picker.has-value .customer-picker-input {
            font-weight: 600; color: #0f172a;
        }
    </style>
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
        if (subtotal > 0 && subtotal >= freeShippingThreshold) return 0;
        return zoneShippingFee();
    }

    function openDeliveryPanel(filled) {
        var panel = document.getElementById('delivery-address-panel');
        var btn = document.getElementById('toggle-delivery-address');
        var hint = document.getElementById('delivery-hint');
        panel.classList.remove('d-none');
        btn.innerHTML = '<i class="fas fa-times mr-1"></i> Hide Address';
        if (hint) {
            hint.textContent = filled
                ? 'Loaded from customer saved address.'
                : 'Enter the delivery address for this order.';
        }
    }

    function closeDeliveryPanel() {
        var panel = document.getElementById('delivery-address-panel');
        var btn = document.getElementById('toggle-delivery-address');
        var hint = document.getElementById('delivery-hint');
        panel.classList.add('d-none');
        btn.innerHTML = '<i class="fas fa-plus mr-1"></i> Add Address';
        if (hint) hint.textContent = 'Select a customer to auto-fill, or add manually.';
    }

    function applyCustomer(customer) {
        var picker = document.getElementById('customer-picker');
        var hidden = document.getElementById('customer-select');
        var search = document.getElementById('customer-search');
        var clearBtn = document.getElementById('customer-clear');

        if (!customer) {
            hidden.value = '';
            search.value = '';
            clearBtn.classList.remove('is-visible');
            picker.classList.remove('has-value');
            document.getElementById('customer_name').value = '';
            document.getElementById('customer_email').value = '';
            document.getElementById('customer_phone').value = '';
            document.getElementById('customer_address').value = '';
            document.getElementById('customer_city').value = '';
            document.getElementById('customer_zip').value = '';
            closeDeliveryPanel();
            return;
        }

        hidden.value = customer.id;
        search.value = customer.name + (customer.phone ? ' · ' + customer.phone : '') + ' · ' + customer.email;
        clearBtn.classList.add('is-visible');
        picker.classList.add('has-value');
        document.getElementById('customer_name').value = customer.name || '';
        document.getElementById('customer_email').value = customer.email || '';
        document.getElementById('customer_phone').value = customer.phone || '';
        document.getElementById('customer_city').value = customer.city || '';
        document.getElementById('customer_zip').value = customer.zip || '';
        var addressParts = [customer.address, customer.city, customer.zip]
            .map(function (part) { return (part || '').trim(); })
            .filter(Boolean);
        document.getElementById('customer_address').value = addressParts.join(', ');
        if (customer.address || customer.phone) openDeliveryPanel(true);
        else openDeliveryPanel(false);
    }

    (function initCustomerPicker() {
        var customers = [];
        try {
            customers = JSON.parse(document.getElementById('customer-picker-data').textContent || '[]');
        } catch (e) { customers = []; }

        var picker = document.getElementById('customer-picker');
        var search = document.getElementById('customer-search');
        var dropdown = document.getElementById('customer-dropdown');
        var clearBtn = document.getElementById('customer-clear');
        var hidden = document.getElementById('customer-select');
        var activeIndex = -1;

        function hideDropdown() {
            dropdown.classList.add('d-none');
            dropdown.innerHTML = '';
            activeIndex = -1;
            dropdown._results = [];
        }

        function escapeHtml(value) {
            return String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        function highlightActive() {
            dropdown.querySelectorAll('.customer-picker-option').forEach(function (el, index) {
                el.classList.toggle('is-active', index === activeIndex);
            });
        }

        function showCustomers(list) {
            if (!list.length) {
                dropdown.innerHTML = '<div class="customer-picker-empty">No customer found</div>';
                dropdown.classList.remove('d-none');
                activeIndex = -1;
                dropdown._results = [];
                return;
            }
            dropdown.innerHTML = list.map(function (customer, index) {
                return '<button type="button" class="customer-picker-option" data-index="' + index + '" data-id="' + customer.id + '">' +
                    '<span class="customer-picker-option-name">' + escapeHtml(customer.name) + '</span>' +
                    '<span class="customer-picker-option-meta">' +
                        (customer.phone ? '<span><i class="fas fa-phone"></i> ' + escapeHtml(customer.phone) + '</span>' : '') +
                        '<span><i class="fas fa-envelope"></i> ' + escapeHtml(customer.email) + '</span>' +
                    '</span></button>';
            }).join('');
            dropdown.classList.remove('d-none');
            activeIndex = 0;
            highlightActive();
            dropdown._results = list;
        }

        function filterCustomers(term) {
            term = (term || '').toLowerCase().replace(/\s+/g, ' ').trim();
            if (!term) return customers.slice(0, 20);
            return customers.filter(function (customer) {
                return (customer.search || '').indexOf(term) > -1;
            }).slice(0, 30);
        }

        search.addEventListener('focus', function () {
            showCustomers(filterCustomers(search.value));
        });
        search.addEventListener('input', function () {
            hidden.value = '';
            clearBtn.classList.remove('is-visible');
            picker.classList.remove('has-value');
            showCustomers(filterCustomers(this.value));
        });
        search.addEventListener('keydown', function (e) {
            var results = dropdown._results || [];
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (!results.length) return;
                activeIndex = (activeIndex + 1) % results.length;
                highlightActive();
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (!results.length) return;
                activeIndex = (activeIndex - 1 + results.length) % results.length;
                highlightActive();
            } else if (e.key === 'Enter') {
                if (!dropdown.classList.contains('d-none') && results[activeIndex]) {
                    e.preventDefault();
                    applyCustomer(results[activeIndex]);
                    hideDropdown();
                }
            } else if (e.key === 'Escape') {
                hideDropdown();
            }
        });
        dropdown.addEventListener('mousedown', function (e) {
            var option = e.target.closest('.customer-picker-option');
            if (!option) return;
            e.preventDefault();
            var id = option.getAttribute('data-id');
            var customer = customers.find(function (item) { return String(item.id) === String(id); });
            if (customer) applyCustomer(customer);
            hideDropdown();
        });
        clearBtn.addEventListener('click', function () {
            applyCustomer(null);
            search.focus();
            showCustomers(filterCustomers(''));
        });
        document.addEventListener('click', function (e) {
            if (!picker.contains(e.target)) hideDropdown();
        });

        if (hidden.value) {
            var selected = customers.find(function (item) { return String(item.id) === String(hidden.value); });
            if (selected) {
                search.value = selected.name + (selected.phone ? ' · ' + selected.phone : '') + ' · ' + selected.email;
                clearBtn.classList.add('is-visible');
                picker.classList.add('has-value');
                if (!document.getElementById('customer_name').value) {
                    document.getElementById('customer_name').value = selected.name || '';
                }
                if (!document.getElementById('customer_email').value) {
                    document.getElementById('customer_email').value = selected.email || '';
                }
            }
        }
    })();

    document.getElementById('toggle-delivery-address').addEventListener('click', function () {
        var panel = document.getElementById('delivery-address-panel');
        if (panel.classList.contains('d-none')) openDeliveryPanel(false);
        else closeDeliveryPanel();
    });

    function addItemCard() {
        var tpl = document.getElementById('item-row-template');
        var index = itemIndex++;
        var html = tpl.innerHTML.replace(/__INDEX__/g, index);
        document.getElementById('items-body').insertAdjacentHTML('beforeend', html);
        updateItemAmounts();
    }

    function addPaymentRow() {
        var tpl = document.getElementById('payment-row-template');
        var index = paymentIndex++;
        var html = tpl.innerHTML.replace(/__INDEX__/g, index);
        document.getElementById('payments-body').insertAdjacentHTML('beforeend', html);
        document.getElementById('payments-table').classList.remove('d-none');
        var msg = document.getElementById('no-payments-msg');
        if (msg) msg.classList.add('d-none');
        toggleManualPaymentFields();
    }

    document.getElementById('add-item-row').addEventListener('click', function () { addItemCard(); });
    document.getElementById('add-payment-row').addEventListener('click', function () { addPaymentRow(); });

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.remove-row');
        if (!btn) return;
        var card = btn.closest('.order-item-card');
        if (card) {
            card.remove();
            updateItemAmounts();
            syncTotalsFromItems();
            return;
        }
        var row = btn.closest('tr');
        if (row) {
            row.remove();
            toggleManualPaymentFields();
        }
    });

    function updateItemAmounts() {
        document.querySelectorAll('#items-body .order-item-card').forEach(function (card) {
            var qty = parseFloat(card.querySelector('.item-qty')?.value) || 0;
            var price = parseFloat(card.querySelector('.item-price')?.value) || 0;
            var amount = card.querySelector('.item-amount');
            if (amount) amount.value = (qty * price).toFixed(2);
        });
    }

    function sumItems() {
        var total = 0;
        document.querySelectorAll('#items-body .order-item-card').forEach(function (card) {
            var qty = parseFloat(card.querySelector('.item-qty')?.value) || 0;
            var price = parseFloat(card.querySelector('.item-price')?.value) || 0;
            total += qty * price;
        });
        return total;
    }

    function syncTotalsFromItems() {
        var subtotal = sumItems();
        document.getElementById('subtotal').value = subtotal.toFixed(2);
        document.getElementById('shipping').value = calcShipping(subtotal).toFixed(2);
        recalcTotal();
    }

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

    function applyShippingAndTotal() {
        var sub = sumItems();
        if (sub <= 0) {
            sub = parseFloat(document.getElementById('subtotal').value) || 0;
        } else {
            document.getElementById('subtotal').value = sub.toFixed(2);
        }
        document.getElementById('shipping').value = calcShipping(sub).toFixed(2);
        recalcTotal();
    }

    document.getElementById('shipping_zone').addEventListener('change', applyShippingAndTotal);

    ['subtotal', 'discount', 'shipping'].forEach(function (id) {
        document.getElementById(id).addEventListener('input', function () {
            if (id !== 'shipping') {
                var sub = parseFloat(document.getElementById('subtotal').value) || 0;
                document.getElementById('shipping').value = calcShipping(sub).toFixed(2);
            }
            recalcTotal();
        });
    });

    document.getElementById('total').addEventListener('input', function () {
        updateTotalPreview(parseFloat(this.value) || 0);
    });

    document.getElementById('items-body').addEventListener('input', function (e) {
        if (e.target.classList.contains('item-qty') || e.target.classList.contains('item-price')) {
            updateItemAmounts();
            syncTotalsFromItems();
        }
    });

    function toggleManualPaymentFields() {
        var hasPayments = document.querySelectorAll('#payments-body tr').length > 0;
        var manualFields = document.getElementById('manual-payment-fields');
        var manualStatus = document.getElementById('manual-payment-status');
        if (manualFields) manualFields.style.display = hasPayments ? 'none' : 'block';
        if (manualStatus) manualStatus.style.display = hasPayments ? 'none' : 'block';
    }

    function setImagePreview(preview, src) {
        if (!preview || !src) return;
        preview.classList.remove('order-item-image-preview--empty');
        preview.innerHTML = '<img src="' + src + '" alt="Preview">';
    }

    document.getElementById('items-body').addEventListener('change', function (e) {
        if (e.target.type !== 'file') return;
        var label = e.target.closest('.order-item-upload');
        var text = label ? label.querySelector('span') : null;
        var preview = e.target.closest('.order-item-card')?.querySelector('.order-item-image-preview');
        var file = e.target.files && e.target.files[0];
        if (label && text) {
            if (file) {
                text.textContent = file.name.length > 18 ? file.name.slice(0, 16) + '…' : file.name;
                label.classList.add('has-file');
            } else {
                text.textContent = 'Upload';
                label.classList.remove('has-file');
            }
        }
        if (file && file.type.startsWith('image/') && preview) {
            var reader = new FileReader();
            reader.onload = function (event) { setImagePreview(preview, event.target.result); };
            reader.readAsDataURL(file);
        }
    });

    @if (old('user_id') || old('address') || old('customer_phone'))
    openDeliveryPanel({{ old('address') || old('customer_phone') ? 'true' : 'false' }});
    @endif

    document.querySelector('.order-create-form').addEventListener('submit', function (e) {
        var hidden = document.getElementById('customer-select');
        if (!hidden.value) {
            e.preventDefault();
            document.getElementById('customer-search').focus();
            alert('Please select a customer.');
        }
    });

    addItemCard();
    toggleManualPaymentFields();
    applyShippingAndTotal();
})();
</script>
@endpush
