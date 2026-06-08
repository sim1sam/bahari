@extends('layouts.ecommerce')

@section('title', 'Order Confirmed')

@section('content')
    <section class="py-16 lg:py-24">
        <div class="container-store max-w-2xl text-center">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-brand-100 text-brand-600 mb-8">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>

            <h1 class="text-3xl sm:text-4xl font-bold tracking-tight text-ink">Thank You for Your Order!</h1>
            <p class="mt-4 text-ink-muted text-lg">Your order has been placed successfully. We'll send a confirmation to <strong class="text-ink">{{ $order['customer']['email'] }}</strong>.</p>

            <div class="mt-10 p-6 sm:p-8 bg-surface-elevated rounded-2xl border border-border text-left">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-border">
                    <div>
                        <p class="text-sm text-ink-muted">Order Number</p>
                        <p class="text-xl font-bold text-brand-600 mt-1">{{ $order['number'] }}</p>
                    </div>
                    <div class="text-sm text-ink-muted">
                        Placed on {{ \Carbon\Carbon::parse($order['placed_at'])->format('M d, Y \a\t g:i A') }}
                    </div>
                </div>

                <div class="py-6 border-b border-border">
                    <h2 class="text-sm font-semibold text-ink uppercase tracking-wide">Shipping To</h2>
                    <p class="mt-2 text-ink">
                        {{ $order['customer']['name'] }}<br>
                        {{ $order['customer']['address'] }}<br>
                        {{ $order['customer']['city'] }}, {{ $order['customer']['zip'] }}
                    </p>
                    <p class="mt-2 text-sm text-ink-muted">{{ $order['customer']['phone'] }}</p>
                </div>

                <div class="py-6">
                    <h2 class="text-sm font-semibold text-ink uppercase tracking-wide mb-4">Order Items</h2>
                    <ul class="space-y-4">
                        @foreach ($order['items'] as $item)
                            <li class="flex gap-4">
                                <div class="shrink-0 w-16 aspect-[3/4] rounded-lg overflow-hidden bg-brand-50 border border-border">
                                    <img src="{{ $item['image'] }}" alt="" class="w-full h-full object-cover object-top">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <a href="{{ route('products.show', $item['slug']) }}" class="text-sm font-medium text-ink hover:text-brand-600 transition-colors">{{ $item['name'] }}</a>
                                    <p class="text-xs text-ink-muted mt-0.5">Qty {{ $item['quantity'] }} · Size {{ $item['size'] }} · {{ $item['color'] }}</p>
                                </div>
                                <p class="text-sm font-medium text-ink shrink-0">{{ money($item['price'] * $item['quantity']) }}</p>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <dl class="pt-6 border-t border-border space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-ink-muted">Subtotal</dt>
                        <dd>{{ money($order['subtotal']) }}</dd>
                    </div>
                    @if (($order['discount'] ?? 0) > 0)
                        <div class="flex justify-between text-brand-600">
                            <dt>Discount@if (! empty($order['coupon']['code'])) ({{ $order['coupon']['code'] }})@endif</dt>
                            <dd>−{{ money($order['discount']) }}</dd>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <dt class="text-ink-muted">Shipping</dt>
                        <dd>{{ money_or_free($order['shipping']) }}</dd>
                    </div>
                    <div class="flex justify-between pt-2 text-base font-bold text-ink">
                        <dt>Total Paid</dt>
                        <dd>{{ money($order['total']) }}</dd>
                    </div>
                </dl>
            </div>

            <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                <x-ui.button :href="route('home')" size="lg">Continue Shopping</x-ui.button>
                <x-ui.button :href="route('products.show', $order['items'][array_key_first($order['items'])]['slug'])" variant="secondary" size="lg">View Product</x-ui.button>
            </div>
        </div>
    </section>
@endsection
