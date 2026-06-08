<div class="rounded-2xl bg-surface-elevated border border-border overflow-hidden lg:hidden">
    <div class="px-4 py-3 border-b border-border bg-surface text-sm font-semibold">Items</div>
    <div class="divide-y divide-border">
        @foreach ($order->items as $item)
            <div class="flex gap-3 p-4">
                @if ($item->imageUrl())
                    <img src="{{ $item->imageUrl() }}" alt="" class="w-16 h-20 rounded-xl object-cover border border-border shrink-0">
                @endif
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-sm">{{ $item->product_name }}</p>
                    <p class="text-xs text-ink-muted mt-1">
                        @if ($item->size || $item->color)
                            {{ $item->size }} · {{ $item->color }} ·
                        @endif
                        Qty {{ $item->quantity }}
                    </p>
                    @if ($item->product_link)
                        <a href="{{ $item->product_link }}" target="_blank" rel="noopener" class="text-xs text-brand-600 hover:underline mt-1 inline-block truncate max-w-full">View product link</a>
                    @endif
                    <p class="text-sm font-semibold text-brand-700 mt-2">${{ number_format($item->price * $item->quantity, 2) }}</p>
                </div>
            </div>
        @endforeach
    </div>
</div>
