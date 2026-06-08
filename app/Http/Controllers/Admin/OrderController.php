<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\MediaStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        return view('admin.orders.index', [
            'orders' => Order::latest()->paginate(20),
        ]);
    }

    public function show(Order $order): View
    {
        return view('admin.orders.show', [
            'order' => $order->load('items'),
        ]);
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,shipped,completed,cancelled',
        ]);

        $order->update($validated);

        return back()->with('success', 'Order status updated.');
    }

    public function destroy(Order $order, MediaStorageService $media): RedirectResponse
    {
        if (! $order->canBeDeleted()) {
            return back()->with('error', 'Cannot delete an order after processing has started.');
        }

        $order->load('items');
        $media->delete($order->payment_screenshot);

        foreach ($order->items as $item) {
            if ($item->image && ! str_starts_with($item->image, 'http')) {
                $media->delete($item->image);
            }
        }

        $order->delete();

        return redirect()
            ->route('admin.orders.index')
            ->with('success', 'Order deleted successfully.');
    }
}
