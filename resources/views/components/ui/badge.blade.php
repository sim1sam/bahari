@props([
    'variant' => 'default',
])

@php
    $variants = [
        'default' => 'bg-zinc-100 text-zinc-700',
        'sale' => 'bg-red-100 text-red-700',
        'new' => 'bg-brand-100 text-brand-700',
        'hot' => 'bg-amber-100 text-amber-700',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ' . ($variants[$variant] ?? $variants['default'])]) }}>
    {{ $slot }}
</span>
