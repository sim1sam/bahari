<div class="rounded-2xl bg-surface-elevated border border-border overflow-hidden lg:hidden">
    <div class="px-4 py-3 border-b border-border bg-surface text-sm font-semibold">Items</div>
    <div class="divide-y divide-border">
        @foreach ($order->items as $item)
            <div class="flex gap-3 p-4">
                @if ($item->image)
                    <img src="{{ $item->image }}" alt="" class="w-16 h-20 rounded-xl object-cover border border-border shrink-0">
                @endif
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-sm">{{ $item->product_name }}</p>
                    <p class="text-xs text-ink-muted mt-1">{{ $item->size }} · {{ $item->color }} · Qty {{ $item->quantity }}</p>
                    <p class="text-sm font-semibold text-brand-700 mt-2">${{ number_format($item->price * $item->quantity, 2) }}</p>
                </div>
            </div>
        @endforeach
    </div>
</div>
