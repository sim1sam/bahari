<div class="rounded-2xl bg-surface-elevated border border-border p-5 lg:hidden">
    <h2 class="text-sm font-semibold mb-3">Shipping Address</h2>
    <div class="text-sm text-ink-muted space-y-1">
        <p class="font-medium text-ink">{{ $order->customer_name }}</p>
        <p>{{ $order->address }}</p>
        @if ($order->city || $order->zip)
            <p>{{ collect([$order->city, $order->zip])->filter()->implode(', ') }}</p>
        @endif
        <p>{{ $order->customer_phone }}</p>
        @if ($order->shipping_zone)
            <p class="pt-2 text-ink">{{ \App\Support\ShippingZone::label($order->shipping_zone) }} · {{ money_or_free($order->shipping) }}</p>
        @endif
    </div>
</div>
