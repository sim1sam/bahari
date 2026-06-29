@extends('layouts.ecommerce')

@section('title', $product['name'])

@section('content')
    <div class="bg-surface-elevated border-b border-border">
        <div class="container-store py-3">
            <nav class="flex items-center gap-2 text-sm text-ink-muted">
                <a href="{{ route('home') }}" class="hover:text-brand-600 transition-colors">Home</a>
                <span>/</span>
                @if ($categorySlug ?? null)
                    <a href="{{ route('categories.show', $categorySlug) }}" class="hover:text-brand-600 transition-colors">{{ $product['category'] }}</a>
                @else
                    <span class="text-ink">{{ $product['category'] }}</span>
                @endif
                <span>/</span>
                <span class="text-ink truncate">{{ $product['name'] }}</span>
            </nav>
        </div>
    </div>

    @php
        $productSizes = array_values(array_filter($product['sizes'] ?? [], fn ($value) => trim((string) $value) !== ''));
        $sizeText = $productSizes === []
            ? ''
            : (count($productSizes) === 1 ? $productSizes[0] : implode(', ', $productSizes));
    @endphp

    <section class="py-10 lg:py-16">
        <div class="container-store">
            <div class="grid lg:grid-cols-2 gap-10 lg:gap-16">
                {{-- Gallery --}}
                <div x-data="{ active: 0, images: @js($product['images']) }">
                    <div class="aspect-[3/4] rounded-2xl overflow-hidden bg-brand-50 border border-border">
                        <img
                            :src="images[active]"
                            alt="{{ $product['name'] }}"
                            class="w-full h-full object-cover object-top"
                        >
                    </div>
                    @if (count($product['images']) > 1)
                        <div class="flex gap-3 mt-4">
                            @foreach ($product['images'] as $index => $img)
                                <button
                                    type="button"
                                    @click="active = {{ $index }}"
                                    :class="active === {{ $index }} ? 'ring-2 ring-brand-600' : 'opacity-70 hover:opacity-100'"
                                    class="w-20 h-24 rounded-lg overflow-hidden border border-border transition-all"
                                >
                                    <img src="{{ $img }}" alt="" class="w-full h-full object-cover object-top">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Details --}}
                <div class="flex flex-col" x-data="{}">
                    <div class="flex items-start gap-3">
                        @if ($product['badge'] ?? null)
                            <x-ui.badge :variant="$product['badge_variant'] ?? 'default'">{{ $product['badge'] }}</x-ui.badge>
                        @endif
                        <div>
                            @if ($product['brand'] ?? null)
                                <p class="text-sm font-medium text-brand-600">{{ $product['brand'] }}</p>
                            @endif
                            <span class="text-sm text-ink-muted">{{ $product['category'] }}</span>
                        </div>
                    </div>

                    <h1 class="mt-3 text-3xl lg:text-4xl font-bold tracking-tight text-ink">{{ $product['name'] }}</h1>

                    @if ($product['short_description'] ?? null)
                        <p class="mt-3 text-ink-muted leading-relaxed">{{ $product['short_description'] }}</p>
                    @endif

                    @if ($product['rating'] ?? null)
                        <div class="flex items-center gap-2 mt-4">
                            <div class="flex items-center gap-0.5">
                                @for ($i = 1; $i <= 5; $i++)
                                    <svg class="w-4 h-4 {{ $i <= floor($product['rating']) ? 'text-amber-400' : 'text-stone-200' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                            </div>
                            <span class="text-sm text-ink-muted">{{ $product['rating'] }} / 5</span>
                        </div>
                    @endif

                    <div class="flex items-baseline gap-3 mt-6">
                        <span class="text-3xl font-bold text-ink">{{ money($product['price']) }}</span>
                        @if ($product['original_price'] ?? null)
                            <span class="text-lg text-ink-muted line-through">{{ money($product['original_price']) }}</span>
                            <x-ui.badge variant="sale">Save {{ round((1 - $product['price'] / $product['original_price']) * 100) }}%</x-ui.badge>
                        @endif
                    </div>

                    @php
                        $productColors = array_values(array_filter($product['colors'] ?? [], fn ($value) => trim((string) $value) !== ''));
                        $maxQty = ($product['is_manual'] ?? false)
                            ? max(1, min(10, (int) ($product['stock'] ?? 0) ?: 1))
                            : 10;
                        $outOfStock = ($product['is_manual'] ?? false) && ! ($product['in_stock'] ?? true);
                    @endphp

                    @if ($outOfStock)
                        <div class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                            Out of stock
                        </div>
                    @endif

                    {{-- Add to cart --}}
                    <form action="{{ route('cart.add') }}" method="POST" class="mt-8" x-data="{ qty: 1 }" @submit.prevent="$dispatch('cart:add', { form: $el })">
                        @csrf
                        <input type="hidden" name="slug" value="{{ $product['slug'] }}">

                        <div class="flex flex-wrap items-end gap-4 mb-8">
                            <div class="shrink-0">
                                <label for="product-qty" class="block text-sm font-medium text-ink-muted mb-1.5">Qty:</label>
                                <input
                                    id="product-qty"
                                    type="number"
                                    name="quantity"
                                    x-model.number="qty"
                                    min="1"
                                    max="{{ $maxQty }}"
                                    @disabled($outOfStock)
                                    class="w-16 rounded-lg border border-border bg-surface-elevated py-2.5 px-2 text-center text-sm font-semibold text-ink focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/30"
                                >
                            </div>
                            @if ($sizeText !== '')
                                <div class="flex-1 min-w-[200px]">
                                    <label for="product-size" class="sr-only">Your size</label>
                                    <input
                                        id="product-size"
                                        type="text"
                                        name="size"
                                        placeholder="{{ $sizeText }}"
                                        required
                                        @disabled($outOfStock)
                                        class="w-full rounded-lg border border-border bg-surface-elevated py-2.5 px-3 text-sm text-ink placeholder:text-ink-muted focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/30"
                                    >
                                </div>
                            @endif
                            @if ($productColors !== [])
                                <div class="flex-1 min-w-[200px]">
                                    <label for="product-color" class="block text-sm font-medium text-ink-muted mb-1.5">Color</label>
                                    <select
                                        id="product-color"
                                        name="color"
                                        @disabled($outOfStock)
                                        class="w-full rounded-lg border border-border bg-surface-elevated py-2.5 px-3 text-sm text-ink focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/30"
                                    >
                                        @foreach ($productColors as $color)
                                            <option value="{{ $color }}">{{ $color }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-col sm:flex-row gap-3">
                        <x-ui.button type="submit" size="lg" class="flex-1" :disabled="$outOfStock">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            Add to Cart
                        </x-ui.button>
                        <x-ui.button :href="route('cart.index')" variant="secondary" size="lg">View Cart</x-ui.button>
                        </div>
                    </form>

                    <div class="mt-8 grid grid-cols-2 gap-4 pt-8 border-t border-border">
                        <div class="flex items-start gap-3">
                            <div class="p-2 rounded-lg bg-brand-50 text-brand-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-ink">Free Shipping</p>
                                <p class="text-xs text-ink-muted mt-0.5">On orders over $50</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="p-2 rounded-lg bg-brand-50 text-brand-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-ink">Easy Returns</p>
                                <p class="text-xs text-ink-muted mt-0.5">30-day return policy</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Description --}}
            @if ($product['description'] ?? null)
                @push('styles')
                    <style>
                        .product-description p + p { margin-top: 0.75rem; }
                        .product-description ul { list-style: disc; padding-left: 1.25rem; margin: 0.75rem 0; }
                        .product-description ol { list-style: decimal; padding-left: 1.25rem; margin: 0.75rem 0; }
                        .product-description li + li { margin-top: 0.25rem; }
                        .product-description img { max-width: 100%; height: auto; border-radius: 0.75rem; margin: 1rem 0; }
                        .product-description a { color: #0891b2; text-decoration: underline; }
                        .product-description h1,
                        .product-description h2,
                        .product-description h3 { color: #18181b; font-weight: 600; margin-top: 1.25rem; margin-bottom: 0.5rem; }
                    </style>
                @endpush
                <div class="mt-12 lg:mt-16 pt-10 border-t border-border">
                    <h2 class="text-xl font-semibold text-ink">Description</h2>
                    <div class="product-description mt-4 text-ink-muted leading-relaxed max-w-3xl">
                        {!! strip_tags($product['description'], '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6><a><img><blockquote><span><hr>') !!}
                    </div>
                </div>
            @endif
        </div>
    </section>

    @if (! empty($related))
        <section class="py-16 bg-surface border-t border-border">
            <div class="container-store">
                <x-ui.section-heading title="You May Also Like" subtitle="Complete your look with these styles" />
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
                    @foreach ($related as $item)
                        <x-ecommerce.product-card
                            :name="$item['name']"
                            :price="$item['price']"
                            :slug="$item['slug']"
                            :originalPrice="$item['original_price'] ?? null"
                            :image="$item['image'] ?? null"
                            :badge="$item['badge'] ?? null"
                            :badgeVariant="$item['badge_variant'] ?? 'default'"
                            :rating="$item['rating'] ?? null"
                            :href="$item['href']"
                        />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
