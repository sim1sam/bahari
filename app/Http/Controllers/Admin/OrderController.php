<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderPayment;
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
            'order' => $order->load(['items', 'payments.recorder']),
            'banks' => config('payment.banks', []),
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

    public function approve(Request $request, Order $order): RedirectResponse
    {
        if ($order->status !== 'pending') {
            return back()->with('error', 'Only pending orders can be approved.');
        }

        $validated = $request->validate([
            'payment_status' => 'required|in:paid,partial,due',
            'amount_paid' => 'required_if:payment_status,partial|nullable|numeric|min:0',
        ]);

        $order->status = 'processing';

        if ($validated['payment_status'] === 'paid') {
            $order->amount_paid = $order->total;
            $order->payment_status = 'paid';
        } elseif ($validated['payment_status'] === 'due') {
            $order->amount_paid = 0;
            $order->payment_status = 'due';
        } else {
            $amountPaid = min((float) ($validated['amount_paid'] ?? 0), (float) $order->total);
            $order->amount_paid = $amountPaid;
            $order->recalculatePaymentStatus();
        }

        $order->save();

        return back()->with('success', 'Order approved. Payment status: '.$order->paymentStatusLabel().'.');
    }

    public function storePayment(Request $request, Order $order, MediaStorageService $media): RedirectResponse
    {
        $banks = array_keys(config('payment.banks', []));

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cod,cash,bank_transfer',
            'bank_name' => 'nullable|required_if:payment_method,bank_transfer|string|in:'.implode(',', $banks),
            'screenshot' => 'nullable|image|max:5120',
            'notes' => 'nullable|string|max:500',
        ]);

        $amount = round((float) $validated['amount'], 2);
        $due = $order->amountDue();

        if ($amount > $due) {
            return back()->with('error', 'Payment amount cannot exceed balance due ($'.number_format($due, 2).').');
        }

        $screenshotPath = null;
        if ($request->hasFile('screenshot')) {
            $screenshotPath = $media->storeUpload(
                $request->file('screenshot'),
                'orders/payments',
                field: 'screenshot'
            );
        }

        $bankLabel = isset($validated['bank_name'])
            ? (config('payment.banks')[$validated['bank_name']] ?? $validated['bank_name'])
            : null;

        OrderPayment::create([
            'order_id' => $order->id,
            'recorded_by' => auth()->id(),
            'amount' => $amount,
            'payment_method' => $validated['payment_method'],
            'bank_name' => $bankLabel,
            'screenshot' => $screenshotPath,
            'notes' => $validated['notes'] ?? null,
        ]);

        $order->amount_paid = round((float) $order->amount_paid + $amount, 2);
        $order->recalculatePaymentStatus();

        if ($order->status === 'pending') {
            $order->status = 'processing';
        }

        $order->save();

        return back()->with('success', 'Payment of $'.number_format($amount, 2).' recorded.');
    }

    public function destroy(Order $order, MediaStorageService $media): RedirectResponse
    {
        if (! $order->canBeDeleted()) {
            return back()->with('error', 'Cannot delete an order after processing has started.');
        }

        $order->load(['items', 'payments']);

        $media->delete($order->payment_screenshot);

        foreach ($order->items as $item) {
            if ($item->image && ! str_starts_with($item->image, 'http')) {
                $media->delete($item->image);
            }
        }

        foreach ($order->payments as $payment) {
            $media->delete($payment->screenshot);
        }

        $order->delete();

        return redirect()
            ->route('admin.orders.index')
            ->with('success', 'Order deleted successfully.');
    }
}
