@if ($orders->isEmpty())
    <div class="rounded-2xl bg-surface-elevated border border-border p-8 text-center">
        <p class="font-medium text-ink">No orders yet</p>
        <a href="{{ route('home') }}" class="inline-block mt-4 px-5 py-2.5 rounded-xl bg-brand-600 text-white text-sm font-semibold">Shop Now</a>
    </div>
@else
    <div class="space-y-3">
        @foreach ($orders as $order)
            <div class="rounded-2xl bg-surface-elevated border border-border p-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="font-semibold text-sm">{{ $order->number }}</p>
                        <p class="text-xs text-ink-muted mt-0.5">{{ $order->created_at->format('M d, Y') }}</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-lg text-xs font-medium {{ $order->statusColor() }}">{{ $order->statusLabel() }}</span>
                </div>
                <div class="flex items-center justify-between mt-3 pt-3 border-t border-border">
                    <span class="text-xs text-ink-muted">{{ $order->items->count() }} items · ${{ number_format($order->total, 2) }}</span>
                    @if ($order->isProcessed())
                        <span class="text-[10px] text-ink-muted">Locked</span>
                    @endif
                </div>
                <div class="flex gap-2 mt-3">
                    <a href="{{ route('account.orders.show', $order) }}" class="flex-1 py-2.5 rounded-xl border border-border text-sm font-medium text-center text-brand-600 hover:bg-brand-50">View</a>
                    @if ($order->canBeDeleted())
                        <form
                            action="{{ route('account.orders.destroy', $order) }}"
                            method="POST"
                            class="flex-1"
                            onsubmit="return confirm('Delete this order?')"
                        >
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full py-2.5 rounded-xl border border-red-200 text-sm font-medium text-red-600 bg-red-50">Delete</button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif
