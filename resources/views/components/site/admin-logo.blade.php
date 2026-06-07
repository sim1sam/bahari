@php
    $settings = app(\App\Services\SiteSettingsService::class);
    $logoUrl = $settings->logoUrl();
    $siteName = $settings->siteName();
    $initial = $settings->logoInitial();
@endphp

@if ($logoUrl)
    <img src="{{ $logoUrl }}" alt="{{ $siteName }}" class="brand-image elevation-3" style="max-height:33px;width:auto;opacity:.9">
@else
    <span class="brand-image img-circle elevation-3 bg-info d-flex align-items-center justify-content-center" style="width:33px;height:33px;opacity:.9">
        <span class="text-white font-weight-bold" style="font-size:14px">{{ $initial }}</span>
    </span>
@endif
<span class="brand-text font-weight-light">{{ $siteName }} Admin</span>
