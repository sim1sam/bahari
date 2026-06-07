<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function index(): View|RedirectResponse
    {
        if (empty($this->cart->items())) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        return view('pages.checkout.index', [
            'items' => $this->cart->items(),
            'subtotal' => $this->cart->subtotal(),
            'shipping' => $this->cart->shipping(),
            'discount' => $this->cart->discount(),
            'coupon' => $this->cart->coupon(),
            'total' => $this->cart->total(),
        ]);
    }

    public function applyCoupon(Request $request): RedirectResponse
    {
        if (empty($this->cart->items())) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $validated = $request->validate([
            'code' => 'required|string|max:30',
        ]);

        if ($error = $this->cart->applyCoupon($validated['code'])) {
            return back()->with('error', $error);
        }

        return back()->with('success', 'Coupon applied!');
    }

    public function removeCoupon(): RedirectResponse
    {
        $this->cart->removeCoupon();

        return back()->with('success', 'Coupon removed.');
    }

    public function store(Request $request): RedirectResponse
    {
        if (empty($this->cart->items())) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'email' => 'required|email|max:150',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'zip' => 'required|string|max:20',
            'payment' => 'required|in:card,cod',
        ]);

        $orderNumber = 'LW-'.strtoupper(substr(uniqid(), -8));

        session([
            'last_order' => [
                'number' => $orderNumber,
                'customer' => $validated,
                'items' => $this->cart->items(),
                'subtotal' => $this->cart->subtotal(),
                'shipping' => $this->cart->shipping(),
                'discount' => $this->cart->discount(),
                'coupon' => $this->cart->coupon(),
                'total' => $this->cart->total(),
                'placed_at' => now()->toDateTimeString(),
            ],
        ]);

        $this->cart->clear();

        return redirect()->route('order.success');
    }

    public function success(): View|RedirectResponse
    {
        $order = session('last_order');

        if (! $order) {
            return redirect()->route('home');
        }

        return view('pages.checkout.success', compact('order'));
    }
}
