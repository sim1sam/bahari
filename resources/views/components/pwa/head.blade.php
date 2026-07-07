@php
    $themeColor = $siteSettings->theme_primary ?? '#0891b2';
    $siteName = $site->siteName();
@endphp

<link rel="manifest" href="{{ route('pwa.manifest') }}">
<meta name="theme-color" content="{{ $themeColor }}">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="{{ $siteName }}">
<link rel="apple-touch-icon" href="{{ route('pwa.icon', ['size' => 192]) }}">
