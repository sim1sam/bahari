@extends('layouts.ecommerce')

@section('title', $category['name'])

@section('content')
    {{-- Primary cyan header --}}
    <section class="bg-brand-600 border-b border-brand-700">
        <div class="container-store py-8 sm:py-10">
            <nav class="flex items-center gap-2 text-sm text-brand-100 mb-3">
                <a href="{{ route('home') }}" class="hover:text-white transition-colors">Home</a>
                <span>/</span>
                <a href="{{ route('categories.index') }}" class="hover:text-white transition-colors">Categories</a>
                <span>/</span>
                <span class="text-white">{{ $category['name'] }}</span>
            </nav>
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl sm:text-4xl font-bold tracking-tight text-white">{{ $category['name'] }}</h1>
                    <p class="mt-2 text-brand-50 text-sm sm:text-base max-w-xl">{{ $category['description'] }}</p>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    <span class="px-4 py-2 rounded-lg bg-brand-700 text-white text-sm font-medium">
                        {{ count($products) }} {{ Str::plural('product', count($products)) }}
                    </span>
                </div>
            </div>
        </div>
    </section>

    <section class="py-8 lg:py-12 bg-surface" x-data="{ filtersOpen: false }">
        <div class="container-store">
            {{-- Mobile filter toggle --}}
            <div class="lg:hidden mb-4">
                <button
                    type="button"
                    @click="filtersOpen = !filtersOpen"
                    class="flex items-center justify-center gap-2 w-full py-3 rounded-xl bg-brand-600 text-white text-sm font-semibold hover:bg-brand-700 transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    Filters & Sort
                    @if ($activeFilterCount > 0)
                        <span class="px-2 py-0.5 rounded-full bg-white text-brand-600 text-xs font-bold">{{ $activeFilterCount }}</span>
                    @endif
                </button>
            </div>

            <div class="flex flex-col lg:flex-row lg:items-start gap-6 lg:gap-10">
                {{-- Left sidebar filters --}}
                <aside class="w-full lg:w-64 shrink-0 lg:sticky lg:top-28 lg:block" :class="{ 'hidden': !filtersOpen }">
                    <div class="bg-surface-elevated rounded-2xl border border-border p-5 sm:p-6 shadow-sm">
                        <div class="flex items-center justify-between mb-6 pb-4 border-b border-brand-100">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-brand-600 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                                    </svg>
                                </div>
                                <h2 class="font-semibold text-ink">Filters</h2>
                            </div>
                            @if ($activeFilterCount > 0)
                                <span class="px-2 py-0.5 rounded-full bg-brand-100 text-brand-700 text-xs font-bold">{{ $activeFilterCount }}</span>
                            @endif
                        </div>

                        <x-ecommerce.category-filters
                            :filterOptions="$filterOptions"
                            :filters="$filters"
                            :sort="$sort"
                        />
                    </div>
                </aside>

                {{-- Right products --}}
                <div class="flex-1 min-w-0 w-full">
                    <div class="hidden lg:flex items-center justify-between mb-6 pb-4 border-b border-border">
                        <p class="text-sm text-ink-muted">
                            Showing <span class="font-semibold text-brand-700">{{ count($products) }}</span> {{ Str::plural('result', count($products)) }}
                        </p>
                        @if ($activeFilterCount > 0)
                            <a href="{{ url()->current() }}" class="text-sm font-medium text-brand-600 hover:text-brand-700 transition-colors">Clear filters</a>
                        @endif
                    </div>

                    @if (empty($products))
                        <div class="text-center py-16 bg-surface-elevated rounded-2xl border border-border">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-brand-50 text-brand-400 mb-5">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                                </svg>
                            </div>
                            <h2 class="text-lg font-semibold text-ink">No products match your filters</h2>
                            <p class="mt-2 text-sm text-ink-muted max-w-sm mx-auto">Try adjusting your filters or browse other categories.</p>
                            <div class="flex flex-col sm:flex-row items-center justify-center gap-3 mt-6">
                                <a href="{{ url()->current() }}" class="px-5 py-2.5 rounded-lg bg-brand-600 text-white text-sm font-semibold hover:bg-brand-700 transition-colors">Clear Filters</a>
                                <x-ui.button :href="route('categories.index')" variant="secondary">Browse Categories</x-ui.button>
                            </div>
                        </div>
                    @else
                        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
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
            </div>
        </div>
    </section>

    @if (! empty($relatedCategories))
        <section class="py-12 bg-surface-elevated border-t border-border">
            <div class="container-store">
                <x-ui.section-heading title="Explore More" subtitle="Discover other collections" />
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    @foreach ($relatedCategories as $cat)
                        <x-ecommerce.category-card
                            :name="$cat['name']"
                            :count="$cat['count'] ?? null"
                            :href="$cat['href']"
                            :image="$cat['image'] ?? null"
                            :color="$cat['color'] ?? 'brand'"
                        />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
