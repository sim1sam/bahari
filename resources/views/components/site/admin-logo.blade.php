@php
    $logoUrl = $site->logoUrl();
    $siteName = $site->siteName();
    $initial = $site->logoInitial();
@endphp

@if ($logoUrl)
    <img src="{{ $logoUrl }}" alt="{{ $siteName }}" class="admin-sidebar-logo">
    <span class="brand-text admin-sidebar-brand-text font-weight-light">{{ $siteName }}</span>
@else
    <span class="admin-sidebar-logo-fallback">{{ $initial }}</span>
    <span class="brand-text admin-sidebar-brand-text font-weight-light">{{ $siteName }}</span>
@endif
