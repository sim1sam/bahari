@extends('layouts.admin')

@section('title', 'Order API Transfer Setting')
@section('page_title', 'Order API Transfer Setting')

@section('content')
    <div class="row">
        <div class="col-lg-7">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Transfer Site</h3>
                </div>
                <form action="{{ route('admin.orders.transfer-settings.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <p class="text-muted small">
                            Only one website can be configured. When an order status changes to Processing, the full order payload will be sent to this site.
                        </p>
                        <div class="form-group">
                            <label>Site Name</label>
                            <input type="text" name="site_name" class="form-control @error('site_name') is-invalid @enderror" value="{{ old('site_name', $setting->site_name) }}" placeholder="Second Store">
                            @error('site_name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label>Domain *</label>
                            <input type="url" name="domain" class="form-control @error('domain') is-invalid @enderror" value="{{ old('domain', $setting->domain) }}" placeholder="https://example.com">
                            @error('domain')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label>Endpoint Path *</label>
                            <input type="text" name="endpoint_path" class="form-control @error('endpoint_path') is-invalid @enderror" value="{{ old('endpoint_path', $setting->endpoint_path ?: '/api/orders/import') }}" required>
                            <small class="form-text text-muted">Final URL: {{ rtrim($setting->domain ?? 'https://example.com', '/') }}{{ $setting->endpoint_path ?: '/api/orders/import' }}</small>
                            @error('endpoint_path')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label>API Key *</label>
                            <div class="input-group">
                                <input type="text" name="api_key" class="form-control @error('api_key') is-invalid @enderror" value="{{ old('api_key', $setting->api_key) }}">
                                <div class="input-group-append">
                                    <button type="submit" form="generate-api-key-form" class="btn btn-outline-secondary" onclick="return confirm('Generate a new API key? Old key will stop working after save/use.')">Generate</button>
                                </div>
                            </div>
                            @error('api_key')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label>Access Token *</label>
                            <textarea name="access_token" class="form-control @error('access_token') is-invalid @enderror" rows="4">{{ old('access_token', $setting->access_token) }}</textarea>
                            <button type="submit" form="generate-access-token-form" class="btn btn-sm btn-outline-secondary mt-2" onclick="return confirm('Generate a new access token? Old token will stop working after save/use.')">Generate Access Token</button>
                            @error('access_token')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" @checked(old('is_active', $setting->is_active))>
                            <label class="custom-control-label" for="is_active">Enable order transfer</label>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Save Setting</button>
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-default">Back to Orders</a>
                    </div>
                </form>
            </div>

            <form id="generate-api-key-form" action="{{ route('admin.orders.transfer-settings.generate') }}" method="POST" class="d-none">
                @csrf
                <input type="hidden" name="type" value="api_key">
            </form>
            <form id="generate-access-token-form" action="{{ route('admin.orders.transfer-settings.generate') }}" method="POST" class="d-none">
                @csrf
                <input type="hidden" name="type" value="access_token">
            </form>
        </div>
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Receiver Requirements</h3></div>
                <div class="card-body">
                    <p class="text-muted">The receiver website should accept a JSON POST request.</p>
                    <p><strong>Headers sent:</strong></p>
                    <ul class="small">
                        <li><code>Accept: application/json</code></li>
                        <li><code>X-API-Key: your API key</code></li>
                        <li><code>Authorization: Bearer your_access_token</code></li>
                    </ul>
                    <p><strong>Payload includes:</strong> order, items, and payments.</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3 class="card-title">Payload Field Names</h3></div>
                <div class="card-body small">
                    <p class="mb-1"><strong>order:</strong></p>
                    <p><code>number, status, type, customer_name, customer_email, customer_phone, address, city, zip, payment_method, payment_status, reference_code, bank_name, notes, coupon_code, subtotal, discount, shipping, total, amount_paid, created_at</code></p>
                    <p class="mb-1"><strong>items[]:</strong></p>
                    <p><code>product_slug, product_name, product_link, image, size, color, quantity, price</code></p>
                    <p class="mb-1"><strong>payments[]:</strong></p>
                    <p class="mb-0"><code>amount, payment_method, bank_name, notes, created_at</code></p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Laravel Receiver Script Example</h3></div>
        <div class="card-body">
            <p class="text-muted small">Add this on the other website. Keep the API key and token same as this setting page.</p>
<pre class="bg-dark text-white rounded p-3 mb-0" style="white-space: pre-wrap;"><code>@verbatim
// routes/api.php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/orders/import', function (Request $request) {
    $apiKey = 'YOUR_API_KEY';
    $accessToken = 'YOUR_ACCESS_TOKEN';

    if ($request->header('X-API-Key') !== $apiKey) {
        return response()->json(['message' => 'Invalid API key'], 401);
    }

    if ($request->bearerToken() !== $accessToken) {
        return response()->json(['message' => 'Invalid access token'], 401);
    }

    $data = $request->validate([
        'order' => 'required|array',
        'order.number' => 'required|string|max:100',
        'order.status' => 'nullable|string|max:50',
        'order.type' => 'nullable|string|max:50',
        'order.customer_name' => 'nullable|string|max:200',
        'order.customer_email' => 'nullable|email|max:150',
        'order.customer_phone' => 'nullable|string|max:50',
        'order.address' => 'nullable|string|max:255',
        'order.city' => 'nullable|string|max:100',
        'order.zip' => 'nullable|string|max:50',
        'order.payment_method' => 'nullable|string|max:50',
        'order.payment_status' => 'nullable|string|max:50',
        'order.reference_code' => 'nullable|string|max:100',
        'order.bank_name' => 'nullable|string|max:100',
        'order.notes' => 'nullable|string|max:2000',
        'order.coupon_code' => 'nullable|string|max:30',
        'order.subtotal' => 'nullable|numeric',
        'order.discount' => 'nullable|numeric',
        'order.shipping' => 'nullable|numeric',
        'order.total' => 'nullable|numeric',
        'order.amount_paid' => 'nullable|numeric',
        'order.created_at' => 'nullable|string',
        'items' => 'nullable|array',
        'items.*.product_slug' => 'nullable|string|max:255',
        'items.*.product_name' => 'nullable|string|max:255',
        'items.*.product_link' => 'nullable|string|max:500',
        'items.*.image' => 'nullable|string|max:500',
        'items.*.size' => 'nullable|string|max:50',
        'items.*.color' => 'nullable|string|max:50',
        'items.*.quantity' => 'nullable|integer|min:1',
        'items.*.price' => 'nullable|numeric|min:0',
        'payments' => 'nullable|array',
        'payments.*.amount' => 'nullable|numeric|min:0',
        'payments.*.payment_method' => 'nullable|string|max:50',
        'payments.*.bank_name' => 'nullable|string|max:100',
        'payments.*.notes' => 'nullable|string|max:500',
        'payments.*.created_at' => 'nullable|string',
    ]);

    // Example: save raw payload first, then map fields to your own tables.
    // DB::table('received_orders')->updateOrInsert(
    //     ['number' => $data['order']['number']],
    //     ['payload' => json_encode($data), 'updated_at' => now(), 'created_at' => now()]
    // );

    return response()->json([
        'message' => 'Order received',
        'order_number' => $data['order']['number'],
    ]);
});
@endverbatim</code></pre>
        </div>
    </div>
@endsection
