@extends('layouts.account')

@section('title', 'My Account')
@section('page_title', 'Dashboard')
@section('mobile_title', 'Hello, '.explode(' ', auth()->user()->name)[0])
@section('page_subtitle', 'Welcome back, '.auth()->user()->name)

@section('content')
    {{-- ========== MOBILE APP LAYOUT ========== --}}
    <div class="lg:hidden px-4 pt-4 space-y-5">
        <div class="rounded-2xl bg-gradient-to-br from-brand-600 to-brand-800 p-5 text-white shadow-lg shadow-brand-600/20">
            <div class="flex items-center gap-4">
                @if ($user->avatarUrl())
                    <img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}" class="w-14 h-14 rounded-2xl object-cover border-2 border-white/30 shadow-sm shrink-0">
                @else
                    <div class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center text-2xl font-bold shrink-0">
                        {{ $user->initials() }}
                    </div>
                @endif
                <div class="min-w-0 flex-1">
                    <p class="text-brand-100 text-sm">Welcome back</p>
                    <p class="text-xl font-bold truncate">{{ $user->name }}</p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3 mt-5">
                <div class="rounded-xl bg-white/15 px-4 py-3">
                    <p class="text-2xl font-bold">{{ $ordersCount }}</p>
                    <p class="text-xs text-brand-100">Orders</p>
                </div>
                <div class="rounded-xl bg-white/15 px-4 py-3">
                    <p class="text-2xl font-bold">${{ number_format($totalSpent, 0) }}</p>
                    <p class="text-xs text-brand-100">Spent</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-4 gap-2">
            @foreach ([['Order', 'account.orders', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'], ['Custom', 'account.custom-order', 'M12 6v6m0 0v6m0-6h6m-6 0H6'], ['Transaction', 'account.transactions', 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'], ['Menu', 'account.menu', 'M4 6h16M4 12h16M4 18h16']] as [$label, $route, $path])
                <a href="{{ route($route) }}" class="flex flex-col items-center gap-1.5 p-3 rounded-2xl bg-surface-elevated border border-border active:scale-95 transition-transform">
                    <span class="w-10 h-10 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $path }}"/></svg>
                    </span>
                    <span class="text-[10px] font-semibold text-ink">{{ $label }}</span>
                </a>
            @endforeach
        </div>

        <div>
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-base font-semibold">Recent Orders</h2>
                @if ($orders->isNotEmpty())
                    <a href="{{ route('account.orders') }}" class="text-sm text-brand-600 font-medium">See all</a>
                @endif
            </div>
            @include('pages.account.partials.orders-list-mobile', ['orders' => $orders])
        </div>

        <form action="{{ route('logout') }}" method="POST" class="pb-2">
            @csrf
            <button type="submit" class="w-full py-3 rounded-xl border border-red-200 text-red-600 text-sm font-semibold bg-red-50">Logout</button>
        </form>
    </div>

    {{-- ========== DESKTOP DASHBOARD LAYOUT ========== --}}
    <div class="hidden lg:block px-8 pt-8 space-y-8">
        <div class="grid grid-cols-4 gap-5">
            <div class="account-stat-card">
                <p class="text-sm text-ink-muted">Total Orders</p>
                <p class="text-3xl font-bold text-ink mt-1">{{ $ordersCount }}</p>
            </div>
            <div class="account-stat-card">
                <p class="text-sm text-ink-muted">Total Spent</p>
                <p class="text-3xl font-bold text-brand-700 mt-1">${{ number_format($totalSpent, 2) }}</p>
            </div>
            <div class="account-stat-card">
                <p class="text-sm text-ink-muted">Cart Items</p>
                <p class="text-3xl font-bold text-ink mt-1">{{ $cartCount ?? 0 }}</p>
            </div>
            <div class="account-stat-card">
                <p class="text-sm text-ink-muted">Member Since</p>
                <p class="text-3xl font-bold text-ink mt-1">{{ $user->created_at->format('Y') }}</p>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-8">
            <div class="col-span-2">
                <div class="account-panel">
                    <div class="account-panel-header">
                        <h2 class="font-semibold text-ink">Recent Orders</h2>
                        <a href="{{ route('account.orders') }}" class="text-sm text-brand-600 hover:text-brand-700 font-medium">View all →</a>
                    </div>
                    @if ($orders->isEmpty())
                        <div class="account-panel-body text-center py-12">
                            <p class="text-ink-muted">No orders yet.</p>
                            <a href="{{ route('home') }}" class="inline-block mt-4 px-5 py-2 rounded-lg bg-brand-600 text-white text-sm font-medium">Browse Products</a>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="account-table w-full">
                                <thead>
                                    <tr>
                                        <th>Order</th>
                                        <th>Date</th>
                                        <th>Items</th>
                                        <th>Status</th>
                                        <th class="text-right">Total</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($orders as $order)
                                        <tr>
                                            <td class="font-medium text-ink">{{ $order->number }}</td>
                                            <td class="text-ink-muted">{{ $order->created_at->format('M d, Y') }}</td>
                                            <td class="text-ink-muted">{{ $order->items->sum('quantity') }}</td>
                                            <td><span class="px-2.5 py-1 rounded-md text-xs font-medium {{ $order->statusColor() }}">{{ $order->statusLabel() }}</span></td>
                                            <td class="text-right font-semibold text-brand-700">${{ number_format($order->total, 2) }}</td>
                                            <td class="text-right"><a href="{{ route('account.orders.show', $order) }}" class="text-sm text-brand-600 hover:underline">View</a></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <div class="space-y-5">
                <div class="account-panel">
                    <div class="account-panel-header"><h2 class="font-semibold text-ink">Quick Actions</h2></div>
                    <div class="account-panel-body space-y-2">
                        <a href="{{ route('account.orders') }}" class="account-quick-link">View all orders</a>
                        <a href="{{ route('account.profile') }}" class="account-quick-link">Edit profile</a>
                        <a href="{{ route('categories.index') }}" class="account-quick-link">Browse categories</a>
                        <a href="{{ route('cart.index') }}" class="account-quick-link">Go to cart</a>
                    </div>
                </div>
                <div class="account-panel">
                    <div class="account-panel-header"><h2 class="font-semibold text-ink">Account Info</h2></div>
                    <div class="account-panel-body text-sm space-y-2 text-ink-muted">
                        <p><span class="font-medium text-ink">Name:</span> {{ $user->name }}</p>
                        <p><span class="font-medium text-ink">Email:</span> {{ $user->email }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
