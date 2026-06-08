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
        @if ($order->isCustom())
            <span class="inline-block px-2.5 py-1 rounded-lg text-xs font-semibold bg-violet-100 text-violet-700">Custom Order</span>
        @endif
        @include('pages.account.partials.order-items', ['order' => $order])
        @include('pages.account.partials.order-summary', ['order' => $order])
        @include('pages.account.partials.order-payment', ['order' => $order])
        @if (! $order->isCustom())
            @include('pages.account.partials.order-delivery', ['order' => $order])
        @endif
        <div class="flex gap-2 pt-2">
            <a href="{{ route('account.orders') }}" class="flex-1 py-3 rounded-xl border border-border text-sm font-medium text-center text-ink-muted">Back to Orders</a>
            @if ($order->canBeDeleted())
                <form action="{{ route('account.orders.destroy', $order) }}" method="POST" class="flex-1" onsubmit="return confirm('Delete this order?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full py-3 rounded-xl border border-red-200 text-sm font-semibold text-red-600 bg-red-50">Delete Order</button>
                </form>
            @elseif ($order->isProcessed())
                <p class="flex-1 py-3 text-xs text-center text-ink-muted">Cannot delete after processing</p>
            @endif
        </div>
    </div>

    {{-- Desktop --}}
    <div class="hidden lg:block px-8 pt-8">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <div class="flex items-center gap-3">
                <span class="px-3 py-1.5 rounded-lg text-sm font-medium {{ $order->statusColor() }}">{{ $order->statusLabel() }}</span>
                @if ($order->isCustom())
                    <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-violet-100 text-violet-700">Custom Order</span>
                @endif
                <span class="text-sm text-ink-muted">{{ $order->paymentMethodLabel() }}</span>
                @if ($order->isProcessed())
                    <span class="text-xs text-ink-muted">· Cannot delete after processing</span>
                @endif
            </div>
            @if ($order->canBeDeleted())
                <form action="{{ route('account.orders.destroy', $order) }}" method="POST" onsubmit="return confirm('Delete this order?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 rounded-lg border border-red-200 text-sm font-medium text-red-600 hover:bg-red-50">Delete Order</button>
                </form>
            @endif
        </div>

        <div class="grid grid-cols-3 gap-8">
            <div class="col-span-2 space-y-6">
                <div class="account-panel">
                    <div class="account-panel-header"><h2 class="font-semibold">Order Items</h2></div>
                    <div class="account-panel-body divide-y divide-border !py-0">
                        @foreach ($order->items as $item)
                            <div class="flex gap-4 py-4 first:pt-0 last:pb-0">
                                @if ($item->imageUrl())
                                    <img src="{{ $item->imageUrl() }}" alt="" class="w-20 h-24 rounded-lg object-cover border border-border shrink-0">
                                @endif
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-ink">{{ $item->product_name }}</p>
                                    <p class="text-sm text-ink-muted mt-1">
                                        @if ($item->size || $item->color)
                                            Size: {{ $item->size ?: '—' }} · Color: {{ $item->color ?: '—' }} ·
                                        @endif
                                        Qty: {{ $item->quantity }} · ${{ number_format($item->price, 2) }} each
                                    </p>
                                    @if ($item->product_link)
                                        <a href="{{ $item->product_link }}" target="_blank" rel="noopener" class="text-sm text-brand-600 hover:underline mt-1 inline-block">View product link</a>
                                    @endif
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
                @include('pages.account.partials.order-payment', ['order' => $order])
                @if (! $order->isCustom() && $order->address)
                    <div class="account-panel">
                        <div class="account-panel-header"><h2 class="font-semibold">Delivery Address</h2></div>
                        <div class="account-panel-body text-sm text-ink-muted space-y-1">
                            <p class="font-medium text-ink">{{ $order->customer_name }}</p>
                            <p>{{ $order->address }}</p>
                            <p>{{ $order->city }}, {{ $order->zip }}</p>
                            <p>{{ $order->customer_phone }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
