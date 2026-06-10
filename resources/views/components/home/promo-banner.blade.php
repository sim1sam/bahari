@props(['banner'])

@if ($banner)
<section class="py-8">
    <div class="container-store">
        <a href="{{ $banner['button_href'] ?? '#' }}" class="group block relative overflow-hidden rounded-3xl bg-brand-950 text-white min-h-[220px]">
            @if (!empty($banner['image']))
                <img
                    src="{{ $banner['image'] }}"
                    alt="{{ $banner['title'] ?? '' }}"
                    class="absolute inset-0 w-full h-full object-cover object-top opacity-50 group-hover:scale-105 transition-transform duration-700"
                    loading="lazy"
                >
            @endif
            <div class="absolute inset-0 bg-gradient-to-r from-brand-950/60 via-brand-900/35 to-transparent"></div>

            <div class="relative px-8 py-12 lg:px-16 lg:py-16 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-8">
                <div class="max-w-lg">
                    @if (!empty($banner['badge']))
                        <x-ui.badge variant="hot" class="mb-4">{{ $banner['badge'] }}</x-ui.badge>
                    @endif
                    <h2 class="text-3xl sm:text-4xl font-bold tracking-tight">{{ $banner['title'] }}</h2>
                    @if (!empty($banner['subtitle']))
                        <p class="mt-3 text-brand-100 leading-relaxed">{{ $banner['subtitle'] }}</p>
                    @endif
                </div>
                @if (!empty($banner['button_text']))
                    <span class="inline-flex items-center justify-center shrink-0 px-6 py-3 rounded-lg bg-white text-brand-800 text-base font-semibold group-hover:bg-brand-50 transition-colors">
                        {{ $banner['button_text'] }}
                        <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </span>
                @endif
            </div>
        </a>
    </div>
</section>
@endif
