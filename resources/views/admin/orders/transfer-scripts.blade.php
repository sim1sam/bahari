@extends('layouts.admin')

@section('title', 'Order API Scripts')
@section('page_title', 'Order API Scripts')

@section('content')
    @php
        $mainStatusUrl = url('/api/orders');
        $mainStatusUrlAlt = url('/api/orders/status-update');
    @endphp

    <div class="mb-3 d-flex flex-wrap gap-2">
        <a href="{{ route('admin.orders.transfer-settings.edit') }}" class="btn btn-default btn-sm">Back to Transfer Setting</a>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-default btn-sm">Back to Orders</a>
    </div>

    <div class="alert alert-info">
        <strong>Same Order Transfer API</strong> —
        Transfer out and status back use the same <code>X-API-Key</code> + Bearer token from Transfer Settings.
        Status callback URL on this site: <code>{{ $mainStatusUrl }}</code>
        (also <code>{{ $mainStatusUrlAlt }}</code>).
    </div>

    <div class="card mb-3">
        <div class="card-header"><h3 class="card-title">1) Other Site: Receive Order Data</h3></div>
        <div class="card-body">
            <p class="text-muted small mb-2">
                Add this to the other website <code>routes/api.php</code>.
                Save <code>order.number</code> (e.g. <code>BF-A5045D18</code>) — you must send that same value back as <code>order_number</code> when status changes.
            </p>
<pre class="bg-dark text-white rounded p-3 mb-0" style="white-space: pre-wrap;"><code>@verbatim
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

Route::post('/orders/import', function (Request $request) {
    $apiKey = 'PASTE_API_KEY_HERE';
    $accessToken = 'PASTE_ACCESS_TOKEN_HERE';

    if ($request->header('X-API-Key') !== $apiKey) {
        return response()->json(['message' => 'Invalid API key'], 401);
    }

    if ($request->bearerToken() !== $accessToken) {
        return response()->json(['message' => 'Invalid access token'], 401);
    }

    $data = $request->validate([
        'status_callback_url' => 'nullable|string',
        'order' => 'required|array',
        'order.number' => 'required|string|max:100',
        'order.order_number' => 'nullable|string|max:100',
        'order.source_order_number' => 'nullable|string|max:100',
        'order.status' => 'nullable|string',
        'order.type' => 'nullable|string',
        'order.customer_name' => 'nullable|string',
        'order.customer_email' => 'nullable|string',
        'order.customer_phone' => 'nullable|string',
        'order.address' => 'nullable|string',
        'order.city' => 'nullable|string',
        'order.zip' => 'nullable|string',
        'order.payment_method' => 'nullable|string',
        'order.payment_status' => 'nullable|string',
        'order.reference_code' => 'nullable|string',
        'order.bank_name' => 'nullable|string',
        'order.notes' => 'nullable|string',
        'order.coupon_code' => 'nullable|string',
        'order.subtotal' => 'nullable|numeric',
        'order.discount' => 'nullable|numeric',
        'order.shipping' => 'nullable|numeric',
        'order.total' => 'nullable|numeric',
        'order.amount_paid' => 'nullable|numeric',
        'order.created_at' => 'nullable|string',
        'shipping' => 'nullable|array',
        'items' => 'nullable|array',
        'items.*.product_slug' => 'nullable|string',
        'items.*.product_name' => 'nullable|string',
        'items.*.brand' => 'nullable|string',
        'items.*.product_link' => 'nullable|string',
        'items.*.image' => 'nullable|string',
        'items.*.size' => 'nullable|string',
        'items.*.color' => 'nullable|string',
        'items.*.quantity' => 'nullable|integer',
        'items.*.price' => 'nullable|numeric',
        'payments' => 'nullable|array',
        'payments.*.amount' => 'nullable|numeric',
        'payments.*.payment_method' => 'nullable|string',
        'payments.*.bank_name' => 'nullable|string',
        'payments.*.notes' => 'nullable|string',
        'payments.*.created_at' => 'nullable|string',
    ]);

    $sourceNumber = $data['order']['number'];
    $callbackUrl = $data['status_callback_url'] ?? null;

    // Save into your own tables. Keep sourceNumber for status sync.
    DB::table('received_orders')->updateOrInsert(
        ['number' => $sourceNumber],
        [
            'payload' => json_encode($data),
            'status_callback_url' => $callbackUrl,
            'admin_status' => 'pending',
            'updated_at' => now(),
            'created_at' => now(),
        ]
    );

    // Optional: return admin_status in same API response (main site will apply it)
    return response()->json([
        'message' => 'Order received',
        'order_number' => $sourceNumber,
        'admin_status' => 'pending',
    ]);
});
@endverbatim</code></pre>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header"><h3 class="card-title">2) Other Site: Send Status Back (same API key/token)</h3></div>
        <div class="card-body">
            <p class="text-muted small mb-2">
                Call this whenever status changes on the receiver site.
                Use original website order number (<code>BF-...</code>), not only receiver display number (<code>OR-BF-...</code>).
            </p>
            <p class="small mb-2">
                <strong>admin_status values:</strong>
                <code>pending</code>,
                <code>confirmed</code>,
                <code>kolkata_warehouse</code>,
                <code>shipped</code>,
                <code>dhaka_warehouse</code>,
                <code>ready_for_delivery</code>,
                <code>dispatched</code>,
                <code>delivered</code>,
                <code>cancelled</code>
            </p>
<pre class="bg-dark text-white rounded p-3 mb-0" style="white-space: pre-wrap;"><code>use Illuminate\Support\Facades\Http;

Http::acceptJson()
    ->withHeaders([
        'X-API-Key' => 'PASTE_API_KEY_HERE',
        'Authorization' => 'Bearer PASTE_ACCESS_TOKEN_HERE',
    ])
    ->post('{{ $mainStatusUrl }}', [
        // IMPORTANT: original website number (BF-...), not OR-BF-...
        'order_number' => 'BF-A5045D18',
        'admin_status' => 'kolkata_warehouse',
        // optional:
        // 'payment_status' => 'paid',
        // 'amount_paid' => 2200,
        // 'message' => 'Arrived at Kolkata warehouse',
    ]);
// Same admin_status label is shown to customers + admin on this site.</code></pre>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header"><h3 class="card-title">3) Other Site: Auto-push status when order status changes (Observer)</h3></div>
        <div class="card-body">
            <p class="text-muted small mb-2">
                Example: when your local order <code>admin_status</code> changes, push to this site automatically.
                Adjust model/table field names to match your receiver app.
            </p>
<pre class="bg-dark text-white rounded p-3 mb-0" style="white-space: pre-wrap;"><code>@verbatim
// app/Observers/OrderStatusObserver.php
namespace App\Observers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OrderStatusObserver
{
    public function updated($order): void
    {
        if (! $order->wasChanged('admin_status') && ! $order->wasChanged('status')) {
            return;
        }

        // Prefer original website number saved at import time
        $orderNumber = $order->order_reference
            ?? $order->source_order_number
            ?? $order->number;

        $adminStatus = $order->admin_status ?? $order->status;

        $response = Http::acceptJson()
            ->withHeaders([
                'X-API-Key' => env('MAIN_SITE_API_KEY'),
                'Authorization' => 'Bearer '.env('MAIN_SITE_ACCESS_TOKEN'),
            ])
            ->post(env('MAIN_SITE_STATUS_URL'), [
                'order_number' => $orderNumber,
                'admin_status' => $adminStatus,
                'message' => 'Synced from receiver',
            ]);

        if (! $response->successful()) {
            Log::warning('Status sync failed', [
                'order_number' => $orderNumber,
                'body' => $response->body(),
            ]);
        }
    }
}
@endverbatim</code></pre>
            <p class="small text-muted mt-2 mb-0">
                <code>.env</code> on receiver:
                <code>MAIN_SITE_STATUS_URL={{ $mainStatusUrl }}</code>,
                <code>MAIN_SITE_API_KEY=...</code>,
                <code>MAIN_SITE_ACCESS_TOKEN=...</code>
            </p>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Quick test (curl)</h3></div>
        <div class="card-body">
<pre class="bg-dark text-white rounded p-3 mb-0" style="white-space: pre-wrap;"><code>curl -X POST "{{ $mainStatusUrl }}" \
  -H "Accept: application/json" \
  -H "X-API-Key: PASTE_API_KEY_HERE" \
  -H "Authorization: Bearer PASTE_ACCESS_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d "{\"order_number\":\"BF-A5045D18\",\"admin_status\":\"kolkata_warehouse\"}"</code></pre>
        </div>
    </div>
@endsection
