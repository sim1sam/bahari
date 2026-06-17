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
                            <input type="text" name="api_key" class="form-control @error('api_key') is-invalid @enderror" value="{{ old('api_key', $setting->api_key) }}">
                            @error('api_key')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label>Access Token *</label>
                            <textarea name="access_token" class="form-control @error('access_token') is-invalid @enderror" rows="4">{{ old('access_token', $setting->access_token) }}</textarea>
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
        </div>
    </div>
@endsection
