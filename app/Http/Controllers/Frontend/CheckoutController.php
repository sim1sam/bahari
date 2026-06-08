<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $items = array_values($this->cart->items());
        $subtotal = $this->cart->subtotal();
        $shipping = $this->cart->shipping();
        $discount = $this->cart->discount();
        $coupon = $this->cart->coupon();
        $total = $this->cart->total();

        DB::transaction(function () use ($validated, $orderNumber, $items, $subtotal, $shipping, $discount, $coupon, $total) {
            $order = Order::create([
                'user_id' => auth()->id(),
                'number' => $orderNumber,
                'customer_name' => $validated['name'],
                'customer_email' => $validated['email'],
                'customer_phone' => $validated['phone'],
                'address' => $validated['address'],
                'city' => $validated['city'],
                'zip' => $validated['zip'],
                'payment_method' => $validated['payment'],
                'subtotal' => $subtotal,
                'discount' => $discount,
                'shipping' => $shipping,
                'total' => $total,
                'coupon_code' => $coupon['code'] ?? null,
                'status' => 'pending',
            ]);

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_slug' => $item['slug'],
                    'product_name' => $item['name'],
                    'image' => $item['image'],
                    'size' => $item['size'],
                    'color' => $item['color'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }
        });

        session([
            'last_order' => [
                'number' => $orderNumber,
                'customer' => $validated,
                'items' => $items,
                'subtotal' => $subtotal,
                'shipping' => $shipping,
                'discount' => $discount,
                'coupon' => $coupon,
                'total' => $total,
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
