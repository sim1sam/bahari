@extends('layouts.ecommerce')

@section('title', 'Home')

@section('content')
    <x-home.hero :slides="$heroSlides" />

    @if (count($categories) > 0)
        <x-home.categories :categories="$categories" />
    @endif

    @if (count($featuredProducts) > 0)
        <x-home.featured-products
            :products="$featuredProducts"
            title="Best Selling Dresses"
            subtitle="Our most loved women's fashion picks"
            :actionHref="route('categories.index')"
        />
    @endif

    @foreach ($banners as $banner)
        <x-home.promo-banner :banner="$banner" />
    @endforeach

    @if (count($newArrivals) > 0)
        <x-home.featured-products
            :products="$newArrivals"
            title="New Arrivals"
            subtitle="Latest girls fashion & dress styles"
            :actionHref="route('categories.index')"
        />
    @endif

    <x-home.features :features="$features" />
@endsection
