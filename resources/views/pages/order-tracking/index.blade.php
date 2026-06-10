@extends('layouts.ecommerce')

@section('title', 'Track Your Order')

@section('content')
    <div class="bg-surface-elevated border-b border-border">
        <div class="container-store py-8 lg:py-10">
            <h1 class="text-3xl font-bold tracking-tight text-ink">Track Your Order</h1>
            <p class="mt-2 text-ink-muted">Enter your order number and the email or mobile number used at checkout.</p>
        </div>
    </div>

    <section class="py-10 lg:py-14">
        <div class="container-store max-w-3xl">
            <div class="rounded-2xl border border-border bg-surface-elevated p-6 sm:p-8 shadow-sm">
                <form action="{{ route('order.track.lookup') }}" method="POST" class="space-y-5">
                    @csrf

                    <div>
                        <label for="order_number" class="block text-sm font-medium text-ink mb-1.5">Order Number</label>
                        <input
                            type="text"
                            id="order_number"
                            name="order_number"
                            value="{{ $search['order_number'] ?? '' }}"
                            required
                            autocomplete="off"
                            placeholder="e.g. ORD-20260609-ABC123"
                            class="w-full rounded-xl border border-border bg-surface px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/25 focus:border-brand-500 @error('order_number') border-red-400 @enderror"
                        >
                        @error('order_number')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="contact" class="block text-sm font-medium text-ink mb-1.5">Email or Mobile Number</label>
                        <input
                            type="text"
                            id="contact"
                            name="contact"
                            value="{{ $search['contact'] ?? '' }}"
                            required
                            autocomplete="email tel"
                            placeholder="you@email.com or 0300 1234567"
                            class="w-full rounded-xl border border-border bg-surface px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/25 focus:border-brand-500 @error('contact') border-red-400 @enderror"
                        >
                        @error('contact')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-ui.button type="submit" size="lg" class="w-full sm:w-auto">
                        Track Order
                    </x-ui.button>
                </form>
            </div>

            @if ($order)
                <div class="order-track-result mt-10 space-y-8">
                    <div class="rounded-2xl border border-border bg-surface-elevated p-6 sm:p-8 shadow-sm">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-ink-muted">Order Number</p>
                                <p class="text-2xl font-bold text-brand-600 mt-0.5">{{ $order->number }}</p>
                                <p class="mt-2 text-sm text-ink-muted">
                                    Placed on {{ $order->created_at->format('F j, Y \a\t g:i A') }}
                                </p>
                            </div>
                            <span class="inline-flex w-fit items-center rounded-xl px-4 py-2 text-sm font-semibold {{ $order->statusColor() }}">
                                {{ $order->statusLabel() }}
                            </span>
                        </div>

                        <div class="mt-8 border-t border-border pt-8">
                            <h2 class="text-sm font-semibold uppercase tracking-wide text-ink mb-6">Order Progress</h2>
                            <x-order.tracking-timeline :order="$order" />
                        </div>
                    </div>

                    <div class="grid gap-6 lg:grid-cols-2">
                        <div class="rounded-2xl border border-border bg-surface-elevated p-6 sm:p-8">
                            <h2 class="text-sm font-semibold uppercase tracking-wide text-ink mb-4">Order Items</h2>
                            <ul class="divide-y divide-border">
                                @foreach ($order->items as $item)
                                    <li class="flex gap-4 py-4 first:pt-0 last:pb-0">
                                        @if ($item->imageUrl())
                                            <img src="{{ $item->imageUrl() }}" alt="" class="h-20 w-16 shrink-0 rounded-lg border border-border object-cover">
                                        @endif
                                        <div class="min-w-0 flex-1">
                                            <p class="font-medium text-ink">{{ $item->product_name }}</p>
                                            <p class="mt-1 text-sm text-ink-muted">
                                                Qty {{ $item->quantity }}
                                                @if ($item->size || $item->color)
                                                    · {{ $item->size }} · {{ $item->color }}
                                                @endif
                                            </p>
                                        </div>
                                        <p class="shrink-0 text-sm font-semibold text-ink">{{ money($item->price * $item->quantity) }}</p>
                                    </li>
                                @endforeach
                            </ul>
                            <dl class="mt-4 space-y-2 border-t border-border pt-4 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-ink-muted">Subtotal</dt>
                                    <dd>{{ money($order->subtotal) }}</dd>
                                </div>
                                @if ($order->discount > 0)
                                    <div class="flex justify-between text-brand-600">
                                        <dt>Discount</dt>
                                        <dd>−{{ money($order->discount) }}</dd>
                                    </div>
                                @endif
                                <div class="flex justify-between">
                                    <dt class="text-ink-muted">Shipping</dt>
                                    <dd>{{ money_or_free($order->shipping) }}</dd>
                                </div>
                                <div class="flex justify-between pt-2 text-base font-bold text-ink">
                                    <dt>Total</dt>
                                    <dd>{{ money($order->total) }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div class="rounded-2xl border border-border bg-surface-elevated p-6 sm:p-8">
                            <h2 class="text-sm font-semibold uppercase tracking-wide text-ink mb-4">Delivery Details</h2>
                            <div class="space-y-4 text-sm">
                                <div>
                                    <p class="text-ink-muted">Customer</p>
                                    <p class="mt-1 font-medium text-ink">{{ $order->customer_name }}</p>
                                </div>
                                @if ($order->address)
                                    <div>
                                        <p class="text-ink-muted">Address</p>
                                        <p class="mt-1 text-ink">
                                            {{ $order->address }}<br>
                                            {{ $order->city }}, {{ $order->zip }}
                                        </p>
                                    </div>
                                @endif
                                <div>
                                    <p class="text-ink-muted">Contact</p>
                                    <p class="mt-1 text-ink">{{ $order->customer_email }}</p>
                                    @if ($order->customer_phone)
                                        <p class="text-ink">{{ $order->customer_phone }}</p>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-ink-muted">Payment</p>
                                    <p class="mt-1 text-ink">{{ $order->paymentMethodLabel() }}</p>
                                    <span class="mt-2 inline-flex rounded-lg px-2.5 py-1 text-xs font-medium {{ $order->paymentStatusColor() }}">
                                        {{ $order->paymentStatusLabel() }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if (auth()->check() && $order->user_id === auth()->id())
                        <div class="text-center">
                            <a href="{{ route('account.orders.show', $order) }}" class="text-sm font-medium text-brand-600 hover:underline">
                                View full details in your account →
                            </a>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </section>
@endsection
