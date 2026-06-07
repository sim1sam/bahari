@props(['slides' => []])

@if (count($slides) > 0)
<section
    class="hero-slider relative w-full h-[85vh] min-h-[560px] max-h-[820px] overflow-hidden bg-brand-950"
    x-data="{
        current: 0,
        total: {{ count($slides) }},
        timer: null,
        next() { this.current = (this.current + 1) % this.total },
        prev() { this.current = (this.current - 1 + this.total) % this.total },
        goTo(i) { this.current = i },
        startAutoplay() {
            clearInterval(this.timer);
            this.timer = setInterval(() => this.next(), 6000);
        }
    }"
    x-init="startAutoplay()"
    @mouseenter="clearInterval(timer)"
    @mouseleave="startAutoplay()"
>
    {{-- Full-screen background slides --}}
    @foreach ($slides as $index => $slide)
        <div
            class="absolute inset-0 z-0 bg-brand-900 bg-cover bg-center bg-no-repeat"
            style="background-image: url('{{ $slide['image'] }}')"
            x-show="current === {{ $index }}"
            x-transition:enter="transition ease-out duration-700"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-500"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            role="img"
            aria-label="{{ $slide['title'] ?? 'Hero slide' }}"
        ></div>
    @endforeach

    {{-- Cyan overlay — fixed layer above slides --}}
    <div class="hero-slider-overlay absolute inset-0 z-[1] pointer-events-none"></div>
    <div class="hero-slider-overlay-bottom absolute inset-0 z-[1] pointer-events-none"></div>

    {{-- Left-side text --}}
    <div class="relative z-10 h-full flex items-center">
        <div class="container-store w-full py-12">
            @foreach ($slides as $index => $slide)
                <div
                    x-show="current === {{ $index }}"
                    x-cloak
                    x-transition:enter="transition ease-out duration-500 delay-150"
                    x-transition:enter-start="opacity-0 translate-y-6"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="max-w-xl text-white"
                >
                    @if (!empty($slide['badge']))
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-white/15 text-brand-100 border border-white/25 backdrop-blur-sm mb-5">
                            {{ $slide['badge'] }}
                        </span>
                    @endif

                    <h1 class="text-4xl sm:text-5xl lg:text-[3.5rem] font-bold tracking-tight leading-[1.08] drop-shadow-md">
                        {{ $slide['title'] }}
                    </h1>

                    @if (!empty($slide['subtitle']))
                        <p class="mt-5 text-base sm:text-lg text-white/85 leading-relaxed max-w-md">
                            {{ $slide['subtitle'] }}
                        </p>
                    @endif

                    <div class="mt-8 flex flex-wrap gap-3 sm:gap-4">
                        @if (!empty($slide['primary_btn']))
                            <a
                                href="{{ $slide['primary_href'] ?? '/shop' }}"
                                class="inline-flex items-center justify-center px-6 py-3 rounded-lg bg-white text-brand-900 text-sm font-semibold hover:bg-brand-50 transition-colors shadow-lg"
                            >
                                {{ $slide['primary_btn'] }}
                            </a>
                        @endif
                        @if (!empty($slide['secondary_btn']))
                            <a
                                href="{{ $slide['secondary_href'] ?? '/deals' }}"
                                class="inline-flex items-center justify-center px-6 py-3 rounded-lg border border-white/50 text-white text-sm font-semibold hover:bg-white/10 transition-colors backdrop-blur-sm"
                            >
                                {{ $slide['secondary_btn'] }}
                            </a>
                        @endif
                    </div>

                    @if (!empty($slide['features']))
                        <div class="mt-8 flex flex-wrap items-center gap-5 sm:gap-8 text-sm text-white/75">
                            @foreach ($slide['features'] as $feature)
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-brand-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    {{ $feature }}
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- Slider controls --}}
    @if (count($slides) > 1)
        <button
            type="button"
            @click="prev(); startAutoplay()"
            class="absolute left-4 sm:left-8 top-1/2 -translate-y-1/2 z-20 w-11 h-11 rounded-full bg-black/30 hover:bg-black/50 text-white backdrop-blur-sm border border-white/20 flex items-center justify-center transition-colors"
            aria-label="Previous slide"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>
        <button
            type="button"
            @click="next(); startAutoplay()"
            class="absolute right-4 sm:right-8 top-1/2 -translate-y-1/2 z-20 w-11 h-11 rounded-full bg-black/30 hover:bg-black/50 text-white backdrop-blur-sm border border-white/20 flex items-center justify-center transition-colors"
            aria-label="Next slide"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </button>

        <div class="absolute bottom-8 left-1/2 -translate-x-1/2 z-20 flex items-center gap-2.5">
            @foreach ($slides as $index => $slide)
                <button
                    type="button"
                    @click="goTo({{ $index }}); startAutoplay()"
                    class="h-2 rounded-full transition-all duration-300"
                    :class="current === {{ $index }} ? 'w-9 bg-white' : 'w-2 bg-white/40 hover:bg-white/70'"
                    aria-label="Go to slide {{ $index + 1 }}"
                ></button>
            @endforeach
        </div>
    @endif
</section>
@endif
