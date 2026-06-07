<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use App\Services\ProductCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            $item['sizes'] = $product['sizes'] ?? ['XS', 'S', 'M', 'L', 'XL'];

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
        $validated = $request->validate([
            'slug' => 'required|string',
            'quantity' => 'nullable|integer|min:1|max:10',
            'size' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:30',
        ]);

        if (! $this->cart->add(
            $validated['slug'],
            $validated['quantity'] ?? 1,
            $validated['size'] ?? null,
            $validated['color'] ?? null,
        )) {
            return back()->with('error', 'Product not found.');
        }

        return back()->with('success', 'Added to cart!');
    }

    public function update(Request $request, string $key): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:10',
            'size' => 'nullable|string|max:10',
        ]);

        $this->cart->update($key, $validated['quantity'], $validated['size'] ?? null);

        return redirect()->route('cart.index')->with('success', 'Cart updated.');
    }

    public function remove(string $key): RedirectResponse
    {
        $this->cart->remove($key);

        return redirect()->route('cart.index')->with('success', 'Item removed from cart.');
    }
}
