@props([
    'name',
    'price',
    'slug' => null,
    'originalPrice' => null,
    'image' => null,
    'badge' => null,
    'badgeVariant' => 'default',
    'rating' => null,
    'href' => '#',
])

<article {{ $attributes->merge(['class' => 'group relative flex flex-col w-full bg-surface-elevated rounded-2xl border border-border overflow-hidden hover:shadow-lg hover:border-brand-200 transition-all duration-300']) }}>
    <div class="relative aspect-[3/4] bg-brand-50 overflow-hidden">
        <a href="{{ $href }}" class="block w-full h-full">
            @if ($image)
                <img
                    src="{{ $image }}"
                    alt="{{ $name }}"
                    class="w-full h-full object-cover object-top group-hover:scale-105 transition-transform duration-500"
                    loading="lazy"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'"
                >
                <div class="hidden w-full h-full items-center justify-center bg-gradient-to-br from-brand-50 to-brand-100">
                    <svg class="w-14 h-14 text-brand-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            @else
                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-brand-50 to-brand-100">
                    <svg class="w-14 h-14 text-brand-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            @endif
        </a>

        @if ($badge)
            <div class="absolute top-3 left-3 pointer-events-none">
                <x-ui.badge :variant="$badgeVariant">{{ $badge }}</x-ui.badge>
            </div>
        @endif

        @if ($slug)
            <div class="absolute inset-x-0 bottom-0 p-3 translate-y-full group-hover:translate-y-0 transition-transform duration-300 z-10">
                <form action="{{ route('cart.add') }}" method="POST" x-data @submit.prevent="$dispatch('cart:add', { form: $el })">
                    @csrf
                    <input type="hidden" name="slug" value="{{ $slug }}">
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" class="w-full py-2.5 rounded-lg bg-brand-700/95 text-white text-sm font-medium backdrop-blur-sm hover:bg-brand-800 transition-colors">
                        Add to Cart
                    </button>
                </form>
            </div>
        @endif
    </div>

    <div class="p-4 flex flex-col flex-1">
        <a href="{{ $href }}" class="font-medium text-ink hover:text-brand-600 transition-colors line-clamp-2">{{ $name }}</a>

        @if ($rating)
            <div class="flex items-center gap-1 mt-1.5">
                @for ($i = 1; $i <= 5; $i++)
                    <svg class="w-3.5 h-3.5 {{ $i <= floor($rating) ? 'text-amber-400' : 'text-stone-200' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                @endfor
                <span class="text-xs text-ink-muted ml-1">({{ $rating }})</span>
            </div>
        @endif

        <div class="mt-auto pt-3 flex items-baseline gap-2">
            <span class="text-lg font-bold text-ink">{{ money($price) }}</span>
            @if ($originalPrice)
                <span class="text-sm text-ink-muted line-through">{{ money($originalPrice) }}</span>
            @endif
        </div>
    </div>
</article>
