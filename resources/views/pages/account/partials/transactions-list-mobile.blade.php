@if ($orders->isEmpty())
    <div class="rounded-2xl bg-surface-elevated border border-border p-8 text-center">
        <p class="font-medium text-ink">No transactions yet</p>
        <p class="text-sm text-ink-muted mt-1">Your payments will show up here</p>
        <a href="{{ route('home') }}" class="inline-block mt-4 px-5 py-2.5 rounded-xl bg-brand-600 text-white text-sm font-semibold">Start Shopping</a>
    </div>
@else
    <div class="space-y-3">
        @foreach ($orders as $order)
            <a href="{{ route('account.orders.show', $order) }}" class="block rounded-2xl bg-surface-elevated border border-border p-4 active:scale-[0.99] transition-transform">
                <div class="flex items-start gap-3">
                    <span class="shrink-0 w-10 h-10 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <p class="font-semibold text-sm truncate">{{ $order->number }}</p>
                                <p class="text-xs text-ink-muted mt-0.5 capitalize">{{ str_replace('_', ' ', $order->payment_method ?? 'card') }}</p>
                            </div>
                            <p class="font-bold text-brand-700 shrink-0">${{ number_format($order->total, 2) }}</p>
                        </div>
                        <div class="flex items-center justify-between mt-2">
                            <span class="text-xs text-ink-muted">{{ $order->created_at->format('M d, Y · g:i A') }}</span>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-medium {{ $order->statusColor() }}">{{ $order->statusLabel() }}</span>
                        </div>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
@endif
