@extends('layouts.account')

@section('title', 'Order '.$order->number)
@section('page_title', 'Order '.$order->number)
@section('page_subtitle', 'Placed on '.$order->created_at->format('F j, Y'))

@section('back_url', route('account.orders'))

@section('breadcrumb')
    <a href="{{ route('account.dashboard') }}" class="hover:text-brand-600">Dashboard</a>
    <span>/</span>
    <a href="{{ route('account.orders') }}" class="hover:text-brand-600">Orders</a>
    <span>/</span>
    <span class="text-ink">{{ $order->number }}</span>
@endsection

@section('content')
    {{-- Mobile --}}
    <div class="lg:hidden px-4 pt-4 space-y-4">
        <div class="rounded-2xl bg-surface-elevated border border-border p-5">
            <div class="flex justify-between gap-3">
                <div>
                    <p class="text-xs text-ink-muted uppercase">Order</p>
                    <p class="text-lg font-bold mt-0.5">{{ $order->number }}</p>
                </div>
                <span class="px-3 py-1.5 rounded-xl text-sm font-medium h-fit {{ $order->statusColor() }}">{{ $order->statusLabel() }}</span>
            </div>
        </div>
        @include('pages.account.partials.order-items', ['order' => $order])
        @include('pages.account.partials.order-summary', ['order' => $order])
        @include('pages.account.partials.order-delivery', ['order' => $order])
    </div>

    {{-- Desktop --}}
    <div class="hidden lg:block px-8 pt-8">
        <div class="flex items-center gap-3 mb-6">
            <span class="px-3 py-1.5 rounded-lg text-sm font-medium {{ $order->statusColor() }}">{{ $order->statusLabel() }}</span>
            <span class="text-sm text-ink-muted">{{ $order->payment_method === 'cod' ? 'Cash on Delivery' : 'Card Payment' }}</span>
        </div>

        <div class="grid grid-cols-3 gap-8">
            <div class="col-span-2 space-y-6">
                <div class="account-panel">
                    <div class="account-panel-header"><h2 class="font-semibold">Order Items</h2></div>
                    <div class="account-panel-body divide-y divide-border !py-0">
                        @foreach ($order->items as $item)
                            <div class="flex gap-4 py-4 first:pt-0 last:pb-0">
                                @if ($item->image)
                                    <img src="{{ $item->image }}" alt="" class="w-20 h-24 rounded-lg object-cover border border-border shrink-0">
                                @endif
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-ink">{{ $item->product_name }}</p>
                                    <p class="text-sm text-ink-muted mt-1">Size: {{ $item->size }} · Color: {{ $item->color }} · Qty: {{ $item->quantity }}</p>
                                </div>
                                <p class="font-semibold text-brand-700 shrink-0">${{ number_format($item->price * $item->quantity, 2) }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="space-y-6">
                <div class="account-panel">
                    <div class="account-panel-header"><h2 class="font-semibold">Summary</h2></div>
                    <div class="account-panel-body">
                        @include('pages.account.partials.order-summary-rows', ['order' => $order])
                    </div>
                </div>
                <div class="account-panel">
                    <div class="account-panel-header"><h2 class="font-semibold">Delivery Address</h2></div>
                    <div class="account-panel-body text-sm text-ink-muted space-y-1">
                        <p class="font-medium text-ink">{{ $order->customer_name }}</p>
                        <p>{{ $order->address }}</p>
                        <p>{{ $order->city }}, {{ $order->zip }}</p>
                        <p>{{ $order->customer_phone }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
