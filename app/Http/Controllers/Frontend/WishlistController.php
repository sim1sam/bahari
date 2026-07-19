<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\ProductCatalog;
use App\Services\WishlistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function __construct(
        private WishlistService $wishlist,
        private ProductCatalog $catalog,
    ) {}

    public function index(): View
    {
        $items = collect($this->wishlist->products())
            ->map(fn (array $item) => $this->catalog->toCard($item + [
                'slug' => $item['slug'],
            ]))
            ->all();

        return view('pages.account.wishlist', [
            'items' => $items,
            'count' => $this->wishlist->count(),
        ]);
    }

    public function toggle(Request $request): RedirectResponse|JsonResponse
    {
        if ($redirect = $this->guardCustomer($request)) {
            return $redirect;
        }

        $validated = $request->validate([
            'slug' => 'required|string',
        ]);

        $slug = $validated['slug'];

        if (! $this->catalog->find($slug)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Product not found.'], 404);
            }

            return back()->with('error', 'Product not found.');
        }

        $added = $this->wishlist->toggle($slug);

        if ($request->expectsJson()) {
            return response()->json([
                'added' => $added,
                'count' => $this->wishlist->count(),
                'slugs' => $this->wishlist->slugs(),
                'message' => $added ? 'Added to wishlist.' : 'Removed from wishlist.',
            ]);
        }

        return back()->with('success', $added ? 'Added to wishlist.' : 'Removed from wishlist.');
    }

    public function remove(Request $request, string $slug): RedirectResponse|JsonResponse
    {
        if ($redirect = $this->guardCustomer($request)) {
            return $redirect;
        }

        $this->wishlist->remove($slug);

        if ($request->expectsJson()) {
            return response()->json([
                'added' => false,
                'count' => $this->wishlist->count(),
                'slugs' => $this->wishlist->slugs(),
                'message' => 'Removed from wishlist.',
            ]);
        }

        return back()->with('success', 'Removed from wishlist.');
    }

    private function guardCustomer(Request $request): RedirectResponse|JsonResponse|null
    {
        if (! Auth::check()) {
            session()->put('url.intended', url()->previous() ?: route('home'));

            if ($request->expectsJson()) {
                return response()->json(['redirect' => route('login')], 401);
            }

            return redirect()->route('login')->with('error', 'Please sign in to save wishlist items.');
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
                return response()->json(['message' => 'Please sign in with a customer account to use wishlist.'], 403);
            }

            return redirect()->route('home')->with('error', 'Please sign in with a customer account to use wishlist.');
        }

        return null;
    }
}
