@props([
    'user',
    'size' => 'md',
    'rounded' => 'xl',
])

@php
    $sizeClasses = [
        'sm' => 'w-10 h-10 text-sm',
        'md' => 'w-12 h-12 text-lg',
        'lg' => 'w-16 h-16 text-xl',
        'xl' => 'w-24 h-24 text-3xl',
    ];
    $class = ($sizeClasses[$size] ?? $sizeClasses['md']).' rounded-'.$rounded;
@endphp

@if ($user->avatarUrl())
    <img
        src="{{ $user->avatarUrl() }}"
        alt="{{ $user->name }}"
        {{ $attributes->merge(['class' => $class.' object-cover border border-border shadow-sm shrink-0']) }}
    >
@else
    <div {{ $attributes->merge(['class' => $class.' bg-brand-600 text-white flex items-center justify-center font-bold shadow-sm shrink-0']) }}>
        {{ $user->initials() }}
    </div>
@endif
