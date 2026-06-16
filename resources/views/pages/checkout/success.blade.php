@extends('layouts.ecommerce')

@section('title', 'Order Confirmed')

@section('content')
    @php
        $customer = $order['customer'] ?? [];
        $items = $order['items'] ?? [];
    @endphp

    <section class="py-16 lg:py-24">
        <div class="container-store max-w-2xl text-center">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-brand-100 text-brand-600 mb-8">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>

            <h1 class="text-3xl sm:text-4xl font-bold tracking-tight text-ink">Thank You for Your Order!</h1>
            <p class="mt-4 text-ink-muted text-lg">Your order has been placed successfully. We'll send a confirmation to <strong class="text-ink">{{ $customer['email'] ?? 'your email' }}</strong>.</p>

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
                        {{ $customer['name'] ?? 'Customer' }}<br>
                        {{ $customer['address'] ?? '' }}<br>
                        {{ $customer['city'] ?? '' }}{{ ! empty($customer['zip']) ? ', '.$customer['zip'] : '' }}
                    </p>
                    @if (! empty($customer['phone']))
                        <p class="mt-2 text-sm text-ink-muted">{{ $customer['phone'] }}</p>
                    @endif
                </div>

                <div class="py-6">
                    <h2 class="text-sm font-semibold text-ink uppercase tracking-wide mb-4">Order Items</h2>
                    <ul class="space-y-4">
                        @foreach ($items as $item)
                            @php
                                $itemMeta = collect([
                                    'Qty '.($item['quantity'] ?? 1),
                                    ! empty($item['size']) ? 'Size '.$item['size'] : null,
                                    $item['color'] ?? null,
                                ])->filter()->implode(' · ');
                            @endphp
                            <li class="flex gap-4">
                                <div class="shrink-0 w-16 aspect-3/4 rounded-lg overflow-hidden bg-brand-50 border border-border">
                                    <img src="{{ $item['image'] }}" alt="" class="w-full h-full object-cover object-top">
                                </div>
                                <div class="flex-1 min-w-0">
                                    @if (! empty($item['slug']))
                                        <a href="{{ route('products.show', $item['slug']) }}" class="text-sm font-medium text-ink hover:text-brand-600 transition-colors">{{ $item['name'] ?? 'Product' }}</a>
                                    @else
                                        <p class="text-sm font-medium text-ink">{{ $item['name'] ?? 'Product' }}</p>
                                    @endif
                                    <p class="text-xs text-ink-muted mt-0.5">{{ $itemMeta }}</p>
                                </div>
                                <p class="text-sm font-medium text-ink shrink-0">{{ money(($item['price'] ?? 0) * ($item['quantity'] ?? 1)) }}</p>
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
                            <dt>Discount{{ ! empty($order['coupon']['code']) ? ' ('.$order['coupon']['code'].')' : '' }}</dt>
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
                <x-ui.button :href="route('order.track', ['order' => $order['number']])" variant="secondary" size="lg">Track Order</x-ui.button>
            </div>
        </div>
    </section>
@endsection
