@extends('layouts.ecommerce')

@section('title', 'Categories')

@section('content')
    {{-- Color hero --}}
    <section class="bg-brand-600">
        <div class="container-store py-10 sm:py-14">
            <nav class="flex items-center gap-2 text-sm text-brand-100 mb-4">
                <a href="{{ route('home') }}" class="hover:text-white transition-colors">Home</a>
                <span>/</span>
                <span class="text-white">Categories</span>
            </nav>
            <h1 class="text-3xl sm:text-4xl font-bold tracking-tight text-white">Shop by Category</h1>
            <p class="mt-3 text-brand-50 text-sm sm:text-base max-w-lg">Browse our collections and find your perfect style.</p>
            <div class="flex flex-wrap gap-3 mt-6">
                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-brand-700 text-sm text-white font-medium">
                    {{ count($categories) }} Collections
                </span>
                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-brand-700 text-sm text-white font-medium">
                    {{ $totalProducts }}+ Styles
                </span>
            </div>
        </div>
    </section>

    {{-- Category grid --}}
    <section class="py-10 lg:py-14 bg-surface">
        <div class="container-store">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                @foreach ($categories as $category)
                    <x-ecommerce.category-card
                        :name="$category['name']"
                        :count="$category['count_label']"
                        :href="route('categories.show', $category['slug'])"
                        :image="$category['card_image']"
                        :color="$category['color']"
                    />
                @endforeach
            </div>
        </div>
    </section>
@endsection
