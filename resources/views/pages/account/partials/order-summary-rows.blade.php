<div class="space-y-2 text-sm">
    <div class="flex justify-between"><span class="text-ink-muted">Subtotal</span><span>${{ number_format($order->subtotal, 2) }}</span></div>
    @if ($order->discount > 0)
        <div class="flex justify-between text-green-600"><span>Discount</span><span>-${{ number_format($order->discount, 2) }}</span></div>
    @endif
    <div class="flex justify-between"><span class="text-ink-muted">Shipping</span><span>{{ $order->shipping > 0 ? '$'.number_format($order->shipping, 2) : 'Free' }}</span></div>
    <div class="flex justify-between pt-2 border-t border-border font-bold text-base"><span>Total</span><span class="text-brand-700">${{ number_format($order->total, 2) }}</span></div>
</div>
