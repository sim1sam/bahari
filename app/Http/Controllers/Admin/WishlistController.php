<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search'));

        $query = Wishlist::query()
            ->with(['user', 'product.category'])
            ->latest();

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->whereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('product', function ($productQuery) use ($search) {
                        $productQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('slug', 'like', "%{$search}%")
                            ->orWhere('brand', 'like', "%{$search}%");
                    });
            });
        }

        return view('admin.wishlists.index', [
            'wishlists' => $query->paginate(20)->withQueryString(),
            'search' => $search,
            'stats' => [
                'total' => Wishlist::query()->count(),
                'customers' => Wishlist::query()->distinct('user_id')->count('user_id'),
                'products' => Wishlist::query()->distinct('product_id')->count('product_id'),
                'today' => Wishlist::query()->whereDate('created_at', today())->count(),
            ],
        ]);
    }

    public function destroy(Wishlist $wishlist): RedirectResponse
    {
        $wishlist->delete();

        return back()->with('success', 'Wishlist item removed.');
    }
}
