@props([
    'filterOptions' => [],
    'filters' => [],
    'sort' => null,
])

@php
    $priceMinLimit = 0;
    $priceMaxLimit = 9999;
    $hasPriceFilter = ((int) ($filters['min_price'] ?? $priceMinLimit) > $priceMinLimit)
        || ((int) ($filters['max_price'] ?? $priceMaxLimit) < $priceMaxLimit);
    $hasFilters = ($filters['sale'] ?? false)
        || ! empty($filters['brands'] ?? [])
        || ! empty($filters['sizes'] ?? [])
        || $hasPriceFilter
        || ! empty($filters['category'] ?? '');
@endphp

<form method="GET" action="{{ route('shop.index') }}" {{ $attributes->merge(['class' => 'grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 sm:gap-8']) }}>
    @if (filled($sort))
        <input type="hidden" name="sort" value="{{ $sort }}">
    @endif

    @if (! empty($filterOptions['brands']))
        <div class="min-w-0">
            <h3 class="text-xs font-bold uppercase tracking-wider text-brand-700 mb-2.5 sm:mb-3">Brand</h3>
            <div class="space-y-1 max-h-36 sm:max-h-48 overflow-y-auto overscroll-contain pr-1">
                @foreach ($filterOptions['brands'] as $brand)
                    <label class="flex items-center gap-3 min-h-11 sm:min-h-10 py-1 cursor-pointer group rounded-lg px-1 -mx-1 active:bg-surface">
                        <input
                            type="checkbox"
                            name="brands[]"
                            value="{{ $brand }}"
                            @checked(in_array($brand, $filters['brands'] ?? [], true))
                            onchange="this.form.submit()"
                            class="h-4 w-4 shrink-0 rounded text-brand-600 border-border focus:ring-brand-500"
                        >
                        <span class="text-sm text-ink-muted group-hover:text-brand-600 transition-colors break-words">{{ $brand }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    @endif

    @if (! empty($filterOptions['categories']))
        <div class="min-w-0">
            <h3 class="text-xs font-bold uppercase tracking-wider text-brand-700 mb-2.5 sm:mb-3">Category</h3>
            <div class="space-y-1">
                <label class="flex items-center gap-3 min-h-11 sm:min-h-10 py-1 cursor-pointer group rounded-lg px-1 -mx-1 active:bg-surface">
                    <input type="radio" name="category" value="" @checked(empty($filters['category'])) onchange="this.form.submit()" class="h-4 w-4 shrink-0 text-brand-600 border-border focus:ring-brand-500">
                    <span class="text-sm text-ink-muted group-hover:text-brand-600 transition-colors">All categories</span>
                </label>
                @foreach ($filterOptions['categories'] as $category)
                    <label class="flex items-center gap-3 min-h-11 sm:min-h-10 py-1 cursor-pointer group rounded-lg px-1 -mx-1 active:bg-surface">
                        <input
                            type="radio"
                            name="category"
                            value="{{ $category }}"
                            @checked(($filters['category'] ?? '') === $category)
                            onchange="this.form.submit()"
                            class="h-4 w-4 shrink-0 text-brand-600 border-border focus:ring-brand-500"
                        >
                        <span class="text-sm text-ink-muted group-hover:text-brand-600 transition-colors break-words">{{ $category }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    @endif

    <div class="min-w-0">
        <h3 class="text-xs font-bold uppercase tracking-wider text-brand-700 mb-2.5 sm:mb-3">Price (BDT)</h3>
        @php
            $priceMinLimit = 0;
            $priceMaxLimit = 9999;
            $priceMin = max($priceMinLimit, min($priceMaxLimit, (int) ($filters['min_price'] ?? $priceMinLimit)));
            $priceMax = max($priceMinLimit, min($priceMaxLimit, (int) ($filters['max_price'] ?? $priceMaxLimit)));
            if ($priceMin > $priceMax) {
                [$priceMin, $priceMax] = [$priceMax, $priceMin];
            }
        @endphp
        <div
            class="space-y-3"
            x-data="{
                minLimit: {{ $priceMinLimit }},
                maxLimit: {{ $priceMaxLimit }},
                minVal: {{ $priceMin }},
                maxVal: {{ $priceMax }},
                clamp() {
                    this.minVal = Math.max(this.minLimit, Math.min(this.maxLimit, Number(this.minVal) || this.minLimit));
                    this.maxVal = Math.max(this.minLimit, Math.min(this.maxLimit, Number(this.maxVal) || this.maxLimit));
                    if (this.minVal > this.maxVal) {
                        const swap = this.minVal;
                        this.minVal = this.maxVal;
                        this.maxVal = swap;
                    }
                },
                get progressLeft() {
                    return ((this.minVal - this.minLimit) / (this.maxLimit - this.minLimit)) * 100;
                },
                get progressRight() {
                    return 100 - ((this.maxVal - this.minLimit) / (this.maxLimit - this.minLimit)) * 100;
                },
                apply() {
                    this.clamp();
                    this.$nextTick(() => this.$el.closest('form')?.requestSubmit());
                },
            }"
        >
            <div class="flex items-center justify-between gap-2 text-sm">
                <span class="font-semibold text-ink" x-text="'৳' + Number(minVal).toLocaleString()"></span>
                <span class="text-ink-muted text-xs">to</span>
                <span class="font-semibold text-ink" x-text="'৳' + Number(maxVal).toLocaleString()"></span>
            </div>

            <div class="relative h-6 flex items-center">
                <div class="absolute inset-x-0 h-1.5 rounded-full bg-border"></div>
                <div
                    class="absolute h-1.5 rounded-full bg-brand-600"
                    :style="'left:' + progressLeft + '%; right:' + progressRight + '%'"
                ></div>
                <input
                    type="range"
                    min="{{ $priceMinLimit }}"
                    max="{{ $priceMaxLimit }}"
                    step="1"
                    x-model.number="minVal"
                    @input="if (minVal > maxVal) minVal = maxVal"
                    @change="apply()"
                    class="shop-price-range absolute inset-0 w-full appearance-none bg-transparent pointer-events-none [&::-webkit-slider-thumb]:pointer-events-auto [&::-moz-range-thumb]:pointer-events-auto"
                    aria-label="Minimum price"
                >
                <input
                    type="range"
                    min="{{ $priceMinLimit }}"
                    max="{{ $priceMaxLimit }}"
                    step="1"
                    x-model.number="maxVal"
                    @input="if (maxVal < minVal) maxVal = minVal"
                    @change="apply()"
                    class="shop-price-range absolute inset-0 w-full appearance-none bg-transparent pointer-events-none [&::-webkit-slider-thumb]:pointer-events-auto [&::-moz-range-thumb]:pointer-events-auto"
                    aria-label="Maximum price"
                >
            </div>

            <div class="flex items-center justify-between text-[11px] text-ink-muted">
                <span>৳{{ number_format($priceMinLimit) }}</span>
                <span>৳{{ number_format($priceMaxLimit) }}</span>
            </div>

            <input type="hidden" name="min_price" :value="minVal">
            <input type="hidden" name="max_price" :value="maxVal">
        </div>
    </div>

    @if (! empty($filterOptions['sizes']))
        <div class="min-w-0">
            <h3 class="text-xs font-bold uppercase tracking-wider text-brand-700 mb-2.5 sm:mb-3">Size</h3>
            <div class="flex flex-wrap gap-2">
                @foreach ($filterOptions['sizes'] as $size)
                    <label class="cursor-pointer">
                        <input type="checkbox" name="sizes[]" value="{{ $size }}" @checked(in_array($size, $filters['sizes'] ?? [])) onchange="this.form.submit()" class="peer sr-only">
                        <span class="inline-flex items-center justify-center min-h-11 min-w-11 sm:min-h-10 sm:min-w-10 px-3 py-2 rounded-lg border border-border text-sm font-medium text-ink-muted peer-checked:bg-brand-600 peer-checked:border-brand-600 peer-checked:text-white hover:border-brand-300 transition-colors">{{ $size }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    @endif

    @if ($filterOptions['has_sale'] ?? false)
        <div class="min-w-0">
            <h3 class="text-xs font-bold uppercase tracking-wider text-brand-700 mb-2.5 sm:mb-3">Offers</h3>
            <label class="flex items-center gap-3 min-h-11 sm:min-h-10 py-1 cursor-pointer group rounded-lg px-1 -mx-1 active:bg-surface">
                <input
                    type="checkbox"
                    name="sale"
                    value="1"
                    @checked($filters['sale'] ?? false)
                    onchange="this.form.submit()"
                    class="h-4 w-4 shrink-0 rounded text-brand-600 border-border focus:ring-brand-500"
                >
                <span class="text-sm text-ink-muted group-hover:text-brand-600 transition-colors">On Sale only</span>
            </label>
        </div>
    @endif

    @if ($hasFilters)
        <div class="sm:col-span-2 lg:col-span-4">
            <a href="{{ route('shop.index', array_filter(['sort' => $sort])) }}#shop-collection" class="inline-flex min-h-11 sm:min-h-10 items-center text-sm font-medium text-brand-600 hover:text-brand-700 transition-colors">
                Clear all filters
            </a>
        </div>
    @endif
</form>

<style>
    .shop-price-range {
        height: 1.5rem;
        margin: 0;
        outline: none;
    }

    .shop-price-range::-webkit-slider-runnable-track {
        height: 0.375rem;
        background: transparent;
    }

    .shop-price-range::-moz-range-track {
        height: 0.375rem;
        background: transparent;
        border: none;
    }

    .shop-price-range::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 1rem;
        height: 1rem;
        margin-top: -0.3125rem;
        border-radius: 9999px;
        background: #0891b2;
        border: 2px solid #fff;
        box-shadow: 0 0 0 1px rgba(8, 145, 178, 0.35);
        cursor: pointer;
        pointer-events: auto;
        position: relative;
        z-index: 2;
    }

    .shop-price-range::-moz-range-thumb {
        width: 1rem;
        height: 1rem;
        border-radius: 9999px;
        background: #0891b2;
        border: 2px solid #fff;
        box-shadow: 0 0 0 1px rgba(8, 145, 178, 0.35);
        cursor: pointer;
        pointer-events: auto;
        position: relative;
        z-index: 2;
    }
</style>
