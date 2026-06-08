@php
    $tabs = [
        [
            'label' => 'Home',
            'route' => 'account.dashboard',
            'icon' => 'home',
            'active' => ['account.dashboard'],
        ],
        [
            'label' => 'Order',
            'route' => 'account.orders',
            'icon' => 'orders',
            'active' => ['account.orders', 'account.orders.show'],
        ],
        [
            'label' => 'Transaction',
            'route' => 'account.transactions',
            'icon' => 'transaction',
            'active' => ['account.transactions'],
        ],
        [
            'label' => 'Profile',
            'route' => 'account.profile',
            'icon' => 'profile',
            'active' => ['account.profile'],
        ],
        [
            'label' => 'Menu',
            'route' => 'account.menu',
            'icon' => 'menu',
            'active' => ['account.menu'],
        ],
    ];
@endphp

<nav class="account-tab-bar fixed bottom-0 inset-x-0 z-50 lg:hidden bg-surface-elevated/95 backdrop-blur-lg border-t border-border safe-bottom" aria-label="Account navigation">
    <div class="grid grid-cols-5 h-[4.25rem] max-w-lg mx-auto px-1">
        @foreach ($tabs as $tab)
            @php
                $active = collect($tab['active'])->contains(fn ($name) => request()->routeIs($name));
            @endphp
            <a
                href="{{ route($tab['route']) }}"
                class="account-tab-item flex flex-col items-center justify-center gap-0.5 relative {{ $active ? 'text-brand-600' : 'text-ink-muted' }}"
            >
                <span class="relative flex items-center justify-center w-9 h-9 rounded-xl transition-colors {{ $active ? 'bg-brand-50' : '' }}">
                    @if ($tab['icon'] === 'home')
                        <svg class="w-[22px] h-[22px]" fill="{{ $active ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="{{ $active ? '0' : '1.75' }}" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    @elseif ($tab['icon'] === 'orders')
                        <svg class="w-[22px] h-[22px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    @elseif ($tab['icon'] === 'transaction')
                        <svg class="w-[22px] h-[22px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    @elseif ($tab['icon'] === 'profile')
                        <svg class="w-[22px] h-[22px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    @else
                        <svg class="w-[22px] h-[22px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    @endif
                </span>
                <span class="text-[10px] font-semibold leading-none">{{ $tab['label'] }}</span>
            </a>
        @endforeach
    </div>
</nav>
