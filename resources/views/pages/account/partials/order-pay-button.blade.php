@props(['order', 'class' => ''])

@if ($order->canAcceptPayment())
    <button
        type="button"
        {{ $attributes->merge(['class' => trim('inline-flex items-center justify-center rounded-xl bg-brand-600 text-white text-sm font-semibold hover:bg-brand-700 '.$class)]) }}
        @click="openPaymentModal(@js([
            'number' => $order->number,
            'pay_url' => route('account.orders.pay', $order),
            'balance_due' => (float) $order->amountDue(),
            'payment_status_label' => $order->paymentStatusLabel(),
        ]))"
    >Pay {{ money($order->amountDue()) }}</button>
@endif
