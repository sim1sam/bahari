@php
    $addressLines = array_filter([
        $order->address,
        trim(($order->city ?? '').($order->zip ? ', '.$order->zip : '')),
        $order->customer_phone,
        $order->customer_email,
    ]);
    $businessLines = array_filter([
        $settings->footer_description,
        $settings->contact_phone,
        $settings->contact_email,
    ]);
    $statusClass = match ($order->status) {
        'processing' => 'status-processing',
        'shipped' => 'status-shipped',
        'completed', 'delivered' => 'status-completed',
        'cancelled' => 'status-cancelled',
        default => 'status-pending',
    };
    $paymentClass = match ($order->payment_status) {
        'paid' => 'status-paid',
        'partial' => 'status-partial',
        'due' => 'status-due',
        default => 'status-pending',
    };
    $formatMoney = fn ($amount, $decimals = null) => ($pdfMode ?? false)
        ? 'Tk '.number_format((float) ($amount ?? 0), $decimals ?? (int) config('currency.decimals', 2))
        : money($amount, $decimals);
    $formatMoneyOrFree = fn ($amount, $decimals = null) => (float) ($amount ?? 0) > 0
        ? $formatMoney($amount, $decimals)
        : 'Free';
@endphp

<header class="invoice-header">
    <table class="invoice-header-table" width="100%">
        <tr>
            <td class="brand-cell" valign="top">
                <div class="brand-block">
                    @if ($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $siteName }}" class="brand-logo">
                    @else
                        <div class="brand-fallback">{{ $logoInitial }}</div>
                    @endif
                    <h1 class="brand-name">{{ $siteName }}</h1>
                    @if ($businessLines !== [])
                        <div class="brand-address">
                            @foreach ($businessLines as $line)
                                <div>{{ $line }}</div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </td>
            <td class="meta-cell" valign="top">
                <div class="invoice-meta">
                    <h2 class="invoice-title">INVOICE</h2>
                    <table class="meta-table">
                        <tr>
                            <th>Order No.</th>
                            <td><strong>{{ $order->number }}</strong></td>
                        </tr>
                        <tr>
                            <th>Date</th>
                            <td>{{ $order->created_at->format('d M, Y') }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td><span class="status-badge {{ $statusClass }}">{{ $order->statusLabel() }}</span></td>
                        </tr>
                        <tr>
                            <th>Payment Status</th>
                            <td><span class="status-badge {{ $paymentClass }}">{{ $order->paymentStatusLabel() }}</span></td>
                        </tr>
                        <tr>
                            <th>Payment Method</th>
                            <td>{{ $order->paymentMethodLabel() }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>
</header>

<section class="address-section">
    <table class="address-section-table" width="100%">
        <tr>
            <td class="address-box" valign="top">
                <h3>Billing Address</h3>
                <p><strong>{{ $order->customer_name }}</strong></p>
                @if ($addressLines !== [])
                    @foreach ($addressLines as $line)
                        <p>{{ $line }}</p>
                    @endforeach
                @else
                    <p class="text-muted">—</p>
                @endif
            </td>
            <td class="address-box" valign="top">
                <h3>Shipping Address</h3>
                <p><strong>{{ $order->customer_name }}</strong></p>
                @if ($addressLines !== [])
                    @foreach ($addressLines as $line)
                        <p>{{ $line }}</p>
                    @endforeach
                    @if ($order->shipping_zone)
                        <p>{{ \App\Support\ShippingZone::label($order->shipping_zone) }}</p>
                    @endif
                @else
                    <p>—</p>
                @endif
            </td>
        </tr>
    </table>
</section>

<div class="items-table-wrap">
<table class="items-table">
    <thead>
        <tr>
            <th style="width: 40px;">#</th>
            <th>Item / Style</th>
            <th style="width: 110px;">Size</th>
            <th class="text-center" style="width: 60px;">Qty</th>
            <th class="text-right" style="width: 110px;">Unit Price</th>
            <th class="text-right" style="width: 110px;">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($order->items as $index => $item)
            <tr>
                <td data-label="#">{{ $index + 1 }}</td>
                <td class="item-cell-lead" data-label="">
                    <div class="item-style">{{ $item->product_name }}</div>
                    @if ($item->color)
                        <div class="item-meta">Color: {{ $item->color }}</div>
                    @endif
                </td>
                <td data-label="Size">{{ $item->size ?: '—' }}</td>
                <td class="text-center" data-label="Qty">{{ $item->quantity }}</td>
                <td class="text-right" data-label="Unit Price">{{ $formatMoney($item->price) }}</td>
                <td class="text-right" data-label="Total">{{ $formatMoney($item->price * $item->quantity) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
</div>

<div class="totals-wrap">
    <table class="totals-table">
        <tr>
            <th>Subtotal</th>
            <td>{{ $formatMoney($order->subtotal) }}</td>
        </tr>
        <tr>
            <th>
                Shipping
                @if ($order->shipping_zone)
                    <div style="font-weight: 400; font-size: 11px;">{{ \App\Support\ShippingZone::label($order->shipping_zone) }}</div>
                @endif
            </th>
            <td>{{ $formatMoneyOrFree($order->shipping) }}</td>
        </tr>
        @if ((float) $order->discount > 0)
            <tr>
                <th>
                    Discount
                    @if ($order->coupon_code)
                        <div style="font-weight: 400; font-size: 11px;">Coupon: {{ $order->coupon_code }}</div>
                    @endif
                </th>
                <td>{{ ($pdfMode ?? false) ? '-' : '−' }}{{ $formatMoney($order->discount) }}</td>
            </tr>
        @endif
        <tr class="grand-total">
            <th>Total</th>
            <td>{{ $formatMoney($order->total) }}</td>
        </tr>
    </table>
</div>

<div class="amount-words">
    <strong>Total Amount in Words:</strong> {{ amount_in_words($order->total) }}
</div>

@if ($order->amount_paid > 0 || $order->amountDue() > 0)
    <div class="amount-words" style="margin-top: 12px;">
        <strong>Payment Summary:</strong>
        Paid {{ $formatMoney($order->amount_paid) }}
        @if ($order->amountDue() > 0)
            · Due {{ $formatMoney($order->amountDue()) }}
        @endif
    </div>
@endif

<footer class="invoice-footer">
    Thank you for your order. — {{ $siteName }}
</footer>
