@props([
    'priceMinLimit' => 0,
    'priceMaxLimit' => 1000,
    'minPrice' => null,
    'maxPrice' => null,
])

@php
    $priceMinLimit = (int) $priceMinLimit;
    $priceMaxLimit = (int) $priceMaxLimit;
    if ($priceMaxLimit <= $priceMinLimit) {
        $priceMaxLimit = $priceMinLimit + 100;
    }
    $priceMin = max($priceMinLimit, min($priceMaxLimit, (int) ($minPrice ?? $priceMinLimit)));
    $priceMax = max($priceMinLimit, min($priceMaxLimit, (int) ($maxPrice ?? $priceMaxLimit)));
    if ($priceMin > $priceMax) {
        [$priceMin, $priceMax] = [$priceMax, $priceMin];
    }
    $span = max(1, $priceMaxLimit - $priceMinLimit);
    $step = $span > 5000 ? 100 : ($span > 1000 ? 50 : ($span > 200 ? 10 : 1));
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
            step="{{ $step }}"
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
            step="{{ $step }}"
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

@once
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
@endonce
