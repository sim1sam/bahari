@extends('layouts.ecommerce')

@section('title', $settings->hero_title ?: 'Shop')

@section('content')
@php
    $activeBrands = $activeBrands ?? [];
    $activeBrandLabel = count($activeBrands) > 0 ? implode(' · ', $activeBrands) : null;
    $categoryRows = $categoryRows ?? [];
    $productCount = $productCount ?? 0;
    $shopTitle = $settings->hero_title ?: 'Shop';
    $shopSubtitle = $settings->hero_subtitle
        ?: ($settings->section_subtitle ?: 'Newest uploads appear first — filter by brand, price, and style.');
@endphp

{{-- Theme header (same style as category pages) --}}
<section class="bg-brand-600 border-b border-brand-700">
    <div class="container-store py-8 sm:py-10">
        <nav class="flex items-center gap-2 text-sm text-brand-100 mb-3">
            <a href="{{ route('home') }}" class="hover:text-white transition-colors">Home</a>
            <span>/</span>
            <span class="text-white">Shop</span>
        </nav>
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                @if ($activeBrandLabel)
                    <p class="text-sm font-semibold tracking-wide text-brand-100 mb-1">{{ $activeBrandLabel }}</p>
                @endif
                <h1 class="text-3xl sm:text-4xl font-bold tracking-tight text-white">{{ $shopTitle }}</h1>
                <p class="mt-2 text-brand-50 text-sm sm:text-base max-w-xl">{{ $shopSubtitle }}</p>
            </div>
            <span class="px-4 py-2 rounded-lg bg-brand-700 text-white text-sm font-medium shrink-0">
                {{ $productCount }} {{ Str::plural('product', $productCount) }}
            </span>
        </div>
    </div>
</section>

@if ($productCount > 0)
<section id="shop-collection" class="relative py-8 lg:py-12 bg-surface scroll-mt-24 overflow-x-clip" x-data="{ filtersOpen: {{ $activeFilterCount > 0 ? 'true' : 'false' }} }">
    <div class="container-store relative">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 mb-6 sm:mb-8 pb-4 border-b border-border">
            <p class="text-sm text-ink-muted">
                @if ($settings->section_title)
                    <span class="font-semibold text-ink">{{ $settings->section_title }}</span>
                    <span class="mx-1.5">·</span>
                @endif
                Showing <span class="font-semibold text-brand-700">{{ $productCount }}</span> {{ Str::plural('result', $productCount) }}
            </p>

            <form method="GET" action="{{ route('shop.index') }}" class="flex w-full sm:w-auto items-center gap-2 sm:gap-3">
                @foreach (request()->except(['sort', 'page']) as $key => $value)
                    @if (is_array($value))
                        @foreach ($value as $item)
                            <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                        @endforeach
                    @elseif (filled($value))
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach
                <select id="shop-sort" name="sort" onchange="this.form.submit()" class="h-11 w-1/2 sm:w-40 min-w-0 rounded-lg border border-border bg-surface-elevated px-2.5 sm:px-3 text-sm text-ink focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500">
                    <option value="" @selected(($sort ?? '') === '')>Newest first</option>
                    <option value="price_asc" @selected($sort === 'price_asc')>Price: Low to High</option>
                    <option value="price_desc" @selected($sort === 'price_desc')>Price: High to Low</option>
                    <option value="name" @selected($sort === 'name')>Name A–Z</option>
                </select>
                <button type="button" @click="filtersOpen = !filtersOpen" class="inline-flex h-11 w-1/2 sm:w-auto min-w-0 items-center justify-center gap-2 rounded-lg border border-border bg-surface-elevated px-3 sm:px-4 text-sm font-medium text-ink hover:border-brand-300 hover:text-brand-700 transition-colors">
                    Filters
                    @if ($activeFilterCount > 0)
                        <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-md bg-brand-600 px-1.5 text-[11px] font-bold text-white">{{ $activeFilterCount }}</span>
                    @endif
                </button>
            </form>
        </div>

        <div
            x-show="filtersOpen"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            class="mb-8 sm:mb-10 border-t border-border pt-5 sm:pt-8"
        >
            <x-ecommerce.shop-filters :filterOptions="$filterOptions" :filters="$filters" :sort="$sort" />
        </div>

        <div class="space-y-8 sm:space-y-10">
            @foreach ($categoryRows as $row)
                @php
                    $category = $row['category'];
                    $rowProducts = $row['products'];
                @endphp
                <div
                    class="shop-category-row"
                    x-data="{
                        canLeft: false,
                        canRight: false,
                        slideStep() {
                            const el = this.$refs.track;
                            if (!el) return 260;
                            const slide = el.querySelector('.shop-product-slide');
                            if (!slide) return Math.min(280, el.clientWidth * 0.8);
                            const styles = getComputedStyle(el);
                            const gap = parseFloat(styles.columnGap || styles.gap) || 16;
                            return slide.getBoundingClientRect().width + gap;
                        },
                        refresh() {
                            const el = this.$refs.track;
                            if (!el) return;
                            this.canLeft = el.scrollLeft > 2;
                            this.canRight = el.scrollLeft + el.clientWidth < el.scrollWidth - 2;
                        },
                        slide(dir) {
                            const el = this.$refs.track;
                            if (!el) return;
                            el.scrollBy({ left: dir * this.slideStep(), behavior: 'smooth' });
                            setTimeout(() => this.refresh(), 350);
                        }
                    }"
                    x-init="
                        refresh();
                        $nextTick(() => refresh());
                        if ($refs.track) {
                            new ResizeObserver(() => refresh()).observe($refs.track);
                        }
                    "
                >
                    <div class="flex items-center justify-end gap-3 mb-3 sm:mb-4 px-0.5">
                        <div class="flex items-center gap-2 shrink-0">
                            <div class="hidden sm:flex items-center gap-1.5">
                                <button
                                    type="button"
                                    @click="slide(-1)"
                                    :disabled="!canLeft"
                                    :class="canLeft ? 'bg-brand-600 text-white hover:bg-brand-700' : 'bg-brand-100 text-brand-300 cursor-not-allowed'"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-full transition-colors"
                                    aria-label="Previous products"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                                </button>
                                <button
                                    type="button"
                                    @click="slide(1)"
                                    :disabled="!canRight"
                                    :class="canRight ? 'bg-brand-600 text-white hover:bg-brand-700' : 'bg-brand-100 text-brand-300 cursor-not-allowed'"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-full transition-colors"
                                    aria-label="Next products"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                </button>
                            </div>
                            <a href="{{ $category['href'] }}" class="inline-flex items-center rounded-md bg-brand-600 px-3 py-1.5 text-xs sm:text-sm font-semibold text-white hover:bg-brand-700 transition-colors">
                                View All
                            </a>
                        </div>
                    </div>

                    {{-- Left category image + right product slider --}}
                    <div class="flex flex-row items-stretch gap-5 sm:gap-6 lg:gap-8">
                        <a
                            href="{{ $category['href'] }}"
                            class="shop-category-card group relative block shrink-0 self-stretch overflow-hidden rounded-lg bg-brand-800 border-2 border-brand-600"
                            style="width: 280px; min-width: 280px; min-height: 380px;"
                            aria-label="{{ $category['name'] }}"
                        >
                            @if (! empty($category['image']))
                                <img
                                    src="{{ $category['image'] }}"
                                    alt="{{ $category['name'] }}"
                                    class="absolute inset-0 z-0 h-full w-full object-cover object-center transition-transform duration-500 group-hover:scale-105"
                                    loading="eager"
                                    decoding="async"
                                    onerror="this.onerror=null; this.remove();"
                                >
                            @endif
                            <div class="shop-category-overlay absolute inset-0 z-[1] pointer-events-none"></div>
                            <span class="absolute inset-0 z-[2] flex items-center justify-center p-4 sm:p-5 text-center text-2xl sm:text-3xl lg:text-4xl font-bold leading-tight text-white line-clamp-3 drop-shadow-md">
                                {{ $category['name'] }}
                            </span>
                        </a>

                        <div class="relative min-w-0 flex-1">
                            {{-- Side arrows (mobile + desktop overlay) --}}
                            <button
                                type="button"
                                @click="slide(-1)"
                                x-show="canLeft"
                                x-cloak
                                class="absolute left-0 top-1/2 z-20 -translate-y-1/2 -translate-x-1 sm:-translate-x-2 inline-flex h-9 w-9 sm:h-10 sm:w-10 items-center justify-center rounded-full bg-brand-600 text-white shadow-lg hover:bg-brand-700 transition-colors"
                                aria-label="Previous products"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <button
                                type="button"
                                @click="slide(1)"
                                x-show="canRight"
                                x-cloak
                                class="absolute right-0 top-1/2 z-20 -translate-y-1/2 translate-x-1 sm:translate-x-2 inline-flex h-9 w-9 sm:h-10 sm:w-10 items-center justify-center rounded-full bg-brand-600 text-white shadow-lg hover:bg-brand-700 transition-colors"
                                aria-label="Next products"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                            </button>

                            <div
                                x-ref="track"
                                @scroll.debounce.40ms="refresh()"
                                class="shop-product-track shop-category-scroll flex gap-3 sm:gap-4 overflow-x-auto overscroll-x-contain scroll-smooth snap-x snap-mandatory touch-pan-x"
                            >
                                @foreach ($rowProducts as $product)
                                    <div class="snap-start shrink-0 shop-product-slide" style="width: 240px; min-width: 240px;">
                                        <x-ecommerce.product-card
                                            :name="$product['name']"
                                            :price="$product['price']"
                                            :slug="$product['slug']"
                                            :originalPrice="$product['original_price'] ?? null"
                                            :image="$product['image'] ?? null"
                                            :badge="$product['badge'] ?? null"
                                            :badgeVariant="$product['badge_variant'] ?? 'default'"
                                            :rating="$product['rating'] ?? null"
                                            :href="$product['href']"
                                            list-name="Shop — {{ $category['name'] }}"
                                            :position="$loop->iteration"
                                            class="w-full h-full !rounded-lg shop-landing-product"
                                            style="animation-delay: {{ min($loop->index * 40, 400) }}ms"
                                        />
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@else
<section class="py-8 lg:py-12 bg-surface">
    <div class="container-store">
        <div class="text-center py-16 bg-surface-elevated rounded-2xl border border-border">
            <h2 class="text-lg font-semibold text-ink">No products to show</h2>
            <p class="mt-2 text-sm text-ink-muted">Check back soon or browse categories.</p>
            <a href="{{ route('categories.index') }}" class="inline-flex mt-6 px-5 py-2.5 rounded-lg bg-brand-600 text-white text-sm font-semibold hover:bg-brand-700 transition-colors">
                Browse Categories
            </a>
        </div>
    </div>
</section>
@endif

{{-- Closing band (above footer) --}}
<section class="relative overflow-hidden bg-brand-900 text-white min-h-[280px] flex items-center">
    <div class="shop-banner-overlay-empty absolute inset-0"></div>
    <div class="container-store relative py-16 sm:py-20 text-center">
        <h2 class="text-3xl sm:text-4xl font-bold tracking-tight">{{ $settings->hero_title ?: 'Shop the Latest' }}</h2>
        @if ($settings->hero_subtitle)
            <p class="mt-3 text-brand-100/90 max-w-lg mx-auto text-sm sm:text-base">{{ $settings->hero_subtitle }}</p>
        @endif
        <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
            <a href="#shop-collection" class="inline-flex px-6 py-3 rounded-lg bg-white text-brand-900 text-sm font-semibold hover:bg-brand-50 transition-colors">
                {{ $settings->hero_cta_label ?: 'Browse products' }}
            </a>
            <a href="{{ route('home') }}" class="inline-flex px-6 py-3 rounded-lg border border-white/40 text-white text-sm font-semibold hover:bg-white/10 transition-colors">Return home</a>
        </div>
    </div>
</section>

<style>
    .shop-landing-product { animation: shop-fade-up 0.55s ease-out both; }
    .shop-landing-product {
        background: color-mix(in srgb, var(--theme-primary, #0891b2) 10%, white);
        border-color: color-mix(in srgb, var(--theme-primary, #0891b2) 25%, transparent);
    }
    .shop-category-scroll {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    .shop-category-scroll::-webkit-scrollbar { display: none; }
    .shop-category-card {
        background: linear-gradient(135deg, var(--theme-primary, #0891b2), #0f172a);
    }
    .shop-category-overlay {
        background: color-mix(in srgb, var(--theme-primary-dark, #0e7490) 45%, transparent);
    }
    @media (max-width: 640px) {
        .shop-category-card {
            width: 130px !important;
            min-width: 130px !important;
            min-height: 100% !important;
            align-self: stretch;
        }
        .shop-product-track {
            display: grid !important;
            grid-template-rows: repeat(2, auto);
            grid-auto-flow: column;
            grid-auto-columns: 148px;
            gap: 0.75rem;
            align-items: stretch;
        }
        .shop-product-slide {
            width: 148px !important;
            min-width: 0 !important;
            max-width: 148px;
            scroll-snap-align: start;
        }
        .shop-product-slide .shop-landing-product {
            font-size: 0.8125rem;
        }
    }
    @keyframes shop-fade-up { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: translateY(0); } }
    @media (prefers-reduced-motion: reduce) {
        .shop-landing-product { animation: none !important; }
    }
</style>
@endsection
