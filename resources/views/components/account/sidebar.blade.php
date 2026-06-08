@php
    $links = [
        ['label' => 'Dashboard', 'route' => 'account.dashboard', 'icon' => 'grid'],
        ['label' => 'My Orders', 'route' => 'account.orders', 'icon' => 'orders'],
        ['label' => 'Custom Order', 'route' => 'account.custom-order', 'icon' => 'custom'],
        ['label' => 'Transactions', 'route' => 'account.transactions', 'icon' => 'transaction'],
        ['label' => 'Profile & Settings', 'route' => 'account.profile', 'icon' => 'user'],
    ];
@endphp

<aside class="account-sidebar hidden lg:flex lg:flex-col lg:w-72 lg:shrink-0 lg:h-full bg-surface-elevated border-r border-border">
    <div class="px-6 py-5 border-b border-border">
        <a href="{{ route('home') }}">
            <x-site.logo :show-name="true" />
        </a>
        <p class="text-xs text-ink-muted mt-2">Customer Portal</p>
    </div>

    <div class="px-6 py-5 border-b border-border bg-brand-50/50">
        <div class="flex items-center gap-3">
            <x-account.avatar :user="auth()->user()" size="md" />
            <div class="min-w-0">
                <p class="font-semibold text-ink truncate">{{ auth()->user()->name }}</p>
                <p class="text-xs text-ink-muted truncate">{{ auth()->user()->email }}</p>
            </div>
        </div>
    </div>

    <nav class="flex-1 px-4 py-5 space-y-1 overflow-y-auto">
        <p class="px-4 pb-2 text-[11px] font-semibold uppercase tracking-wider text-ink-muted">Menu</p>
        @foreach ($links as $link)
            <a
                href="{{ route($link['route']) }}"
                class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition-all {{ request()->routeIs($link['route'].'*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/20' : 'text-ink-muted hover:bg-surface hover:text-ink' }}"
            >
                @if ($link['icon'] === 'grid')
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                @elseif ($link['icon'] === 'orders')
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                @elseif ($link['icon'] === 'transaction')
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                @elseif ($link['icon'] === 'custom')
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                @else
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                @endif
                {{ $link['label'] }}
            </a>
        @endforeach
    </nav>

    <div class="px-4 py-5 border-t border-border mt-auto">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="flex items-center gap-3 w-full px-4 py-3 rounded-lg text-sm font-medium text-red-600 hover:bg-red-50 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Sign Out
            </button>
        </form>
    </div>
</aside>
