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
                                <label class="block text-sm font-medium mb-1">Unit Price ($)</label>
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
                                Line total: <span class="font-semibold text-brand-700" x-text="'$' + lineTotal(item).toFixed(2)"></span>
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
                <span class="text-xl font-bold text-brand-700" x-text="'$' + grandTotal().toFixed(2)"></span>
            </div>
        </div>

        {{-- Notes --}}
        <div class="account-panel mb-5">
            <div class="account-panel-header"><h2 class="font-semibold text-ink">Notes (optional)</h2></div>
            <div class="account-panel-body">
                <textarea name="notes" rows="2" placeholder="Size, color, delivery notes..." class="account-input resize-none">{{ old('notes') }}</textarea>
            </div>
        </div>

        {{-- Payment options --}}
        <div class="account-panel mb-5">
            <div class="account-panel-header"><h2 class="font-semibold text-ink">Payment</h2></div>
            <div class="account-panel-body space-y-4">
                {{-- COD --}}
                <div class="rounded-xl border border-border p-4">
                    <div class="flex items-start gap-3">
                        <input type="radio" id="pay_cod" value="cod" x-model="paymentMode" class="mt-1 text-brand-600">
                        <div class="flex-1">
                            <label for="pay_cod" class="font-medium text-ink cursor-pointer">COD</label>
                            <p class="text-xs text-ink-muted mt-0.5">Cash on Delivery — pay when you receive your order</p>
                        </div>
                    </div>
                </div>

                {{-- Manual bank transfer --}}
                <div class="rounded-xl border border-border p-4">
                    <div class="flex items-start gap-3">
                        <input type="radio" id="pay_manual" value="manual" x-model="paymentMode" class="mt-1 text-brand-600">
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

    {{-- Manual payment modal --}}
    <div
        x-show="showModal"
        x-cloak
        class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center p-4"
        @keydown.escape.window="closeModal()"
    >
        <div class="absolute inset-0 bg-black/50" @click="closeModal()"></div>
        <div
            class="relative w-full max-w-md rounded-2xl bg-surface-elevated border border-border shadow-xl overflow-hidden"
            @click.stop
        >
            <div class="px-5 py-4 border-b border-border flex items-center justify-between">
                <h3 class="font-semibold text-ink">Manual Payment</h3>
                <button type="button" @click="closeModal()" class="text-ink-muted hover:text-ink p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-5 space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Amount ($)</label>
                    <input
                        type="number"
                        name="payment_amount"
                        form="custom-order-form"
                        x-model.number="paymentAmount"
                        min="0"
                        step="0.01"
                        required
                        class="account-input"
                    >
                    <p class="text-xs text-ink-muted mt-1">Editable — adjust if needed</p>
                    @error('payment_amount')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Select Bank</label>
                    <select name="bank_name" form="custom-order-form" x-model="bankName" required class="account-input">
                        <option value="">Choose bank...</option>
                        @foreach ($banks as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('bank_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Payment Screenshot</label>
                    <input
                        type="file"
                        name="payment_screenshot"
                        form="custom-order-form"
                        accept="image/*"
                        @change="onScreenshot($event)"
                        required
                        class="account-input file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-brand-50 file:text-brand-700"
                    >
                    @error('payment_screenshot')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    <template x-if="screenshotPreview">
                        <img :src="screenshotPreview" alt="Preview" class="mt-2 w-full max-h-40 object-contain rounded-lg border border-border">
                    </template>
                </div>
            </div>
            <div class="px-5 py-4 border-t border-border flex gap-3">
                <button type="button" @click="closeModal()" class="flex-1 py-2.5 rounded-xl border border-border text-sm font-medium">Cancel</button>
                <button
                    type="button"
                    @click="confirmManual()"
                    class="flex-1 py-2.5 rounded-xl bg-brand-600 text-white text-sm font-semibold hover:bg-brand-700"
                >Confirm Payment</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function customOrderForm() {
    return {
        items: [{ id: 1, name: '', product_link: '', imagePreview: null, quantity: 1, unit_price: 0 }],
        nextId: 2,
        paymentMode: @json(old('payment_mode', 'cod')),
        showModal: false,
        paymentAmount: {{ old('payment_amount', 0) }},
        bankName: @json(old('bank_name', '')),
        screenshotPreview: null,
        manualReady: false,

        init() {
            if (this.paymentAmount <= 0) {
                this.paymentAmount = this.grandTotal();
            }
            this.$watch('items', () => {
                if (!this.manualReady) {
                    this.paymentAmount = this.grandTotal();
                }
            }, { deep: true });
        },

        addItem() {
            this.items.push({ id: this.nextId++, name: '', product_link: '', imagePreview: null, quantity: 1, unit_price: 0 });
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

        lineTotal(item) {
            return (Number(item.quantity) || 0) * (Number(item.unit_price) || 0);
        },

        grandTotal() {
            return this.items.reduce((sum, item) => sum + this.lineTotal(item), 0);
        },

        openModal() {
            this.paymentMode = 'manual';
            if (this.paymentAmount <= 0) {
                this.paymentAmount = this.grandTotal();
            }
            this.showModal = true;
        },

        closeModal() {
            this.showModal = false;
        },

        onScreenshot(event) {
            const file = event.target.files[0];
            if (file) {
                this.screenshotPreview = URL.createObjectURL(file);
            }
        },

        confirmManual() {
            if (!this.bankName || this.paymentAmount < 0) {
                alert('Please select a bank and enter amount.');
                return;
            }
            const fileInput = document.querySelector('input[name="payment_screenshot"]');
            if (!fileInput?.files?.length) {
                alert('Please upload payment screenshot.');
                return;
            }
            this.manualReady = true;
            this.closeModal();
        },

        prepareSubmit(event) {
            if (this.paymentMode === 'manual' && !this.manualReady) {
                event.preventDefault();
                this.openModal();
            }
        },
    };
}
</script>
@endpush
