@php
    $navLinks = [
        ['label' => 'Home', 'href' => route('home')],
        ['label' => 'Dresses', 'href' => route('categories.show', 'dresses')],
        ['label' => 'Tops', 'href' => route('categories.show', 'tops')],
        ['label' => 'Party Wear', 'href' => route('categories.show', 'party-wear')],
        ['label' => 'Sale', 'href' => route('deals')],
    ];
@endphp

<header class="sticky top-0 z-50 bg-surface-elevated/95 backdrop-blur-md border-b border-border" x-data="{ mobileOpen: false, searchOpen: false }">
    <div class="container-store">
        {{-- Main bar --}}
        <div class="flex items-center justify-between gap-4 py-4">
            {{-- Mobile menu toggle --}}
            <button
                type="button"
                class="lg:hidden p-2 -ml-2 text-ink-muted hover:text-ink transition-colors"
                @click="mobileOpen = !mobileOpen"
                aria-label="Toggle menu"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            {{-- Logo --}}
            <a href="{{ route('home') }}" class="flex items-center gap-2 shrink-0">
                <span class="flex items-center justify-center w-9 h-9 rounded-lg bg-brand-600 text-white font-bold text-lg">L</span>
                <span class="font-semibold text-xl tracking-tight hidden sm:block">{{ config('app.name', 'Shop') }}</span>
            </a>

            {{-- Desktop search --}}
            <div class="hidden lg:flex flex-1 max-w-xl mx-8">
                <form action="#" class="relative w-full">
                    <input
                        type="search"
                        placeholder="Search dresses, tops, styles..."
                        class="w-full rounded-full border border-border bg-surface py-2.5 pl-5 pr-12 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 transition-shadow"
                    >
                    <button type="submit" class="absolute right-1.5 top-1/2 -translate-y-1/2 p-2 rounded-full bg-brand-600 text-white hover:bg-brand-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>
                </form>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-1 sm:gap-2">
                <button
                    type="button"
                    class="lg:hidden p-2 text-ink-muted hover:text-ink transition-colors"
                    @click="searchOpen = !searchOpen"
                    aria-label="Search"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>

                <a href="#" class="p-2 text-ink-muted hover:text-ink transition-colors hidden sm:block" aria-label="Wishlist">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </a>

                <a href="#" class="p-2 text-ink-muted hover:text-ink transition-colors hidden sm:block" aria-label="Account">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </a>

                <a href="{{ route('cart.index') }}" class="relative p-2 text-ink-muted hover:text-ink transition-colors" aria-label="Cart">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    @if (($cartCount ?? 0) > 0)
                        <span class="absolute -top-0.5 -right-0.5 flex items-center justify-center min-w-4 h-4 px-1 text-[10px] font-bold bg-brand-600 text-white rounded-full">{{ $cartCount }}</span>
                    @endif
                </a>
            </div>
        </div>

        {{-- Mobile search --}}
        <div x-show="searchOpen" x-cloak class="lg:hidden pb-4">
            <form action="#" class="relative">
                <input
                    type="search"
                    placeholder="Search products..."
                    class="w-full rounded-full border border-border bg-surface py-2.5 pl-5 pr-12 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500"
                >
                <button type="submit" class="absolute right-1.5 top-1/2 -translate-y-1/2 p-2 rounded-full bg-brand-600 text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>
            </form>
        </div>

        {{-- Desktop nav --}}
        <nav class="hidden lg:flex items-center gap-8 pb-4 border-t border-border pt-4 -mt-px">
            @foreach ($navLinks as $link)
                <a
                    href="{{ $link['href'] }}"
                    class="text-sm font-medium text-ink-muted hover:text-brand-600 transition-colors {{ request()->routeIs('home') && $link['label'] === 'Home' ? 'text-brand-600' : '' }}"
                >
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>
    </div>

    {{-- Mobile nav drawer --}}
    <div
        x-show="mobileOpen"
        x-cloak
        class="lg:hidden border-t border-border bg-surface-elevated"
        @click.outside="mobileOpen = false"
    >
        <nav class="container-store py-4 flex flex-col gap-1">
            @foreach ($navLinks as $link)
                <a
                    href="{{ $link['href'] }}"
                    class="px-3 py-2.5 rounded-lg text-sm font-medium text-ink-muted hover:bg-surface hover:text-brand-600 transition-colors"
                    @click="mobileOpen = false"
                >
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>
    </div>
</header>
