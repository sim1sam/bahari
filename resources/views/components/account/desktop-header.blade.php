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
    </div>
</header>
