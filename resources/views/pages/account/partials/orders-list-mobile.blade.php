@if ($orders->isEmpty())
    <div class="rounded-2xl bg-surface-elevated border border-border p-8 text-center">
        <p class="font-medium text-ink">No orders yet</p>
        <a href="{{ route('home') }}" class="inline-block mt-4 px-5 py-2.5 rounded-xl bg-brand-600 text-white text-sm font-semibold">Shop Now</a>
    </div>
@else
    <div class="space-y-3">
        @foreach ($orders as $order)
            <a href="{{ route('account.orders.show', $order) }}" class="block rounded-2xl bg-surface-elevated border border-border p-4 active:scale-[0.99] transition-transform">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="font-semibold text-sm">{{ $order->number }}</p>
                        <p class="text-xs text-ink-muted mt-0.5">{{ $order->created_at->format('M d, Y') }}</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-lg text-xs font-medium {{ $order->statusColor() }}">{{ $order->statusLabel() }}</span>
                </div>
                <div class="flex items-center justify-between mt-3 pt-3 border-t border-border">
                    <span class="text-xs text-ink-muted">{{ $order->items->count() }} items</span>
                    <span class="font-bold text-brand-700">${{ number_format($order->total, 2) }}</span>
                </div>
            </a>
        @endforeach
    </div>
@endif
