<div class="space-y-2 text-sm">
    <div class="flex justify-between"><span class="text-ink-muted">Subtotal</span><span>{{ money($order->subtotal) }}</span></div>
    @if ($order->discount > 0)
        <div class="flex justify-between text-green-600"><span>Discount</span><span>-{{ money($order->discount) }}</span></div>
    @endif
    <div class="flex justify-between"><span class="text-ink-muted">Shipping</span><span>{{ money_or_free($order->shipping) }}</span></div>
    <div class="flex justify-between pt-2 border-t border-border font-bold text-base"><span>Total</span><span class="text-brand-700">{{ money($order->total) }}</span></div>
</div>
