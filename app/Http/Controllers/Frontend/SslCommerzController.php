<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\CartService;
use App\Services\SslCommerzService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class SslCommerzController extends Controller
{
    public function __construct(
        private SslCommerzService $ssl,
        private CartService $cart,
    ) {}

    public function success(Request $request): RedirectResponse|View
    {
        $order = $this->findOrder($request);

        if (! $request->filled('val_id')) {
            return redirect()->route('checkout.index')->with('error', 'Payment validation failed. Please try again.');
        }

        try {
            $gatewayData = $this->ssl->validateTransaction((string) $request->input('val_id'));

            if (($gatewayData['tran_id'] ?? '') !== $order->number) {
                return redirect()->route('checkout.index')->with('error', 'Payment reference mismatch.');
            }

            if ($order->payment_status !== 'paid') {
                $this->ssl->markOrderPaid($order, $gatewayData);
            }

            $this->cart->clear();
            session(['last_order' => $this->orderSessionPayload($order)]);

            return redirect()->route('order.success');
        } catch (\Throwable) {
            return redirect()->route('checkout.index')->with('error', 'We could not verify your payment. Contact support if money was deducted.');
        }
    }

    public function fail(Request $request): RedirectResponse
    {
        $order = $this->findOrder($request, false);

        return redirect()
            ->route('checkout.index')
            ->with('error', 'Payment failed'.($order ? ' for order '.$order->number : '').'. Please try again.');
    }

    public function cancel(Request $request): RedirectResponse
    {
        $order = $this->findOrder($request, false);

        return redirect()
            ->route('checkout.index')
            ->with('error', 'Payment cancelled'.($order ? ' for order '.$order->number : '').'.');
    }

    public function ipn(Request $request): Response
    {
        if (! $request->filled('val_id') || ! $request->filled('tran_id')) {
            return response('Invalid request', 400);
        }

        $order = Order::query()->where('number', $request->input('tran_id'))->first();

        if (! $order) {
            return response('Order not found', 404);
        }

        try {
            $gatewayData = $this->ssl->validateTransaction((string) $request->input('val_id'));

            if (($gatewayData['tran_id'] ?? '') === $order->number && $order->payment_status !== 'paid') {
                $this->ssl->markOrderPaid($order, $gatewayData);
            }

            return response('OK', 200);
        } catch (\Throwable) {
            return response('Validation failed', 400);
        }
    }

    private function findOrder(Request $request, bool $required = true): ?Order
    {
        $tranId = $request->input('tran_id');

        if (! $tranId) {
            abort_if($required, 404);

            return null;
        }

        return Order::query()->where('number', $tranId)->firstOrFail();
    }

    /** @return array<string, mixed> */
    private function orderSessionPayload(Order $order): array
    {
        $order->load('items');

        return [
            'number' => $order->number,
            'customer' => [
                'name' => $order->customer_name,
                'email' => $order->customer_email,
                'phone' => $order->customer_phone,
                'address' => $order->address,
                'city' => $order->city,
                'zip' => $order->zip,
            ],
            'items' => $order->items->map(fn ($item) => [
                'name' => $item->product_name,
                'slug' => $item->product_slug,
                'image' => $item->image,
                'size' => $item->size,
                'color' => $item->color,
                'quantity' => $item->quantity,
                'price' => $item->price,
            ])->all(),
            'subtotal' => $order->subtotal,
            'shipping' => $order->shipping,
            'discount' => $order->discount,
            'coupon' => $order->coupon_code ? ['code' => $order->coupon_code] : null,
            'total' => $order->total,
            'placed_at' => $order->created_at?->toDateTimeString(),
        ];
    }
}
