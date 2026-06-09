@props([
    'size' => 'md',
    'showName' => true,
    'nameClass' => '',
    'link' => true,
])

@php
    $settings = app(\App\Services\SiteSettingsService::class);
    $logoUrl = $settings->logoUrl();
    $siteName = $settings->siteName();
    $initial = $settings->logoInitial();

    $sizes = [
        'sm' => ['box' => 'w-8 h-8 text-sm rounded-lg', 'img' => 'h-8', 'text' => 'text-base'],
        'md' => ['box' => 'w-9 h-9 text-lg rounded-lg', 'img' => 'h-9', 'text' => 'text-xl'],
        'lg' => ['box' => 'w-11 h-11 text-xl rounded-xl', 'img' => 'h-11', 'text' => 'text-2xl'],
        'admin' => ['box' => 'w-[33px] h-[33px] text-sm rounded-circle', 'img' => 'h-[33px]', 'text' => ''],
    ];
    $s = $sizes[$size] ?? $sizes['md'];
@endphp

@if ($link)
    <a href="{{ route('home') }}" {{ $attributes->merge(['class' => 'inline-flex items-center gap-2 shrink-0']) }}>
@else
    <span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2 shrink-0']) }}>
@endif
    @if ($logoUrl)
        <img src="{{ $logoUrl }}" alt="{{ $siteName }}" class="{{ $s['img'] }} w-auto object-contain">
    @else
        <span class="flex items-center justify-center {{ $s['box'] }} bg-brand-600 text-white font-bold">{{ $initial }}</span>
    @endif
    @if ($showName && ! $logoUrl)
        <span class="font-semibold tracking-tight {{ $s['text'] }} {{ $nameClass }}">{{ $siteName }}</span>
    @endif
@if ($link)
    </a>
@else
    </span>
@endif
