@extends('layouts.ecommerce')

@section('title', 'New Arrivals')

@section('content')
    <section class="bg-brand-600 border-b border-brand-700">
        <div class="container-store py-8 sm:py-10">
            <nav class="flex items-center gap-2 text-sm text-brand-100 mb-3">
                <a href="{{ route('home') }}" class="hover:text-white transition-colors">Home</a>
                <span>/</span>
                <span class="text-white">New Arrivals</span>
            </nav>
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl sm:text-4xl font-bold tracking-tight text-white">New Arrivals</h1>
                    <p class="mt-2 text-brand-50 text-sm sm:text-base max-w-xl">Fresh styles just added — newest live products shown first.</p>
                </div>
                <span class="px-4 py-2 rounded-lg bg-brand-700 text-white text-sm font-medium shrink-0">
                    {{ count($products) }} {{ Str::plural('product', count($products)) }}
                </span>
            </div>
        </div>
    </section>

    <section class="py-8 lg:py-12 bg-surface">
        <div class="container-store">
            @if (empty($products))
                <div class="text-center py-16 bg-surface-elevated rounded-2xl border border-border">
                    <h2 class="text-lg font-semibold text-ink">No new arrivals yet</h2>
                    <p class="mt-2 text-sm text-ink-muted">Check back soon for the latest styles.</p>
                    <a href="{{ route('categories.index') }}" class="inline-flex mt-6 px-5 py-2.5 rounded-lg bg-brand-600 text-white text-sm font-semibold hover:bg-brand-700 transition-colors">
                        Browse Categories
                    </a>
                </div>
            @else
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
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
                        />
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
