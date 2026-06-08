@if ($order->isCustom())
    <div class="rounded-2xl lg:rounded-xl bg-surface-elevated border border-border overflow-hidden">
        <div class="px-4 lg:px-6 py-3 lg:py-4 border-b border-border bg-surface/50">
            <h2 class="font-semibold text-sm lg:text-base">Payment Details</h2>
        </div>
        <div class="p-4 lg:p-6 text-sm space-y-2">
            <div class="flex justify-between">
                <span class="text-ink-muted">Method</span>
                <span class="font-medium">{{ $order->paymentMethodLabel() }}</span>
            </div>
            @if ($order->bank_name)
                <div class="flex justify-between">
                    <span class="text-ink-muted">Bank</span>
                    <span class="font-medium">{{ $order->bank_name }}</span>
                </div>
            @endif
            @if ($order->paymentScreenshotUrl())
                <div class="pt-2">
                    <p class="text-ink-muted mb-2">Payment Screenshot</p>
                    <a href="{{ $order->paymentScreenshotUrl() }}" target="_blank" rel="noopener">
                        <img src="{{ $order->paymentScreenshotUrl() }}" alt="Payment screenshot" class="max-h-48 rounded-lg border border-border">
                    </a>
                </div>
            @endif
            @if ($order->notes)
                <div class="pt-2 border-t border-border">
                    <p class="text-ink-muted mb-1">Notes</p>
                    <p>{{ $order->notes }}</p>
                </div>
            @endif
        </div>
    </div>
@endif
