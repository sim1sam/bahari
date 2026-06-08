@props(['title' => 'My Account'])

<header class="account-desktop-header hidden lg:block sticky top-0 z-30 bg-surface-elevated border-b border-border">
    <div class="flex items-center justify-between px-8 py-5">
        <div>
            @hasSection('breadcrumb')
                <nav class="flex items-center gap-2 text-sm text-ink-muted mb-1">
                    @yield('breadcrumb')
                </nav>
            @endif
            <h1 class="text-2xl font-bold text-ink tracking-tight">{{ $title }}</h1>
            @hasSection('page_subtitle')
                <p class="text-sm text-ink-muted mt-0.5">@yield('page_subtitle')</p>
            @endif
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-border text-sm font-medium text-ink-muted hover:text-brand-600 hover:border-brand-300 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                Store
            </a>
            <a href="{{ route('cart.index') }}" class="relative inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-brand-600 text-white text-sm font-medium hover:bg-brand-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Cart
                @if (($cartCount ?? 0) > 0)
                    <span class="absolute -top-1.5 -right-1.5 min-w-5 h-5 px-1 flex items-center justify-center text-[10px] font-bold bg-white text-brand-700 rounded-full">{{ $cartCount }}</span>
                @endif
            </a>
        </div>
    </div>
</header>
