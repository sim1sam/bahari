<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentBank;
use App\Models\PaymentTransaction;
use App\Models\User;
use App\Services\CartService;
use App\Services\MediaStorageService;
use App\Services\SiteSettingsService;
use App\Services\SslCommerzService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService $cart,
        private MediaStorageService $media,
        private SiteSettingsService $siteSettings,
        private SslCommerzService $sslCommerz,
    ) {}

    public function index(): View|RedirectResponse
    {
        if (empty($this->cart->items())) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $user = $this->user();
        $addresses = $user->addresses()->latest('is_default')->latest()->get();
        $selectedAddress = old('address_id')
            ? $addresses->firstWhere('id', (int) old('address_id'))
            : $user->defaultAddress();

        return view('pages.checkout.index', [
            'items' => $this->cart->items(),
            'subtotal' => $this->cart->subtotal(),
            'shipping' => $this->cart->shipping(),
            'discount' => $this->cart->discount(),
            'coupon' => $this->cart->coupon(),
            'total' => $this->cart->total(),
            'addresses' => $addresses,
            'selectedAddress' => $selectedAddress,
            'addressTypes' => CustomerAddress::types(),
            'banks' => PaymentBank::activeForCheckout(),
            'sslCommerzEnabled' => $this->siteSettings->sslCommerzConfigured(),
            'checkoutDetails' => [
                'name' => $selectedAddress?->recipient_name ?? $user->name,
                'email' => $user->email,
                'phone' => $selectedAddress?->phone,
                'address' => $selectedAddress?->address_line,
                'city' => $selectedAddress?->city,
                'zip' => $selectedAddress?->zip,
            ],
        ]);
    }

    public function applyCoupon(Request $request): RedirectResponse
    {
        if (empty($this->cart->items())) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $validated = $request->validate([
            'code' => 'required|string|max:30',
        ]);

        if ($error = $this->cart->applyCoupon($validated['code'])) {
            return back()->with('error', $error);
        }

        return back()->with('success', 'Coupon applied!');
    }

    public function removeCoupon(): RedirectResponse
    {
        $this->cart->removeCoupon();

        return back()->with('success', 'Coupon removed.');
    }

    public function store(Request $request): RedirectResponse
    {
        if (empty($this->cart->items())) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $paymentMethods = ['cod', 'bank_transfer'];
        if ($this->siteSettings->sslCommerzConfigured()) {
            $paymentMethods[] = 'sslcommerz';
        }

        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'email' => 'required|email|max:150',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'zip' => 'required|string|max:20',
            'payment' => 'required|in:'.implode(',', $paymentMethods),
            'payment_amount' => 'required|numeric|min:0',
            'bank_id' => 'required_if:payment,bank_transfer|nullable|integer|exists:payment_banks,id',
            'payment_screenshot' => 'required_if:payment,bank_transfer|nullable|image|max:5120',
            'address_mode' => 'nullable|in:existing,new',
            'address_id' => 'nullable|integer',
            'address_type' => 'nullable|in:home,office,other',
            'address_label' => 'nullable|string|max:100',
            'save_address' => 'nullable|boolean',
            'make_default' => 'nullable|boolean',
        ]);

        $bank = null;
        $screenshotPath = null;

        if ($validated['payment'] === 'bank_transfer') {
            $bank = PaymentBank::query()
                ->where('is_active', true)
                ->findOrFail($validated['bank_id']);

            if ($request->hasFile('payment_screenshot')) {
                $screenshotPath = $this->media->storeUpload(
                    $request->file('payment_screenshot'),
                    'orders/payments',
                    field: 'payment_screenshot'
                );
            }
        }

        $user = $this->user();
        $selectedAddress = ! empty($validated['address_id'])
            ? $user->addresses()->whereKey($validated['address_id'])->first()
            : null;
        $isNewAddress = ($validated['address_mode'] ?? 'existing') === 'new' || ! $selectedAddress;

        if ($isNewAddress && ($request->boolean('save_address') || ! $user->addresses()->exists())) {
            $makeDefault = $request->boolean('make_default') || ! $user->addresses()->exists();

            if ($makeDefault) {
                $user->addresses()->update(['is_default' => false]);
            }

            $user->addresses()->create([
                'type' => $validated['address_type'] ?? CustomerAddress::TYPE_HOME,
                'label' => $validated['address_label'] ?? null,
                'recipient_name' => $validated['name'],
                'phone' => $validated['phone'],
                'address_line' => $validated['address'],
                'city' => $validated['city'],
                'zip' => $validated['zip'],
                'is_default' => $makeDefault,
            ]);
        }

        $orderNumber = 'LW-'.strtoupper(substr(uniqid(), -8));
        $items = array_values($this->cart->items());
        $subtotal = $this->cart->subtotal();
        $shipping = $this->cart->shipping();
        $discount = $this->cart->discount();
        $coupon = $this->cart->coupon();
        $total = $this->cart->total();
        $isSslCommerz = $validated['payment'] === 'sslcommerz';

        $order = DB::transaction(function () use ($validated, $orderNumber, $items, $subtotal, $shipping, $discount, $coupon, $total, $bank, $screenshotPath, $isSslCommerz) {
            $isBankTransfer = $validated['payment'] === 'bank_transfer';
            $paymentAmount = $isSslCommerz ? $total : round((float) $validated['payment_amount'], 2);

            $order = Order::create([
                'user_id' => Auth::id(),
                'number' => $orderNumber,
                'customer_name' => $validated['name'],
                'customer_email' => $validated['email'],
                'customer_phone' => $validated['phone'],
                'address' => $validated['address'],
                'city' => $validated['city'],
                'zip' => $validated['zip'],
                'payment_method' => $validated['payment'],
                'bank_name' => $bank?->displayName(),
                'payment_screenshot' => $screenshotPath,
                'notes' => match (true) {
                    $isSslCommerz => 'Awaiting SSLCommerz payment',
                    $isBankTransfer => null,
                    default => 'COD amount confirmed: '.money($paymentAmount),
                },
                'subtotal' => $subtotal,
                'discount' => $discount,
                'shipping' => $shipping,
                'total' => $total,
                'coupon_code' => $coupon['code'] ?? null,
                'status' => 'pending',
                'payment_status' => $isSslCommerz || $isBankTransfer ? 'pending' : 'due',
                'amount_paid' => 0,
            ]);

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_slug' => $item['slug'],
                    'product_name' => $item['name'],
                    'image' => $item['image'],
                    'size' => $item['size'],
                    'color' => $item['color'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }

            if ($isBankTransfer && $screenshotPath) {
                PaymentTransaction::create([
                    'order_id' => $order->id,
                    'user_id' => Auth::id(),
                    'amount' => $paymentAmount,
                    'bank_name' => $bank?->displayName(),
                    'screenshot' => $screenshotPath,
                    'status' => PaymentTransaction::STATUS_PENDING,
                ]);
            }

            return $order;
        });

        if ($isSslCommerz) {
            try {
                $gatewayUrl = $this->sslCommerz->initiatePayment($order);
            } catch (\Throwable $e) {
                return back()->withInput()->with('error', $e->getMessage());
            }

            $this->cart->clear();

            return redirect()->away($gatewayUrl);
        }

        session([
            'last_order' => [
                'number' => $orderNumber,
                'customer' => $validated,
                'items' => $items,
                'subtotal' => $subtotal,
                'shipping' => $shipping,
                'discount' => $discount,
                'coupon' => $coupon,
                'total' => $total,
                'placed_at' => now()->toDateTimeString(),
            ],
        ]);

        $this->cart->clear();

        return redirect()->route('order.success');
    }

    public function success(): View|RedirectResponse
    {
        $order = session('last_order');

        if (! $order) {
            return redirect()->route('home');
        }

        return view('pages.checkout.success', compact('order'));
    }

    private function user(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }
}
