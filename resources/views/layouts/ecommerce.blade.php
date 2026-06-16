<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <x-site.meta
        :title="trim($__env->yieldContent('title')) ?: null"
        :description="trim($__env->yieldContent('meta_description')) ?: null"
    />

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-site.theme-styles />
    @stack('styles')
</head>
<body class="storefront-body min-h-screen flex flex-col">
    <x-ecommerce.top-bar />
    <x-ecommerce.header />
    <x-ui.flash />

    <main class="flex-1">
        @yield('content')
    </main>

    <x-ecommerce.footer />
    <x-ecommerce.mobile-tab-bar />

    @stack('scripts')
</body>
</html>
