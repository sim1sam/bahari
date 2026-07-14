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
                        <div class="form-group mb-3">
                            <label>Customer *</label>
                            <div class="customer-picker" id="customer-picker">
                                <input type="hidden" name="user_id" id="customer-select" value="{{ old('user_id', $order->user_id) }}">
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

                        <input type="hidden" name="customer_name" id="customer_name" value="{{ old('customer_name', $order->customer_name) }}">
                        <input type="hidden" name="customer_email" id="customer_email" value="{{ old('customer_email', $order->customer_email) }}">
                        <input type="hidden" name="city" id="customer_city" value="{{ old('city', $order->city) }}">
                        <input type="hidden" name="zip" id="customer_zip" value="{{ old('zip', $order->zip) }}">
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
                                <small class="text-muted" id="delivery-hint">Select a customer to auto-fill, or edit manually.</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="toggle-delivery-address">
                                <i class="fas fa-plus mr-1"></i> Add Address
                            </button>
                        </div>

                        <div id="delivery-address-panel" class="order-delivery-panel">
                            <div class="form-group">
                                <label>Phone</label>
                                <input type="text" name="customer_phone" id="customer_phone" class="form-control" value="{{ old('customer_phone', $order->customer_phone) }}" placeholder="01XXXXXXXXX">
                            </div>
                            <div class="form-group mb-0">
                                <label>Address</label>
                                <textarea name="address" id="customer_address" class="form-control" rows="3" placeholder="Full delivery address">{{ old('address', $order->address) }}</textarea>
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
                    <div class="card-body">
                        <div id="items-body" class="order-item-cards">
                            @foreach ($order->items as $item)
                                @php
                                    $itemQty = (float) old('items.'.$item->id.'.quantity', $item->quantity);
                                    $itemPrice = (float) old('items.'.$item->id.'.price', $item->price);
                                @endphp
                                <div class="order-item-card" data-existing="1">
                                    <div class="order-item-card-header">
                                        <span class="order-item-card-title">Product Item</span>
                                        <label class="order-row-remove mb-0" title="Remove item">
                                            <input type="checkbox" name="delete_items[]" value="{{ $item->id }}" class="item-delete-check">
                                            <span><i class="fas fa-trash"></i></span>
                                        </label>
                                    </div>
                                    <div class="form-group">
                                        <label>Product Name <span class="text-danger">*</span></label>
                                        <input type="text" name="items[{{ $item->id }}][product_name]" class="form-control item-name" value="{{ old('items.'.$item->id.'.product_name', $item->product_name) }}" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Product URL</label>
                                        <input type="text" name="items[{{ $item->id }}][product_link]" class="form-control" value="{{ old('items.'.$item->id.'.product_link', $item->product_link) }}" placeholder="https://...">
                                    </div>
                                    <div class="row align-items-end">
                                        <div class="col-4">
                                            <div class="form-group mb-0">
                                                <label>Size</label>
                                                <input type="text" name="items[{{ $item->id }}][size]" class="form-control" value="{{ old('items.'.$item->id.'.size', $item->size) }}" placeholder="Size">
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="form-group mb-0">
                                                <label>Quantity <span class="text-danger">*</span></label>
                                                <input type="number" name="items[{{ $item->id }}][quantity]" class="form-control item-qty" min="1" value="{{ $itemQty }}" required>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="form-group mb-0">
                                                <label>Price <span class="text-danger">*</span></label>
                                                <input type="number" name="items[{{ $item->id }}][price]" class="form-control item-price" min="0" step="0.01" value="{{ $itemPrice }}" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-2 align-items-end order-item-meta-row">
                                        <div class="col-12 col-sm-7">
                                            <div class="form-group mb-0">
                                                <label>Image</label>
                                                <div class="order-item-image-block">
                                                    @if ($item->imageUrl())
                                                        <a href="{{ $item->imageUrl() }}" target="_blank" rel="noopener" class="order-item-image-preview">
                                                            <img src="{{ $item->imageUrl() }}" alt="{{ $item->product_name }}">
                                                        </a>
                                                    @else
                                                        <span class="order-item-image-preview order-item-image-preview--empty">
                                                            <i class="fas fa-image"></i>
                                                        </span>
                                                    @endif
                                                    <div class="order-item-image-fields">
                                                        <label class="order-item-upload">
                                                            <i class="fas fa-cloud-upload-alt"></i>
                                                            <span>{{ $item->imageUrl() ? 'Replace' : 'Upload' }}</span>
                                                            <input type="file" name="items[{{ $item->id }}][image]" accept="image/*">
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-sm-5">
                                            <div class="form-group mb-0">
                                                <label>Amount</label>
                                                <input type="text" class="form-control item-amount" value="{{ number_format($itemQty * $itemPrice, 2, '.', '') }}" readonly tabindex="-1">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
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
                                <small class="text-muted">
                                    Inside {{ money($shippingFeeInside) }} · Outside {{ money($shippingFeeOutside) }}.
                                    Free above {{ money($freeShippingThreshold) }}. Updates automatically.
                                </small>
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
        <div class="order-item-card">
            <div class="order-item-card-header">
                <span class="order-item-card-title">Product Item</span>
                <button type="button" class="btn btn-xs btn-outline-danger remove-row" title="Remove item"><i class="fas fa-trash"></i></button>
            </div>
            <div class="form-group">
                <label>Product Name <span class="text-danger">*</span></label>
                <input type="text" name="new_items[__INDEX__][product_name]" class="form-control item-name" placeholder="Product name" required>
            </div>
            <div class="form-group">
                <label>Product URL</label>
                <input type="text" name="new_items[__INDEX__][product_link]" class="form-control" placeholder="https://...">
            </div>
            <div class="row align-items-end">
                <div class="col-4">
                    <div class="form-group mb-0">
                        <label>Size</label>
                        <input type="text" name="new_items[__INDEX__][size]" class="form-control" placeholder="Size">
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group mb-0">
                        <label>Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="new_items[__INDEX__][quantity]" class="form-control item-qty" min="1" value="1" required>
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group mb-0">
                        <label>Price <span class="text-danger">*</span></label>
                        <input type="number" name="new_items[__INDEX__][price]" class="form-control item-price" min="0" step="0.01" value="0" required>
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
                                    <input type="file" name="new_items[__INDEX__][image]" accept="image/*">
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
                ? 'Delivery address for this order.'
                : 'Enter the delivery address for this order.';
        }
    }

    function closeDeliveryPanel() {
        var panel = document.getElementById('delivery-address-panel');
        var btn = document.getElementById('toggle-delivery-address');
        var hint = document.getElementById('delivery-hint');
        panel.classList.add('d-none');
        btn.innerHTML = '<i class="fas fa-plus mr-1"></i> Add Address';
        if (hint) hint.textContent = 'Select a customer to auto-fill, or edit manually.';
    }

    function applyCustomer(customer, overwriteAddress) {
        var picker = document.getElementById('customer-picker');
        var hidden = document.getElementById('customer-select');
        var search = document.getElementById('customer-search');
        var clearBtn = document.getElementById('customer-clear');

        if (!customer) {
            hidden.value = '';
            search.value = '';
            clearBtn.classList.remove('is-visible');
            picker.classList.remove('has-value');
            return;
        }

        hidden.value = customer.id;
        search.value = customer.name + (customer.phone ? ' · ' + customer.phone : '') + ' · ' + customer.email;
        clearBtn.classList.add('is-visible');
        picker.classList.add('has-value');
        document.getElementById('customer_name').value = customer.name || '';
        document.getElementById('customer_email').value = customer.email || '';

        if (overwriteAddress) {
            document.getElementById('customer_phone').value = customer.phone || '';
            document.getElementById('customer_city').value = customer.city || '';
            document.getElementById('customer_zip').value = customer.zip || '';
            var addressParts = [customer.address, customer.city, customer.zip]
                .map(function (part) { return (part || '').trim(); })
                .filter(Boolean);
            document.getElementById('customer_address').value = addressParts.join(', ');
            openDeliveryPanel(!!(customer.address || customer.phone));
        }
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
                    applyCustomer(results[activeIndex], true);
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
            if (customer) applyCustomer(customer, true);
            hideDropdown();
        });
        clearBtn.addEventListener('click', function () {
            applyCustomer(null, false);
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
            } else {
                var name = document.getElementById('customer_name').value;
                var email = document.getElementById('customer_email').value;
                search.value = [name, email].filter(Boolean).join(' · ');
                if (search.value) {
                    clearBtn.classList.add('is-visible');
                    picker.classList.add('has-value');
                }
            }
        } else {
            var fallbackName = document.getElementById('customer_name').value;
            var fallbackEmail = document.getElementById('customer_email').value;
            search.value = [fallbackName, fallbackEmail].filter(Boolean).join(' · ');
            if (search.value) {
                clearBtn.classList.add('is-visible');
                picker.classList.add('has-value');
            }
        }
    })();

    openDeliveryPanel(true);

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
            if (card.querySelector('.item-delete-check')?.checked) return;
            if (card.classList.contains('is-marked-delete')) return;
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

    document.getElementById('items-body').addEventListener('change', function (e) {
        if (e.target.classList.contains('item-delete-check')) {
            var card = e.target.closest('.order-item-card');
            if (card) card.classList.toggle('is-marked-delete', e.target.checked);
            syncTotalsFromItems();
            return;
        }

        if (e.target.type !== 'file') return;
        var label = e.target.closest('.order-item-upload');
        var text = label ? label.querySelector('span') : null;
        var preview = e.target.closest('.order-item-card')?.querySelector('.order-item-image-preview');
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
                    preview.href = event.target.result;
                } else {
                    preview.classList.remove('order-item-image-preview--empty');
                    preview.innerHTML = '<img src="' + event.target.result + '" alt="Preview">';
                }
            };
            reader.readAsDataURL(file);
        }
    });

    function toggleManualPaymentFields() {
        var hasPayments = document.querySelectorAll('#payments-body tr').length > 0;
        var manualFields = document.getElementById('manual-payment-fields');
        var manualStatus = document.getElementById('manual-payment-status');
        if (manualFields) manualFields.style.display = hasPayments ? 'none' : 'block';
        if (manualStatus) manualStatus.style.display = hasPayments ? 'none' : 'block';
    }

    toggleManualPaymentFields();
    updateItemAmounts();
    updateTotalPreview(parseFloat(document.getElementById('total').value) || 0);
})();
</script>
@endpush
