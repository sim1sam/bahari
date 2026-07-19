@props(['title' => 'My Account', 'back' => null])

<header class="account-mobile-header sticky top-0 z-40 lg:hidden bg-surface-elevated/95 backdrop-blur-md border-b border-border">
    <div class="flex items-center justify-between h-14 px-4 gap-3">
        <div class="flex items-center gap-3 min-w-0 flex-1">
            @if ($back)
                <a href="{{ $back }}" class="shrink-0 p-1 -ml-1 text-ink-muted hover:text-brand-600" aria-label="Back">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
            @endif
            <h1 class="text-lg font-semibold text-ink truncate">{{ $title }}</h1>
        </div>
        <a
            href="{{ route('home') }}"
            class="inline-flex items-center gap-1.5 shrink-0 px-3 py-1.5 rounded-lg bg-brand-600 text-white text-xs font-semibold hover:bg-brand-700 transition-colors"
        >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 22V12h6v10"/>
            </svg>
            Store
        </a>
    </div>
</header>
