<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use App\Services\ProductCatalog;
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

    public function add(Request $request): RedirectResponse
    {
        if (! Auth::check()) {
            session()->put('url.intended', url()->previous() ?: route('home'));

            return redirect()->route('login')->with('error', 'Please sign in before adding items to your cart.');
        }

        if (! Auth::user()->hasActiveRole()) {
            Auth::logout();

            return redirect()->route('login')->with('error', 'Your account role has been deactivated.');
        }

        if (Auth::user()->isAdmin()) {
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
            return back()->with('error', 'Product not found.');
        }

        return back()
            ->with('success', 'Added to cart!')
            ->with('cart_drawer_open', true);
    }

    public function update(Request $request, string $key): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:10',
            'size' => 'nullable|string|max:255',
        ]);

        $this->cart->update($key, $validated['quantity'], $validated['size'] ?? null);

        $redirect = $request->boolean('cart_drawer')
            ? back()->with('cart_drawer_open', true)
            : redirect()->route('cart.index');

        return $redirect->with('success', 'Cart updated.');
    }

    public function remove(Request $request, string $key): RedirectResponse
    {
        $this->cart->remove($key);

        $redirect = $request->boolean('cart_drawer')
            ? back()->with('cart_drawer_open', true)
            : redirect()->route('cart.index');

        return $redirect->with('success', 'Item removed from cart.');
    }
}
