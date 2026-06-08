@extends('layouts.account')

@section('title', 'Menu')
@section('page_title', 'Menu')
@section('mobile_title', 'Menu')
@section('page_subtitle', 'Quick links and store navigation')

@section('breadcrumb')
    <a href="{{ route('account.dashboard') }}" class="hover:text-brand-600">Dashboard</a>
    <span>/</span>
    <span class="text-ink">Menu</span>
@endsection

@section('content')
    @php
        $sections = [
            'Shopping' => [
                ['label' => 'Browse Store', 'route' => 'home', 'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z'],
                ['label' => 'Categories', 'route' => 'categories.index', 'icon' => 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z'],
                ['label' => 'Deals', 'route' => 'deals', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['label' => 'My Cart', 'route' => 'cart.index', 'icon' => 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z', 'badge' => $cartCount ?? 0],
            ],
            'Account' => [
                ['label' => 'Dashboard', 'route' => 'account.dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                ['label' => 'My Orders', 'route' => 'account.orders', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                ['label' => 'Custom Order', 'route' => 'account.custom-order', 'icon' => 'M12 6v6m0 0v6m0-6h6m-6 0H6'],
                ['label' => 'Transactions', 'route' => 'account.transactions', 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
                ['label' => 'Profile & Settings', 'route' => 'account.profile', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
            ],
        ];
    @endphp

    {{-- Mobile --}}
    <div class="lg:hidden px-4 pt-4 space-y-5 pb-2">
        <div class="rounded-2xl bg-surface-elevated border border-border p-4 flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-brand-600 text-white flex items-center justify-center text-xl font-bold">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div class="min-w-0">
                <p class="font-semibold text-ink truncate">{{ $user->name }}</p>
                <p class="text-sm text-ink-muted truncate">{{ $user->email }}</p>
            </div>
        </div>

        @foreach ($sections as $title => $items)
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-ink-muted mb-2 px-1">{{ $title }}</p>
                <div class="rounded-2xl bg-surface-elevated border border-border overflow-hidden divide-y divide-border">
                    @foreach ($items as $item)
                        <a href="{{ route($item['route']) }}" class="flex items-center gap-3 px-4 py-3.5 active:bg-surface transition-colors">
                            <span class="w-9 h-9 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $item['icon'] }}"/></svg>
                            </span>
                            <span class="flex-1 text-sm font-medium text-ink">{{ $item['label'] }}</span>
                            @if (($item['badge'] ?? 0) > 0)
                                <span class="min-w-5 h-5 px-1.5 flex items-center justify-center text-[10px] font-bold bg-brand-600 text-white rounded-full">{{ $item['badge'] }}</span>
                            @endif
                            <svg class="w-4 h-4 text-ink-muted shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="w-full flex items-center justify-center gap-2 py-3.5 rounded-2xl border border-red-200 text-red-600 text-sm font-semibold bg-red-50">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Logout
            </button>
        </form>
    </div>

    {{-- Desktop --}}
    <div class="hidden lg:grid lg:grid-cols-2 xl:grid-cols-3 lg:gap-6 px-8 pt-8 w-full">
        @foreach ($sections as $title => $items)
            <div class="account-panel">
                <div class="account-panel-header"><h2 class="font-semibold">{{ $title }}</h2></div>
                <div class="account-panel-body !p-0 divide-y divide-border">
                    @foreach ($items as $item)
                        <a href="{{ route($item['route']) }}" class="flex items-center gap-3 px-6 py-4 hover:bg-surface/60 transition-colors">
                            <span class="w-9 h-9 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $item['icon'] }}"/></svg>
                            </span>
                            <span class="flex-1 text-sm font-medium">{{ $item['label'] }}</span>
                            @if (($item['badge'] ?? 0) > 0)
                                <span class="text-xs font-semibold bg-brand-100 text-brand-700 px-2 py-0.5 rounded-full">{{ $item['badge'] }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
@endsection
