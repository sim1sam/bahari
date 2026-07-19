@extends('layouts.account')

@section('title', 'Custom Order')
@section('page_title', 'Custom Order')
@section('mobile_title', 'Custom Order')
@section('page_subtitle', 'Add products manually and pay with COD or bank transfer')

@section('breadcrumb')
    <a href="{{ route('account.dashboard') }}" class="hover:text-brand-600">Dashboard</a>
    <span>/</span>
    <span class="text-ink">Custom Order</span>
@endsection

@section('content')
@php
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

<div
    class="px-4 lg:px-8 pt-4 lg:pt-8 w-full pb-6"
    x-data="customOrderForm()"
>
    <form
        id="custom-order-form"
        action="{{ route('account.custom-order.store') }}"
        method="POST"
        enctype="multipart/form-data"
        @submit="prepareSubmit"
    >
        @csrf
        <input type="hidden" name="payment_mode" x-model="paymentMode">
        <input type="hidden" name="bank_id" :value="selectedBankId">
        <input type="hidden" name="payment_amount" :value="paymentAmount">

        {{-- Items --}}
        <div class="account-panel mb-5">
            <div class="account-panel-header">
                <h2 class="font-semibold text-ink">Products</h2>
                <button type="button" @click="addItem()" class="text-sm font-medium text-brand-600 hover:text-brand-700">+ Add Item</button>
            </div>
            <div class="account-panel-body space-y-4">
                <template x-for="(item, index) in items" :key="item.id">
                    <div class="rounded-xl border border-border p-4 bg-surface/40 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold uppercase tracking-wider text-ink-muted" x-text="'Item ' + (index + 1)"></span>
                            <button
                                type="button"
                                x-show="items.length > 1"
                                @click="removeItem(index)"
                                class="text-xs text-red-600 font-medium"
                            >Remove</button>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium mb-1">Product Name</label>
                                <input
                                    type="text"
                                    :name="'items[' + index + '][name]'"
                                    x-model="item.name"
                                    required
                                    placeholder="e.g. Silk Evening Dress"
                                    class="account-input"
                                >
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium mb-1">Product Link</label>
                                <input
                                    type="url"
                                    :name="'items[' + index + '][product_link]'"
                                    x-model="item.product_link"
                                    placeholder="https://store.com/product-page"
                                    class="account-input"
                                >
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium mb-1">Product Image</label>
                                <input
                                    type="file"
                                    :name="'items[' + index + '][image_file]'"
                                    accept="image/*"
                                    @change="onItemImage($event, index)"
                                    class="account-input file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-brand-50 file:text-brand-700"
                                >
                                <p class="text-xs text-ink-muted mt-1">Upload product photo (JPG, PNG, WebP — max 5MB)</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Size</label>
                                <input
                                    type="text"
                                    :name="'items[' + index + '][size]'"
                                    x-model="item.size"
                                    placeholder="e.g. M, L, XL, 38"
                                    class="account-input"
                                >
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Qty</label>
                                <input
                                    type="number"
                                    :name="'items[' + index + '][quantity]'"
                                    x-model.number="item.quantity"
                                    min="1"
                                    required
                                    class="account-input"
                                >
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Unit Price ({{ $currencyCode }})</label>
                                <input
                                    type="number"
                                    :name="'items[' + index + '][unit_price]'"
                                    x-model.number="item.unit_price"
                                    min="0"
                                    step="0.01"
                                    required
                                    class="account-input"
                                >
                            </div>
                        </div>
                        <div class="flex items-center gap-3 pt-1">
                            <template x-if="item.imagePreview">
                                <img :src="item.imagePreview" alt="" class="w-14 h-14 rounded-lg object-cover border border-border shrink-0">
                            </template>
                            <p class="text-sm text-ink-muted ml-auto">
                                Line total: <span class="font-semibold text-brand-700" x-text="formatMoney(lineTotal(item))"></span>
                            </p>
                        </div>
                    </div>
                </template>

                @if ($errors->has('items') || $errors->has('items.0.name'))
                    <p class="text-sm text-red-600">{{ $errors->first('items') ?: $errors->first('items.0.name') }}</p>
                @endif
            </div>
            <div class="account-panel-footer flex items-center justify-between">
                <span class="text-sm text-ink-muted">Calculated total</span>
                <span class="text-xl font-bold text-brand-700" x-text="formatMoney(grandTotal())"></span>
            </div>
        </div>

        {{-- Notes --}}
        <div class="account-panel mb-5">
            <div class="account-panel-header"><h2 class="font-semibold text-ink">Notes (optional)</h2></div>
            <div class="account-panel-body">
                <textarea name="notes" rows="2" placeholder="Color, delivery notes..." class="account-input resize-none">{{ old('notes') }}</textarea>
            </div>
        </div>

        {{-- Payment options --}}
        <div class="account-panel mb-5">
            <div class="account-panel-header"><h2 class="font-semibold text-ink">Payment</h2></div>
            <div class="account-panel-body space-y-4">
                <div class="rounded-xl border border-border p-4">
                    <div class="flex items-start gap-3">
                        <input type="radio" id="pay_cod" value="cod" x-model="paymentMode" class="mt-1 text-brand-600">
                        <div class="flex-1">
                            <label for="pay_cod" class="font-medium text-ink cursor-pointer">COD</label>
                            <p class="text-xs text-ink-muted mt-0.5">Cash on Delivery — pay when you receive your order</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-border p-4 @if ($banks->isEmpty()) opacity-50 @endif">
                    <div class="flex items-start gap-3">
                        <input type="radio" id="pay_manual" value="manual" x-model="paymentMode" class="mt-1 text-brand-600" @disabled($banks->isEmpty())>
                        <div class="flex-1">
                            <label for="pay_manual" class="font-medium text-ink cursor-pointer">Manual Payment</label>
                            <p class="text-xs text-ink-muted mt-0.5">Pay via bank transfer and upload screenshot</p>
                            <button
                                type="button"
                                x-show="paymentMode === 'manual'"
                                x-cloak
                                @click="openModal()"
                                class="mt-3 w-full sm:w-auto px-5 py-2.5 rounded-xl bg-brand-600 text-white text-sm font-semibold hover:bg-brand-700"
                            >
                                Open Payment Details
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-3">
            <button
                type="submit"
                class="flex-1 px-6 py-3 rounded-xl bg-brand-600 text-white text-sm font-semibold hover:bg-brand-700 disabled:opacity-50"
                :disabled="paymentMode === 'manual' && !manualReady"
            >
                Place Custom Order
            </button>
            <a href="{{ route('account.menu') }}" class="px-6 py-3 rounded-xl border border-border text-sm font-medium text-center text-ink-muted hover:text-ink">Cancel</a>
        </div>
    </form>

    {{-- Payment details modal (same as checkout) --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-10000 flex items-center justify-center p-3 sm:p-4" @keydown.escape.window="closeModal()">
        <div class="absolute inset-0 bg-black/50" @click="closeModal()"></div>
        <div class="relative flex h-auto max-h-[92dvh] sm:max-h-[min(760px,calc(100dvh-2rem))] w-full max-w-xl flex-col rounded-2xl bg-surface-elevated border border-border shadow-xl overflow-hidden my-auto" @click.stop>
            <div class="px-4 sm:px-5 py-2.5 sm:py-3.5 border-b border-border flex items-center justify-between shrink-0">
                <div>
                    <h3 class="text-sm sm:text-base font-semibold text-ink">Bank Payment Details</h3>
                </div>
                <button type="button" class="p-1 text-ink-muted hover:text-ink" @click="closeModal()">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="checkout-payment-body flex flex-1 min-h-0 flex-col overflow-hidden">
                <div class="min-h-0 shrink px-4 sm:px-5 pt-2.5 sm:pt-3">
                    <label class="block text-xs font-medium text-ink mb-1.5">Select Bank / Wallet</label>
                    <div class="checkout-bank-list checkout-bank-scroll space-y-1.5 pr-1">
                        @forelse ($banks as $bank)
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
                        @empty
                            <p class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">No banks available. Please contact the store.</p>
                        @endforelse
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

            <div class="px-4 sm:px-5 pt-2.5 sm:pt-3 pb-2.5 sm:pb-3 border-t border-border shrink-0 bg-surface-elevated safe-area-pb space-y-2.5 sm:space-y-3">
                <div>
                    <label for="payment_screenshot" class="block text-xs sm:text-sm font-medium text-ink mb-1">Payment Screenshot</label>
                    <div class="flex items-center gap-2">
                        <input
                            type="file"
                            name="payment_screenshot"
                            id="payment_screenshot"
                            form="custom-order-form"
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
                        @click="closeModal()"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="checkout-modal-confirm flex-1 inline-flex items-center justify-center gap-1.5 rounded-xl bg-brand-600 px-4 py-2.5 sm:py-3 text-xs sm:text-sm font-semibold text-white shadow-sm transition-colors hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2"
                        @click="confirmManual()"
                    >
                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="whitespace-nowrap">Confirm Payment</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
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

@push('scripts')
<script>
function customOrderForm() {
    return {
        currencySymbol: @json($currencySymbol),
        items: [{ id: 1, name: '', product_link: '', size: '', imagePreview: null, quantity: 1, unit_price: 0 }],
        nextId: 2,
        paymentMode: @json(old('payment_mode', 'cod')),
        showModal: false,
        paymentAmount: {{ old('payment_amount', 0) }},
        selectedBankId: @json((string) old('bank_id', '')),
        banks: @json($bankPayload),
        screenshotPreview: null,
        manualReady: false,

        get selectedBank() {
            return this.banks.find((bank) => String(bank.id) === String(this.selectedBankId)) || null;
        },
        get bankChargePercent() {
            return Number(this.selectedBank?.charge_percent || 0);
        },
        get cartAmount() {
            return Math.max(0, Number(this.grandTotal() || 0));
        },
        get bankChargeAmount() {
            if (this.bankChargePercent <= 0) {
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
            return Math.round((this.cartAmount + this.bankChargeAmount) * 100) / 100;
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

        init() {
            this.$watch('items', () => {
                if (this.paymentMode === 'manual') {
                    this.updateBankPaymentAmount();
                }
            }, { deep: true });

            if (this.paymentMode === 'manual') {
                this.ensureBankSelected();
                this.updateBankPaymentAmount();
            }
        },

        addItem() {
            this.items.push({ id: this.nextId++, name: '', product_link: '', size: '', imagePreview: null, quantity: 1, unit_price: 0 });
        },

        onItemImage(event, index) {
            const file = event.target.files[0];
            if (file) {
                this.items[index].imagePreview = URL.createObjectURL(file);
            }
        },

        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            }
        },

        formatMoney(amount) {
            return this.currencySymbol + Number(amount || 0).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
        },

        lineTotal(item) {
            return (Number(item.quantity) || 0) * (Number(item.unit_price) || 0);
        },

        grandTotal() {
            return this.items.reduce((sum, item) => sum + this.lineTotal(item), 0);
        },

        ensureBankSelected() {
            if (! this.selectedBankId && this.banks.length > 0) {
                this.selectedBankId = String(this.banks[0].id);
            }
        },

        updateBankPaymentAmount() {
            this.paymentAmount = this.totalPayable;
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

        openModal() {
            this.paymentMode = 'manual';
            this.ensureBankSelected();
            this.updateBankPaymentAmount();
            this.showModal = true;
        },

        closeModal() {
            this.showModal = false;
        },

        onScreenshot(event) {
            const file = event.target.files[0];
            this.screenshotPreview = file ? URL.createObjectURL(file) : null;
        },

        confirmManual() {
            this.updateBankPaymentAmount();

            if (Number(this.paymentAmount) <= 0) {
                alert('Please enter payment amount.');
                return;
            }
            if (! this.selectedBankId) {
                alert('Please select a bank.');
                return;
            }
            const fileInput = document.getElementById('payment_screenshot');
            if (! fileInput?.files?.length) {
                alert('Please upload payment screenshot.');
                return;
            }

            this.manualReady = true;
            this.closeModal();
        },

        prepareSubmit(event) {
            if (this.paymentMode === 'manual' && ! this.manualReady) {
                event.preventDefault();
                this.openModal();
            }
        },
    };
}
</script>
@endpush
