@extends('layouts.admin')

@section('title', 'Order API Settings')
@section('page_title', 'Order API Settings')

@section('content')
    @php
        $endpointUrl = $setting->endpointUrl() ?? (rtrim($setting->domain ?? 'https://example.com', '/').($setting->endpoint_path ?: '/api/orders/import'));
        $hasApiKey = filled($setting->api_key);
        $hasToken = filled($setting->access_token);
        $isConfigured = $setting->isConfigured();
    @endphp

    <div class="transfer-settings-page">
        <section class="transfer-hero">
            <div>
                <span class="transfer-eyebrow">Order management</span>
                <h2>Order API Settings</h2>
                <p>Configure the remote site that receives orders when status changes to Processing.</p>
                <div class="transfer-hero-meta">
                    <span class="transfer-hero-chip">
                        <i class="fas fa-globe"></i> {{ $setting->site_name ?: 'Transfer site' }}
                    </span>
                    <span class="transfer-hero-chip">
                        <i class="fas fa-link"></i> {{ $endpointUrl }}
                    </span>
                </div>
            </div>
            <div class="transfer-hero-actions">
                <a href="{{ route('admin.orders.transfer-settings.scripts') }}" class="btn btn-primary">
                    <i class="fas fa-code mr-1"></i> View Scripts
                </a>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-info">
                    <i class="fas fa-list mr-1"></i> All Orders
                </a>
            </div>
        </section>

        <section class="row transfer-stats">
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="transfer-stat transfer-stat--status {{ $setting->is_active ? 'transfer-stat--active' : 'transfer-stat--inactive' }}">
                    <span class="transfer-stat-icon"><i class="fas fa-power-off"></i></span>
                    <div>
                        <div class="transfer-stat-value">{{ $setting->is_active ? 'Active' : 'Inactive' }}</div>
                        <div class="transfer-stat-label">Transfer Status</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="transfer-stat transfer-stat--endpoint">
                    <span class="transfer-stat-icon"><i class="fas fa-plug"></i></span>
                    <div>
                        <div class="transfer-stat-value">{{ filled($setting->domain) ? 'Set' : 'Missing' }}</div>
                        <div class="transfer-stat-label">Endpoint Domain</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="transfer-stat transfer-stat--key">
                    <span class="transfer-stat-icon"><i class="fas fa-key"></i></span>
                    <div>
                        <div class="transfer-stat-value">{{ $hasApiKey ? 'Set' : 'Missing' }}</div>
                        <div class="transfer-stat-label">API Key</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="transfer-stat transfer-stat--token">
                    <span class="transfer-stat-icon"><i class="fas fa-shield-alt"></i></span>
                    <div>
                        <div class="transfer-stat-value">{{ $hasToken ? 'Set' : 'Missing' }}</div>
                        <div class="transfer-stat-label">Access Token</div>
                    </div>
                </article>
            </div>
        </section>

        @unless ($isConfigured)
            <div class="transfer-alert transfer-alert--warning">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <strong>Setup incomplete</strong>
                    <span>Enable transfer and fill domain, API key, and access token before orders can be sent.</span>
                </div>
            </div>
        @endunless

        <div class="row">
            <div class="col-lg-7 mb-3">
                <div class="transfer-card">
                    <div class="transfer-card-head">
                        <div>
                            <h3 class="mb-0">Transfer Site</h3>
                            <p class="mb-0 text-muted">Only one website can be configured for outbound order transfer.</p>
                        </div>
                        <span class="transfer-ready-badge {{ $isConfigured ? 'transfer-ready-badge--ready' : 'transfer-ready-badge--pending' }}">
                            <i class="fas fa-circle"></i> {{ $isConfigured ? 'Ready to send' : 'Not ready' }}
                        </span>
                    </div>
                    <form action="{{ route('admin.orders.transfer-settings.update') }}" method="POST" id="transfer-settings-form">
                        @csrf
                        @method('PUT')
                        <div class="transfer-card-body">
                            <div class="transfer-form-steps">
                                <span class="transfer-form-step transfer-form-step--1"><i class="fas fa-globe"></i> Site</span>
                                <span class="transfer-form-step-arrow"><i class="fas fa-chevron-right"></i></span>
                                <span class="transfer-form-step transfer-form-step--2"><i class="fas fa-key"></i> Credentials</span>
                                <span class="transfer-form-step-arrow"><i class="fas fa-chevron-right"></i></span>
                                <span class="transfer-form-step transfer-form-step--3"><i class="fas fa-power-off"></i> Activate</span>
                            </div>

                            <section class="transfer-form-panel">
                                <div class="transfer-form-panel-head">
                                    <span class="transfer-form-panel-icon transfer-form-panel-icon--site"><i class="fas fa-server"></i></span>
                                    <div>
                                        <h4>Remote Site Details</h4>
                                        <p>Where orders will be sent when status becomes Processing.</p>
                                    </div>
                                </div>
                                <div class="transfer-form-panel-body">
                                    <div class="transfer-form-row">
                                        <div class="transfer-form-field">
                                            <label for="site_name">Site Name</label>
                                            <div class="transfer-input-wrap">
                                                <span class="transfer-input-icon"><i class="fas fa-store"></i></span>
                                                <input type="text" name="site_name" id="site_name" class="form-control @error('site_name') is-invalid @enderror" value="{{ old('site_name', $setting->site_name) }}" placeholder="Second Store">
                                            </div>
                                            @error('site_name')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                        </div>
                                        <div class="transfer-form-field">
                                            <label for="domain">Domain *</label>
                                            <div class="transfer-input-wrap">
                                                <span class="transfer-input-icon"><i class="fas fa-link"></i></span>
                                                <input type="url" name="domain" id="domain" class="form-control @error('domain') is-invalid @enderror" value="{{ old('domain', $setting->domain) }}" placeholder="https://example.com">
                                            </div>
                                            @error('domain')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="transfer-form-field">
                                        <label for="endpoint_path">Endpoint Path *</label>
                                        <div class="transfer-input-wrap">
                                            <span class="transfer-input-icon"><i class="fas fa-route"></i></span>
                                            <input type="text" name="endpoint_path" id="endpoint_path" class="form-control @error('endpoint_path') is-invalid @enderror" value="{{ old('endpoint_path', $setting->endpoint_path ?: '/api/orders/import') }}" required>
                                        </div>
                                        @error('endpoint_path')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="transfer-endpoint-preview">
                                        <span class="transfer-endpoint-preview-label"><i class="fas fa-broadcast-tower"></i> Final endpoint URL</span>
                                        <code id="transfer-endpoint-preview">{{ $endpointUrl }}</code>
                                    </div>
                                </div>
                            </section>

                            <section class="transfer-form-panel">
                                <div class="transfer-form-panel-head">
                                    <span class="transfer-form-panel-icon transfer-form-panel-icon--cred"><i class="fas fa-shield-alt"></i></span>
                                    <div>
                                        <h4>API Credentials</h4>
                                        <p>Share these with the receiver site for authentication.</p>
                                    </div>
                                </div>
                                <div class="transfer-form-panel-body">
                                    <div class="transfer-cred-card">
                                        <div class="transfer-cred-head">
                                            <label for="api_key">API Key *</label>
                                            <button type="submit" form="generate-api-key-form" class="btn btn-sm btn-primary" onclick="return confirm('Generate a new API key? Old key will stop working after save/use.')">
                                                <i class="fas fa-sync mr-1"></i> Generate
                                            </button>
                                        </div>
                                        <div class="transfer-input-wrap transfer-input-wrap--mono">
                                            <span class="transfer-input-icon"><i class="fas fa-key"></i></span>
                                            <input type="text" name="api_key" id="api_key" class="form-control @error('api_key') is-invalid @enderror" value="{{ old('api_key', $setting->api_key) }}" placeholder="ok_...">
                                        </div>
                                        @error('api_key')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="transfer-cred-card">
                                        <div class="transfer-cred-head">
                                            <label for="access_token">Access Token *</label>
                                            <button type="submit" form="generate-access-token-form" class="btn btn-sm btn-info" onclick="return confirm('Generate a new access token? Old token will stop working after save/use.')">
                                                <i class="fas fa-sync mr-1"></i> Generate Token
                                            </button>
                                        </div>
                                        <div class="transfer-input-wrap transfer-input-wrap--mono">
                                            <span class="transfer-input-icon transfer-input-icon--top"><i class="fas fa-lock"></i></span>
                                            <textarea name="access_token" id="access_token" class="form-control @error('access_token') is-invalid @enderror" rows="4" placeholder="Long secure token...">{{ old('access_token', $setting->access_token) }}</textarea>
                                        </div>
                                        @error('access_token')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                            </section>

                            <section class="transfer-form-panel transfer-form-panel--activate">
                                <div class="transfer-activate-box {{ $setting->is_active ? 'transfer-activate-box--on' : '' }}">
                                    <div class="transfer-activate-copy">
                                        <h4>Enable Order Transfer</h4>
                                        <p>When turned on, orders moving to <strong>Processing</strong> are sent automatically to the endpoint above.</p>
                                    </div>
                                    <label class="transfer-toggle" for="is_active">
                                        <input type="checkbox" class="transfer-toggle-input" id="is_active" name="is_active" value="1" @checked(old('is_active', $setting->is_active))>
                                        <span class="transfer-toggle-track">
                                            <span class="transfer-toggle-thumb"></span>
                                        </span>
                                        <span class="transfer-toggle-label">{{ $setting->is_active ? 'Enabled' : 'Disabled' }}</span>
                                    </label>
                                </div>
                            </section>
                        </div>
                        <div class="transfer-card-footer">
                            <button type="submit" class="btn btn-info btn-lg">
                                <i class="fas fa-save mr-1"></i> Save Setting
                            </button>
                            <a href="{{ route('admin.orders.transfer-settings.scripts') }}" class="btn btn-primary">
                                <i class="fas fa-code mr-1"></i> View Scripts
                            </a>
                            <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">Back to Orders</a>
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

            <div class="col-lg-5 mb-3">
                <div class="transfer-side-card">
                    <div class="transfer-side-head">
                        <span class="transfer-side-icon transfer-side-icon--info"><i class="fas fa-server"></i></span>
                        <div>
                            <h4>Receiver Requirements</h4>
                            <p>The receiver website should accept a JSON POST request.</p>
                        </div>
                    </div>
                    <div class="transfer-side-body">
                        <p class="transfer-side-label">Headers sent</p>
                        <ul class="transfer-code-list">
                            <li><code>Accept: application/json</code></li>
                            <li><code>X-API-Key: your API key</code></li>
                            <li><code>Authorization: Bearer your_access_token</code></li>
                        </ul>
                        <p class="transfer-side-label mb-0">Payload includes</p>
                        <p class="mb-0 text-muted">order, items, and payments.</p>
                    </div>
                </div>

                <div class="transfer-side-card">
                    <div class="transfer-side-head">
                        <span class="transfer-side-icon transfer-side-icon--fields"><i class="fas fa-list"></i></span>
                        <div>
                            <h4>Payload Field Names</h4>
                            <p>Fields included in the outbound transfer payload.</p>
                        </div>
                    </div>
                    <div class="transfer-side-body transfer-side-body--compact">
                        <p class="transfer-side-label">order</p>
                        <p><code>number, status, type, customer_name, customer_email, customer_phone, address, city, zip, payment_method, payment_status, reference_code, bank_name, notes, coupon_code, subtotal, discount, shipping, total, amount_paid, created_at</code></p>
                        <p class="transfer-side-label">items[]</p>
                        <p><code>product_slug, product_name, product_link, image, size, color, quantity, price</code></p>
                        <p class="transfer-side-label">payments[]</p>
                        <p><code>amount, payment_method, bank_name, notes, created_at</code></p>
                        <hr>
                        <p class="transfer-side-label">Incoming status update to this site</p>
                        <p class="mb-0"><code>POST {{ url('/api/orders/status-update') }}</code> with <code>order_number, admin_status</code> (optional: <code>payment_status, amount_paid, message</code>). <code>admin_status</code>: pending, confirmed, kolkata_warehouse, shipped, dhaka_warehouse, ready_for_delivery, dispatched, delivered, cancelled</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="transfer-card transfer-card--code mb-3">
            <div class="transfer-card-head">
                <div>
                    <h3 class="mb-0">Laravel Receiver Script Example</h3>
                    <p class="mb-0 text-muted">Add this on the other website. Keep the API key and token same as this setting page.</p>
                </div>
            </div>
            <div class="transfer-card-body p-0">
<pre class="transfer-code-block mb-0"><code>@verbatim
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

    return response()->json([
        'message' => 'Order received',
        'order_number' => $data['order']['number'],
    ]);
});
@endverbatim</code></pre>
            </div>
        </div>

        <div class="transfer-card transfer-card--code">
            <div class="transfer-card-head">
                <div>
                    <h3 class="mb-0">Send Status Update Back To This Site</h3>
                    <p class="mb-0 text-muted">Use this from the other website when its order status changes. It uses the same API key and access token.</p>
                </div>
            </div>
            <div class="transfer-card-body p-0">
<pre class="transfer-code-block mb-0"><code>@verbatim
use Illuminate\Support\Facades\Http;

$response = Http::acceptJson()
    ->withHeaders([
        'X-API-Key' => 'YOUR_API_KEY',
        'Authorization' => 'Bearer YOUR_ACCESS_TOKEN',
    ])
    ->post('https://your-main-site.com/api/orders/status-update', [
        'order_number' => 'ORD-1001',
        'admin_status' => 'kolkata_warehouse',
        // optional: payment_status, amount_paid, message
    ]);

if ($response->successful()) {
    // status updated on main site
}
@endverbatim</code></pre>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .transfer-settings-page {
        --transfer-ink: #0f172a;
        --transfer-muted: #64748b;
        --transfer-border: #e2e8f0;
    }

    .transfer-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.25rem;
        padding: 1.35rem 1.5rem;
        border-radius: 1.1rem;
        color: #fff;
        background:
            radial-gradient(circle at 88% 15%, rgba(103, 232, 249, 0.25), transparent 35%),
            linear-gradient(135deg, #0f3d47 0%, #0e7490 55%, #0891b2 100%);
        box-shadow: 0 14px 32px rgba(8, 145, 178, 0.18);
    }

    .transfer-eyebrow {
        display: block;
        margin-bottom: 0.2rem;
        color: #a5f3fc;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.09em;
        text-transform: uppercase;
    }

    .transfer-hero h2 {
        margin: 0;
        font-size: 1.55rem;
        font-weight: 700;
    }

    .transfer-hero p {
        margin: 0.4rem 0 0;
        color: rgba(255, 255, 255, 0.82);
    }

    .transfer-hero-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        margin-top: 0.65rem;
    }

    .transfer-hero-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        max-width: 100%;
        padding: 0.25rem 0.65rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.14);
        color: #ecfeff;
        font-size: 0.72rem;
        font-weight: 600;
        word-break: break-all;
    }

    .transfer-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.55rem;
        flex-shrink: 0;
    }

    .transfer-hero-actions .btn {
        font-weight: 700;
        border: 0;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.18);
    }

    .transfer-stat {
        display: flex;
        align-items: center;
        gap: 0.9rem;
        height: 100%;
        padding: 1rem 1.1rem;
        border: 1px solid var(--transfer-border);
        border-radius: 0.95rem;
        background: #fff;
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.04);
    }

    .transfer-stat-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.6rem;
        height: 2.6rem;
        border-radius: 0.75rem;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .transfer-stat--active .transfer-stat-icon { background: #ecfdf5; color: #059669; }
    .transfer-stat--inactive .transfer-stat-icon { background: #fef2f2; color: #dc2626; }
    .transfer-stat--endpoint .transfer-stat-icon { background: #ecfeff; color: #0891b2; }
    .transfer-stat--key .transfer-stat-icon { background: #eff6ff; color: #2563eb; }
    .transfer-stat--token .transfer-stat-icon { background: #f5f3ff; color: #7c3aed; }

    .transfer-stat-value {
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--transfer-ink);
        line-height: 1.1;
    }

    .transfer-stat-label {
        margin-top: 0.15rem;
        color: var(--transfer-muted);
        font-size: 0.82rem;
        font-weight: 500;
    }

    .transfer-alert {
        display: flex;
        align-items: flex-start;
        gap: 0.85rem;
        margin-bottom: 1rem;
        padding: 0.85rem 1rem;
        border-radius: 0.85rem;
    }

    .transfer-alert > i { font-size: 1.1rem; margin-top: 0.1rem; }
    .transfer-alert strong { display: block; font-size: 0.9rem; }
    .transfer-alert span { display: block; margin-top: 0.15rem; font-size: 0.82rem; }

    .transfer-alert--warning {
        border: 1px solid #fde68a;
        background: #fffbeb;
        color: #b45309;
    }

    .transfer-card,
    .transfer-side-card {
        border: 1px solid var(--transfer-border);
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }

    .transfer-side-card {
        margin-bottom: 1rem;
    }

    .transfer-card-head,
    .transfer-side-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.15rem;
        border-bottom: 1px solid #eef2f7;
        background: #fff;
    }

    .transfer-card-head h3,
    .transfer-side-head h4 {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: var(--transfer-ink);
    }

    .transfer-card-head p,
    .transfer-side-head p {
        margin: 0.25rem 0 0;
        font-size: 0.8rem;
        color: var(--transfer-muted);
    }

    .transfer-ready-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.3rem 0.65rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .transfer-ready-badge i { font-size: 0.45rem; }

    .transfer-ready-badge--ready {
        background: #ecfdf5;
        color: #047857;
    }

    .transfer-ready-badge--pending {
        background: #fff7ed;
        color: #c2410c;
    }

    .transfer-card-body,
    .transfer-side-body {
        padding: 1rem 1.15rem;
    }

    .transfer-side-body--compact {
        font-size: 0.8rem;
    }

    .transfer-side-head {
        align-items: center;
    }

    .transfer-side-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.4rem;
        height: 2.4rem;
        border-radius: 0.7rem;
        flex-shrink: 0;
        font-size: 0.95rem;
    }

    .transfer-side-icon--info { background: #ecfeff; color: #0891b2; }
    .transfer-side-icon--fields { background: #f5f3ff; color: #7c3aed; }

    .transfer-side-label {
        margin: 0 0 0.35rem;
        color: var(--transfer-muted);
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .transfer-code-list {
        margin: 0 0 0.85rem;
        padding-left: 1.1rem;
        color: #475569;
        font-size: 0.82rem;
    }

    .transfer-form-steps {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1rem;
        padding: 0.75rem;
        border: 1px solid #e2e8f0;
        border-radius: 0.85rem;
        background: #f8fafc;
        flex-wrap: wrap;
    }

    .transfer-form-step {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.35rem 0.65rem;
        border-radius: 999px;
        font-size: 0.76rem;
        font-weight: 700;
        color: #475569;
        background: #fff;
        border: 1px solid #e2e8f0;
    }

    .transfer-form-step--1 { color: #0891b2; border-color: #a5f3fc; background: #ecfeff; }
    .transfer-form-step--2 { color: #2563eb; border-color: #bfdbfe; background: #eff6ff; }
    .transfer-form-step--3 { color: #7c3aed; border-color: #ddd6fe; background: #f5f3ff; }

    .transfer-form-step-arrow {
        color: #cbd5e1;
        font-size: 0.7rem;
    }

    .transfer-form-panel {
        margin-bottom: 1rem;
        border: 1px solid #e2e8f0;
        border-radius: 0.95rem;
        background: linear-gradient(180deg, #fafbfc 0%, #fff 100%);
        overflow: hidden;
    }

    .transfer-form-panel--activate {
        margin-bottom: 0;
        background: #fff;
    }

    .transfer-form-panel-head {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.9rem 1rem;
        border-bottom: 1px solid #eef2f7;
        background: #fff;
    }

    .transfer-form-panel-head h4 {
        margin: 0;
        font-size: 0.92rem;
        font-weight: 700;
        color: var(--transfer-ink);
    }

    .transfer-form-panel-head p {
        margin: 0.15rem 0 0;
        font-size: 0.78rem;
        color: var(--transfer-muted);
    }

    .transfer-form-panel-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.35rem;
        height: 2.35rem;
        border-radius: 0.7rem;
        flex-shrink: 0;
        font-size: 0.95rem;
    }

    .transfer-form-panel-icon--site { background: #ecfeff; color: #0891b2; }
    .transfer-form-panel-icon--cred { background: #eff6ff; color: #2563eb; }

    .transfer-form-panel-body {
        padding: 1rem;
    }

    .transfer-form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.85rem;
        margin-bottom: 0.85rem;
    }

    .transfer-form-field label {
        display: block;
        margin-bottom: 0.35rem;
        color: var(--transfer-muted);
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .transfer-input-wrap {
        position: relative;
        display: flex;
        align-items: stretch;
    }

    .transfer-input-icon {
        position: absolute;
        left: 0.85rem;
        top: 50%;
        transform: translateY(-50%);
        z-index: 2;
        color: #94a3b8;
        font-size: 0.85rem;
        pointer-events: none;
    }

    .transfer-input-icon--top {
        top: 0.95rem;
        transform: none;
    }

    .transfer-input-wrap .form-control {
        width: 100%;
        min-height: 2.65rem;
        padding-left: 2.45rem;
        border: 1.5px solid #dbe3ed;
        border-radius: 0.7rem;
        background: #fff;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .transfer-input-wrap textarea.form-control {
        min-height: 6.5rem;
        padding-top: 0.75rem;
        resize: vertical;
    }

    .transfer-input-wrap--mono .form-control {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 0.8rem;
    }

    .transfer-input-wrap .form-control:focus {
        border-color: #22d3ee;
        box-shadow: 0 0 0 3px rgba(34, 211, 238, 0.12);
    }

    .transfer-endpoint-preview {
        margin-top: 0.85rem;
        padding: 0.85rem 1rem;
        border: 1px solid #a5f3fc;
        border-radius: 0.75rem;
        background: linear-gradient(135deg, #ecfeff 0%, #f0fdfa 100%);
    }

    .transfer-endpoint-preview-label {
        display: block;
        margin-bottom: 0.35rem;
        color: #0e7490;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .transfer-endpoint-preview code {
        display: block;
        word-break: break-all;
        color: #0f172a;
        font-size: 0.82rem;
        background: transparent;
    }

    .transfer-cred-card {
        padding: 0.9rem;
        border: 1px solid #e2e8f0;
        border-radius: 0.8rem;
        background: #fff;
    }

    .transfer-cred-card + .transfer-cred-card {
        margin-top: 0.85rem;
    }

    .transfer-cred-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.55rem;
    }

    .transfer-cred-head label {
        margin: 0;
        color: var(--transfer-muted);
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .transfer-cred-head .btn {
        font-weight: 700;
        border: 0;
        white-space: nowrap;
    }

    .transfer-activate-box {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem;
        border: 2px dashed #cbd5e1;
        border-radius: 0.85rem;
        background: #f8fafc;
        transition: all 0.15s ease;
    }

    .transfer-activate-box--on {
        border-style: solid;
        border-color: #6ee7b7;
        background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 100%);
    }

    .transfer-activate-copy h4 {
        margin: 0 0 0.25rem;
        font-size: 0.92rem;
        font-weight: 700;
        color: var(--transfer-ink);
    }

    .transfer-activate-copy p {
        margin: 0;
        color: var(--transfer-muted);
        font-size: 0.8rem;
        line-height: 1.45;
    }

    .transfer-toggle {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.4rem;
        margin: 0;
        cursor: pointer;
        flex-shrink: 0;
    }

    .transfer-toggle-input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .transfer-toggle-track {
        position: relative;
        width: 3.4rem;
        height: 1.85rem;
        border-radius: 999px;
        background: #cbd5e1;
        transition: background 0.15s ease;
    }

    .transfer-toggle-thumb {
        position: absolute;
        top: 0.2rem;
        left: 0.2rem;
        width: 1.45rem;
        height: 1.45rem;
        border-radius: 999px;
        background: #fff;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.15);
        transition: transform 0.15s ease;
    }

    .transfer-toggle-input:checked + .transfer-toggle-track {
        background: #059669;
    }

    .transfer-toggle-input:checked + .transfer-toggle-track .transfer-toggle-thumb {
        transform: translateX(1.55rem);
    }

    .transfer-toggle-label {
        color: #475569;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .transfer-toggle-input:checked ~ .transfer-toggle-label {
        color: #047857;
    }

    .transfer-card-footer {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        padding: 0.9rem 1.15rem;
        border-top: 1px solid #eef2f7;
        background: #f8fafc;
    }

    .transfer-card-footer .btn {
        font-weight: 700;
        border: 0;
    }

    .transfer-code-block {
        margin: 0;
        padding: 1rem 1.15rem;
        background: #0f172a;
        color: #e2e8f0;
        font-size: 0.78rem;
        line-height: 1.5;
        white-space: pre-wrap;
        overflow-x: auto;
    }

    .transfer-code-block code {
        color: inherit;
        background: transparent;
    }

    @media (max-width: 767.98px) {
        .transfer-hero {
            align-items: flex-start;
            flex-direction: column;
            padding: 1.1rem;
        }

        .transfer-hero-actions {
            width: 100%;
        }

        .transfer-hero-actions .btn {
            flex: 1;
        }

        .transfer-form-row {
            grid-template-columns: 1fr;
        }

        .transfer-activate-box {
            flex-direction: column;
            align-items: stretch;
        }

        .transfer-toggle {
            align-self: flex-end;
        }

        .transfer-card-head {
            flex-direction: column;
        }
    }
</style>
@endpush

@push('scripts')
<script>
(function () {
    var domainInput = document.getElementById('domain');
    var pathInput = document.getElementById('endpoint_path');
    var previewEl = document.getElementById('transfer-endpoint-preview');
    var heroEndpointChip = document.querySelector('.transfer-hero-chip:last-child');
    var toggleInput = document.getElementById('is_active');
    var toggleLabel = document.querySelector('.transfer-toggle-label');
    var activateBox = document.querySelector('.transfer-activate-box');

    function buildEndpointUrl() {
        var domain = (domainInput && domainInput.value.trim()) || 'https://example.com';
        var path = (pathInput && pathInput.value.trim()) || '/api/orders/import';
        domain = domain.replace(/\/+$/, '');
        if (!path.startsWith('/')) path = '/' + path;
        return domain + path;
    }

    function syncEndpointPreview() {
        var url = buildEndpointUrl();
        if (previewEl) previewEl.textContent = url;
        if (heroEndpointChip) {
            heroEndpointChip.innerHTML = '<i class="fas fa-link"></i> ' + url;
        }
    }

    function syncToggleState() {
        if (!toggleInput) return;
        var enabled = toggleInput.checked;
        if (toggleLabel) toggleLabel.textContent = enabled ? 'Enabled' : 'Disabled';
        if (activateBox) activateBox.classList.toggle('transfer-activate-box--on', enabled);
    }

    if (domainInput) domainInput.addEventListener('input', syncEndpointPreview);
    if (pathInput) pathInput.addEventListener('input', syncEndpointPreview);
    if (toggleInput) toggleInput.addEventListener('change', syncToggleState);

    syncToggleState();
})();
</script>
@endpush
