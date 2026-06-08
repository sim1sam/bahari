<div class="rounded-2xl bg-surface-elevated border border-border p-5 lg:hidden">
    <h2 class="text-sm font-semibold mb-3">Delivery</h2>
    <div class="text-sm text-ink-muted space-y-1">
        <p class="font-medium text-ink">{{ $order->customer_name }}</p>
        <p>{{ $order->address }}</p>
        <p>{{ $order->city }}, {{ $order->zip }}</p>
        <p>{{ $order->customer_phone }}</p>
    </div>
</div>
