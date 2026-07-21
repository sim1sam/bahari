<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentBank;
use App\Models\PaymentTransaction;
use App\Services\CustomerLedgerService;
use App\Services\FinancialTransactionService;
use App\Services\MediaStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function __construct(private CustomerLedgerService $ledger) {}

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
            'orders' => $this->userOrders()->with(['items', 'paymentTransactions'])->latest()->simplePaginate(10),
            'banks' => PaymentBank::activeForCheckout(),
            'sslCommerzEnabled' => app(\App\Services\SiteSettingsService::class)->sslCommerzConfigured(),
        ]);
    }

    public function orderShow(Order $order): View|RedirectResponse
    {
        if (! $this->ownsOrder($order)) {
            abort(403);
        }

        $order->load(['items', 'payments', 'paymentTransactions']);

        return view('pages.account.order-show', [
            'order' => $order,
            'banks' => PaymentBank::activeForCheckout(),
            'sslCommerzEnabled' => app(\App\Services\SiteSettingsService::class)->sslCommerzConfigured(),
        ]);
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

    public function payOrder(Request $request, Order $order, MediaStorageService $media, FinancialTransactionService $financialTransactions): RedirectResponse
    {
        if (! $this->ownsOrder($order)) {
            abort(403);
        }

        $order->load('paymentTransactions');

        if ($order->amountDue() <= 0) {
            return back()->with('error', 'No balance due for this order.');
        }

        $ssl = app(\App\Services\SslCommerzService::class);
        $paymentOptions = ['bank_transfer'];

        if ($ssl->isConfigured()) {
            $paymentOptions[] = 'sslcommerz';
        }

        $validated = $request->validate([
            'payment' => 'required|in:'.implode(',', $paymentOptions),
            'bank_id' => 'required_if:payment,bank_transfer|nullable|integer|exists:payment_banks,id',
            'payment_screenshot' => 'required_if:payment,bank_transfer|nullable|image|max:5120',
        ]);

        if (! $order->canAcceptPayment()) {
            return back()->with('error', 'Payment is not available for this order.');
        }

        if ($validated['payment'] === 'sslcommerz') {
            if (! $ssl->isConfigured() || ! $order->canPayOnline()) {
                return back()->with('error', 'Online payment is not available for this order.');
            }

            return redirect()->away($ssl->initiatePayment($order));
        }

        $bank = PaymentBank::query()
            ->where('is_active', true)
            ->findOrFail($validated['bank_id']);

        $due = $order->amountDue();
        $chargeSplit = $financialTransactions->splitForBank($bank, $due);
        $screenshotPath = $media->storeUpload(
            $request->file('payment_screenshot'),
            'orders/payments',
            field: 'payment_screenshot'
        );

        $transaction = PaymentTransaction::create([
            'order_id' => $order->id,
            'user_id' => auth()->id(),
            'payment_bank_id' => $bank->id,
            'amount' => $chargeSplit['total_amount'],
            'sale_amount' => $chargeSplit['base_amount'],
            'bank_charge_percent' => $chargeSplit['bank_charge_percent'],
            'bank_charge_amount' => $chargeSplit['bank_charge_amount'],
            'bank_name' => $bank->displayName(),
            'screenshot' => $screenshotPath,
            'status' => PaymentTransaction::STATUS_PENDING,
        ]);

        $financialTransactions->recordFromPaymentTransaction($transaction, pending: true);

        $order->forceFill([
            'payment_method' => 'bank_transfer',
            'payment_status' => 'pending',
            'bank_name' => $bank->displayName(),
            'payment_screenshot' => $screenshotPath,
        ])->save();

        return back()->with('success', 'Payment screenshot submitted. We will review it shortly.');
    }

    public function transactions(): View
    {
        $orderIds = $this->userOrders()->pluck('id');

        $baseQuery = PaymentTransaction::query()
            ->with('order')
            ->whereIn('order_id', $orderIds);

        return view('pages.account.transactions', [
            'transactions' => (clone $baseQuery)->latest()->simplePaginate(15),
            'transactionsCount' => (clone $baseQuery)->count(),
            'totalSpent' => (clone $baseQuery)->where('status', PaymentTransaction::STATUS_APPROVED)->sum('amount'),
        ]);
    }

    public function ledger(): View
    {
        $user = auth()->user();

        return view('pages.account.ledger', [
            'entries' => $this->ledger->entriesForUser($user),
            'totals' => $this->ledger->totalsForUser($user),
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
