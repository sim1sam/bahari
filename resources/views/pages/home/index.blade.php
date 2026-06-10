@extends('layouts.ecommerce')

@section('title', 'Home')

@section('content')
    <x-home.hero :slides="$heroSlides" />

    @foreach ($banners as $banner)
        <x-home.promo-banner :banner="$banner" />
    @endforeach

    <x-home.features :features="$features" />
@endsection
