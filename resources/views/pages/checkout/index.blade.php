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
                    total: {{ (float) $total }},
                    showPaymentModal: false,
                    paymentConfirmed: false,
                    paymentAmount: {{ old('payment_amount', (float) $total) }},
                    selectedBankId: @js((string) old('bank_id', '')),
                    banks: @js($bankPayload),
                    screenshotPreview: null,
                    get selectedBank() {
                        return this.banks.find((bank) => String(bank.id) === String(this.selectedBankId)) || null;
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
                        this.paymentAmount = this.paymentAmount > 0 ? this.paymentAmount : this.total;
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
                        if (! this.paymentConfirmed) {
                            event.preventDefault();
                            this.openPaymentModal();
                        }
                    },
                }"
            >
                @csrf
                <input type="hidden" name="address_mode" :value="mode">
                <input type="hidden" name="address_id" :value="mode === 'existing' ? selectedId : ''">
                <input type="hidden" name="payment_amount" :value="paymentAmount">
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
                                    <input type="radio" name="payment" value="cod" x-model="payment" class="text-brand-600 focus:ring-brand-500">
                                    <div>
                                        <p class="font-medium text-ink">Cash on Delivery</p>
                                        <p class="text-sm text-ink-muted">Confirm payable amount, then pay when your order arrives</p>
                                    </div>
                                </label>
                                <label class="flex items-center gap-4 p-4 rounded-xl border border-border cursor-pointer hover:border-brand-300 transition-colors has-checked:border-brand-600 has-checked:bg-brand-50">
                                    <input type="radio" name="payment" value="bank_transfer" x-model="payment" class="text-brand-600 focus:ring-brand-500" @disabled($banks->isEmpty())>
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

                            {{-- Coupon --}}
                            <div class="mt-6 pt-6 border-t border-border">
                                <p class="text-sm font-medium text-ink mb-3">Coupon Code</p>
                                @if ($coupon)
                                    <div class="flex items-center justify-between gap-3 p-3 rounded-xl bg-brand-50 border border-brand-200">
                                        <div>
                                            <p class="text-sm font-semibold text-brand-700">{{ $coupon['code'] }}</p>
                                            <p class="text-xs text-brand-600 mt-0.5">{{ $coupon['label'] }}</p>
                                        </div>
                                        <form action="{{ route('checkout.coupon.remove') }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-ink-muted hover:text-red-600 transition-colors">Remove</button>
                                        </form>
                                    </div>
                                @else
                                    <form action="{{ route('checkout.coupon.apply') }}" method="POST" class="flex gap-2">
                                        @csrf
                                        <input
                                            type="text"
                                            name="code"
                                            value="{{ old('code') }}"
                                            placeholder="e.g. LUXE10"
                                            class="flex-1 rounded-lg border border-border bg-surface px-3 py-2 text-sm uppercase placeholder:normal-case focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500"
                                        >
                                        <button type="submit" class="shrink-0 px-4 py-2 rounded-lg bg-brand-600 text-white text-sm font-medium hover:bg-brand-700 transition-colors">
                                            Apply
                                        </button>
                                    </form>
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
                                    <dt class="text-ink-muted">Shipping</dt>
                                    <dd class="font-medium">{{ money_or_free($shipping) }}</dd>
                                </div>
                                <div class="flex justify-between pt-3 border-t border-border text-base">
                                    <dt class="font-semibold text-ink">Total</dt>
                                    <dd class="font-bold text-lg">{{ money($total) }}</dd>
                                </div>
                            </dl>

                            <x-ui.button type="submit" size="lg" class="w-full mt-6">
                                Place Order
                            </x-ui.button>

                            <a href="{{ route('cart.index') }}" class="block text-center mt-4 text-sm text-brand-600 hover:text-brand-700 transition-colors">
                                Back to Cart
                            </a>
                        </div>
                    </div>
                </div>

                <div x-show="showPaymentModal" x-cloak class="fixed inset-0 z-10000 flex items-end sm:items-center justify-center p-4">
                    <div class="absolute inset-0 bg-black/50" @click="closePaymentModal()"></div>
                    <div class="relative w-full max-w-lg rounded-2xl bg-surface-elevated border border-border shadow-xl overflow-hidden" @click.stop>
                        <div class="px-5 py-4 border-b border-border flex items-center justify-between">
                            <div>
                                <h3 class="font-semibold text-ink" x-text="payment === 'bank_transfer' ? 'Bank Payment Details' : 'Confirm Cash on Delivery'"></h3>
                                <p class="text-xs text-ink-muted mt-0.5">Order total: {{ money($total) }}</p>
                            </div>
                            <button type="button" class="p-1 text-ink-muted hover:text-ink" @click="closePaymentModal()">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <div class="p-5 space-y-4">
                            <div>
                                <label for="payment_amount_visible" class="block text-sm font-medium text-ink mb-1.5">Amount</label>
                                <input
                                    type="number"
                                    id="payment_amount_visible"
                                    x-model.number="paymentAmount"
                                    min="0"
                                    step="0.01"
                                    class="w-full rounded-lg border border-border bg-surface px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500"
                                >
                                <p class="mt-1 text-xs text-ink-muted">Enter the amount customer will pay.</p>
                            </div>

                            <div x-show="payment === 'bank_transfer'" x-cloak class="space-y-4">
                                <div>
                                    <label for="bank_id" class="block text-sm font-medium text-ink mb-1.5">Select Bank / Wallet</label>
                                    <select
                                        name="bank_id"
                                        id="bank_id"
                                        x-model="selectedBankId"
                                        class="w-full rounded-lg border border-border bg-surface px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500"
                                    >
                                        <option value="">Choose bank...</option>
                                        @foreach ($banks as $bank)
                                            <option value="{{ $bank->id }}">{{ $bank->displayName() }}</option>
                                        @endforeach
                                    </select>
                                    @error('bank_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>

                                <template x-if="selectedBank">
                                    <div class="rounded-xl border border-brand-100 bg-brand-50/60 p-4">
                                        <div class="flex gap-4">
                                            <template x-if="selectedBank.image_url">
                                                <img :src="selectedBank.image_url" alt="" class="w-24 h-24 rounded-lg object-contain bg-white border border-border shrink-0">
                                            </template>
                                            <div class="min-w-0 text-sm">
                                                <p class="font-semibold text-ink" x-text="selectedBank.name"></p>
                                                <p class="mt-1 text-ink-muted" x-show="selectedBank.account_name">Name: <span class="text-ink" x-text="selectedBank.account_name"></span></p>
                                                <p class="mt-1 text-ink-muted" x-show="selectedBank.account_number">Number: <span class="text-ink font-semibold" x-text="selectedBank.account_number"></span></p>
                                                <p class="mt-1 text-ink-muted" x-show="selectedBank.branch">Branch/Type: <span class="text-ink" x-text="selectedBank.branch"></span></p>
                                                <p class="mt-2 text-brand-700" x-show="selectedBank.instructions" x-text="selectedBank.instructions"></p>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <div>
                                    <label for="payment_screenshot" class="block text-sm font-medium text-ink mb-1.5">Payment Screenshot</label>
                                    <input
                                        type="file"
                                        name="payment_screenshot"
                                        id="payment_screenshot"
                                        accept="image/*"
                                        @change="onScreenshot($event)"
                                        class="w-full rounded-lg border border-border bg-surface px-4 py-2.5 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-1 file:text-sm file:font-medium file:text-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/30"
                                    >
                                    @error('payment_screenshot')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                    <template x-if="screenshotPreview">
                                        <img :src="screenshotPreview" alt="Payment screenshot preview" class="mt-3 max-h-44 w-full rounded-lg object-contain border border-border bg-surface">
                                    </template>
                                </div>
                            </div>
                        </div>

                        <div class="px-5 py-4 border-t border-border flex gap-3">
                            <button type="button" class="flex-1 rounded-xl border border-border py-2.5 text-sm font-semibold text-ink-muted hover:text-ink" @click="closePaymentModal()">Cancel</button>
                            <button type="button" class="flex-1 rounded-xl bg-brand-600 py-2.5 text-sm font-semibold text-white hover:bg-brand-700" @click="confirmPayment()">Confirm & Place Order</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection
