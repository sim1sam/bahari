<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\MediaStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function dashboard(): View
    {
        $user = auth()->user();
        $orders = $this->userOrders()->with('items')->latest()->take(5)->get();

        return view('pages.account.dashboard', [
            'user' => $user,
            'orders' => $orders,
            'ordersCount' => $this->userOrders()->count(),
            'totalSpent' => $this->userOrders()->sum('total'),
        ]);
    }

    public function orders(): View
    {
        return view('pages.account.orders', [
            'orders' => $this->userOrders()->with('items')->latest()->simplePaginate(10),
        ]);
    }

    public function orderShow(Order $order): View|RedirectResponse
    {
        if (! $this->ownsOrder($order)) {
            abort(403);
        }

        $order->load(['items', 'payments']);

        return view('pages.account.order-show', compact('order'));
    }

    public function destroyOrder(Order $order, MediaStorageService $media): RedirectResponse
    {
        if (! $this->ownsOrder($order)) {
            abort(403);
        }

        if (! $order->canBeDeleted()) {
            return back()->with('error', 'This order cannot be deleted after processing has started.');
        }

        $order->load('items');
        $this->deleteOrderFiles($order, $media);
        $order->delete();

        return redirect()
            ->route('account.orders')
            ->with('success', 'Order deleted successfully.');
    }

    public function transactions(): View
    {
        $query = $this->userOrders();

        return view('pages.account.transactions', [
            'orders' => (clone $query)->latest()->simplePaginate(15),
            'transactionsCount' => $query->count(),
            'totalSpent' => (clone $query)->sum('total'),
        ]);
    }

    public function menu(): View
    {
        return view('pages.account.menu', [
            'user' => auth()->user(),
        ]);
    }

    public function profile(): View
    {
        return view('pages.account.profile', [
            'user' => auth()->user(),
        ]);
    }

    public function updateProfile(Request $request, MediaStorageService $media): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:users,email,'.$user->id,
            'avatar' => 'nullable|image|max:2048',
            'remove_avatar' => 'nullable|boolean',
            'current_password' => 'nullable|required_with:password',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if (! empty($validated['password'])) {
            if (! Hash::check($validated['current_password'], $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.']);
            }
            $user->password = Hash::make($validated['password']);
        }

        if ($request->boolean('remove_avatar')) {
            $media->delete($user->avatar);
            $user->avatar = null;
        } elseif ($request->hasFile('avatar')) {
            $user->avatar = $media->storeUpload(
                $request->file('avatar'),
                'users/avatars',
                $user->avatar,
                field: 'avatar'
            );
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->save();

        return back()->with('success', 'Profile updated successfully.');
    }

    private function userOrders()
    {
        $user = auth()->user();

        return Order::query()
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere('customer_email', $user->email);
            });
    }

    private function ownsOrder(Order $order): bool
    {
        $user = auth()->user();

        return $order->user_id === $user->id || $order->customer_email === $user->email;
    }

    private function deleteOrderFiles(Order $order, MediaStorageService $media): void
    {
        $media->delete($order->payment_screenshot);

        foreach ($order->items as $item) {
            if ($item->image && ! str_starts_with($item->image, 'http')) {
                $media->delete($item->image);
            }
        }
    }
}
