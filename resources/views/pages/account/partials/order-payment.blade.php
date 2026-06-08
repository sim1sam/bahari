<div class="rounded-2xl lg:rounded-xl bg-surface-elevated border border-border overflow-hidden">
    <div class="px-4 lg:px-6 py-3 lg:py-4 border-b border-border bg-surface/50">
        <h2 class="font-semibold text-sm lg:text-base">Payment</h2>
    </div>
    <div class="p-4 lg:p-6 text-sm space-y-3">
        <div class="flex justify-between items-center">
            <span class="text-ink-muted">Status</span>
            <span class="px-2.5 py-1 rounded-md text-xs font-semibold {{ $order->paymentStatusColor() }}">{{ $order->paymentStatusLabel() }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-ink-muted">Method</span>
            <span class="font-medium">{{ $order->paymentMethodLabel() }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-ink-muted">Total</span>
            <span class="font-medium">{{ money($order->total) }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-ink-muted">Paid</span>
            <span class="font-medium text-green-700">{{ money($order->amount_paid) }}</span>
        </div>
        @if ($order->amountDue() > 0)
            <div class="flex justify-between">
                <span class="text-ink-muted">Balance Due</span>
                <span class="font-bold text-red-600">{{ money($order->amountDue()) }}</span>
            </div>
        @endif

        @if ($order->isCustom())
            @if ($order->bank_name)
                <div class="flex justify-between">
                    <span class="text-ink-muted">Bank</span>
                    <span class="font-medium">{{ $order->bank_name }}</span>
                </div>
            @endif
            @if ($order->paymentScreenshotUrl())
                <div class="pt-2">
                    <p class="text-ink-muted mb-2">Submitted Screenshot</p>
                    <a href="{{ $order->paymentScreenshotUrl() }}" target="_blank" rel="noopener">
                        <img src="{{ $order->paymentScreenshotUrl() }}" alt="Payment screenshot" class="max-h-48 rounded-lg border border-border">
                    </a>
                </div>
            @endif
        @endif

        @if ($order->payments->isNotEmpty())
            <div class="pt-3 border-t border-border">
                <p class="text-ink-muted mb-2 font-medium">Payment History</p>
                <div class="space-y-2">
                    @foreach ($order->payments as $payment)
                        <div class="flex justify-between items-start gap-2 text-xs rounded-lg bg-surface/60 p-3">
                            <div>
                                <p class="font-semibold text-ink">{{ money($payment->amount) }}</p>
                                <p class="text-ink-muted mt-0.5">{{ $payment->methodLabel() }} · {{ $payment->created_at->format('M d, Y') }}</p>
                                @if ($payment->notes)
                                    <p class="text-ink-muted mt-0.5">{{ $payment->notes }}</p>
                                @endif
                            </div>
                            @if ($payment->screenshotUrl())
                                <a href="{{ $payment->screenshotUrl() }}" target="_blank" rel="noopener" class="text-brand-600 shrink-0">Receipt</a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
