@extends('layouts.ecommerce')

@section('title', 'Home')

@section('content')
    <x-home.hero :slides="$heroSlides" />

    <x-home.categories :categories="$categories" />

    <x-home.featured-products
        :products="$featuredProducts"
        title="Best Selling Dresses"
        subtitle="Our most loved women's fashion picks"
    />

    <x-home.promo-banner />

    <x-home.featured-products
        :products="$newArrivals"
        title="New Arrivals"
        subtitle="Latest girls fashion & dress styles"
    />

    <x-home.features :features="$features" />
@endsection
