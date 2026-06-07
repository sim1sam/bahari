@props([
    'name',
    'count' => null,
    'icon' => null,
    'href' => '#',
    'color' => 'brand',
    'image' => null,
])

@php
    $overlays = [
        'brand' => 'from-brand-600/90 to-brand-800/90',
        'blue' => 'from-blue-600/90 to-blue-800/90',
        'purple' => 'from-purple-600/90 to-purple-800/90',
        'amber' => 'from-amber-600/90 to-amber-800/90',
        'rose' => 'from-rose-600/90 to-rose-800/90',
        'cyan' => 'from-cyan-600/90 to-cyan-800/90',
    ];

    $gradients = [
        'brand' => 'from-brand-500 to-brand-700',
        'blue' => 'from-blue-500 to-blue-700',
        'purple' => 'from-purple-500 to-purple-700',
        'amber' => 'from-amber-500 to-amber-700',
        'rose' => 'from-rose-500 to-rose-700',
        'cyan' => 'from-cyan-500 to-cyan-700',
    ];
@endphp

<a href="{{ $href }}" class="group relative flex flex-col items-center justify-center p-6 rounded-2xl text-white overflow-hidden hover:shadow-xl hover:scale-[1.02] transition-all duration-300 min-h-[160px]">
    @if ($image)
        <img
            src="{{ $image }}"
            alt="{{ $name }}"
            class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
            loading="lazy"
        >
        <div class="absolute inset-0 bg-gradient-to-t {{ $overlays[$color] ?? $overlays['brand'] }}"></div>
    @else
        <div class="absolute inset-0 bg-gradient-to-br {{ $gradients[$color] ?? $gradients['brand'] }}"></div>
    @endif

    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors"></div>

    <div class="relative z-10 text-center">
        @if ($icon)
            <div class="mb-3 flex justify-center">{!! $icon !!}</div>
        @endif
        <h3 class="font-semibold text-lg drop-shadow-sm">{{ $name }}</h3>
        @if ($count)
            <p class="text-sm text-white/90 mt-1 drop-shadow-sm">{{ $count }}</p>
        @endif
    </div>
</a>
