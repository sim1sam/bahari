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
        <a
            href="{{ route('home') }}"
            class="inline-flex items-center gap-2 shrink-0 px-4 py-2.5 rounded-xl bg-brand-600 text-white text-sm font-semibold hover:bg-brand-700 transition-colors"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 22V12h6v10"/>
            </svg>
            Store
        </a>
    </div>
</header>
