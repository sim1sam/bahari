@extends('layouts.ecommerce')

@section('title', $query !== '' ? 'Search: '.$query : 'Search')

@section('content')
    <section class="bg-brand-600 border-b border-brand-700">
        <div class="container-store py-8 sm:py-10">
            <nav class="flex items-center gap-2 text-sm text-brand-100 mb-3">
                <a href="{{ route('home') }}" class="hover:text-white transition-colors">Home</a>
                <span>/</span>
                <span class="text-white">Search</span>
            </nav>
            <h1 class="text-3xl sm:text-4xl font-bold tracking-tight text-white">
                @if ($query !== '')
                    Results for “{{ $query }}”
                @else
                    Search products
                @endif
            </h1>
            <div class="mt-6 max-w-xl">
                <x-ecommerce.search-bar :query="$query" />
            </div>
        </div>
    </section>

    <section class="py-8 lg:py-12 bg-surface">
        <div class="container-store">
            @if ($query === '')
                <div class="text-center py-16 bg-surface-elevated rounded-2xl border border-border">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-brand-50 text-brand-500 mb-5">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <h2 class="text-lg font-semibold text-ink">Find your next look</h2>
                    <p class="mt-2 text-sm text-ink-muted max-w-sm mx-auto">Search by product name, style, or category.</p>
                </div>
            @elseif (empty($products))
                <div class="text-center py-16 bg-surface-elevated rounded-2xl border border-border">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-brand-50 text-brand-400 mb-5">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <h2 class="text-lg font-semibold text-ink">No products found</h2>
                    <p class="mt-2 text-sm text-ink-muted max-w-sm mx-auto">Try a different keyword or browse our categories.</p>
                    <div class="mt-6">
                        <x-ui.button :href="route('categories.index')" variant="primary">Browse Categories</x-ui.button>
                    </div>
                </div>
            @else
                <p class="text-sm text-ink-muted mb-6">
                    Showing <span class="font-semibold text-brand-700">{{ count($products) }}</span> {{ Str::plural('result', count($products)) }}
                </p>

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
                    @foreach ($products as $product)
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
                            class="w-full"
                        />
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
