<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderPayment;
use App\Models\User;
use App\Services\MediaStorageService;
use App\Services\OrderTransferService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        return view('admin.orders.index', [
            'orders' => Order::query()->latest()->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.orders.create', [
            'banks' => config('payment.banks', []),
            'customers' => User::customers()->orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    public function store(Request $request, MediaStorageService $media, OrderTransferService $transfer): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => 'nullable|integer|exists:users,id',
            'customer_name' => 'required|string|max:200',
            'customer_email' => 'required|email|max:150',
            'customer_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'zip' => 'nullable|string|max:20',
            'status' => 'required|in:pending,processing,shipped,completed,cancelled',
            'order_type' => 'nullable|in:standard,custom',
            'payment_method' => 'required|string|max:50',
            'reference_code' => 'nullable|string|max:100',
            'bank_name' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:2000',
            'coupon_code' => 'nullable|string|max:30',
            'subtotal' => 'required|numeric|min:0',
            'discount' => 'required|numeric|min:0',
            'shipping' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'amount_paid' => 'nullable|numeric|min:0',
            'payment_status' => 'nullable|in:pending,paid,partial,due',
            'payment_screenshot' => 'nullable|image|max:5120',
            'items' => 'required|array|min:1',
            'items.*.product_name' => 'required|string|max:255',
            'items.*.product_slug' => 'nullable|string|max:255',
            'items.*.product_link' => 'nullable|string|max:500',
            'items.*.image' => 'nullable|string|max:500',
            'items.*.size' => 'nullable|string|max:50',
            'items.*.color' => 'nullable|string|max:50',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'payments' => 'nullable|array',
            'payments.*.amount' => 'required_with:payments|numeric|min:0.01',
            'payments.*.payment_method' => 'required_with:payments|in:cod,cash,bank_transfer',
            'payments.*.bank_name' => 'nullable|string|max:100',
            'payments.*.notes' => 'nullable|string|max:500',
        ]);

        $orderNumber = 'LW-'.strtoupper(substr(uniqid(), -8));

        $order = DB::transaction(function () use ($request, $media, $validated, $orderNumber) {
            $screenshotPath = null;
            if ($request->hasFile('payment_screenshot')) {
                $screenshotPath = $media->storeUpload(
                    $request->file('payment_screenshot'),
                    'orders/payments',
                    field: 'payment_screenshot'
                );
            }

            $order = Order::create([
                'user_id' => $validated['user_id'] ?? null,
                'number' => $orderNumber,
                'order_type' => $validated['order_type'] ?? 'standard',
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'city' => $validated['city'] ?? null,
                'zip' => $validated['zip'] ?? null,
                'status' => $validated['status'],
                'payment_method' => $validated['payment_method'],
                'reference_code' => $validated['reference_code'] ?? null,
                'bank_name' => $validated['bank_name'] ?? null,
                'payment_screenshot' => $screenshotPath,
                'notes' => $validated['notes'] ?? null,
                'coupon_code' => $validated['coupon_code'] ?? null,
                'subtotal' => round((float) $validated['subtotal'], 2),
                'discount' => round((float) $validated['discount'], 2),
                'shipping' => round((float) $validated['shipping'], 2),
                'total' => round((float) $validated['total'], 2),
                'payment_status' => $validated['payment_status'] ?? 'pending',
                'amount_paid' => 0,
            ]);

            foreach ($validated['items'] as $itemData) {
                if (empty($itemData['product_name'])) {
                    continue;
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_name' => $itemData['product_name'],
                    'product_slug' => $this->resolveProductSlug($itemData),
                    'product_link' => $itemData['product_link'] ?? null,
                    'image' => $itemData['image'] ?? null,
                    'size' => $itemData['size'] ?? null,
                    'color' => $itemData['color'] ?? null,
                    'quantity' => (int) $itemData['quantity'],
                    'price' => round((float) $itemData['price'], 2),
                ]);
            }

            foreach ($validated['payments'] ?? [] as $paymentData) {
                if (empty($paymentData['amount']) || (float) $paymentData['amount'] <= 0) {
                    continue;
                }

                $bankLabel = ! empty($paymentData['bank_name'])
                    ? (config('payment.banks')[$paymentData['bank_name']] ?? $paymentData['bank_name'])
                    : null;

                OrderPayment::create([
                    'order_id' => $order->id,
                    'recorded_by' => Auth::id(),
                    'amount' => round((float) $paymentData['amount'], 2),
                    'payment_method' => $paymentData['payment_method'],
                    'bank_name' => $bankLabel,
                    'notes' => $paymentData['notes'] ?? null,
                ]);
            }

            $order->refresh()->load('payments');

            if ($order->payments->isNotEmpty()) {
                $order->amount_paid = round((float) $order->payments->sum('amount'), 2);
                $order->recalculatePaymentStatus();
            } else {
                $paymentStatus = $validated['payment_status'] ?? 'pending';
                $amountPaid = min(round((float) ($validated['amount_paid'] ?? 0), 2), (float) $validated['total']);
                $order->amount_paid = $amountPaid;
                $order->payment_status = $paymentStatus;
                if ($paymentStatus === 'paid') {
                    $order->amount_paid = (float) $validated['total'];
                } elseif ($paymentStatus === 'due') {
                    $order->amount_paid = 0;
                }
            }

            $order->save();

            return $order;
        });

        $message = 'Order created successfully.';

        if ($order->status === 'processing') {
            $message .= $transfer->transfer($order->fresh())
                ? ' Order transferred to API site.'
                : ' Order transfer did not complete. Check transfer status.';
        }

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', $message);
    }

    public function show(Order $order): View
    {
        return view('admin.orders.show', [
            'order' => $order->load(['items', 'payments.recorder', 'paymentTransactions']),
            'banks' => config('payment.banks', []),
        ]);
    }

    public function edit(Order $order): View
    {
        return view('admin.orders.edit', [
            'order' => $order->load(['items', 'payments']),
            'banks' => config('payment.banks', []),
        ]);
    }

    public function update(Request $request, Order $order, MediaStorageService $media, OrderTransferService $transfer): RedirectResponse
    {
        $banks = array_keys(config('payment.banks', []));
        $wasProcessing = $order->status === 'processing';

        $validated = $request->validate([
            'customer_name' => 'required|string|max:200',
            'customer_email' => 'required|email|max:150',
            'customer_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'zip' => 'nullable|string|max:20',
            'status' => 'required|in:pending,processing,shipped,completed,cancelled',
            'payment_method' => 'required|string|max:50',
            'reference_code' => 'nullable|string|max:100',
            'bank_name' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:2000',
            'coupon_code' => 'nullable|string|max:30',
            'subtotal' => 'required|numeric|min:0',
            'discount' => 'required|numeric|min:0',
            'shipping' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'amount_paid' => 'nullable|numeric|min:0',
            'payment_status' => 'nullable|in:pending,paid,partial,due',
            'payment_screenshot' => 'nullable|image|max:5120',
            'remove_payment_screenshot' => 'nullable|boolean',
            'items' => 'nullable|array',
            'items.*.product_name' => 'required_with:items|string|max:255',
            'items.*.product_slug' => 'nullable|string|max:255',
            'items.*.product_link' => 'nullable|string|max:500',
            'items.*.image' => 'nullable|string|max:500',
            'items.*.size' => 'nullable|string|max:50',
            'items.*.color' => 'nullable|string|max:50',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'items.*.price' => 'required_with:items|numeric|min:0',
            'new_items' => 'nullable|array',
            'new_items.*.product_name' => 'required_with:new_items|string|max:255',
            'new_items.*.product_slug' => 'nullable|string|max:255',
            'new_items.*.product_link' => 'nullable|string|max:500',
            'new_items.*.image' => 'nullable|string|max:500',
            'new_items.*.size' => 'nullable|string|max:50',
            'new_items.*.color' => 'nullable|string|max:50',
            'new_items.*.quantity' => 'required_with:new_items|integer|min:1',
            'new_items.*.price' => 'required_with:new_items|numeric|min:0',
            'delete_items' => 'nullable|array',
            'delete_items.*' => 'integer|exists:order_items,id',
            'payments' => 'nullable|array',
            'payments.*.amount' => 'required_with:payments|numeric|min:0',
            'payments.*.payment_method' => 'required_with:payments|in:cod,cash,bank_transfer',
            'payments.*.bank_name' => 'nullable|string|max:100',
            'payments.*.notes' => 'nullable|string|max:500',
            'delete_payments' => 'nullable|array',
            'delete_payments.*' => 'integer|exists:order_payments,id',
            'new_payments' => 'nullable|array',
            'new_payments.*.amount' => 'required_with:new_payments|numeric|min:0.01',
            'new_payments.*.payment_method' => 'required_with:new_payments|in:cod,cash,bank_transfer',
            'new_payments.*.bank_name' => 'nullable|string|max:100',
            'new_payments.*.notes' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($request, $order, $media, $validated, $banks) {
            $order->load(['items', 'payments']);

            foreach ($validated['delete_items'] ?? [] as $itemId) {
                $item = $order->items->firstWhere('id', $itemId);
                if ($item) {
                    if ($item->image && ! str_starts_with($item->image, 'http')) {
                        $media->delete($item->image);
                    }
                    OrderItem::query()->whereKey($item->getKey())->delete();
                }
            }

            foreach ($validated['items'] ?? [] as $itemId => $itemData) {
                $item = $order->items->firstWhere('id', (int) $itemId);
                if (! $item) {
                    continue;
                }

                $item->update([
                    'product_name' => $itemData['product_name'],
                    'product_slug' => $this->resolveProductSlug($itemData),
                    'product_link' => $itemData['product_link'] ?? null,
                    'image' => $itemData['image'] ?? null,
                    'size' => $itemData['size'] ?? null,
                    'color' => $itemData['color'] ?? null,
                    'quantity' => (int) $itemData['quantity'],
                    'price' => round((float) $itemData['price'], 2),
                ]);
            }

            foreach ($validated['new_items'] ?? [] as $itemData) {
                if (empty($itemData['product_name'])) {
                    continue;
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_name' => $itemData['product_name'],
                    'product_slug' => $this->resolveProductSlug($itemData),
                    'product_link' => $itemData['product_link'] ?? null,
                    'image' => $itemData['image'] ?? null,
                    'size' => $itemData['size'] ?? null,
                    'color' => $itemData['color'] ?? null,
                    'quantity' => (int) $itemData['quantity'],
                    'price' => round((float) $itemData['price'], 2),
                ]);
            }

            foreach ($validated['delete_payments'] ?? [] as $paymentId) {
                $payment = $order->payments->firstWhere('id', $paymentId);
                if ($payment) {
                    $media->delete($payment->screenshot);
                    OrderPayment::query()->whereKey($payment->getKey())->delete();
                }
            }

            foreach ($validated['payments'] ?? [] as $paymentId => $paymentData) {
                $payment = $order->payments->firstWhere('id', (int) $paymentId);
                if (! $payment) {
                    continue;
                }

                $bankLabel = ! empty($paymentData['bank_name'])
                    ? (config('payment.banks')[$paymentData['bank_name']] ?? $paymentData['bank_name'])
                    : null;

                $payment->update([
                    'amount' => round((float) $paymentData['amount'], 2),
                    'payment_method' => $paymentData['payment_method'],
                    'bank_name' => $bankLabel,
                    'notes' => $paymentData['notes'] ?? null,
                ]);
            }

            foreach ($validated['new_payments'] ?? [] as $paymentData) {
                if (empty($paymentData['amount']) || (float) $paymentData['amount'] <= 0) {
                    continue;
                }

                $bankLabel = ! empty($paymentData['bank_name'])
                    ? (config('payment.banks')[$paymentData['bank_name']] ?? $paymentData['bank_name'])
                    : null;

                OrderPayment::create([
                    'order_id' => $order->id,
                    'recorded_by' => Auth::id(),
                    'amount' => round((float) $paymentData['amount'], 2),
                    'payment_method' => $paymentData['payment_method'],
                    'bank_name' => $bankLabel,
                    'notes' => $paymentData['notes'] ?? null,
                ]);
            }

            $screenshotPath = $order->payment_screenshot;
            if ($request->boolean('remove_payment_screenshot')) {
                $media->delete($screenshotPath);
                $screenshotPath = null;
            }
            if ($request->hasFile('payment_screenshot')) {
                $media->delete($screenshotPath);
                $screenshotPath = $media->storeUpload(
                    $request->file('payment_screenshot'),
                    'orders/payments',
                    field: 'payment_screenshot'
                );
            }

            $order->update([
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'city' => $validated['city'] ?? null,
                'zip' => $validated['zip'] ?? null,
                'status' => $validated['status'],
                'payment_method' => $validated['payment_method'],
                'reference_code' => $validated['reference_code'] ?? null,
                'bank_name' => $validated['bank_name'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'coupon_code' => $validated['coupon_code'] ?? null,
                'subtotal' => round((float) $validated['subtotal'], 2),
                'discount' => round((float) $validated['discount'], 2),
                'shipping' => round((float) $validated['shipping'], 2),
                'total' => round((float) $validated['total'], 2),
                'payment_screenshot' => $screenshotPath,
            ]);

            $order->refresh()->load('payments');

            if ($order->payments->isNotEmpty()) {
                $order->amount_paid = round((float) $order->payments->sum('amount'), 2);
                $order->recalculatePaymentStatus();
            } else {
                $paymentStatus = $validated['payment_status'] ?? $order->payment_status;
                $amountPaid = min(round((float) ($validated['amount_paid'] ?? $order->amount_paid), 2), (float) $validated['total']);
                $order->amount_paid = $amountPaid;
                $order->payment_status = $paymentStatus;
                if ($paymentStatus === 'paid') {
                    $order->amount_paid = (float) $validated['total'];
                } elseif ($paymentStatus === 'due') {
                    $order->amount_paid = 0;
                }
            }

            $order->save();
        });

        $message = 'Order updated successfully.';

        if (! $wasProcessing && $order->fresh()->status === 'processing') {
            $message .= $transfer->transfer($order->fresh())
                ? ' Order transferred to API site.'
                : ' Order transfer did not complete. Check transfer status.';
        }

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', $message);
    }

    public function updateStatus(Request $request, Order $order, OrderTransferService $transfer): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,shipped,completed,cancelled',
        ]);

        $wasProcessing = $order->status === 'processing';
        $order->update($validated);

        $message = 'Order status updated.';

        if (! $wasProcessing && $order->status === 'processing') {
            $message .= $transfer->transfer($order)
                ? ' Order transferred to API site.'
                : ' Order transfer did not complete. Check transfer status.';
        }

        return back()->with('success', $message);
    }

    public function approve(Request $request, Order $order, OrderTransferService $transfer): RedirectResponse
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

        $message = 'Order approved. Payment status: '.$order->paymentStatusLabel().'.';
        $message .= $transfer->transfer($order)
            ? ' Order transferred to API site.'
            : ' Order transfer did not complete. Check transfer status.';

        return back()->with('success', $message);
    }

    public function storePayment(Request $request, Order $order, MediaStorageService $media, OrderTransferService $transfer): RedirectResponse
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
            return back()->with('error', 'Payment amount cannot exceed balance due ('.money($due).').');
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
            'recorded_by' => Auth::id(),
            'amount' => $amount,
            'payment_method' => $validated['payment_method'],
            'bank_name' => $bankLabel,
            'screenshot' => $screenshotPath,
            'notes' => $validated['notes'] ?? null,
        ]);

        $order->amount_paid = round((float) $order->amount_paid + $amount, 2);
        $order->recalculatePaymentStatus();

        $movedToProcessing = $order->status === 'pending';

        if ($movedToProcessing) {
            $order->status = 'processing';
        }

        $order->save();

        $message = 'Payment of '.money($amount).' recorded.';

        if ($movedToProcessing) {
            $message .= $transfer->transfer($order)
                ? ' Order transferred to API site.'
                : ' Order transfer did not complete. Check transfer status.';
        }

        return back()->with('success', $message);
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

        Order::query()->whereKey($order->getKey())->delete();

        return redirect()
            ->route('admin.orders.index')
            ->with('success', 'Order deleted successfully.');
    }

    private function resolveProductSlug(array $itemData): string
    {
        if (filled($itemData['product_slug'] ?? null)) {
            return $itemData['product_slug'];
        }

        return Str::slug($itemData['product_name'] ?? '') ?: 'custom';
    }
}
