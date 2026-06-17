@extends('layouts.admin')

@section('title', 'Order API Scripts')
@section('page_title', 'Order API Scripts')

@section('content')
    <div class="mb-3">
        <a href="{{ route('admin.orders.transfer-settings.edit') }}" class="btn btn-default btn-sm">Back to Transfer Setting</a>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-default btn-sm">Back to Orders</a>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Other Site: Receive Order Data</h3></div>
        <div class="card-body">
            <p class="text-muted small">Add this to the other website <code>routes/api.php</code>. Create a <code>received_orders</code> table or change the save section to your own order tables.</p>
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
        'order' => 'required|array',
        'order.number' => 'required|string|max:100',
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

        'items' => 'nullable|array',
        'items.*.product_slug' => 'nullable|string',
        'items.*.product_name' => 'nullable|string',
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

    DB::table('received_orders')->updateOrInsert(
        ['number' => $data['order']['number']],
        [
            'payload' => json_encode($data),
            'updated_at' => now(),
            'created_at' => now(),
        ]
    );

    return response()->json([
        'message' => 'Order received',
        'order_number' => $data['order']['number'],
    ]);
});
@endverbatim</code></pre>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Other Site: Send Status Back</h3></div>
        <div class="card-body">
            <p class="text-muted small">Use this on the other website when its order status changes.</p>
<pre class="bg-dark text-white rounded p-3 mb-0" style="white-space: pre-wrap;"><code>@verbatim
use Illuminate\Support\Facades\Http;

Http::acceptJson()
    ->withHeaders([
        'X-API-Key' => 'PASTE_API_KEY_HERE',
        'Authorization' => 'Bearer PASTE_ACCESS_TOKEN_HERE',
    ])
    ->post('https://MAIN-SITE-DOMAIN.com/api/orders/status-update', [
        'order_number' => 'LW-12345678',
        'status' => 'shipped', // pending, processing, shipped, completed, cancelled
        'payment_status' => 'paid', // optional: pending, paid, partial, due
        'amount_paid' => 2200, // optional
        'message' => 'Order shipped from second site', // optional
    ]);
@endverbatim</code></pre>
        </div>
    </div>
@endsection
