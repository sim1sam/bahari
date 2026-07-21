<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentBank;
use App\Models\PaymentTransaction;
use App\Services\FinancialTransactionService;
use App\Services\MediaStorageService;
use App\Services\SiteSettingsService;
use App\Support\ShippingZone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CustomOrderController extends Controller
{
    public function __construct(
        private MediaStorageService $media,
        private SiteSettingsService $siteSettings,
        private FinancialTransactionService $financialTransactions,
    ) {}

    public function create(): View
    {
        $user = auth()->user();
        $addresses = $user->addresses()
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->get();
        $selectedAddress = $addresses->firstWhere('is_default') ?? $addresses->first();

        return view('pages.account.custom-order', [
            'banks' => PaymentBank::activeForCheckout(),
            'addresses' => $addresses,
            'addressPayload' => $addresses->map(fn ($address) => [
                'id' => $address->id,
                'name' => $address->recipient_name,
                'phone' => $address->phone,
                'address' => $address->address_line,
            ])->values(),
            'selectedAddress' => $selectedAddress,
            'addressTypes' => CustomerAddress::types(),
            'shippingZones' => ShippingZone::labels(),
            'shippingFeeInside' => $this->siteSettings->shippingFeeInsideDhaka(),
            'shippingFeeOutside' => $this->siteSettings->shippingFeeOutsideDhaka(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'items' => collect($request->input('items', []))->map(function (array $item) {
                if (blank($item['product_link'] ?? null)) {
                    $item['product_link'] = null;
                }

                return $item;
            })->all(),
        ]);

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:200',
            'items.*.brand' => 'nullable|string|max:150',
            'items.*.product_link' => 'nullable|string|max:500',
            'items.*.size' => 'nullable|string|max:50',
            'items.*.image_file' => 'nullable|image|max:5120',
            'items.*.quantity' => 'required|integer|min:1|max:9999',
            'items.*.unit_price' => 'required|numeric|min:0',
            'name' => 'required|string|max:200',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'address_mode' => 'nullable|in:existing,new',
            'address_id' => 'nullable|integer',
            'address_type' => 'nullable|in:home,office,other',
            'address_label' => 'nullable|string|max:100',
            'save_address' => 'nullable|boolean',
            'make_default' => 'nullable|boolean',
            'shipping_zone' => 'required|in:inside_dhaka,outside_dhaka',
            'payment_mode' => 'required|in:cod,manual',
            'bank_id' => 'required_if:payment_mode,manual|nullable|integer|exists:payment_banks,id',
            'payment_amount' => 'required_if:payment_mode,manual|nullable|numeric|min:0',
            'payment_screenshot' => 'required_if:payment_mode,manual|nullable|image|max:5120',
            'notes' => 'nullable|string|max:1000',
        ]);

        $rawItems = $request->input('items', []);
        $items = collect($rawItems)->map(function (array $item, int $index) use ($request) {
            $imagePath = null;

            if ($request->hasFile("items.$index.image_file")) {
                $imagePath = $this->media->storeUpload(
                    $request->file("items.$index.image_file"),
                    'orders/custom-items',
                    field: "items.$index.image_file"
                );
            }

            return [
                'name' => $item['name'] ?? '',
                'brand' => filled($item['brand'] ?? null) ? trim((string) $item['brand']) : null,
                'product_link' => $item['product_link'] ?? null,
                'size' => $item['size'] ?? null,
                'image' => $imagePath,
                'quantity' => (int) ($item['quantity'] ?? 1),
                'unit_price' => round((float) ($item['unit_price'] ?? 0), 2),
            ];
        })->filter(fn ($item) => $item['name'] !== '')->values();

        if ($items->isEmpty()) {
            return back()->withInput()->withErrors(['items' => 'Add at least one product.']);
        }

        $user = auth()->user();
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
                'city' => '',
                'zip' => '',
                'is_default' => $makeDefault,
            ]);
        }

        $subtotal = $items->sum(fn ($item) => $item['quantity'] * $item['unit_price']);
        $shippingZone = $validated['shipping_zone'];
        $shipping = $this->siteSettings->shippingFeeForZone($shippingZone);
        $isManual = $validated['payment_mode'] === 'manual';
        $total = round($subtotal + $shipping, 2);

        $bank = null;
        $screenshotPath = null;

        if ($isManual) {
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

        $orderNumber = $this->siteSettings->generateOrderNumber(custom: true);
        $paymentAmount = $isManual
            ? round((float) $validated['payment_amount'], 2)
            : $total;

        $order = DB::transaction(function () use (
            $validated,
            $items,
            $subtotal,
            $shipping,
            $shippingZone,
            $total,
            $bank,
            $screenshotPath,
            $user,
            $orderNumber,
            $isManual,
            $paymentAmount,
        ) {
            $chargeSplit = $isManual && $bank
                ? $this->financialTransactions->splitFromTotal($total, $paymentAmount, $bank)
                : [
                    'base_amount' => $paymentAmount,
                    'bank_charge_percent' => 0,
                    'bank_charge_amount' => 0,
                    'total_amount' => $paymentAmount,
                ];

            $order = Order::create([
                'user_id' => $user->id,
                'number' => $orderNumber,
                'order_type' => 'custom',
                'customer_name' => $validated['name'],
                'customer_email' => $user->email,
                'customer_phone' => $validated['phone'],
                'address' => $validated['address'],
                'city' => null,
                'zip' => null,
                'payment_method' => $isManual ? 'bank_transfer' : 'cod',
                'bank_name' => $bank?->displayName(),
                'payment_screenshot' => $screenshotPath,
                'notes' => $validated['notes'] ?? null,
                'subtotal' => $subtotal,
                'discount' => 0,
                'shipping' => $shipping,
                'shipping_zone' => $shippingZone,
                'total' => $total,
                'status' => 'pending',
                'payment_status' => $isManual ? 'pending' : 'due',
                'amount_paid' => 0,
            ]);

            foreach ($items as $index => $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_slug' => 'custom-'.Str::slug($item['name']).'-'.$index,
                    'product_name' => $item['name'],
                    'brand' => $item['brand'],
                    'product_link' => $item['product_link'],
                    'image' => $item['image'],
                    'size' => $item['size'] ?: null,
                    'quantity' => $item['quantity'],
                    'price' => $item['unit_price'],
                ]);
            }

            if ($isManual && $screenshotPath) {
                $paymentTransaction = PaymentTransaction::create([
                    'order_id' => $order->id,
                    'user_id' => $user->id,
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

        $message = $isManual
            ? 'Custom order submitted! Your payment screenshot is pending admin review. Order #'.$orderNumber
            : 'Custom order submitted successfully! Order #'.$orderNumber;

        return redirect()
            ->route('account.orders.show', $order)
            ->with('success', $message);
    }
}
