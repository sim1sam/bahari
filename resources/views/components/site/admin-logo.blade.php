@php
    $logoUrl = $site->logoUrl();
    $siteName = $site->siteName();
    $initial = $site->logoInitial();
@endphp

@if ($logoUrl)
    <img src="{{ $logoUrl }}" alt="{{ $siteName }}" class="admin-sidebar-logo">
@else
    <span class="admin-sidebar-logo-fallback">{{ $initial }}</span>
@endif
