@extends('layouts.account')

@section('title', 'My Orders')
@section('page_title', 'My Orders')
@section('mobile_title', 'Orders')
@section('page_subtitle', 'Track and manage your order history')

@section('breadcrumb')
    <a href="{{ route('account.dashboard') }}" class="hover:text-brand-600">Dashboard</a>
    <span>/</span>
    <span class="text-ink">Orders</span>
@endsection

@section('content')
    {{-- Mobile --}}
    <div class="lg:hidden px-4 pt-4">
        @include('pages.account.partials.orders-list-mobile', ['orders' => $orders])
        <div class="mt-6">{{ $orders->links() }}</div>
    </div>

    {{-- Desktop --}}
    <div class="hidden lg:block px-8 pt-8">
        @if ($orders->isEmpty())
            <div class="account-panel">
                <div class="account-panel-body text-center py-16">
                    <p class="text-lg font-medium text-ink">No orders found</p>
                    <p class="text-ink-muted mt-1">Your order history will appear here</p>
                    <a href="{{ route('home') }}" class="inline-block mt-5 px-6 py-2.5 rounded-lg bg-brand-600 text-white text-sm font-medium">Start Shopping</a>
                </div>
            </div>
        @else
            <div class="account-panel">
                <div class="account-panel-header">
                    <h2 class="font-semibold text-ink">All Orders</h2>
                    <span class="text-sm text-ink-muted">{{ $orders->count() }} shown</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="account-table w-full">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Date</th>
                                <th>Products</th>
                                <th>Status</th>
                                <th class="text-right">Total</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orders as $order)
                                <tr>
                                    <td>
                                        <p class="font-medium text-ink">{{ $order->number }}</p>
                                        <p class="text-xs text-ink-muted">{{ $order->customer_email }}</p>
                                    </td>
                                    <td class="text-ink-muted whitespace-nowrap">{{ $order->created_at->format('M d, Y g:i A') }}</td>
                                    <td>
                                        <div class="flex gap-1.5">
                                            @foreach ($order->items->take(3) as $item)
                                                @if ($item->imageUrl())
                                                    <img src="{{ $item->imageUrl() }}" alt="" class="w-10 h-12 rounded object-cover border border-border">
                                                @endif
                                            @endforeach
                                            @if ($order->items->count() > 3)
                                                <span class="w-10 h-12 rounded bg-surface flex items-center justify-center text-xs text-ink-muted">+{{ $order->items->count() - 3 }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td><span class="px-2.5 py-1 rounded-md text-xs font-medium {{ $order->statusColor() }}">{{ $order->statusLabel() }}</span></td>
                                    <td class="text-right font-semibold text-brand-700">${{ number_format($order->total, 2) }}</td>
                                    <td class="text-right">
                                        <a href="{{ route('account.orders.show', $order) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg border border-border text-sm font-medium text-ink-muted hover:text-brand-600 hover:border-brand-300">Details</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="account-panel-footer">{{ $orders->links() }}</div>
            </div>
        @endif
    </div>
@endsection
