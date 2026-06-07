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
            <form action="{{ route('checkout.store') }}" method="POST">
                @csrf
                <div class="grid lg:grid-cols-3 gap-10">
                    {{-- Shipping form --}}
                    <div class="lg:col-span-2 space-y-8">
                        <div class="p-6 sm:p-8 bg-surface-elevated rounded-2xl border border-border">
                            <h2 class="text-lg font-semibold text-ink">Contact Information</h2>
                            <div class="grid sm:grid-cols-2 gap-4 mt-6">
                                <div class="sm:col-span-2">
                                    <label for="name" class="block text-sm font-medium text-ink mb-1.5">Name</label>
                                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                        class="w-full rounded-lg border border-border bg-surface px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 @error('name') border-red-400 @enderror">
                                    @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label for="email" class="block text-sm font-medium text-ink mb-1.5">Email</label>
                                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                                        class="w-full rounded-lg border border-border bg-surface px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 @error('email') border-red-400 @enderror">
                                    @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-ink mb-1.5">Phone</label>
                                    <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" required
                                        class="w-full rounded-lg border border-border bg-surface px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 @error('phone') border-red-400 @enderror">
                                    @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="p-6 sm:p-8 bg-surface-elevated rounded-2xl border border-border">
                            <h2 class="text-lg font-semibold text-ink">Shipping Address</h2>
                            <div class="grid gap-4 mt-6">
                                <div>
                                    <label for="address" class="block text-sm font-medium text-ink mb-1.5">Street Address</label>
                                    <input type="text" name="address" id="address" value="{{ old('address') }}" required
                                        class="w-full rounded-lg border border-border bg-surface px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 @error('address') border-red-400 @enderror">
                                    @error('address')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div class="grid sm:grid-cols-2 gap-4">
                                    <div>
                                        <label for="city" class="block text-sm font-medium text-ink mb-1.5">City</label>
                                        <input type="text" name="city" id="city" value="{{ old('city') }}" required
                                            class="w-full rounded-lg border border-border bg-surface px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 @error('city') border-red-400 @enderror">
                                        @error('city')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                    </div>
                                    <div>
                                        <label for="zip" class="block text-sm font-medium text-ink mb-1.5">ZIP Code</label>
                                        <input type="text" name="zip" id="zip" value="{{ old('zip') }}" required
                                            class="w-full rounded-lg border border-border bg-surface px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 @error('zip') border-red-400 @enderror">
                                        @error('zip')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 sm:p-8 bg-surface-elevated rounded-2xl border border-border">
                            <h2 class="text-lg font-semibold text-ink">Payment Method</h2>
                            <div class="mt-6 space-y-3">
                                <label class="flex items-center gap-4 p-4 rounded-xl border border-border cursor-pointer hover:border-brand-300 transition-colors has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50">
                                    <input type="radio" name="payment" value="card" {{ old('payment', 'card') === 'card' ? 'checked' : '' }} class="text-brand-600 focus:ring-brand-500">
                                    <div>
                                        <p class="font-medium text-ink">Credit / Debit Card</p>
                                        <p class="text-sm text-ink-muted">Pay securely with your card</p>
                                    </div>
                                </label>
                                <label class="flex items-center gap-4 p-4 rounded-xl border border-border cursor-pointer hover:border-brand-300 transition-colors has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50">
                                    <input type="radio" name="payment" value="cod" {{ old('payment') === 'cod' ? 'checked' : '' }} class="text-brand-600 focus:ring-brand-500">
                                    <div>
                                        <p class="font-medium text-ink">Cash on Delivery</p>
                                        <p class="text-sm text-ink-muted">Pay when your order arrives</p>
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
                                        <div class="shrink-0 w-14 aspect-[3/4] rounded-lg overflow-hidden bg-brand-50 border border-border">
                                            <img src="{{ $item['image'] }}" alt="" class="w-full h-full object-cover object-top">
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-ink line-clamp-2">{{ $item['name'] }}</p>
                                            <p class="text-xs text-ink-muted mt-0.5">Qty {{ $item['quantity'] }} · {{ $item['size'] }}</p>
                                            <p class="text-sm font-medium text-ink mt-1">${{ number_format($item['price'] * $item['quantity'], 2) }}</p>
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
                                    <dd class="font-medium">${{ number_format($subtotal, 2) }}</dd>
                                </div>
                                @if ($discount > 0)
                                    <div class="flex justify-between text-brand-600">
                                        <dt>Discount ({{ $coupon['code'] }})</dt>
                                        <dd class="font-medium">−${{ number_format($discount, 2) }}</dd>
                                    </div>
                                @endif
                                <div class="flex justify-between">
                                    <dt class="text-ink-muted">Shipping</dt>
                                    <dd class="font-medium">{{ $shipping == 0 ? 'Free' : '$'.number_format($shipping, 2) }}</dd>
                                </div>
                                <div class="flex justify-between pt-3 border-t border-border text-base">
                                    <dt class="font-semibold text-ink">Total</dt>
                                    <dd class="font-bold text-lg">${{ number_format($total, 2) }}</dd>
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
            </form>
        </div>
    </section>
@endsection
