<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="{{ $siteSettings->theme_primary ?? '#0891b2' }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <x-site.meta
        :title="trim($__env->yieldContent('title')) ?: 'My Account'"
        :description="trim($__env->yieldContent('meta_description')) ?: null"
    />
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-site.theme-styles />
    <x-site.google-tag-manager location="head" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    @stack('styles')
</head>
<body class="account-app min-h-screen bg-surface text-ink antialiased">
    <x-site.google-tag-manager location="body" />
    <div class="account-shell lg:flex">
        <x-account.sidebar />

        <div class="account-main flex flex-col flex-1 min-w-0 lg:bg-surface">
            @php
                $pageTitle = trim($__env->yieldContent('page_title')) ?: 'My Account';
                $mobileTitle = trim($__env->yieldContent('mobile_title')) ?: $pageTitle;
                $backUrl = trim($__env->yieldContent('back_url')) ?: null;
            @endphp

            <x-account.mobile-header :title="$mobileTitle" :back="$backUrl" />
            <x-account.desktop-header :title="$pageTitle" />

            <main class="account-content flex-1 pb-24 lg:pb-10">
                <x-ui.flash />
                @yield('content')
            </main>
        </div>
    </div>

    <x-account.mobile-tab-bar />

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @stack('scripts')
</body>
</html>
