<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderPayment;
use App\Models\PaymentTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', 'pending');

        $query = PaymentTransaction::with(['order', 'user'])
            ->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        return view('admin.transactions.index', [
            'transactions' => $query->paginate(20)->withQueryString(),
            'status' => $status,
            'pendingCount' => PaymentTransaction::where('status', PaymentTransaction::STATUS_PENDING)->count(),
        ]);
    }

    public function show(PaymentTransaction $transaction): View
    {
        $transaction->load(['order.items', 'user', 'reviewer']);

        return view('admin.transactions.show', compact('transaction'));
    }

    public function approve(Request $request, PaymentTransaction $transaction): RedirectResponse
    {
        if (! $transaction->isPending()) {
            return back()->with('error', 'This transaction has already been reviewed.');
        }

        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($transaction, $validated) {
            $order = $transaction->order;
            $amount = min((float) $transaction->amount, (float) $order->total);

            $transaction->update([
                'status' => PaymentTransaction::STATUS_APPROVED,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'admin_notes' => $validated['admin_notes'] ?? null,
            ]);

            OrderPayment::create([
                'order_id' => $order->id,
                'recorded_by' => auth()->id(),
                'amount' => $amount,
                'payment_method' => 'bank_transfer',
                'bank_name' => $transaction->bank_name,
                'screenshot' => $transaction->screenshot,
                'notes' => 'Approved from payment transaction #'.$transaction->id,
            ]);

            $order->amount_paid = round((float) $order->amount_paid + $amount, 2);
            $order->recalculatePaymentStatus();

            if ($order->status === 'pending') {
                $order->status = 'processing';
            }

            $order->save();
        });

        return redirect()
            ->route('admin.transactions.index')
            ->with('success', 'Payment approved. Order marked as '.$transaction->order->fresh()->paymentStatusLabel().'.');
    }

    public function reject(Request $request, PaymentTransaction $transaction): RedirectResponse
    {
        if (! $transaction->isPending()) {
            return back()->with('error', 'This transaction has already been reviewed.');
        }

        $validated = $request->validate([
            'admin_notes' => 'required|string|max:500',
        ]);

        $transaction->update([
            'status' => PaymentTransaction::STATUS_REJECTED,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'admin_notes' => $validated['admin_notes'],
        ]);

        return redirect()
            ->route('admin.transactions.index')
            ->with('success', 'Payment rejected.');
    }
}
