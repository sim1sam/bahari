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
        ensureBankSelected() {
            if (this.payment === 'bank_transfer' && ! this.selectedBankId && this.banks.length > 0) {
                this.selectedBankId = String(this.banks[0].id);
            }
        },
        get selectedBank() {
            return this.banks.find((bank) => String(bank.id) === String(this.selectedBankId)) || null;
        },
        get balanceDue() {
            return Number(this.activeOrder?.balance_due || 0);
        },
        get bankChargePercent() {
            return Number(this.selectedBank?.charge_percent || 0);
        },
        get bankChargeAmount() {
            if (this.payment !== 'bank_transfer' || this.bankChargePercent <= 0 || this.balanceDue <= 0) {
                return 0;
            }

            const rawCharge = Math.round((this.balanceDue * this.bankChargePercent / 100) * 100) / 100;
            return Math.floor(rawCharge / 5) * 5;
        },
        get totalPayable() {
            return Math.round((this.balanceDue + this.bankChargeAmount) * 100) / 100;
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
        onScreenshot(event) {
            const file = event.target.files[0];
            this.screenshotPreview = file ? URL.createObjectURL(file) : null;
        },
    }"
    @keydown.escape.window="closePaymentModal()"
>
    {{ $slot }}

    <div x-show="showPaymentModal" x-cloak class="fixed inset-0 z-10000 flex items-center justify-center p-3 sm:p-4">
        <div class="absolute inset-0 bg-black/50" @click="closePaymentModal()"></div>
        <div class="relative w-full max-w-xl rounded-2xl bg-surface-elevated border border-border shadow-xl overflow-hidden" @click.stop>
            <form :action="activeOrder?.pay_url || '#'" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="px-4 sm:px-5 py-3 border-b border-border flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-ink">Pay Order</h3>
                        <p class="text-xs text-ink-muted" x-text="activeOrder ? activeOrder.number : ''"></p>
                    </div>
                    <button type="button" class="p-1 text-ink-muted hover:text-ink" @click="closePaymentModal()">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-4 sm:p-5 space-y-4">
                    <div class="rounded-xl border border-border bg-surface/40 p-4 space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-ink-muted">Balance Due</span>
                            <span class="font-semibold text-red-600" x-text="formatMoney(balanceDue)"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-ink-muted">Payment Status</span>
                            <span class="font-medium" x-text="activeOrder?.payment_status_label || ''"></span>
                        </div>
                    </div>

                    <template x-if="! hasPaymentOptions">
                        <p class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                            Online payment is not available right now. Please contact support to complete this payment.
                        </p>
                    </template>

                    <div class="grid gap-3" x-show="hasPaymentOptions">
                        @if ($sslCommerzEnabled)
                            <label class="flex items-center gap-3 p-4 rounded-xl border border-border cursor-pointer has-checked:border-brand-600 has-checked:bg-brand-50">
                                <input type="radio" name="payment" value="sslcommerz" x-model="payment" class="text-brand-600 focus:ring-brand-500">
                                <div>
                                    <p class="font-medium text-ink">Pay Online (SSLCommerz)</p>
                                    <p class="text-sm text-ink-muted">Pay securely with card, bKash, Nagad, or mobile banking.</p>
                                </div>
                            </label>
                        @endif

                        @if ($banks->isNotEmpty())
                            <label class="flex items-center gap-3 p-4 rounded-xl border border-border cursor-pointer has-checked:border-brand-600 has-checked:bg-brand-50">
                                <input type="radio" name="payment" value="bank_transfer" x-model="payment" @change="ensureBankSelected()" class="text-brand-600 focus:ring-brand-500">
                                <div>
                                    <p class="font-medium text-ink">Bank / Mobile Payment</p>
                                    <p class="text-sm text-ink-muted">Upload your payment screenshot for admin review.</p>
                                </div>
                            </label>
                        @endif
                    </div>

                    <div x-show="payment === 'bank_transfer'" x-cloak class="space-y-4">
                        <input type="hidden" name="bank_id" :value="selectedBankId">

                        <div class="space-y-2">
                            <p class="text-sm font-medium text-ink">Select Bank / Wallet</p>
                            <div class="space-y-2 max-h-52 overflow-y-auto pr-1">
                                <template x-for="bank in banks" :key="bank.id">
                                    <button
                                        type="button"
                                        class="w-full rounded-xl border px-3 py-3 text-left transition"
                                        :class="String(selectedBankId) === String(bank.id) ? 'border-brand-600 bg-brand-50' : 'border-border bg-surface hover:border-brand-300'"
                                        @click="selectedBankId = String(bank.id)"
                                    >
                                        <div class="flex items-start gap-3">
                                            <template x-if="bank.image_url">
                                                <img :src="bank.image_url" alt="" class="w-10 h-10 rounded-lg object-cover border border-border shrink-0">
                                            </template>
                                            <div class="min-w-0 flex-1">
                                                <p class="font-medium text-ink" x-text="bank.name"></p>
                                                <p class="text-xs text-ink-muted mt-0.5" x-show="bank.account_name" x-text="'A/C Name: ' + bank.account_name"></p>
                                                <p class="text-xs text-ink-muted" x-show="bank.account_number" x-text="'A/C Number: ' + bank.account_number"></p>
                                                <p class="text-xs text-ink-muted" x-show="bank.branch" x-text="bank.branch"></p>
                                            </div>
                                        </div>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <div class="rounded-xl border border-border bg-surface/40 p-4 space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-ink-muted">Order Due</span>
                                <span class="font-medium" x-text="formatMoney(balanceDue)"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-ink-muted">Bank Charge</span>
                                <span class="font-medium" x-text="formatMoney(bankChargeAmount)"></span>
                            </div>
                            <div class="flex justify-between border-t border-border pt-2">
                                <span class="font-semibold text-ink">Total Payable</span>
                                <span class="font-semibold text-brand-700" x-text="formatMoney(totalPayable)"></span>
                            </div>
                        </div>

                        <div>
                            <label for="order-payment-screenshot" class="block text-sm font-medium text-ink mb-1.5">Payment Screenshot</label>
                            <input id="order-payment-screenshot" type="file" name="payment_screenshot" accept="image/*" :required="payment === 'bank_transfer'" @change="onScreenshot($event)" class="block w-full rounded-lg border border-border bg-surface px-3 py-2 text-sm">
                            <template x-if="screenshotPreview">
                                <img :src="screenshotPreview" alt="" class="mt-3 max-h-40 rounded-lg border border-border">
                            </template>
                        </div>
                    </div>
                </div>

                <div class="px-4 sm:px-5 py-4 border-t border-border flex flex-col sm:flex-row gap-3 sm:justify-end">
                    <button type="button" class="px-5 py-2.5 rounded-xl border border-border text-sm font-medium text-ink-muted" @click="closePaymentModal()">Cancel</button>
                    <button
                        type="submit"
                        class="px-5 py-2.5 rounded-xl bg-brand-600 text-white text-sm font-semibold hover:bg-brand-700 disabled:opacity-50"
                        :disabled="! hasPaymentOptions"
                        x-text="payment === 'sslcommerz' ? 'Continue to Payment' : 'Submit Payment'"
                    ></button>
                </div>
            </form>
        </div>
    </div>
</div>
