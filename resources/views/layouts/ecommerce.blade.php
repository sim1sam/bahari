<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('meta_description', 'Shop the latest products with fast delivery and great prices.')">

    <title>@yield('title', config('app.name', 'Shop')) — {{ config('app.name', 'Shop') }}</title>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-screen flex flex-col">
    <x-ecommerce.top-bar />
    <x-ecommerce.header />
    <x-ui.flash />

    <main class="flex-1">
        @yield('content')
    </main>

    <x-ecommerce.footer />

    @stack('scripts')
</body>
</html>
