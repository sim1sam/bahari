@extends('layouts.ecommerce')

@section('title', 'Checkout')

@section('content')
    <div class="bg-surface-elevated border-b border-border">
        <div class="container-store py-8">
            <h1 class="text-3xl font-bold tracking-tight text-ink">Checkout</h1>
            <p class="mt-1 text-ink-muted">Complete your order details below</p>
        </div>
    </div>

    <section class="py-10 lg:py-14">
        <div class="container-store">
            @php
                $addressPayload = $addresses->map(fn ($address) => [
                    'id' => $address->id,
                    'name' => $address->recipient_name,
                    'phone' => $address->phone,
                    'address' => $address->address_line,
                    'city' => $address->city,
                    'zip' => $address->zip,
                ])->values();
                $bankPayload = $banks->map(fn ($bank) => [
                    'id' => $bank->id,
                    'name' => $bank->name,
                    'account_name' => $bank->account_name,
                    'account_number' => $bank->account_number,
                    'branch' => $bank->branch,
                    'instructions' => $bank->instructions,
                    'charge_percent' => (float) $bank->charge_percent,
                    'image_url' => $bank->imageUrl(),
                ])->values();
            @endphp

            <form
                id="checkout-form"
                action="{{ route('checkout.store') }}"
                method="POST"
                enctype="multipart/form-data"
                @submit="preparePaymentSubmit"
                x-data="{
                    mode: @js(old('address_mode', $selectedAddress ? 'existing' : 'new')),
                    selectedId: @js((string) old('address_id', $selectedAddress?->id ?? '')),
                    addresses: @js($addressPayload),
                    details: {
                        name: @js(old('name', $checkoutDetails['name'])),
                        email: @js(old('email', $checkoutDetails['email'])),
                        phone: @js(old('phone', $checkoutDetails['phone'])),
                        address: @js(old('address', $checkoutDetails['address'])),
                        city: @js(old('city', $checkoutDetails['city'])),
                        zip: @js(old('zip', $checkoutDetails['zip'])),
                    },
                    payment: @js(old('payment', 'cod')),
                    shippingZone: @js(old('shipping_zone', $shippingZone)),
                    subtotal: {{ (float) $subtotal }},
                    discount: {{ (float) $discount }},
                    shippingFeeInside: {{ (float) $shippingFeeInside }},
                    shippingFeeOutside: {{ (float) $shippingFeeOutside }},
                    freeShippingThreshold: {{ (float) $freeShippingAt }},
                    get shippingAmount() {
                        if (this.subtotal <= 0 || this.subtotal >= this.freeShippingThreshold) {
                            return 0;
                        }

                        return this.shippingZone === 'outside_dhaka' ? this.shippingFeeOutside : this.shippingFeeInside;
                    },
                    get orderTotal() {
                        return Math.max(0, this.subtotal - this.discount) + this.shippingAmount;
                    },
                    total: {{ (float) $total }},
                    showPaymentModal: false,
                    paymentConfirmed: false,
                    paymentAmount: {{ old('payment_amount', (float) $total) }},
                    selectedBankId: @js((string) old('bank_id', '')),
                    banks: @js($bankPayload),
                    screenshotPreview: null,
                    selectPayment(method) {
                        this.payment = method;
                        if (method === 'bank_transfer') {
                            this.ensureBankSelected();
                            this.updateBankPaymentAmount();
                        } else {
                            this.paymentAmount = this.cartAmount;
                        }
                    },
                    ensureBankSelected() {
                        if (! this.selectedBankId && this.banks.length > 0) {
                            this.selectedBankId = String(this.banks[0].id);
                        }
                    },
                    get selectedBank() {
                        return this.banks.find((bank) => String(bank.id) === String(this.selectedBankId)) || null;
                    },
                    get bankChargePercent() {
                        return Number(this.selectedBank?.charge_percent || 0);
                    },
                    get cartAmount() {
                        return Math.max(0, Number(this.orderTotal || 0));
                    },
                    get bankChargeAmount() {
                        if (this.payment !== 'bank_transfer' || this.bankChargePercent <= 0) {
                            return 0;
                        }

                        const amount = this.cartAmount;
                        if (amount <= 0) {
                            return 0;
                        }

                        const rawCharge = Math.round((amount * this.bankChargePercent / 100) * 100) / 100;

                        return Math.floor(rawCharge / 5) * 5;
                    },
                    get totalPayable() {
                        if (this.payment !== 'bank_transfer') {
                            return this.cartAmount;
                        }

                        return Math.round((this.cartAmount + this.bankChargeAmount) * 100) / 100;
                    },
                    updateBankPaymentAmount() {
                        if (this.payment === 'bank_transfer') {
                            this.paymentAmount = this.totalPayable;
                        }
                    },
                    formatMoney(value) {
                        return '৳' + Number(value || 0).toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2,
                        });
                    },
                    get selectedBankHasDetails() {
                        if (! this.selectedBank) {
                            return false;
                        }

                        return Boolean(
                            this.selectedBank.account_name
                            || this.selectedBank.account_number
                            || this.selectedBank.branch
                            || this.selectedBank.instructions
                        );
                    },
                    async copyAccountNumber() {
                        const number = this.selectedBank?.account_number;
                        if (! number) {
                            return;
                        }

                        try {
                            await navigator.clipboard.writeText(number);
                            alert('Account number copied.');
                        } catch (e) {
                            alert(number);
                        }
                    },
                    useSavedAddress(id) {
                        this.mode = 'existing';
                        this.selectedId = String(id);
                        const address = this.addresses.find((item) => String(item.id) === this.selectedId);
                        if (! address) return;
                        this.details.name = address.name;
                        this.details.phone = address.phone;
                        this.details.address = address.address;
                        this.details.city = address.city;
                        this.details.zip = address.zip;
                    },
                    addNewAddress() {
                        this.mode = 'new';
                        this.selectedId = '';
                        this.details.name = @js(old('name', auth()->user()->name));
                        this.details.phone = '';
                        this.details.address = '';
                        this.details.city = '';
                        this.details.zip = '';
                    },
                    openPaymentModal() {
                        if (this.payment === 'bank_transfer') {
                            this.ensureBankSelected();
                            this.updateBankPaymentAmount();
                        } else {
                            this.paymentAmount = this.paymentAmount > 0 ? this.paymentAmount : this.cartAmount;
                        }
                        this.showPaymentModal = true;
                    },
                    closePaymentModal() {
                        this.showPaymentModal = false;
                    },
                    onScreenshot(event) {
                        const file = event.target.files[0];
                        this.screenshotPreview = file ? URL.createObjectURL(file) : null;
                    },
                    confirmPayment() {
                        if (this.payment === 'bank_transfer') {
                            this.updateBankPaymentAmount();
                        }

                        if (Number(this.paymentAmount) <= 0) {
                            alert('Please enter payment amount.');
                            return;
                        }
                        if (this.payment === 'bank_transfer') {
                            const fileInput = document.getElementById('payment_screenshot');
                            if (! this.selectedBankId) {
                                alert('Please select a bank.');
                                return;
                            }
                            if (! fileInput?.files?.length) {
                                alert('Please upload payment screenshot.');
                                return;
                            }
                        }
                        this.paymentConfirmed = true;
                        this.showPaymentModal = false;
                        this.$nextTick(() => document.getElementById('checkout-form').requestSubmit());
                    },
                    preparePaymentSubmit(event) {
                        if (this.payment === 'sslcommerz') {
                            this.paymentAmount = this.orderTotal;
                            this.paymentConfirmed = true;
                            return;
                        }
                        if (! this.paymentConfirmed) {
                            event.preventDefault();
                            this.openPaymentModal();
                        }
                    },
                    async syncShippingZone() {
                        this.total = this.orderTotal;
                        if (this.payment === 'bank_transfer') {
                            this.updateBankPaymentAmount();
                        } else {
                            this.paymentAmount = this.orderTotal;
                        }
                        try {
                            await fetch(@js(route('cart.shipping-zone')), {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '',
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify({ shipping_zone: this.shippingZone }),
                            });
                        } catch (e) {}
                    },
                }"
                x-init="if (payment === 'bank_transfer') { ensureBankSelected(); updateBankPaymentAmount(); }"
            >
                @csrf
                <input type="hidden" name="address_mode" :value="mode">
                <input type="hidden" name="address_id" :value="mode === 'existing' ? selectedId : ''">
                <input type="hidden" name="payment_amount" :value="paymentAmount">
                <input type="hidden" name="shipping_zone" :value="shippingZone">
                <div class="grid lg:grid-cols-3 gap-10">
                    {{-- Shipping form --}}
                    <div class="lg:col-span-2 space-y-8">
                        <div class="p-6 sm:p-8 bg-surface-elevated rounded-2xl border border-border">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                <div>
                                    <h2 class="text-lg font-semibold text-ink">Customer Details</h2>
                                    <p class="mt-1 text-sm text-ink-muted">Choose a saved shipping address or add another one.</p>
                                </div>
                                <a href="{{ route('account.addresses.index') }}" class="text-sm font-semibold text-brand-600 hover:text-brand-700">Manage addresses</a>
                            </div>

                            @if ($addresses->isNotEmpty())
                                <div class="mt-6 grid sm:grid-cols-2 gap-3">
                                    @foreach ($addresses as $address)
                                        <label class="relative block rounded-xl border border-border p-4 cursor-pointer transition-colors hover:border-brand-300 has-checked:border-brand-600 has-checked:bg-brand-50">
                                            <input
                                                type="radio"
                                                value="{{ $address->id }}"
                                                x-model="selectedId"
                                                @change="useSavedAddress({{ $address->id }})"
                                                class="absolute right-4 top-4 text-brand-600 focus:ring-brand-500"
                                            >
                                            <span class="inline-flex items-center gap-2 pr-8 text-sm font-semibold text-ink">
                                                {{ $address->label ?: $address->typeLabel() }}
                                                <span class="rounded-full bg-white px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-brand-700">{{ $address->typeLabel() }}</span>
                                            </span>
                                            @if ($address->is_default)
                                                <span class="mt-2 inline-flex rounded-full bg-green-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-green-700">Default</span>
                                            @endif
                                            <p class="mt-3 text-sm text-ink-muted">{{ $address->recipient_name }} · {{ $address->phone }}</p>
                                            <p class="mt-1 text-sm text-ink">{{ $address->address_line }}, {{ $address->city }} {{ $address->zip }}</p>
                                        </label>
                                    @endforeach
                                </div>
                            @endif

                            <button
                                type="button"
                                class="mt-5 inline-flex items-center gap-2 rounded-lg border border-border px-4 py-2.5 text-sm font-semibold text-ink hover:border-brand-300 hover:text-brand-700 transition-colors"
                                @click="addNewAddress()"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 6v12m6-6H6"/></svg>
                                Add another address
                            </button>
                        </div>

                        <div class="p-6 sm:p-8 bg-surface-elevated rounded-2xl border border-border">
                            <h2 class="text-lg font-semibold text-ink">Contact Information</h2>
                            <div class="grid sm:grid-cols-2 gap-4 mt-6">
                                <div class="sm:col-span-2">
                                    <label for="name" class="block text-sm font-medium text-ink mb-1.5">Name</label>
                                    <input type="text" name="name" id="name" x-model="details.name" required
                                        class="w-full rounded-lg border border-border bg-surface px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500">
                                    @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label for="email" class="block text-sm font-medium text-ink mb-1.5">Email</label>
                                    <input type="email" name="email" id="email" x-model="details.email" required
                                        class="w-full rounded-lg border border-border bg-surface px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500">
                                    @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-ink mb-1.5">Phone</label>
                                    <input type="tel" name="phone" id="phone" x-model="details.phone" required
                                        class="w-full rounded-lg border border-border bg-surface px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500">
                                    @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="p-6 sm:p-8 bg-surface-elevated rounded-2xl border border-border">
                            <h2 class="text-lg font-semibold text-ink">Shipping Address</h2>
                            <div class="grid gap-4 mt-6">
                                <div x-show="mode === 'new'" x-cloak class="grid sm:grid-cols-2 gap-4 rounded-xl bg-brand-50/50 border border-brand-100 p-4">
                                    <div>
                                        <label for="address_type" class="block text-sm font-medium text-ink mb-1.5">Address type</label>
                                        <select name="address_type" id="address_type" class="w-full rounded-lg border border-border bg-surface px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500">
                                            @foreach ($addressTypes as $value => $label)
                                                <option value="{{ $value }}" @selected(old('address_type', 'home') === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label for="address_label" class="block text-sm font-medium text-ink mb-1.5">Label <span class="text-ink-muted">(optional)</span></label>
                                        <input type="text" name="address_label" id="address_label" value="{{ old('address_label') }}" placeholder="Apartment, Branch, etc." class="w-full rounded-lg border border-border bg-surface px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500">
                                    </div>
                                    <label class="sm:col-span-2 flex items-center gap-2 text-sm text-ink-muted">
                                        <input type="checkbox" name="save_address" value="1" class="rounded border-border text-brand-600 focus:ring-brand-500" @checked(old('save_address', true))>
                                        Save this address for next checkout
                                    </label>
                                    <label class="sm:col-span-2 flex items-center gap-2 text-sm text-ink-muted">
                                        <input type="checkbox" name="make_default" value="1" class="rounded border-border text-brand-600 focus:ring-brand-500" @checked(old('make_default'))>
                                        Make it my default shipping address
                                    </label>
                                </div>

                                <div>
                                    <label for="address" class="block text-sm font-medium text-ink mb-1.5">Street Address</label>
                                    <input type="text" name="address" id="address" x-model="details.address" required
                                        class="w-full rounded-lg border border-border bg-surface px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500">
                                    @error('address')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div class="grid sm:grid-cols-2 gap-4">
                                    <div>
                                        <label for="city" class="block text-sm font-medium text-ink mb-1.5">City</label>
                                        <input type="text" name="city" id="city" x-model="details.city" required
                                            class="w-full rounded-lg border border-border bg-surface px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500">
                                        @error('city')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                    </div>
                                    <div>
                                        <label for="zip" class="block text-sm font-medium text-ink mb-1.5">ZIP Code</label>
                                        <input type="text" name="zip" id="zip" x-model="details.zip" required
                                            class="w-full rounded-lg border border-border bg-surface px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500">
                                        @error('zip')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 sm:p-8 bg-surface-elevated rounded-2xl border border-border">
                            <h2 class="text-lg font-semibold text-ink">Payment Method</h2>
                            <div class="mt-6 space-y-3">
                                <label class="flex items-center gap-4 p-4 rounded-xl border border-border cursor-pointer hover:border-brand-300 transition-colors has-checked:border-brand-600 has-checked:bg-brand-50">
                                    <input type="radio" name="payment" value="cod" x-model="payment" @change="selectPayment('cod')" class="text-brand-600 focus:ring-brand-500">
                                    <div>
                                        <p class="font-medium text-ink">Cash on Delivery</p>
                                        <p class="text-sm text-ink-muted">Confirm payable amount, then pay when your order arrives</p>
                                    </div>
                                </label>
                                <label class="flex items-center gap-4 p-4 rounded-xl border border-border cursor-pointer hover:border-brand-300 transition-colors has-checked:border-brand-600 has-checked:bg-brand-50">
                                    <input type="radio" name="payment" value="bank_transfer" x-model="payment" @change="selectPayment('bank_transfer')" class="text-brand-600 focus:ring-brand-500" @disabled($banks->isEmpty())>
                                    <div>
                                        <p class="font-medium text-ink">Bank / Mobile Payment</p>
                                        <p class="text-sm text-ink-muted">
                                            @if ($banks->isEmpty())
                                                No active bank details available. Please choose Cash on Delivery.
                                            @else
                                                Select bank details, enter amount, and upload payment screenshot
                                            @endif
                                        </p>
                                    </div>
                                </label>
                                @if ($sslCommerzEnabled)
                                    <label class="flex items-center gap-4 p-4 rounded-xl border border-border cursor-pointer hover:border-brand-300 transition-colors has-checked:border-brand-600 has-checked:bg-brand-50">
                                        <input type="radio" name="payment" value="sslcommerz" x-model="payment" @change="selectPayment('sslcommerz')" class="text-brand-600 focus:ring-brand-500">
                                        <div>
                                            <p class="font-medium text-ink">Pay Online (SSLCommerz)</p>
                                            <p class="text-sm text-ink-muted">Pay securely with card, bKash, Nagad, or mobile banking</p>
                                        </div>
                                    </label>
                                @endif
                            </div>
                            @error('payment')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- Order summary --}}
                    <div class="lg:col-span-1">
                        <div class="sticky top-28 p-6 bg-surface-elevated rounded-2xl border border-border">
                            <h2 class="text-lg font-semibold text-ink">Your Order</h2>

                            <ul class="mt-6 space-y-4 max-h-64 overflow-y-auto">
                                @foreach ($items as $item)
                                    <li class="flex gap-3">
                                        <div class="shrink-0 w-14 aspect-3/4 rounded-lg overflow-hidden bg-brand-50 border border-border">
                                            <img src="{{ $item['image'] }}" alt="" class="w-full h-full object-cover object-top">
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-ink line-clamp-2">{{ $item['name'] }}</p>
                                            <p class="text-xs text-ink-muted mt-0.5">Qty {{ $item['quantity'] }} · {{ $item['size'] }}</p>
                                            <p class="text-sm font-medium text-ink mt-1">{{ money($item['price'] * $item['quantity']) }}</p>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>

                            <div class="mt-6 pt-6 border-t border-border">
                                <p class="text-sm font-medium text-ink mb-3">Delivery Area</p>
                                <div class="space-y-2">
                                    @foreach ($shippingZones as $value => $label)
                                        <label class="flex items-center gap-2 text-sm cursor-pointer">
                                            <input
                                                type="radio"
                                                value="{{ $value }}"
                                                x-model="shippingZone"
                                                @change="syncShippingZone()"
                                                class="text-brand-600 focus:ring-brand-500"
                                            >
                                            <span>{{ $label }}</span>
                                            <span class="text-ink-muted">({{ money($value === 'outside_dhaka' ? $shippingFeeOutside : $shippingFeeInside) }})</span>
                                        </label>
                                    @endforeach
                                </div>
                                @error('shipping_zone')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>

                            {{-- Coupon --}}
                            <div class="mt-6 pt-6 border-t border-border">
                                <p class="text-sm font-medium text-ink mb-3">Coupon Code</p>
                                @if ($coupon)
                                    <div class="flex items-center justify-between gap-3 p-3 rounded-xl bg-brand-50 border border-brand-200">
                                        <div>
                                            <p class="text-sm font-semibold text-brand-700">{{ $coupon['code'] }}</p>
                                            <p class="text-xs text-brand-600 mt-0.5">{{ $coupon['label'] }}</p>
                                        </div>
                                        <button type="submit" form="checkout-coupon-remove-form" class="text-xs text-ink-muted hover:text-red-600 transition-colors">Remove</button>
                                    </div>
                                @else
                                    <div class="flex gap-2">
                                        <input
                                            type="text"
                                            name="code"
                                            form="checkout-coupon-apply-form"
                                            value="{{ old('code') }}"
                                            placeholder="e.g. LUXE10"
                                            class="flex-1 rounded-lg border border-border bg-surface px-3 py-2 text-sm uppercase placeholder:normal-case focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500"
                                        >
                                        <button type="submit" form="checkout-coupon-apply-form" class="shrink-0 px-4 py-2 rounded-lg bg-brand-600 text-white text-sm font-medium hover:bg-brand-700 transition-colors">
                                            Apply
                                        </button>
                                    </div>
                                    <p class="mt-2 text-xs text-ink-muted">Try LUXE10, LUXE20, SAVE15, or FASHION</p>
                                @endif
                            </div>

                            <dl class="mt-6 pt-6 border-t border-border space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-ink-muted">Subtotal</dt>
                                    <dd class="font-medium">{{ money($subtotal) }}</dd>
                                </div>
                                @if ($discount > 0)
                                    <div class="flex justify-between text-brand-600">
                                        <dt>Discount ({{ $coupon['code'] }})</dt>
                                        <dd class="font-medium">−{{ money($discount) }}</dd>
                                    </div>
                                @endif
                                <div class="flex justify-between">
                                    <dt class="text-ink-muted">Shipping <span class="text-xs" x-text="'(' + (shippingZone === 'outside_dhaka' ? 'Outside Dhaka' : 'Inside Dhaka') + ')'"></span></dt>
                                    <dd class="font-medium" x-text="shippingAmount === 0 ? 'Free' : '৳' + shippingAmount.toFixed(2)">{{ money_or_free($shipping) }}</dd>
                                </div>
                                <div class="flex justify-between pt-3 border-t border-border text-base">
                                    <dt class="font-semibold text-ink">Total</dt>
                                    <dd class="font-bold text-lg" x-text="'৳' + orderTotal.toFixed(2)">{{ money($total) }}</dd>
                                </div>
                            </dl>

                            <button type="submit" class="w-full mt-6 inline-flex items-center justify-center rounded-lg bg-brand-600 px-6 py-3 text-base font-medium text-white transition-colors hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                                Place Order
                            </button>

                            <a href="{{ route('cart.index') }}" class="block text-center mt-4 text-sm text-brand-600 hover:text-brand-700 transition-colors">
                                Back to Cart
                            </a>
                        </div>
                    </div>
                </div>

                <div x-show="showPaymentModal" x-cloak class="fixed inset-0 z-10000 flex items-center justify-center p-3 sm:p-4">
                    <div class="absolute inset-0 bg-black/50" @click="closePaymentModal()"></div>
                    <div class="relative flex h-auto max-h-[92dvh] sm:max-h-[min(760px,calc(100dvh-2rem))] w-full max-w-xl flex-col rounded-2xl bg-surface-elevated border border-border shadow-xl overflow-hidden my-auto" @click.stop>
                        <div class="px-4 sm:px-5 py-2.5 sm:py-3.5 border-b border-border flex items-center justify-between shrink-0">
                            <div>
                                <h3 class="text-sm sm:text-base font-semibold text-ink" x-show="payment === 'bank_transfer'">Bank Payment Details</h3>
                                <h3 class="text-sm sm:text-base font-semibold text-ink" x-show="payment === 'cod'">Amount</h3>
                            </div>
                            <button type="button" class="p-1 text-ink-muted hover:text-ink" @click="closePaymentModal()">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <div class="checkout-payment-body flex flex-1 min-h-0 flex-col overflow-hidden">
                            <div class="shrink-0 px-4 sm:px-5 pt-3 sm:pt-4">
                                <div class="grid grid-cols-2 gap-2 sm:gap-3">
                                    <label class="flex items-center justify-center gap-2 rounded-xl border border-border px-2.5 sm:px-3 py-2 sm:py-2.5 text-xs sm:text-sm font-semibold cursor-pointer has-checked:border-brand-600 has-checked:bg-brand-50">
                                        <input type="radio" name="popup_payment_choice" value="cod" x-model="payment" @change="selectPayment('cod')" class="text-brand-600 focus:ring-brand-500">
                                        COD
                                    </label>
                                    <label class="flex items-center justify-center gap-2 rounded-xl border border-border px-2.5 sm:px-3 py-2 sm:py-2.5 text-xs sm:text-sm font-semibold has-checked:border-brand-600 has-checked:bg-brand-50 @if ($banks->isEmpty()) opacity-50 @endif">
                                        <input type="radio" name="popup_payment_choice" value="bank_transfer" x-model="payment" @change="selectPayment('bank_transfer')" class="text-brand-600 focus:ring-brand-500" @disabled($banks->isEmpty())>
                                        Bank / Mobile
                                    </label>
                                </div>
                            </div>

                            <div x-show="payment === 'cod'" class="px-4 sm:px-5 py-3 sm:py-4">
                                <label for="payment_amount_visible" class="block text-sm font-medium text-ink mb-1.5">Amount</label>
                                <input
                                    type="number"
                                    id="payment_amount_visible"
                                    x-model.number="paymentAmount"
                                    min="0"
                                    step="0.01"
                                    class="w-full rounded-lg border border-border bg-surface px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500"
                                >
                            </div>

                            <div x-show="payment === 'bank_transfer'" x-cloak class="flex flex-1 min-h-0 flex-col overflow-hidden">
                                <div class="min-h-0 shrink px-4 sm:px-5 pt-2.5 sm:pt-3">
                                    <label class="block text-xs font-medium text-ink mb-1.5">Select Bank / Wallet</label>
                                    <input type="hidden" name="bank_id" :value="selectedBankId">
                                    <div class="checkout-bank-list checkout-bank-scroll space-y-1.5 pr-1">
                                        @foreach ($banks as $bank)
                                            <button
                                                type="button"
                                                class="checkout-bank-item w-full h-9 rounded-lg border px-2 py-1.5 text-left transition-colors overflow-hidden"
                                                :class="String(selectedBankId) === '{{ $bank->id }}' ? 'border-brand-600 bg-brand-50 ring-1 ring-brand-600/20' : 'border-border bg-surface hover:border-brand-300'"
                                                @click="selectedBankId = '{{ $bank->id }}'; updateBankPaymentAmount()"
                                            >
                                                <div class="flex items-center gap-2">
                                                    @if ($bank->imageUrl())
                                                        <img src="{{ $bank->imageUrl() }}" alt="{{ $bank->name }}" class="w-6 h-6 rounded object-contain bg-white border border-border shrink-0">
                                                    @else
                                                        <span class="w-6 h-6 rounded bg-brand-50 text-brand-600 border border-brand-100 shrink-0 flex items-center justify-center">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 21h18M4 10h16M6 10V7l6-4 6 4v3M7 21v-8m5 8v-8m5 8v-8"/></svg>
                                                        </span>
                                                    @endif
                                                    <div class="min-w-0 flex-1">
                                                        <p class="text-xs font-semibold text-ink truncate leading-tight">{{ $bank->name }}</p>
                                                    </div>
                                                    <span class="checkout-bank-select shrink-0 rounded-full px-1.5 py-px text-[7px] font-bold uppercase tracking-wide leading-none" :class="String(selectedBankId) === '{{ $bank->id }}' ? 'bg-brand-600 text-white' : 'bg-surface-elevated text-ink-muted border border-border'">Select</span>
                                                </div>
                                            </button>
                                        @endforeach
                                    </div>
                                    @error('bank_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>

                                <div class="shrink-0 px-4 sm:px-5 pt-2 pb-1 space-y-1.5">
                                    <div class="rounded-lg border border-border bg-surface px-2 py-1.5 text-xs">
                                        <p class="text-[11px] font-semibold text-ink truncate" x-text="selectedBank?.name || 'Select a bank'"></p>

                                        <template x-if="selectedBank && selectedBankHasDetails">
                                            <div class="mt-1 space-y-1">
                                                <template x-if="selectedBank?.account_number">
                                                    <div class="flex items-center justify-between gap-1.5 rounded-md bg-surface-elevated border border-border px-2 py-0.5">
                                                        <div class="min-w-0">
                                                            <p class="text-[8px] font-semibold uppercase tracking-wide text-ink-muted leading-none">Account No.</p>
                                                            <p class="text-xs font-bold text-ink break-all leading-tight tracking-wide" x-text="selectedBank.account_number"></p>
                                                        </div>
                                                        <button
                                                            type="button"
                                                            class="checkout-bank-copy shrink-0 rounded border border-border px-1.5 py-px text-[8px] font-semibold text-brand-700 hover:border-brand-300 hover:bg-brand-50"
                                                            @click="copyAccountNumber()"
                                                        >
                                                            Copy
                                                        </button>
                                                    </div>
                                                </template>
                                                <div class="grid grid-cols-2 gap-1">
                                                    <template x-if="selectedBank?.account_name">
                                                        <div class="rounded-md bg-surface-elevated border border-border px-2 py-0.5">
                                                            <p class="text-[8px] font-semibold uppercase tracking-wide text-ink-muted leading-none">Name</p>
                                                            <p class="text-[10px] font-semibold text-ink break-words leading-tight" x-text="selectedBank.account_name"></p>
                                                        </div>
                                                    </template>
                                                    <template x-if="selectedBank?.branch">
                                                        <div class="rounded-md bg-surface-elevated border border-border px-2 py-0.5">
                                                            <p class="text-[8px] font-semibold uppercase tracking-wide text-ink-muted leading-none">Branch</p>
                                                            <p class="text-[10px] font-semibold text-ink break-words leading-tight" x-text="selectedBank.branch"></p>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>

                                        <template x-if="selectedBank && ! selectedBankHasDetails">
                                            <p class="mt-1 rounded-md border border-amber-200 bg-amber-50 px-2 py-1 text-[9px] text-amber-800 leading-snug">
                                                Account details not available. Contact store before paying.
                                            </p>
                                        </template>

                                        <template x-if="selectedBank?.instructions">
                                            <p class="mt-1 text-[9px] leading-snug text-brand-700 line-clamp-1" x-text="selectedBank.instructions"></p>
                                        </template>
                                    </div>

                                    <div class="rounded-lg border border-brand-200 bg-brand-50 p-2 text-sm">
                                        <div class="flex items-center justify-between gap-2">
                                            <p class="text-[10px] font-semibold uppercase tracking-wide text-brand-700">Payment Breakdown</p>
                                            <p class="text-[10px] text-ink-muted" x-show="bankChargePercent > 0" x-text="bankChargePercent.toFixed(2) + '% charge'"></p>
                                        </div>
                                        <dl class="mt-1.5 grid grid-cols-3 gap-1.5 text-center">
                                            <div class="rounded-lg bg-white/70 px-1.5 py-1.5">
                                                <dt class="text-[9px] text-ink-muted leading-none">Order</dt>
                                                <dd class="mt-1 text-[11px] font-semibold text-ink leading-none" x-text="formatMoney(cartAmount)"></dd>
                                            </div>
                                            <div class="rounded-lg bg-white/70 px-1.5 py-1.5">
                                                <dt class="text-[9px] text-ink-muted leading-none">Charge</dt>
                                                <dd class="mt-1 text-[11px] font-semibold text-amber-700 leading-none" x-text="'+' + formatMoney(bankChargeAmount)"></dd>
                                            </div>
                                            <div class="rounded-lg bg-brand-600 px-1.5 py-1.5 text-white">
                                                <dt class="text-[9px] text-brand-100 leading-none">Total Pay</dt>
                                                <dd class="mt-1 text-[11px] font-bold leading-none" x-text="formatMoney(totalPayable)"></dd>
                                            </div>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="px-4 sm:px-5 pt-2.5 sm:pt-3 pb-2.5 sm:pb-3 border-t border-border shrink-0 bg-surface-elevated safe-area-pb space-y-2.5 sm:space-y-3">
                            <div x-show="payment === 'bank_transfer'" x-cloak>
                                <label for="payment_screenshot" class="block text-xs sm:text-sm font-medium text-ink mb-1">Payment Screenshot</label>
                                <div class="flex items-center gap-2">
                                    <input
                                        type="file"
                                        name="payment_screenshot"
                                        id="payment_screenshot"
                                        accept="image/*"
                                        @change="onScreenshot($event)"
                                        class="min-w-0 flex-1 rounded-lg border border-border bg-surface px-2.5 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm file:mr-2 file:rounded-lg file:border-0 file:bg-brand-50 file:px-2 sm:file:px-2.5 file:py-0.5 sm:file:py-1 file:text-[10px] sm:file:text-xs file:font-medium file:text-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/30"
                                    >
                                    <template x-if="screenshotPreview">
                                        <img :src="screenshotPreview" alt="Payment screenshot preview" class="h-10 w-10 sm:h-12 sm:w-12 shrink-0 rounded-md object-cover border border-border bg-surface">
                                    </template>
                                </div>
                                @error('payment_screenshot')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div class="flex items-stretch gap-2 sm:gap-3">
                                <button
                                    type="button"
                                    class="checkout-modal-cancel shrink-0 inline-flex items-center justify-center rounded-xl border border-border bg-surface px-3 sm:px-4 py-2 sm:py-2.5 text-[11px] sm:text-xs font-medium text-ink-muted transition-colors hover:border-brand-200 hover:bg-brand-50 hover:text-ink"
                                    @click="closePaymentModal()"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="button"
                                    class="checkout-modal-confirm flex-1 inline-flex items-center justify-center gap-1.5 rounded-xl bg-brand-600 px-4 py-2.5 sm:py-3 text-xs sm:text-sm font-semibold text-white shadow-sm transition-colors hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2"
                                    @click="confirmPayment()"
                                >
                                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span class="whitespace-nowrap">Confirm &amp; Place Order</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <form id="checkout-coupon-apply-form" action="{{ route('checkout.coupon.apply') }}" method="POST" class="hidden">
                @csrf
            </form>
            <form id="checkout-coupon-remove-form" action="{{ route('checkout.coupon.remove') }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </section>
@endsection

@section('tracking_boot')
    <x-site.tracking-boot
        page-type="checkout"
        :event-id="$trackingEventId ?? null"
        :begin-checkout="$trackingCart ?? null"
        :user="$trackingUser ?? null"
    />
@endsection

@push('styles')
<style>
    .checkout-payment-body {
        -webkit-overflow-scrolling: touch;
    }

    .checkout-bank-list {
        max-height: calc(2.25rem * 3 + 0.375rem * 2);
        overflow-y: scroll !important;
        overscroll-behavior: contain;
    }

    @media (min-width: 640px) {
        .checkout-bank-list {
            max-height: calc(2.25rem * 3 + 0.375rem * 2);
        }
    }

    .checkout-bank-item {
        flex-shrink: 0;
    }

    .checkout-bank-select {
        min-width: 2.75rem;
        text-align: center;
    }

    .checkout-bank-copy {
        min-width: 2.25rem;
    }

    .checkout-modal-cancel {
        min-width: 4.5rem;
    }

    @media (min-width: 640px) {
        .checkout-modal-cancel {
            min-width: 5.5rem;
        }
    }

    .checkout-modal-confirm {
        min-height: 2.5rem;
    }

    @media (min-width: 640px) {
        .checkout-modal-confirm {
            min-height: 2.75rem;
        }
    }

    .safe-area-pb {
        padding-bottom: max(0.625rem, env(safe-area-inset-bottom));
    }

    .checkout-bank-scroll {
        scrollbar-gutter: stable;
        scrollbar-width: auto;
        scrollbar-color: #0891b2 #e0f2fe;
    }

    .checkout-bank-scroll::-webkit-scrollbar {
        width: 12px;
        display: block;
    }

    .checkout-bank-scroll::-webkit-scrollbar-button {
        display: none;
        height: 0;
        width: 0;
    }

    .checkout-bank-scroll::-webkit-scrollbar-track {
        background: #e0f2fe;
        border-radius: 8px;
        margin: 2px 0;
    }

    .checkout-bank-scroll::-webkit-scrollbar-thumb {
        background: #0891b2;
        border-radius: 8px;
        border: 2px solid #e0f2fe;
        min-height: 32px;
    }

    .checkout-bank-scroll::-webkit-scrollbar-thumb:hover {
        background: #0e7490;
    }
</style>
@endpush
