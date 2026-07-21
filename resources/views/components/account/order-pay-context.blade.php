@props([
    'banks',
    'sslCommerzEnabled' => false,
])

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

    $defaultPayment = $sslCommerzEnabled
        ? 'sslcommerz'
        : ($banks->isNotEmpty() ? 'bank_transfer' : '');
@endphp

<div
    x-data="{
        showPaymentModal: false,
        activeOrder: null,
        payment: @js($defaultPayment),
        sslEnabled: @js($sslCommerzEnabled),
        banks: @js($bankPayload),
        selectedBankId: '',
        screenshotPreview: null,
        openPaymentModal(order) {
            this.activeOrder = order;
            this.payment = this.sslEnabled ? 'sslcommerz' : (this.banks.length > 0 ? 'bank_transfer' : '');
            this.screenshotPreview = null;
            this.ensureBankSelected();
            this.showPaymentModal = true;
            this.$nextTick(() => {
                const input = document.getElementById('order-payment-screenshot');
                if (input) {
                    input.value = '';
                }
            });
        },
        closePaymentModal() {
            this.showPaymentModal = false;
            this.screenshotPreview = null;
        },
        selectPayment(method) {
            this.payment = method;
            if (method === 'bank_transfer') {
                this.ensureBankSelected();
            }
        },
        ensureBankSelected() {
            if (this.payment === 'bank_transfer' && ! this.selectedBankId && this.banks.length > 0) {
                this.selectedBankId = String(this.banks[0].id);
            }
        },
        get selectedBank() {
            return this.banks.find((bank) => String(bank.id) === String(this.selectedBankId)) || null;
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
        get balanceDue() {
            return Number(this.activeOrder?.balance_due || 0);
        },
        get cartAmount() {
            return Math.max(0, this.balanceDue);
        },
        get bankChargePercent() {
            return Number(this.selectedBank?.charge_percent || 0);
        },
        get bankChargeAmount() {
            if (this.payment !== 'bank_transfer' || this.bankChargePercent <= 0 || this.cartAmount <= 0) {
                return 0;
            }

            const rawCharge = Math.round((this.cartAmount * this.bankChargePercent / 100) * 100) / 100;
            return Math.floor(rawCharge / 5) * 5;
        },
        get totalPayable() {
            if (this.payment !== 'bank_transfer') {
                return this.cartAmount;
            }

            return Math.round((this.cartAmount + this.bankChargeAmount) * 100) / 100;
        },
        get hasPaymentOptions() {
            return this.sslEnabled || this.banks.length > 0;
        },
        formatMoney(value) {
            return '৳' + Number(value || 0).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
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
        onScreenshot(event) {
            const file = event.target.files[0];
            this.screenshotPreview = file ? URL.createObjectURL(file) : null;
        },
        confirmPayment() {
            if (! this.hasPaymentOptions) {
                alert('Online payment is not available right now.');
                return;
            }

            if (this.payment === 'bank_transfer') {
                if (! this.selectedBankId) {
                    alert('Please select a bank.');
                    return;
                }

                const fileInput = document.getElementById('order-payment-screenshot');
                if (! fileInput?.files?.length) {
                    alert('Please upload payment screenshot.');
                    return;
                }
            }

            this.$refs.orderPayForm.requestSubmit();
        },
    }"
    @keydown.escape.window="closePaymentModal()"
>
    {{ $slot }}

    <div x-show="showPaymentModal" x-cloak class="fixed inset-0 z-10000 flex items-center justify-center p-3 sm:p-4">
        <div class="absolute inset-0 bg-black/50" @click="closePaymentModal()"></div>
        <div class="relative flex h-auto max-h-[94dvh] sm:max-h-[min(880px,calc(100dvh-1.5rem))] w-full max-w-xl flex-col rounded-2xl bg-surface-elevated border border-border shadow-xl overflow-hidden my-auto" @click.stop>
            <form x-ref="orderPayForm" :action="activeOrder?.pay_url || '#'" method="POST" enctype="multipart/form-data" class="flex min-h-0 flex-1 flex-col">
                @csrf
                <input type="hidden" name="payment" :value="payment">
                <input type="hidden" name="bank_id" :value="selectedBankId">

                <div class="px-4 sm:px-5 py-2.5 sm:py-3.5 border-b border-border flex items-center justify-between shrink-0">
                    <div>
                        <h3 class="text-sm sm:text-base font-semibold text-ink" x-show="payment === 'bank_transfer'">Bank Payment Details</h3>
                        <h3 class="text-sm sm:text-base font-semibold text-ink" x-show="payment === 'sslcommerz'">Pay Online</h3>
                        <p class="text-xs text-ink-muted mt-0.5" x-text="activeOrder ? ('Order ' + activeOrder.number + ' · Due ' + formatMoney(balanceDue)) : ''"></p>
                    </div>
                    <button type="button" class="p-1 text-ink-muted hover:text-ink" @click="closePaymentModal()">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="checkout-payment-body flex flex-1 min-h-0 flex-col overflow-hidden">
                    <div class="shrink-0 px-4 sm:px-5 pt-3 sm:pt-4">
                        <div class="grid gap-2 sm:gap-3" :class="sslEnabled && banks.length ? 'grid-cols-2' : 'grid-cols-1'">
                            <template x-if="sslEnabled">
                                <label class="flex items-center justify-center gap-2 rounded-xl border border-border px-2.5 sm:px-3 py-2 sm:py-2.5 text-xs sm:text-sm font-semibold cursor-pointer has-checked:border-brand-600 has-checked:bg-brand-50">
                                    <input type="radio" name="popup_payment_choice" value="sslcommerz" x-model="payment" @change="selectPayment('sslcommerz')" class="text-brand-600 focus:ring-brand-500">
                                    Pay Online
                                </label>
                            </template>
                            <label class="flex items-center justify-center gap-2 rounded-xl border border-border px-2.5 sm:px-3 py-2 sm:py-2.5 text-xs sm:text-sm font-semibold has-checked:border-brand-600 has-checked:bg-brand-50" :class="banks.length ? 'cursor-pointer' : 'opacity-50'">
                                <input type="radio" name="popup_payment_choice" value="bank_transfer" x-model="payment" @change="selectPayment('bank_transfer')" class="text-brand-600 focus:ring-brand-500" :disabled="! banks.length">
                                Bank / Mobile
                            </label>
                        </div>
                    </div>

                    <div x-show="payment === 'sslcommerz'" x-cloak class="px-4 sm:px-5 py-3 sm:py-4">
                        <div class="rounded-lg border border-brand-200 bg-brand-50 p-3 text-sm">
                            <p class="font-semibold text-brand-700">Pay securely online</p>
                            <p class="mt-1 text-xs text-ink-muted">Card, bKash, Nagad, or mobile banking via SSLCommerz.</p>
                            <div class="mt-3 flex items-center justify-between rounded-lg bg-white/70 px-3 py-2">
                                <span class="text-xs text-ink-muted">Amount Due</span>
                                <span class="text-sm font-bold text-ink" x-text="formatMoney(cartAmount)"></span>
                            </div>
                        </div>
                    </div>

                    <div x-show="payment === 'bank_transfer'" x-cloak class="flex flex-1 min-h-0 flex-col overflow-hidden">
                        <div class="min-h-0 shrink px-4 sm:px-5 pt-2.5 sm:pt-3">
                            <label class="block text-xs font-medium text-ink mb-1.5">Select Bank / Wallet</label>
                            <div class="checkout-bank-list checkout-bank-scroll space-y-1.5 pr-1">
                                <template x-for="bank in banks" :key="bank.id">
                                    <button
                                        type="button"
                                        class="checkout-bank-item w-full h-9 rounded-lg border px-2 py-1.5 text-left transition-colors overflow-hidden"
                                        :class="String(selectedBankId) === String(bank.id) ? 'border-brand-600 bg-brand-50 ring-1 ring-brand-600/20' : 'border-border bg-surface hover:border-brand-300'"
                                        @click="selectedBankId = String(bank.id)"
                                    >
                                        <div class="flex items-center gap-2">
                                            <template x-if="bank.image_url">
                                                <img :src="bank.image_url" :alt="bank.name" class="w-6 h-6 rounded object-contain bg-white border border-border shrink-0">
                                            </template>
                                            <template x-if="! bank.image_url">
                                                <span class="w-6 h-6 rounded bg-brand-50 text-brand-600 border border-brand-100 shrink-0 flex items-center justify-center">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 21h18M4 10h16M6 10V7l6-4 6 4v3M7 21v-8m5 8v-8m5 8v-8"/></svg>
                                                </span>
                                            </template>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-xs font-semibold text-ink truncate leading-tight" x-text="bank.name"></p>
                                            </div>
                                            <span
                                                class="checkout-bank-select shrink-0 rounded-full px-1.5 py-px text-[7px] font-bold uppercase tracking-wide leading-none"
                                                :class="String(selectedBankId) === String(bank.id) ? 'bg-brand-600 text-white' : 'bg-surface-elevated text-ink-muted border border-border'"
                                            >Select</span>
                                        </div>
                                    </button>
                                </template>
                            </div>
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

                    <template x-if="! hasPaymentOptions">
                        <div class="px-4 sm:px-5 py-4">
                            <p class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                                Online payment is not available right now. Please contact support to complete this payment.
                            </p>
                        </div>
                    </template>
                </div>

                <div class="px-4 sm:px-5 pt-2.5 sm:pt-3 pb-2.5 sm:pb-3 border-t border-border shrink-0 bg-surface-elevated safe-area-pb space-y-2.5 sm:space-y-3">
                    <div x-show="payment === 'bank_transfer'" x-cloak>
                        <label for="order-payment-screenshot" class="block text-xs sm:text-sm font-medium text-ink mb-1">Payment Screenshot</label>
                        <div class="flex items-center gap-2">
                            <input
                                type="file"
                                name="payment_screenshot"
                                id="order-payment-screenshot"
                                accept="image/*"
                                :required="payment === 'bank_transfer'"
                                @change="onScreenshot($event)"
                                class="min-w-0 flex-1 rounded-lg border border-border bg-surface px-2.5 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm file:mr-2 file:rounded-lg file:border-0 file:bg-brand-50 file:px-2 sm:file:px-2.5 file:py-0.5 sm:file:py-1 file:text-[10px] sm:file:text-xs file:font-medium file:text-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/30"
                            >
                            <template x-if="screenshotPreview">
                                <img :src="screenshotPreview" alt="Payment screenshot preview" class="h-10 w-10 sm:h-12 sm:w-12 shrink-0 rounded-md object-cover border border-border bg-surface">
                            </template>
                        </div>
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
                            class="checkout-modal-confirm flex-1 inline-flex items-center justify-center gap-1.5 rounded-xl bg-brand-600 px-4 py-2.5 sm:py-3 text-xs sm:text-sm font-semibold text-white shadow-sm transition-colors hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 disabled:opacity-50"
                            :disabled="! hasPaymentOptions"
                            @click="confirmPayment()"
                        >
                            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="whitespace-nowrap" x-text="payment === 'sslcommerz' ? 'Continue to Payment' : 'Confirm & Pay'"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@once
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
@endonce
