@props([
    'title',
    'subtitle' => null,
    'actionLabel' => null,
    'actionHref' => '#',
])

<div {{ $attributes->merge(['class' => 'flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-8']) }}>
    <div>
        <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-ink">{{ $title }}</h2>
        @if ($subtitle)
            <p class="mt-1 text-ink-muted">{{ $subtitle }}</p>
        @endif
    </div>
    @if ($actionLabel)
        <a href="{{ $actionHref }}" class="inline-flex items-center gap-1 text-sm font-medium text-brand-600 hover:text-brand-700 transition-colors shrink-0">
            {{ $actionLabel }}
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    @endif
</div>
