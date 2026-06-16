@php
    $accountHref = auth()->check() && ! auth()->user()->isAdmin()
        ? route('account.dashboard')
        : route('login');

    $tabs = [
        [
            'label' => 'Home',
            'href' => route('home'),
            'icon' => 'home',
            'active' => request()->routeIs('home'),
        ],
        [
            'label' => 'Shop',
            'href' => route('categories.index'),
            'icon' => 'shop',
            'active' => request()->routeIs('categories.*') && request()->route('slug') !== 'sale',
        ],
        [
            'label' => 'Deals',
            'href' => route('deals'),
            'icon' => 'deals',
            'active' => request()->routeIs('deals') || (request()->routeIs('categories.show') && request()->route('slug') === 'sale'),
        ],
        [
            'label' => 'Account',
            'href' => $accountHref,
            'icon' => 'account',
            'active' => request()->routeIs('account.*') || request()->routeIs('login', 'register'),
        ],
        [
            'label' => 'Cart',
            'href' => route('cart.index'),
            'icon' => 'cart',
            'active' => request()->routeIs('cart.*', 'checkout.*'),
            'badge' => $cartCount ?? 0,
        ],
    ];
@endphp

<nav class="storefront-tab-bar fixed bottom-0 inset-x-0 z-50 lg:hidden bg-surface-elevated/95 backdrop-blur-lg border-t border-border safe-bottom" aria-label="Store navigation">
    <div class="grid grid-cols-5 h-17 max-w-lg mx-auto px-1">
        @foreach ($tabs as $tab)
            <a
                href="{{ $tab['href'] }}"
                class="flex flex-col items-center justify-center gap-0.5 relative {{ $tab['active'] ? 'text-brand-600' : 'text-ink-muted' }}"
            >
                <span class="relative flex items-center justify-center w-9 h-9 rounded-xl transition-colors {{ $tab['active'] ? 'bg-brand-50' : '' }}">
                    @if ($tab['icon'] === 'home')
                        <svg class="w-[22px] h-[22px]" fill="{{ $tab['active'] ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="{{ $tab['active'] ? '0' : '1.75' }}" d="M3 12l2-2m0 0l7-7 7 7m-14 0v10a1 1 0 001 1h3m10-11v10a1 1 0 01-1 1h-3m-6 0v-5a1 1 0 011-1h2a1 1 0 011 1v5"/></svg>
                    @elseif ($tab['icon'] === 'shop')
                        <svg class="w-[22px] h-[22px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 7h16M6 7l1 13h10l1-13M9 7a3 3 0 016 0"/></svg>
                    @elseif ($tab['icon'] === 'deals')
                        <svg class="w-[22px] h-[22px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M7 7h.01M3 11.5V5a2 2 0 012-2h6.5L21 12.5 12.5 21 3 11.5z"/></svg>
                    @elseif ($tab['icon'] === 'account')
                        <svg class="w-[22px] h-[22px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    @else
                        <svg class="w-[22px] h-[22px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2 2h12M9 19.5a.5.5 0 11-1 0 .5.5 0 011 0zm9 0a.5.5 0 11-1 0 .5.5 0 011 0z"/></svg>
                    @endif

                    @if (($tab['badge'] ?? 0) > 0)
                        <span class="absolute -top-1 -right-1 flex min-w-4 h-4 items-center justify-center rounded-full bg-brand-600 px-1 text-[10px] font-bold leading-none text-white">
                            {{ $tab['badge'] }}
                        </span>
                    @endif
                </span>
                <span class="text-[10px] font-semibold leading-none">{{ $tab['label'] }}</span>
            </a>
        @endforeach
    </div>
</nav>
