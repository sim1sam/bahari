@props(['title' => 'My Account', 'back' => null])

<header class="account-mobile-header sticky top-0 z-40 lg:hidden bg-surface-elevated/95 backdrop-blur-md border-b border-border">
    <div class="flex items-center justify-between h-14 px-4">
        <div class="flex items-center gap-3 min-w-0 flex-1">
            @if ($back)
                <a href="{{ $back }}" class="shrink-0 p-1 -ml-1 text-ink-muted hover:text-brand-600" aria-label="Back">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
            @endif
            <h1 class="text-lg font-semibold text-ink truncate">{{ $title }}</h1>
        </div>
        <a href="{{ route('home') }}" class="shrink-0 text-xs font-medium text-brand-600">Store</a>
    </div>
</header>
