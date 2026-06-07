@props([
    'filterOptions' => [],
    'filters' => [],
    'sort' => null,
])

@php
    $hasFilters = ($filters['sale'] ?? false)
        || ! empty($filters['sizes'] ?? [])
        || ! empty($filters['colors'] ?? [])
        || ! empty($filters['price'] ?? '')
        || ! empty($sort);
@endphp

<form method="GET" {{ $attributes->merge(['class' => 'space-y-6']) }}>
    {{-- Sort --}}
    <div>
        <h3 class="text-xs font-bold uppercase tracking-wider text-brand-700 mb-3">Sort By</h3>
        <div class="space-y-2">
            @foreach ([
                '' => 'Featured',
                'price_asc' => 'Price: Low to High',
                'price_desc' => 'Price: High to Low',
                'name' => 'Name A–Z',
            ] as $value => $label)
                <label class="flex items-center gap-3 cursor-pointer group">
                    <input
                        type="radio"
                        name="sort"
                        value="{{ $value }}"
                        @checked(($sort ?? '') === $value)
                        onchange="this.form.submit()"
                        class="text-brand-600 border-border focus:ring-brand-500"
                    >
                    <span class="text-sm text-ink-muted group-hover:text-brand-600 transition-colors {{ ($sort ?? '') === $value ? 'text-brand-700 font-medium' : '' }}">{{ $label }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div class="border-t border-border pt-6">
        <h3 class="text-xs font-bold uppercase tracking-wider text-brand-700 mb-3">Price</h3>
        <div class="space-y-2">
            @foreach ([
                '' => 'All Prices',
                'under_60' => 'Under $60',
                '60_100' => '$60 – $100',
                'over_100' => 'Over $100',
            ] as $value => $label)
                <label class="flex items-center gap-3 cursor-pointer group">
                    <input
                        type="radio"
                        name="price"
                        value="{{ $value }}"
                        @checked(($filters['price'] ?? '') === $value)
                        onchange="this.form.submit()"
                        class="text-brand-600 border-border focus:ring-brand-500"
                    >
                    <span class="text-sm text-ink-muted group-hover:text-brand-600 transition-colors {{ ($filters['price'] ?? '') === $value ? 'text-brand-700 font-medium' : '' }}">{{ $label }}</span>
                </label>
            @endforeach
        </div>
    </div>

    @if (! empty($filterOptions['sizes']))
        <div class="border-t border-border pt-6">
            <h3 class="text-xs font-bold uppercase tracking-wider text-brand-700 mb-3">Size</h3>
            <div class="flex flex-wrap gap-2">
                @foreach ($filterOptions['sizes'] as $size)
                    <label class="cursor-pointer">
                        <input type="checkbox" name="sizes[]" value="{{ $size }}" @checked(in_array($size, $filters['sizes'] ?? [])) onchange="this.form.submit()" class="peer sr-only">
                        <span class="inline-flex items-center justify-center min-w-10 px-3 py-1.5 rounded-lg border border-border text-sm font-medium text-ink-muted peer-checked:bg-brand-600 peer-checked:border-brand-600 peer-checked:text-white hover:border-brand-300 transition-colors">{{ $size }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    @endif

    @if (! empty($filterOptions['colors']))
        <div class="border-t border-border pt-6">
            <h3 class="text-xs font-bold uppercase tracking-wider text-brand-700 mb-3">Color</h3>
            <div class="space-y-2">
                @foreach ($filterOptions['colors'] as $color)
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input
                            type="checkbox"
                            name="colors[]"
                            value="{{ $color }}"
                            @checked(in_array($color, $filters['colors'] ?? []))
                            onchange="this.form.submit()"
                            class="rounded text-brand-600 border-border focus:ring-brand-500"
                        >
                        <span class="text-sm text-ink-muted group-hover:text-brand-600 transition-colors">{{ $color }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    @endif

    @if ($filterOptions['has_sale'] ?? false)
        <div class="border-t border-border pt-6">
            <h3 class="text-xs font-bold uppercase tracking-wider text-brand-700 mb-3">Offers</h3>
            <label class="flex items-center gap-3 cursor-pointer group">
                <input
                    type="checkbox"
                    name="sale"
                    value="1"
                    @checked($filters['sale'] ?? false)
                    onchange="this.form.submit()"
                    class="rounded text-brand-600 border-border focus:ring-brand-500"
                >
                <span class="text-sm text-ink-muted group-hover:text-brand-600 transition-colors">On Sale only</span>
            </label>
        </div>
    @endif

    @if ($hasFilters)
        <div class="border-t border-border pt-6">
            <a href="{{ url()->current() }}" class="block w-full py-2.5 rounded-lg border border-border text-sm font-medium text-ink-muted text-center hover:border-brand-300 hover:text-brand-600 transition-colors">
                Clear All
            </a>
        </div>
    @endif
</form>
