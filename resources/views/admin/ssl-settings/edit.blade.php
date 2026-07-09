@extends('layouts.admin')

@section('title', 'SSL Settings')
@section('page_title', 'SSL Settings')

@section('content')
    <form action="{{ route('admin.ssl-settings.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-lg-8">
                <div class="card card-danger card-outline">
                    <div class="card-header">
                        <h3 class="card-title mb-0">SSLCommerz Payment API</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="sslcommerz_enabled" name="sslcommerz_enabled" value="1" @checked(old('sslcommerz_enabled', $settings->sslcommerz_enabled ?? false))>
                                <label class="custom-control-label" for="sslcommerz_enabled">Enable SSLCommerz online payment</label>
                            </div>
                            <small class="text-muted d-block mt-1">Saved in site settings (database). No <code>.env</code> needed — syncs after git push/pull.</small>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="sslcommerz_sandbox" name="sslcommerz_sandbox" value="1" @checked(old('sslcommerz_sandbox', $settings->sslcommerz_sandbox ?? true))>
                                <label class="custom-control-label" for="sslcommerz_sandbox">Sandbox mode (testing)</label>
                            </div>
                            <small class="text-muted">Turn off for live payments on your production store.</small>
                        </div>
                        <div class="form-group">
                            <label>Store ID</label>
                            <input
                                type="text"
                                name="sslcommerz_store_id"
                                class="form-control @error('sslcommerz_store_id') is-invalid @enderror"
                                value="{{ old('sslcommerz_store_id', $settings->sslcommerz_store_id) }}"
                                placeholder="your_store_id"
                            >
                            @error('sslcommerz_store_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group mb-0">
                            <label>Store Password</label>
                            <input
                                type="password"
                                name="sslcommerz_store_password"
                                class="form-control @error('sslcommerz_store_password') is-invalid @enderror"
                                placeholder="{{ $settings->sslcommerz_store_password ? 'Leave blank to keep current password' : 'Store password from SSLCommerz' }}"
                                autocomplete="new-password"
                            >
                            @error('sslcommerz_store_password')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            <small class="text-muted">Get credentials from your <a href="https://developer.sslcommerz.com/" target="_blank" rel="noopener noreferrer">SSLCommerz merchant panel</a>.</small>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Save SSL Settings</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
