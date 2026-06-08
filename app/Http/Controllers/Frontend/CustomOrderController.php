<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\MediaStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CustomOrderController extends Controller
{
    public function __construct(private MediaStorageService $media) {}

    public function create(): View
    {
        return view('pages.account.custom-order', [
            'banks' => config('payment.banks', []),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $banks = array_keys(config('payment.banks', []));

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:200',
            'items.*.product_link' => 'nullable|url|max:500',
            'items.*.image_file' => 'nullable|image|max:5120',
            'items.*.quantity' => 'required|integer|min:1|max:9999',
            'items.*.unit_price' => 'required|numeric|min:0',
            'payment_mode' => 'required|in:cod,manual',
            'bank_name' => 'required_if:payment_mode,manual|nullable|string|in:'.implode(',', $banks),
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
                'product_link' => $item['product_link'] ?? null,
                'image' => $imagePath,
                'quantity' => (int) ($item['quantity'] ?? 1),
                'unit_price' => round((float) ($item['unit_price'] ?? 0), 2),
            ];
        })->filter(fn ($item) => $item['name'] !== '')->values();

        if ($items->isEmpty()) {
            return back()->withInput()->withErrors(['items' => 'Add at least one product.']);
        }

        $subtotal = $items->sum(fn ($item) => $item['quantity'] * $item['unit_price']);
        $total = $validated['payment_mode'] === 'manual'
            ? round((float) $validated['payment_amount'], 2)
            : round($subtotal, 2);

        $screenshotPath = null;
        if ($validated['payment_mode'] === 'manual' && $request->hasFile('payment_screenshot')) {
            $screenshotPath = $this->media->storeUpload(
                $request->file('payment_screenshot'),
                'orders/payments',
                field: 'payment_screenshot'
            );
        }

        $user = auth()->user();
        $orderNumber = 'LW-C'.strtoupper(substr(uniqid(), -7));
        $bankLabel = $validated['payment_mode'] === 'manual'
            ? (config('payment.banks')[$validated['bank_name']] ?? $validated['bank_name'])
            : null;

        $order = DB::transaction(function () use ($validated, $items, $subtotal, $total, $screenshotPath, $user, $orderNumber, $bankLabel) {
            $order = Order::create([
                'user_id' => $user->id,
                'number' => $orderNumber,
                'order_type' => 'custom',
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'customer_phone' => $user->phone ?? null,
                'payment_method' => $validated['payment_mode'] === 'manual' ? 'bank_transfer' : 'cod',
                'bank_name' => $bankLabel,
                'payment_screenshot' => $screenshotPath,
                'notes' => $validated['notes'] ?? null,
                'subtotal' => $subtotal,
                'discount' => 0,
                'shipping' => 0,
                'total' => $total,
                'status' => 'pending',
            ]);

            foreach ($items as $index => $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_slug' => 'custom-'.Str::slug($item['name']).'-'.$index,
                    'product_name' => $item['name'],
                    'product_link' => $item['product_link'],
                    'image' => $item['image'],
                    'quantity' => $item['quantity'],
                    'price' => $item['unit_price'],
                ]);
            }

            return $order;
        });

        return redirect()
            ->route('account.orders.show', $order)
            ->with('success', 'Custom order submitted successfully! Order #'.$orderNumber);
    }
}
