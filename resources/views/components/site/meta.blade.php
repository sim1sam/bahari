@props(['title' => null, 'description' => null, 'keywords' => null, 'ogTitle' => null, 'ogDescription' => null, 'ogImage' => null])

@php
    $settings = app(\App\Services\SiteSettingsService::class);
    $pageTitle = $settings->pageTitle($title);
    $metaDesc = $description ?? $settings->metaDescription();
    $metaKeys = $keywords ?? $settings->metaKeywords();
    $socialTitle = $ogTitle ?? $settings->ogTitle($title);
    $socialDesc = $ogDescription ?? $settings->ogDescription($description);
    $socialImage = $ogImage ?? $settings->ogImageUrl();
    $favicon = $settings->faviconUrl();
    $siteName = $settings->siteName();
@endphp

<title>{{ $pageTitle }}</title>
@if ($favicon)
    <link rel="icon" href="{{ $favicon }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ $favicon }}">
@endif
<meta name="description" content="{{ $metaDesc }}">
@if ($metaKeys)
    <meta name="keywords" content="{{ $metaKeys }}">
@endif
<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:title" content="{{ $socialTitle }}">
<meta property="og:description" content="{{ $socialDesc }}">
@if ($socialImage)
    <meta property="og:image" content="{{ $socialImage }}">
@endif
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $socialTitle }}">
<meta name="twitter:description" content="{{ $socialDesc }}">
@if ($socialImage)
    <meta name="twitter:image" content="{{ $socialImage }}">
@endif
