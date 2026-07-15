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
use App\Services\FinancialTransactionService;
use App\Services\MediaStorageService;
use App\Services\MetaConversionsApiService;
use App\Services\SiteSettingsService;
use App\Services\SslCommerzService;
use App\Support\ShippingZone;
use App\Support\TrackingPayload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService $cart,
        private MediaStorageService $media,
        private SiteSettingsService $siteSettings,
        private SslCommerzService $sslCommerz,
        private FinancialTransactionService $financialTransactions,
        private MetaConversionsApiService $metaCapi,
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

        $items = array_values($this->cart->items());
        $eventId = $this->metaCapi->newEventId();
        $trackingCart = TrackingPayload::fromCartItems(
            $items,
            (float) $this->cart->total(),
            $this->cart->coupon()['code'] ?? null,
            (float) $this->cart->discount(),
            (float) $this->cart->shipping(),
        );
        $trackingCart['shipping_tier'] = $this->cart->shippingZone();
        $trackingCart['payment_type'] = old('payment');

        $this->metaCapi->send(
            'InitiateCheckout',
            $eventId,
            [
                'content_ids' => $trackingCart['meta_content_ids'],
                'content_type' => 'product',
                'contents' => $trackingCart['meta_contents'],
                'currency' => $trackingCart['currency'],
                'value' => $trackingCart['total_value'],
                'num_items' => $trackingCart['total_quantity'],
            ],
            $this->metaCapi->userDataFromCustomer([
                'name' => $selectedAddress?->recipient_name ?? $user->name,
                'email' => $user->email,
                'phone' => $selectedAddress?->phone,
                'address' => $selectedAddress?->address_line,
                'city' => $selectedAddress?->city,
                'zip' => $selectedAddress?->zip,
            ], $user->id),
            route('checkout.index'),
        );

        return view('pages.checkout.index', [
            'items' => $items,
            'subtotal' => $this->cart->subtotal(),
            'shipping' => $this->cart->shipping(),
            'discount' => $this->cart->discount(),
            'coupon' => $this->cart->coupon(),
            'total' => $this->cart->total(),
            'shippingZone' => $this->cart->shippingZone(),
            'shippingZones' => ShippingZone::labels(),
            'shippingFeeInside' => $this->siteSettings->shippingFeeInsideDhaka(),
            'shippingFeeOutside' => $this->siteSettings->shippingFeeOutsideDhaka(),
            'freeShippingAt' => $this->siteSettings->freeShippingThreshold(),
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
            'trackingEventId' => $eventId,
            'trackingCart' => $trackingCart,
            'trackingUser' => TrackingPayload::userFields([
                'name' => $selectedAddress?->recipient_name ?? $user->name,
                'email' => $user->email,
                'phone' => $selectedAddress?->phone,
                'address' => $selectedAddress?->address_line,
                'city' => $selectedAddress?->city,
                'zip' => $selectedAddress?->zip,
            ], $user->id),
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
            'shipping_zone' => 'required|in:inside_dhaka,outside_dhaka',
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

        $this->cart->setShippingZone($validated['shipping_zone']);

        $orderNumber = $this->siteSettings->generateOrderNumber();
        $items = array_values($this->cart->items());
        $subtotal = $this->cart->subtotal();
        $shipping = $this->cart->shipping();
        $discount = $this->cart->discount();
        $coupon = $this->cart->coupon();
        $total = $this->cart->total();
        $shippingZone = $validated['shipping_zone'];
        $isSslCommerz = $validated['payment'] === 'sslcommerz';
        $trackingEventId = $this->metaCapi->newEventId();

        $order = DB::transaction(function () use ($validated, $orderNumber, $items, $subtotal, $shipping, $shippingZone, $discount, $coupon, $total, $bank, $screenshotPath, $isSslCommerz, $trackingEventId) {
            $isBankTransfer = $validated['payment'] === 'bank_transfer';
            $paymentAmount = $isSslCommerz ? $total : round((float) $validated['payment_amount'], 2);
            $chargeSplit = $isBankTransfer && $bank
                ? $this->financialTransactions->splitFromTotal($total, $paymentAmount, $bank)
                : [
                    'base_amount' => $paymentAmount,
                    'bank_charge_percent' => 0,
                    'bank_charge_amount' => 0,
                    'total_amount' => $paymentAmount,
                ];

            $orderData = [
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
                'shipping_zone' => $shippingZone,
                'total' => $total,
                'coupon_code' => $coupon['code'] ?? null,
                'status' => 'pending',
                'payment_status' => $isSslCommerz || $isBankTransfer ? 'pending' : 'due',
                'amount_paid' => 0,
            ];

            if (Schema::hasColumn('orders', 'tracking_event_id')) {
                $orderData['tracking_event_id'] = $trackingEventId;
            }

            $order = Order::create($orderData);

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
                $paymentTransaction = PaymentTransaction::create([
                    'order_id' => $order->id,
                    'user_id' => Auth::id(),
                    'payment_bank_id' => $bank?->id,
                    'amount' => $chargeSplit['total_amount'],
                    'sale_amount' => $chargeSplit['base_amount'],
                    'bank_charge_percent' => $chargeSplit['bank_charge_percent'],
                    'bank_charge_amount' => $chargeSplit['bank_charge_amount'],
                    'bank_name' => $bank?->displayName(),
                    'screenshot' => $screenshotPath,
                    'status' => PaymentTransaction::STATUS_PENDING,
                ]);

                $this->financialTransactions->recordFromPaymentTransaction($paymentTransaction, pending: true);
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

        $this->sendPurchaseCapi($order, $validated, $items, $trackingEventId);

        session([
            'last_order' => [
                'number' => $orderNumber,
                'tracking_event_id' => $trackingEventId,
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

        $eventId = $order['tracking_event_id'] ?? $this->metaCapi->newEventId();
        $trackingPurchase = TrackingPayload::fromCartItems(
            $order['items'] ?? [],
            (float) ($order['total'] ?? 0),
            is_array($order['coupon'] ?? null) ? ($order['coupon']['code'] ?? null) : ($order['coupon'] ?? null),
            (float) ($order['discount'] ?? 0),
            (float) ($order['shipping'] ?? 0),
        );
        $trackingPurchase['transaction_id'] = $order['number'] ?? null;
        $trackingPurchase['order_id'] = $order['number'] ?? null;
        $trackingPurchase['order_name'] = $order['number'] ?? null;
        $trackingPurchase = array_merge(
            $trackingPurchase,
            TrackingPayload::userFields($order['customer'] ?? null, Auth::id())
        );

        return view('pages.checkout.success', [
            'order' => $order,
            'trackingEventId' => $eventId,
            'trackingPurchase' => $trackingPurchase,
        ]);
    }

    /** @param  array<string, mixed>  $customer
     *  @param  array<int, array<string, mixed>>  $items
     */
    private function sendPurchaseCapi(Order $order, array $customer, array $items, string $eventId): void
    {
        $payload = TrackingPayload::fromCartItems(
            $items,
            (float) $order->total,
            $order->coupon_code,
            (float) $order->discount,
            (float) $order->shipping,
        );

        $this->metaCapi->send(
            'Purchase',
            $eventId,
            [
                'content_ids' => $payload['meta_content_ids'],
                'content_type' => 'product',
                'contents' => $payload['meta_contents'],
                'currency' => $payload['currency'],
                'value' => (float) $order->total,
                'num_items' => $payload['total_quantity'],
                'order_id' => $order->number,
            ],
            $this->metaCapi->userDataFromCustomer($customer, $order->user_id),
            route('order.success'),
        );
    }

    private function user(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }
}
