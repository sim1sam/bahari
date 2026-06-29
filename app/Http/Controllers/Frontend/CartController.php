<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use App\Services\ProductCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        private CartService $cart,
        private ProductCatalog $catalog,
    ) {}

    public function index(): View
    {
        $items = collect($this->cart->items())->map(function ($item) {
            $product = $this->catalog->find($item['slug']);
            $item['size_hint'] = implode(', ', $product['sizes'] ?? []);

            return $item;
        })->all();

        return view('pages.cart.index', [
            'items' => $items,
            'subtotal' => $this->cart->subtotal(),
            'shipping' => $this->cart->shipping(),
            'total' => $this->cart->total(),
        ]);
    }

    public function add(Request $request): RedirectResponse|JsonResponse
    {
        if (! Auth::check()) {
            session()->put('url.intended', url()->previous() ?: route('home'));

            if ($request->expectsJson()) {
                return response()->json(['redirect' => route('login')], 401);
            }

            return redirect()->route('login')->with('error', 'Please sign in before adding items to your cart.');
        }

        if (! Auth::user()->hasActiveRole()) {
            Auth::logout();

            if ($request->expectsJson()) {
                return response()->json(['redirect' => route('login')], 403);
            }

            return redirect()->route('login')->with('error', 'Your account role has been deactivated.');
        }

        if (Auth::user()->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Please sign in with a customer account to add items to your cart.'], 403);
            }

            return redirect()->route('home')->with('error', 'Please sign in with a customer account to add items to your cart.');
        }

        $validated = $request->validate([
            'slug' => 'required|string',
            'quantity' => 'nullable|integer|min:1|max:10',
            'size' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:255',
        ]);

        if (! $this->cart->add(
            $validated['slug'],
            $validated['quantity'] ?? 1,
            $validated['size'] ?? null,
            $validated['color'] ?? null,
        )) {
            $product = $this->catalog->find($validated['slug']);

            if ($product && ($product['is_manual'] ?? false) && ! ($product['in_stock'] ?? true)) {
                $message = 'This product is out of stock.';
            } elseif ($product && ($product['is_manual'] ?? false)) {
                $message = 'Not enough stock available.';
            } else {
                $message = 'Product not found.';
            }

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 404);
            }

            return back()->with('error', $message);
        }

        if ($request->expectsJson()) {
            return response()->json($this->cartPayload());
        }

        return back()
            ->with('success', 'Added to cart!')
            ->with('cart_drawer_open', true);
    }

    public function update(Request $request, string $key): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:10',
            'size' => 'nullable|string|max:255',
        ]);

        $this->cart->update($key, $validated['quantity'], $validated['size'] ?? null);

        if ($request->expectsJson()) {
            return response()->json($this->cartPayload());
        }

        $redirect = $request->boolean('cart_drawer')
            ? back()->with('cart_drawer_open', true)
            : redirect()->route('cart.index');

        return $redirect->with('success', 'Cart updated.');
    }

    public function remove(Request $request, string $key): RedirectResponse|JsonResponse
    {
        $this->cart->remove($key);

        if ($request->expectsJson()) {
            return response()->json($this->cartPayload());
        }

        $redirect = $request->boolean('cart_drawer')
            ? back()->with('cart_drawer_open', true)
            : redirect()->route('cart.index');

        return $redirect->with('success', 'Item removed from cart.');
    }

    private function cartPayload(): array
    {
        $subtotal = $this->cart->subtotal();
        $freeShippingAt = (float) config('currency.free_shipping_threshold', 2000);
        $freeShippingRemaining = max(0, $freeShippingAt - $subtotal);

        return [
            'cart_count' => $this->cart->count(),
            'subtotal' => $subtotal,
            'subtotal_formatted' => money($subtotal),
            'total_formatted' => money($subtotal),
            'free_shipping_remaining' => $freeShippingRemaining,
            'free_shipping_remaining_formatted' => money($freeShippingRemaining),
            'items' => collect($this->cart->items())->map(function ($item) {
                $product = $this->catalog->find($item['slug']);
                $item['size_hint'] = implode(', ', $product['sizes'] ?? []);
                $item['product_url'] = route('products.show', $item['slug']);
                $item['update_url'] = route('cart.update', $item['key']);
                $item['remove_url'] = route('cart.remove', $item['key']);
                $item['line_total_formatted'] = money($item['price'] * $item['quantity']);
                $item['syncing'] = false;

                return $item;
            })->values()->all(),
        ];
    }
}
