@extends('layouts.admin')

@section('title', 'SSL Settings')
@section('page_title', 'SSL Settings')

@section('content')
    @php
        $isEnabled = (bool) old('sslcommerz_enabled', $settings->sslcommerz_enabled ?? false);
        $isSandbox = (bool) old('sslcommerz_sandbox', $settings->sslcommerz_sandbox ?? true);
        $hasStoreId = filled(old('sslcommerz_store_id', $settings->sslcommerz_store_id));
        $hasPassword = filled($settings->sslcommerz_store_password);
        $isReady = $isEnabled && $hasStoreId && $hasPassword;
    @endphp

    <div class="ssl-settings-page">
        <section class="ssl-hero">
            <div>
                <span class="ssl-eyebrow">API settings</span>
                <h2>SSL Settings</h2>
                <p>Configure SSLCommerz online payment for your storefront checkout.</p>
                <div class="ssl-hero-meta">
                    <span class="ssl-hero-chip">
                        <i class="fas fa-credit-card"></i> SSLCommerz
                    </span>
                    <span class="ssl-hero-chip">
                        <i class="fas fa-layer-group"></i> {{ $isSandbox ? 'Sandbox mode' : 'Live mode' }}
                    </span>
                </div>
            </div>
            <div class="ssl-hero-actions">
                <a href="https://developer.sslcommerz.com/" target="_blank" rel="noopener noreferrer" class="btn btn-primary">
                    <i class="fas fa-external-link-alt mr-1"></i> Developer Docs
                </a>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-info">
                    <i class="fas fa-list mr-1"></i> All Orders
                </a>
            </div>
        </section>

        <section class="row ssl-stats">
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="ssl-stat {{ $isEnabled ? 'ssl-stat--enabled' : 'ssl-stat--disabled' }}">
                    <span class="ssl-stat-icon"><i class="fas fa-power-off"></i></span>
                    <div>
                        <div class="ssl-stat-value">{{ $isEnabled ? 'Enabled' : 'Disabled' }}</div>
                        <div class="ssl-stat-label">Online Payment</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="ssl-stat ssl-stat--mode">
                    <span class="ssl-stat-icon"><i class="fas fa-flask"></i></span>
                    <div>
                        <div class="ssl-stat-value">{{ $isSandbox ? 'Sandbox' : 'Live' }}</div>
                        <div class="ssl-stat-label">Environment</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="ssl-stat ssl-stat--store">
                    <span class="ssl-stat-icon"><i class="fas fa-store"></i></span>
                    <div>
                        <div class="ssl-stat-value">{{ $hasStoreId ? 'Set' : 'Missing' }}</div>
                        <div class="ssl-stat-label">Store ID</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="ssl-stat ssl-stat--password">
                    <span class="ssl-stat-icon"><i class="fas fa-lock"></i></span>
                    <div>
                        <div class="ssl-stat-value">{{ $hasPassword ? 'Set' : 'Missing' }}</div>
                        <div class="ssl-stat-label">Store Password</div>
                    </div>
                </article>
            </div>
        </section>

        @unless ($isReady)
            <div class="ssl-alert ssl-alert--warning">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <strong>Setup incomplete</strong>
                    <span>Enable SSLCommerz and provide Store ID and Store Password before customers can pay online.</span>
                </div>
            </div>
        @endunless

        <form action="{{ route('admin.ssl-settings.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-lg-8 mb-3">
                    <div class="ssl-card">
                        <div class="ssl-card-head">
                            <div>
                                <h3 class="mb-0">SSLCommerz Payment API</h3>
                                <p class="mb-0 text-muted">Saved in site settings (database). No <code>.env</code> needed.</p>
                            </div>
                            <span class="ssl-ready-badge {{ $isReady ? 'ssl-ready-badge--ready' : 'ssl-ready-badge--pending' }}">
                                <i class="fas fa-circle"></i> {{ $isReady ? 'Ready for checkout' : 'Not ready' }}
                            </span>
                        </div>

                        <div class="ssl-card-body">
                            <div class="ssl-form-steps">
                                <span class="ssl-form-step ssl-form-step--1"><i class="fas fa-toggle-on"></i> Options</span>
                                <span class="ssl-form-step-arrow"><i class="fas fa-chevron-right"></i></span>
                                <span class="ssl-form-step ssl-form-step--2"><i class="fas fa-key"></i> Credentials</span>
                            </div>

                            <section class="ssl-form-panel">
                                <div class="ssl-form-panel-head">
                                    <span class="ssl-form-panel-icon ssl-form-panel-icon--options"><i class="fas fa-sliders-h"></i></span>
                                    <div>
                                        <h4>Payment Options</h4>
                                        <p>Control whether SSLCommerz is available and which environment to use.</p>
                                    </div>
                                </div>
                                <div class="ssl-form-panel-body">
                                    <div class="ssl-toggle-card {{ $isEnabled ? 'ssl-toggle-card--on' : '' }}" data-toggle-card="enabled">
                                        <div class="ssl-toggle-copy">
                                            <h5>Enable SSLCommerz</h5>
                                            <p>Show SSLCommerz as an online payment option during checkout.</p>
                                        </div>
                                        <label class="ssl-toggle" for="sslcommerz_enabled">
                                            <input type="checkbox" class="ssl-toggle-input" id="sslcommerz_enabled" name="sslcommerz_enabled" value="1" @checked($isEnabled)>
                                            <span class="ssl-toggle-track"><span class="ssl-toggle-thumb"></span></span>
                                            <span class="ssl-toggle-label" data-label-for="enabled">{{ $isEnabled ? 'Enabled' : 'Disabled' }}</span>
                                        </label>
                                    </div>

                                    <div class="ssl-toggle-card {{ $isSandbox ? 'ssl-toggle-card--sandbox' : 'ssl-toggle-card--live' }}" data-toggle-card="sandbox">
                                        <div class="ssl-toggle-copy">
                                            <h5>Sandbox Mode</h5>
                                            <p>Use test credentials for development. Turn off for live customer payments.</p>
                                        </div>
                                        <label class="ssl-toggle" for="sslcommerz_sandbox">
                                            <input type="checkbox" class="ssl-toggle-input" id="sslcommerz_sandbox" name="sslcommerz_sandbox" value="1" @checked($isSandbox)>
                                            <span class="ssl-toggle-track"><span class="ssl-toggle-thumb"></span></span>
                                            <span class="ssl-toggle-label" data-label-for="sandbox">{{ $isSandbox ? 'Sandbox' : 'Live' }}</span>
                                        </label>
                                    </div>
                                </div>
                            </section>

                            <section class="ssl-form-panel">
                                <div class="ssl-form-panel-head">
                                    <span class="ssl-form-panel-icon ssl-form-panel-icon--cred"><i class="fas fa-shield-alt"></i></span>
                                    <div>
                                        <h4>Merchant Credentials</h4>
                                        <p>Store ID and password from your SSLCommerz merchant panel.</p>
                                    </div>
                                </div>
                                <div class="ssl-form-panel-body">
                                    <div class="ssl-field">
                                        <label for="sslcommerz_store_id">Store ID</label>
                                        <div class="ssl-input-wrap">
                                            <span class="ssl-input-icon"><i class="fas fa-store"></i></span>
                                            <input
                                                type="text"
                                                name="sslcommerz_store_id"
                                                id="sslcommerz_store_id"
                                                class="form-control @error('sslcommerz_store_id') is-invalid @enderror"
                                                value="{{ old('sslcommerz_store_id', $settings->sslcommerz_store_id) }}"
                                                placeholder="your_store_id"
                                            >
                                        </div>
                                        @error('sslcommerz_store_id')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="ssl-field">
                                        <label for="sslcommerz_store_password">Store Password</label>
                                        <div class="ssl-input-wrap ssl-input-wrap--mono">
                                            <span class="ssl-input-icon"><i class="fas fa-lock"></i></span>
                                            <input
                                                type="password"
                                                name="sslcommerz_store_password"
                                                id="sslcommerz_store_password"
                                                class="form-control @error('sslcommerz_store_password') is-invalid @enderror"
                                                placeholder="{{ $settings->sslcommerz_store_password ? 'Leave blank to keep current password' : 'Store password from SSLCommerz' }}"
                                                autocomplete="new-password"
                                            >
                                        </div>
                                        @error('sslcommerz_store_password')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                        <small class="ssl-field-hint">
                                            Get credentials from your
                                            <a href="https://developer.sslcommerz.com/" target="_blank" rel="noopener noreferrer">SSLCommerz merchant panel</a>.
                                        </small>
                                    </div>
                                </div>
                            </section>
                        </div>

                        <div class="ssl-card-footer">
                            <button type="submit" class="btn btn-info btn-lg">
                                <i class="fas fa-save mr-1"></i> Save SSL Settings
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 mb-3">
                    <div class="ssl-side-card">
                        <div class="ssl-side-head">
                            <span class="ssl-side-icon ssl-side-icon--check"><i class="fas fa-clipboard-check"></i></span>
                            <div>
                                <h4>Setup Checklist</h4>
                                <p>Complete these steps before going live.</p>
                            </div>
                        </div>
                        <div class="ssl-side-body">
                            <ul class="ssl-checklist">
                                <li class="{{ $isEnabled ? 'ssl-checklist--done' : '' }}">
                                    <i class="fas {{ $isEnabled ? 'fa-check-circle' : 'fa-circle' }}"></i>
                                    Enable SSLCommerz payment
                                </li>
                                <li class="{{ $hasStoreId ? 'ssl-checklist--done' : '' }}">
                                    <i class="fas {{ $hasStoreId ? 'fa-check-circle' : 'fa-circle' }}"></i>
                                    Add Store ID
                                </li>
                                <li class="{{ $hasPassword ? 'ssl-checklist--done' : '' }}">
                                    <i class="fas {{ $hasPassword ? 'fa-check-circle' : 'fa-circle' }}"></i>
                                    Add Store Password
                                </li>
                                <li class="{{ $isEnabled && ! $isSandbox ? 'ssl-checklist--done' : '' }}">
                                    <i class="fas {{ $isEnabled && ! $isSandbox ? 'fa-check-circle' : 'fa-circle' }}"></i>
                                    Switch to Live mode for production
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="ssl-side-card">
                        <div class="ssl-side-head">
                            <span class="ssl-side-icon ssl-side-icon--info"><i class="fas fa-info-circle"></i></span>
                            <div>
                                <h4>How It Works</h4>
                                <p>What happens after you save these settings.</p>
                            </div>
                        </div>
                        <div class="ssl-side-body">
                            <p class="ssl-side-text">Customers choosing SSLCommerz at checkout are redirected to the payment gateway. Successful payments update the order payment status automatically.</p>
                            <p class="ssl-side-text mb-0">Credentials are stored encrypted in the database and sync after git push/pull — no server <code>.env</code> changes required.</p>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('styles')
<style>
    .ssl-settings-page {
        --ssl-ink: #0f172a;
        --ssl-muted: #64748b;
        --ssl-border: #e2e8f0;
    }

    .ssl-hero {
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

    .ssl-eyebrow {
        display: block;
        margin-bottom: 0.2rem;
        color: #a5f3fc;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.09em;
        text-transform: uppercase;
    }

    .ssl-hero h2 { margin: 0; font-size: 1.55rem; font-weight: 700; }
    .ssl-hero p { margin: 0.4rem 0 0; color: rgba(255, 255, 255, 0.82); }

    .ssl-hero-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        margin-top: 0.65rem;
    }

    .ssl-hero-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.25rem 0.65rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.14);
        color: #ecfeff;
        font-size: 0.72rem;
        font-weight: 600;
    }

    .ssl-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.55rem;
        flex-shrink: 0;
    }

    .ssl-hero-actions .btn {
        font-weight: 700;
        border: 0;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.18);
    }

    .ssl-stat {
        display: flex;
        align-items: center;
        gap: 0.9rem;
        height: 100%;
        padding: 1rem 1.1rem;
        border: 1px solid var(--ssl-border);
        border-radius: 0.95rem;
        background: #fff;
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.04);
    }

    .ssl-stat-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.6rem;
        height: 2.6rem;
        border-radius: 0.75rem;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .ssl-stat--enabled .ssl-stat-icon { background: #ecfdf5; color: #059669; }
    .ssl-stat--disabled .ssl-stat-icon { background: #fef2f2; color: #dc2626; }
    .ssl-stat--mode .ssl-stat-icon { background: #fff7ed; color: #d97706; }
    .ssl-stat--store .ssl-stat-icon { background: #ecfeff; color: #0891b2; }
    .ssl-stat--password .ssl-stat-icon { background: #f5f3ff; color: #7c3aed; }

    .ssl-stat-value {
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--ssl-ink);
        line-height: 1.1;
    }

    .ssl-stat-label {
        margin-top: 0.15rem;
        color: var(--ssl-muted);
        font-size: 0.82rem;
        font-weight: 500;
    }

    .ssl-alert {
        display: flex;
        align-items: flex-start;
        gap: 0.85rem;
        margin-bottom: 1rem;
        padding: 0.85rem 1rem;
        border-radius: 0.85rem;
    }

    .ssl-alert > i { font-size: 1.1rem; margin-top: 0.1rem; }
    .ssl-alert strong { display: block; font-size: 0.9rem; }
    .ssl-alert span { display: block; margin-top: 0.15rem; font-size: 0.82rem; }

    .ssl-alert--warning {
        border: 1px solid #fde68a;
        background: #fffbeb;
        color: #b45309;
    }

    .ssl-card,
    .ssl-side-card {
        border: 1px solid var(--ssl-border);
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }

    .ssl-side-card { margin-bottom: 1rem; }

    .ssl-card-head,
    .ssl-side-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.15rem;
        border-bottom: 1px solid #eef2f7;
        background: #fff;
    }

    .ssl-card-head h3,
    .ssl-side-head h4 {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: var(--ssl-ink);
    }

    .ssl-card-head p,
    .ssl-side-head p {
        margin: 0.25rem 0 0;
        font-size: 0.8rem;
        color: var(--ssl-muted);
    }

    .ssl-ready-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.3rem 0.65rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .ssl-ready-badge i { font-size: 0.45rem; }
    .ssl-ready-badge--ready { background: #ecfdf5; color: #047857; }
    .ssl-ready-badge--pending { background: #fff7ed; color: #c2410c; }

    .ssl-card-body { padding: 1rem 1.15rem; }

    .ssl-form-steps {
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

    .ssl-form-step {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.35rem 0.65rem;
        border-radius: 999px;
        font-size: 0.76rem;
        font-weight: 700;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #475569;
    }

    .ssl-form-step--1 { color: #0891b2; border-color: #a5f3fc; background: #ecfeff; }
    .ssl-form-step--2 { color: #2563eb; border-color: #bfdbfe; background: #eff6ff; }

    .ssl-form-step-arrow { color: #cbd5e1; font-size: 0.7rem; }

    .ssl-form-panel {
        margin-bottom: 1rem;
        border: 1px solid #e2e8f0;
        border-radius: 0.95rem;
        background: linear-gradient(180deg, #fafbfc 0%, #fff 100%);
        overflow: hidden;
    }

    .ssl-form-panel:last-child { margin-bottom: 0; }

    .ssl-form-panel-head {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.9rem 1rem;
        border-bottom: 1px solid #eef2f7;
        background: #fff;
    }

    .ssl-form-panel-head h4 {
        margin: 0;
        font-size: 0.92rem;
        font-weight: 700;
        color: var(--ssl-ink);
    }

    .ssl-form-panel-head p {
        margin: 0.15rem 0 0;
        font-size: 0.78rem;
        color: var(--ssl-muted);
    }

    .ssl-form-panel-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.35rem;
        height: 2.35rem;
        border-radius: 0.7rem;
        flex-shrink: 0;
        font-size: 0.95rem;
    }

    .ssl-form-panel-icon--options { background: #ecfeff; color: #0891b2; }
    .ssl-form-panel-icon--cred { background: #eff6ff; color: #2563eb; }

    .ssl-form-panel-body { padding: 1rem; }

    .ssl-toggle-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.9rem 1rem;
        border: 2px dashed #cbd5e1;
        border-radius: 0.85rem;
        background: #f8fafc;
        transition: all 0.15s ease;
    }

    .ssl-toggle-card + .ssl-toggle-card { margin-top: 0.75rem; }

    .ssl-toggle-card--on {
        border-style: solid;
        border-color: #6ee7b7;
        background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 100%);
    }

    .ssl-toggle-card--sandbox {
        border-style: solid;
        border-color: #fdba74;
        background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
    }

    .ssl-toggle-card--live {
        border-style: solid;
        border-color: #6ee7b7;
        background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 100%);
    }

    .ssl-toggle-copy h5 {
        margin: 0 0 0.2rem;
        font-size: 0.88rem;
        font-weight: 700;
        color: var(--ssl-ink);
    }

    .ssl-toggle-copy p {
        margin: 0;
        color: var(--ssl-muted);
        font-size: 0.78rem;
        line-height: 1.4;
    }

    .ssl-toggle {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.4rem;
        margin: 0;
        cursor: pointer;
        flex-shrink: 0;
    }

    .ssl-toggle-input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .ssl-toggle-track {
        position: relative;
        width: 3.4rem;
        height: 1.85rem;
        border-radius: 999px;
        background: #cbd5e1;
        transition: background 0.15s ease;
    }

    .ssl-toggle-thumb {
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

    .ssl-toggle-input:checked + .ssl-toggle-track { background: #059669; }
    .ssl-toggle-input:checked + .ssl-toggle-track .ssl-toggle-thumb { transform: translateX(1.55rem); }

    .ssl-toggle-label {
        color: #475569;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .ssl-field label {
        display: block;
        margin-bottom: 0.35rem;
        color: var(--ssl-muted);
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .ssl-input-wrap { position: relative; }

    .ssl-input-icon {
        position: absolute;
        left: 0.85rem;
        top: 50%;
        transform: translateY(-50%);
        z-index: 2;
        color: #94a3b8;
        font-size: 0.85rem;
        pointer-events: none;
    }

    .ssl-input-wrap .form-control {
        min-height: 2.65rem;
        padding-left: 2.45rem;
        border: 1.5px solid #dbe3ed;
        border-radius: 0.7rem;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
    }

    .ssl-input-wrap--mono .form-control {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 0.8rem;
    }

    .ssl-input-wrap .form-control:focus {
        border-color: #22d3ee;
        box-shadow: 0 0 0 3px rgba(34, 211, 238, 0.12);
    }

    .ssl-field + .ssl-field { margin-top: 0.85rem; }

    .ssl-field-hint {
        display: block;
        margin-top: 0.35rem;
        color: var(--ssl-muted);
        font-size: 0.76rem;
    }

    .ssl-card-footer {
        padding: 0.9rem 1.15rem;
        border-top: 1px solid #eef2f7;
        background: #f8fafc;
    }

    .ssl-card-footer .btn {
        font-weight: 700;
        border: 0;
    }

    .ssl-side-head { align-items: center; }

    .ssl-side-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.4rem;
        height: 2.4rem;
        border-radius: 0.7rem;
        flex-shrink: 0;
        font-size: 0.95rem;
    }

    .ssl-side-icon--check { background: #ecfdf5; color: #059669; }
    .ssl-side-icon--info { background: #ecfeff; color: #0891b2; }

    .ssl-side-body { padding: 1rem 1.15rem; }

    .ssl-side-text {
        margin: 0 0 0.65rem;
        color: var(--ssl-muted);
        font-size: 0.82rem;
        line-height: 1.45;
    }

    .ssl-checklist {
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .ssl-checklist li {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        padding: 0.55rem 0;
        color: #64748b;
        font-size: 0.82rem;
        font-weight: 600;
        border-bottom: 1px solid #f1f5f9;
    }

    .ssl-checklist li:last-child { border-bottom: 0; }

    .ssl-checklist li i {
        color: #cbd5e1;
        font-size: 0.85rem;
    }

    .ssl-checklist--done {
        color: #047857;
    }

    .ssl-checklist--done i {
        color: #10b981;
    }

    @media (max-width: 767.98px) {
        .ssl-hero {
            align-items: flex-start;
            flex-direction: column;
            padding: 1.1rem;
        }

        .ssl-hero-actions { width: 100%; }
        .ssl-hero-actions .btn { flex: 1; }

        .ssl-toggle-card {
            flex-direction: column;
            align-items: stretch;
        }

        .ssl-toggle { align-self: flex-end; }

        .ssl-card-head { flex-direction: column; }
    }
</style>
@endpush

@push('scripts')
<script>
(function () {
    function syncToggle(input) {
        var card = input.closest('[data-toggle-card]');
        var label = card ? card.querySelector('.ssl-toggle-label') : null;
        if (!card || !label) return;

        if (input.id === 'sslcommerz_enabled') {
            card.classList.toggle('ssl-toggle-card--on', input.checked);
            label.textContent = input.checked ? 'Enabled' : 'Disabled';
        }

        if (input.id === 'sslcommerz_sandbox') {
            card.classList.toggle('ssl-toggle-card--sandbox', input.checked);
            card.classList.toggle('ssl-toggle-card--live', !input.checked);
            label.textContent = input.checked ? 'Sandbox' : 'Live';
        }
    }

    document.querySelectorAll('.ssl-toggle-input').forEach(function (input) {
        input.addEventListener('change', function () {
            syncToggle(input);
        });
    });
})();
</script>
@endpush
