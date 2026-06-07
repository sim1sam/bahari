@extends('layouts.ecommerce')

@section('title', 'Shopping Cart')

@section('content')
    <div class="bg-surface-elevated border-b border-border">
        <div class="container-store py-8">
            <h1 class="text-3xl font-bold tracking-tight text-ink">Shopping Cart</h1>
            <p class="mt-1 text-ink-muted">
                @if (count($items) > 0)
                    {{ collect($items)->sum('quantity') }} {{ Str::plural('item', collect($items)->sum('quantity')) }} in your bag
                @else
                    Your bag is empty
                @endif
            </p>
        </div>
    </div>

    <section class="py-10 lg:py-14">
        <div class="container-store">
            @if (empty($items))
                <div class="text-center py-16">
                    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-brand-50 text-brand-300 mb-6">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-semibold text-ink">Your cart is empty</h2>
                    <p class="mt-2 text-ink-muted">Discover our latest dresses and add your favorites.</p>
                    <x-ui.button :href="route('home')" class="mt-6">Continue Shopping</x-ui.button>
                </div>
            @else
                <div class="grid lg:grid-cols-3 gap-10">
                    {{-- Cart items --}}
                    <div class="lg:col-span-2 space-y-4">
                        @foreach ($items as $item)
                            <article class="flex gap-4 sm:gap-6 p-4 sm:p-6 bg-surface-elevated rounded-2xl border border-border">
                                <a href="{{ route('products.show', $item['slug']) }}" class="shrink-0 w-24 sm:w-32 aspect-[3/4] rounded-xl overflow-hidden bg-brand-50 border border-border">
                                    <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover object-top">
                                </a>

                                <div class="flex-1 min-w-0 flex flex-col">
                                    <div class="flex justify-between gap-4">
                                        <div>
                                            <a href="{{ route('products.show', $item['slug']) }}" class="font-medium text-ink hover:text-brand-600 transition-colors line-clamp-2">{{ $item['name'] }}</a>
                                            <p class="mt-1 text-sm text-ink-muted">Color: {{ $item['color'] }}</p>
                                        </div>
                                        <p class="font-semibold text-ink shrink-0">${{ number_format($item['price'] * $item['quantity'], 2) }}</p>
                                    </div>

                                    <div class="mt-auto pt-4 flex flex-wrap items-center justify-between gap-4">
                                        <form
                                            action="{{ route('cart.update', $item['key']) }}"
                                            method="POST"
                                            class="flex flex-wrap items-center gap-4"
                                            x-data="{ qty: {{ $item['quantity'] }} }"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <div class="flex items-center gap-2">
                                                <label class="text-sm text-ink-muted">Size</label>
                                                <select
                                                    name="size"
                                                    onchange="$el.form.requestSubmit()"
                                                    class="rounded-lg border border-border bg-surface py-1.5 px-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/30"
                                                >
                                                    @foreach ($item['sizes'] as $size)
                                                        <option value="{{ $size }}" @selected($item['size'] === $size)>{{ $size }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="flex items-center gap-2">
                                                <label class="text-sm text-ink-muted">Qty</label>
                                                <div class="flex items-center border border-border rounded-lg overflow-hidden">
                                                    <button
                                                        type="button"
                                                        @click="if (qty > 1) { qty--; $refs.qtyInput.value = qty; $el.form.requestSubmit(); }"
                                                        class="w-9 h-9 flex items-center justify-center text-ink-muted hover:bg-surface transition-colors"
                                                    >−</button>
                                                    <span class="w-8 text-center text-sm font-medium" x-text="qty"></span>
                                                    <input type="hidden" name="quantity" x-ref="qtyInput" :value="qty">
                                                    <button
                                                        type="button"
                                                        @click="if (qty < 10) { qty++; $refs.qtyInput.value = qty; $el.form.requestSubmit(); }"
                                                        class="w-9 h-9 flex items-center justify-center text-ink-muted hover:bg-surface transition-colors"
                                                    >+</button>
                                                </div>
                                            </div>
                                        </form>

                                        <form action="{{ route('cart.remove', $item['key']) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-sm text-ink-muted hover:text-red-600 transition-colors">Remove</button>
                                        </form>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    {{-- Order summary --}}
                    <div class="lg:col-span-1">
                        <div class="sticky top-28 p-6 bg-surface-elevated rounded-2xl border border-border">
                            <h2 class="text-lg font-semibold text-ink">Order Summary</h2>

                            <dl class="mt-6 space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-ink-muted">Subtotal</dt>
                                    <dd class="font-medium text-ink">${{ number_format($subtotal, 2) }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-ink-muted">Shipping</dt>
                                    <dd class="font-medium text-ink">
                                        @if ($shipping == 0)
                                            <span class="text-brand-600">Free</span>
                                        @else
                                            ${{ number_format($shipping, 2) }}
                                        @endif
                                    </dd>
                                </div>
                                @if ($subtotal < 50 && $subtotal > 0)
                                    <p class="text-xs text-brand-600 bg-brand-50 rounded-lg px-3 py-2">
                                        Add ${{ number_format(50 - $subtotal, 2) }} more for free shipping!
                                    </p>
                                @endif
                                <div class="flex justify-between pt-3 border-t border-border text-base">
                                    <dt class="font-semibold text-ink">Total</dt>
                                    <dd class="font-bold text-ink text-lg">${{ number_format($total, 2) }}</dd>
                                </div>
                            </dl>

                            <x-ui.button :href="route('checkout.index')" size="lg" class="w-full mt-6">
                                Proceed to Checkout
                            </x-ui.button>

                            <a href="{{ route('home') }}" class="block text-center mt-4 text-sm text-brand-600 hover:text-brand-700 transition-colors">
                                Continue Shopping
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection
