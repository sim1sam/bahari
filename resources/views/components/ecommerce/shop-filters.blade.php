@props([
    'filterOptions' => [],
    'filters' => [],
    'sort' => null,
])

@php
    $hasFilters = ($filters['sale'] ?? false)
        || ! empty($filters['brands'] ?? [])
        || ! empty($filters['sizes'] ?? [])
        || ! empty($filters['price'] ?? '')
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
        <div class="space-y-1">
            @foreach ([
                '' => 'All Prices',
                'under_1000' => 'Under ৳1,000',
                '1000_3000' => '৳1,000 – ৳3,000',
                'over_3000' => 'Over ৳3,000',
            ] as $value => $label)
                <label class="flex items-center gap-3 min-h-11 sm:min-h-10 py-1 cursor-pointer group rounded-lg px-1 -mx-1 active:bg-surface">
                    <input
                        type="radio"
                        name="price"
                        value="{{ $value }}"
                        @checked(($filters['price'] ?? '') === $value)
                        onchange="this.form.submit()"
                        class="h-4 w-4 shrink-0 text-brand-600 border-border focus:ring-brand-500"
                    >
                    <span class="text-sm text-ink-muted group-hover:text-brand-600 transition-colors {{ ($filters['price'] ?? '') === $value ? 'text-brand-700 font-medium' : '' }}">{{ $label }}</span>
                </label>
            @endforeach
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
