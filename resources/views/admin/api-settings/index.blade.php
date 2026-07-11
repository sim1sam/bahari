@extends('layouts.admin')

@section('title', 'Content API Settings')
@section('page_title', 'Content API Settings')

@section('content')
    @php
        $activeSources = $sources->where('is_active', true)->count();
        $showAddForm = $sources->isEmpty() || $errors->any();
    @endphp

    <div class="api-settings-page">
        <section class="api-settings-hero">
            <div>
                <span class="api-settings-eyebrow">API settings</span>
                <h2>Content API Settings</h2>
                <p>Manage sender credentials and the webhook URL that receives product images from external sites.</p>
                <div class="api-settings-hero-meta">
                    <span class="api-settings-hero-chip">
                        <i class="fas fa-plug"></i> {{ number_format($sources->count()) }} source{{ $sources->count() === 1 ? '' : 's' }}
                    </span>
                    <span class="api-settings-hero-chip">
                        <i class="fas fa-link"></i> {{ Str::limit($receiveUrl, 48) }}
                    </span>
                </div>
            </div>
            <div class="api-settings-hero-actions">
                <a href="{{ route('admin.content.index') }}" class="btn btn-primary">
                    <i class="fas fa-cloud-download-alt mr-1"></i> Import Product
                </a>
                <a href="{{ route('admin.processed.index') }}" class="btn btn-info">
                    <i class="fas fa-check-circle mr-1"></i> Processed Product
                </a>
            </div>
        </section>

        <section class="row api-settings-stats">
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="api-settings-stat api-settings-stat--total">
                    <span class="api-settings-stat-icon"><i class="fas fa-server"></i></span>
                    <div>
                        <div class="api-settings-stat-value">{{ number_format($sources->count()) }}</div>
                        <div class="api-settings-stat-label">Source Sites</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="api-settings-stat api-settings-stat--active">
                    <span class="api-settings-stat-icon"><i class="fas fa-check-circle"></i></span>
                    <div>
                        <div class="api-settings-stat-value">{{ number_format($activeSources) }}</div>
                        <div class="api-settings-stat-label">Active Sources</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="api-settings-stat api-settings-stat--webhook">
                    <span class="api-settings-stat-icon"><i class="fas fa-broadcast-tower"></i></span>
                    <div>
                        <div class="api-settings-stat-value">{{ filled($webhookBaseUrl) ? 'Custom' : 'Default' }}</div>
                        <div class="api-settings-stat-label">Webhook Base URL</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="api-settings-stat api-settings-stat--receive">
                    <span class="api-settings-stat-icon"><i class="fas fa-inbox"></i></span>
                    <div>
                        <div class="api-settings-stat-value">Ready</div>
                        <div class="api-settings-stat-label">Receive Endpoint</div>
                    </div>
                </article>
            </div>
        </section>

        @if (session('generated_credentials'))
            <div class="api-settings-alert api-settings-alert--success">
                <i class="fas fa-key"></i>
                <div>
                    <strong>Credentials generated — copy now</strong>
                    <div class="api-settings-cred-copy mt-2">
                        <div>
                            <span>API Key</span>
                            <code id="gen-key">{{ session('generated_credentials')['api_key'] }}</code>
                        </div>
                        <div>
                            <span>API Token</span>
                            <code id="gen-token">{{ session('generated_credentials')['api_token'] }}</code>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="api-settings-card">
            <div class="api-settings-card-head">
                <div>
                    <h3 class="mb-0">Source Sites — API Key &amp; Token</h3>
                    <p class="mb-0 text-muted">Paste credentials from the sending site, or generate new keys and share them with the sender.</p>
                </div>
                <button type="button" class="btn btn-primary" id="toggle-add-source">
                    <i class="fas fa-plus mr-1"></i> Add Site
                </button>
            </div>

            <div class="api-settings-card-body">
                <div id="add-source-form" class="api-settings-add-panel {{ $showAddForm ? 'api-settings-add-panel--open' : '' }}">
                    <div class="api-settings-form-steps">
                        <span class="api-settings-form-step api-settings-form-step--1"><i class="fas fa-paste"></i> Paste</span>
                        <span class="api-settings-form-step-arrow"><i class="fas fa-chevron-right"></i></span>
                        <span class="api-settings-form-step api-settings-form-step--2"><i class="fas fa-magic"></i> Generate</span>
                    </div>
                    <div class="row">
                        <div class="col-lg-6 mb-3 mb-lg-0">
                            <section class="api-settings-form-panel">
                                <div class="api-settings-form-panel-head">
                                    <span class="api-settings-form-panel-icon api-settings-form-panel-icon--paste"><i class="fas fa-paste"></i></span>
                                    <div>
                                        <h4>Paste Credentials</h4>
                                        <p>Add API key and token received from the sender site.</p>
                                    </div>
                                </div>
                                <form action="{{ route('admin.api-settings.sources.store') }}" method="POST">
                                    @csrf
                                    <div class="api-settings-form-panel-body">
                                        <div class="api-settings-field">
                                            <label for="paste_name">Site Name</label>
                                            <div class="api-settings-input-wrap">
                                                <span class="api-settings-input-icon"><i class="fas fa-store"></i></span>
                                                <input type="text" name="name" id="paste_name" class="form-control @error('name') is-invalid @enderror" placeholder="kolkata2dhaka" required value="{{ old('name') }}">
                                            </div>
                                            @error('name')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                        </div>
                                        <div class="api-settings-field">
                                            <label for="paste_api_key">API Key</label>
                                            <div class="api-settings-input-wrap api-settings-input-wrap--mono">
                                                <span class="api-settings-input-icon"><i class="fas fa-key"></i></span>
                                                <input type="text" name="api_key" id="paste_api_key" class="form-control @error('api_key') is-invalid @enderror" placeholder="ak_..." required value="{{ old('api_key') }}">
                                            </div>
                                            @error('api_key')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                        </div>
                                        <div class="api-settings-field">
                                            <label for="paste_api_token">API Token</label>
                                            <div class="api-settings-input-wrap api-settings-input-wrap--mono">
                                                <span class="api-settings-input-icon"><i class="fas fa-lock"></i></span>
                                                <input type="text" name="api_token" id="paste_api_token" class="form-control @error('api_token') is-invalid @enderror" placeholder="at_..." required value="{{ old('api_token') }}">
                                            </div>
                                            @error('api_token')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                        </div>
                                        <div class="api-settings-field">
                                            <label for="paste_base_url">Sender Site URL</label>
                                            <div class="api-settings-input-wrap">
                                                <span class="api-settings-input-icon"><i class="fas fa-link"></i></span>
                                                <input type="url" name="base_url" id="paste_base_url" class="form-control @error('base_url') is-invalid @enderror" placeholder="https://kolkata2dhaka.com" value="{{ old('base_url') }}">
                                            </div>
                                            <small class="api-settings-field-hint">Required when sender sends relative image paths like <code>/storage/...</code></small>
                                            @error('base_url')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="api-settings-form-panel-footer">
                                        <button type="submit" class="btn btn-info">
                                            <i class="fas fa-save mr-1"></i> Save API Key &amp; Token
                                        </button>
                                    </div>
                                </form>
                            </section>
                        </div>
                        <div class="col-lg-6">
                            <section class="api-settings-form-panel api-settings-form-panel--generate">
                                <div class="api-settings-form-panel-head">
                                    <span class="api-settings-form-panel-icon api-settings-form-panel-icon--generate"><i class="fas fa-magic"></i></span>
                                    <div>
                                        <h4>Generate Credentials</h4>
                                        <p>Create keys on this site and copy them into the sender's settings.</p>
                                    </div>
                                </div>
                                <form action="{{ route('admin.api-settings.sources.generate') }}" method="POST">
                                    @csrf
                                    <div class="api-settings-form-panel-body">
                                        <div class="api-settings-generate-box">
                                            <p>Generate a new API key and token pair for a sender site. The credentials will appear at the top of this page after creation.</p>
                                            <div class="api-settings-field">
                                                <label for="generate_name">Site Name</label>
                                                <div class="api-settings-input-wrap">
                                                    <span class="api-settings-input-icon"><i class="fas fa-store"></i></span>
                                                    <input type="text" name="name" id="generate_name" class="form-control" placeholder="kolkata2dhaka" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="api-settings-form-panel-footer">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-sync mr-1"></i> Generate API Key &amp; Token
                                        </button>
                                    </div>
                                </form>
                            </section>
                        </div>
                    </div>
                </div>

                @if ($sources->isEmpty())
                    <div class="api-settings-empty">
                        <i class="fas fa-plug"></i>
                        <strong>No API sources yet</strong>
                        <p>Click <strong>Add Site</strong> to paste or generate credentials for your first sender.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table api-settings-table mb-0">
                            <thead>
                                <tr>
                                    <th>Site</th>
                                    <th>Sender URL</th>
                                    <th>API Key</th>
                                    <th>API Token</th>
                                    <th>Status</th>
                                    <th>Added</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sources as $source)
                                    <tr>
                                        <td>
                                            <div class="api-settings-site-name">{{ $source->name }}</div>
                                        </td>
                                        <td>
                                            <form action="{{ route('admin.api-settings.sources.update', $source) }}" method="POST" class="api-settings-inline-form">
                                                @csrf @method('PUT')
                                                <input type="hidden" name="name" value="{{ $source->name }}">
                                                <input type="hidden" name="is_active" value="{{ $source->is_active ? '1' : '0' }}">
                                                <input type="url" name="base_url" class="form-control form-control-sm" placeholder="https://sender-site.com" value="{{ old('base_url', $source->base_url) }}">
                                                <button type="submit" class="btn btn-sm btn-info">Save</button>
                                            </form>
                                        </td>
                                        <td><code class="api-settings-code">{{ $source->api_key }}</code></td>
                                        <td><code class="api-settings-code api-settings-code--muted">{{ Str::limit($source->api_token, 20) }}…</code></td>
                                        <td>
                                            <span class="api-settings-status {{ $source->is_active ? 'api-settings-status--active' : 'api-settings-status--inactive' }}">
                                                {{ $source->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="api-settings-date">{{ $source->created_at->format('M d, Y') }}</div>
                                        </td>
                                        <td class="text-right">
                                            <form action="{{ route('admin.api-settings.sources.destroy', $source) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this API source?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div class="api-settings-card api-settings-card--webhook">
            <div class="api-settings-card-head">
                <div>
                    <h3 class="mb-0">Receive API — Webhook URL</h3>
                    <p class="mb-0 text-muted">Copy this URL into the sender site so it can push received images here.</p>
                </div>
            </div>
            <div class="api-settings-card-body">
                <div class="api-settings-webhook-preview">
                    <span class="api-settings-webhook-label"><i class="fas fa-broadcast-tower"></i> Webhook URL</span>
                    <div class="api-settings-webhook-copy">
                        <input type="text" class="form-control" id="receive-url" value="{{ $receiveUrl }}" readonly>
                        <button type="button" class="btn btn-primary" id="copy-receive-url">
                            <i class="fas fa-copy mr-1"></i> Copy
                        </button>
                    </div>
                </div>

                <form action="{{ route('admin.api-settings.webhook') }}" method="POST" class="api-settings-webhook-form">
                    @csrf @method('PUT')
                    <div class="api-settings-field">
                        <label for="api_webhook_url">Site URL (optional)</label>
                        <div class="api-settings-input-wrap">
                            <span class="api-settings-input-icon"><i class="fas fa-globe"></i></span>
                            <input type="url" name="api_webhook_url" id="api_webhook_url" class="form-control @error('api_webhook_url') is-invalid @enderror"
                                value="{{ old('api_webhook_url', $webhookBaseUrl) }}"
                                placeholder="https://yourdomain.com">
                        </div>
                        <small class="api-settings-field-hint">Saved in site settings. Leave empty to use this server's <code>APP_URL</code>.</small>
                        @error('api_webhook_url')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                    </div>
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-save mr-1"></i> Save Webhook URL
                    </button>
                </form>

                <div class="api-settings-webhook-notes">
                    <div class="api-settings-note">
                        <strong>Method</strong>
                        <span>POST</span>
                    </div>
                    <div class="api-settings-note">
                        <strong>Headers</strong>
                        <span><code>X-API-Key: {api_key}</code> and <code>Authorization: Bearer {api_token}</code></span>
                    </div>
                    <div class="api-settings-note">
                        <strong>Query</strong>
                        <span><code>?api_key=...&amp;api_token=...</code></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .api-settings-page {
        --api-ink: #0f172a;
        --api-muted: #64748b;
        --api-border: #e2e8f0;
    }

    .api-settings-hero {
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

    .api-settings-eyebrow {
        display: block;
        margin-bottom: 0.2rem;
        color: #a5f3fc;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.09em;
        text-transform: uppercase;
    }

    .api-settings-hero h2 {
        margin: 0;
        font-size: 1.55rem;
        font-weight: 700;
    }

    .api-settings-hero p {
        margin: 0.4rem 0 0;
        color: rgba(255, 255, 255, 0.82);
    }

    .api-settings-hero-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        margin-top: 0.65rem;
    }

    .api-settings-hero-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.25rem 0.65rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.14);
        color: #ecfeff;
        font-size: 0.72rem;
        font-weight: 600;
        word-break: break-all;
    }

    .api-settings-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.55rem;
        flex-shrink: 0;
    }

    .api-settings-hero-actions .btn {
        font-weight: 700;
        border: 0;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.18);
    }

    .api-settings-stat {
        display: flex;
        align-items: center;
        gap: 0.9rem;
        height: 100%;
        padding: 1rem 1.1rem;
        border: 1px solid var(--api-border);
        border-radius: 0.95rem;
        background: #fff;
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.04);
    }

    .api-settings-stat-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.6rem;
        height: 2.6rem;
        border-radius: 0.75rem;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .api-settings-stat--total .api-settings-stat-icon { background: #ecfeff; color: #0891b2; }
    .api-settings-stat--active .api-settings-stat-icon { background: #ecfdf5; color: #059669; }
    .api-settings-stat--webhook .api-settings-stat-icon { background: #eff6ff; color: #2563eb; }
    .api-settings-stat--receive .api-settings-stat-icon { background: #f5f3ff; color: #7c3aed; }

    .api-settings-stat-value {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--api-ink);
        line-height: 1.1;
    }

    .api-settings-stat-label {
        margin-top: 0.15rem;
        color: var(--api-muted);
        font-size: 0.82rem;
        font-weight: 500;
    }

    .api-settings-alert {
        display: flex;
        align-items: flex-start;
        gap: 0.85rem;
        margin-bottom: 1rem;
        padding: 0.85rem 1rem;
        border-radius: 0.85rem;
    }

    .api-settings-alert > i { font-size: 1.1rem; margin-top: 0.1rem; }
    .api-settings-alert strong { display: block; font-size: 0.9rem; }

    .api-settings-alert--success {
        border: 1px solid #a7f3d0;
        background: #ecfdf5;
        color: #047857;
    }

    .api-settings-cred-copy {
        display: grid;
        gap: 0.5rem;
    }

    .api-settings-cred-copy span {
        display: block;
        margin-bottom: 0.15rem;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        opacity: 0.8;
    }

    .api-settings-cred-copy code {
        display: block;
        word-break: break-all;
        font-size: 0.8rem;
        background: rgba(255, 255, 255, 0.65);
        color: inherit;
    }

    .api-settings-card {
        margin-bottom: 1.25rem;
        border: 1px solid var(--api-border);
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }

    .api-settings-card-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.15rem;
        border-bottom: 1px solid #eef2f7;
        background: #fff;
        flex-wrap: wrap;
    }

    .api-settings-card-head h3 {
        font-size: 1rem;
        font-weight: 700;
        color: var(--api-ink);
    }

    .api-settings-card-head p {
        font-size: 0.8rem;
    }

    .api-settings-card-head .btn {
        font-weight: 700;
        border: 0;
    }

    .api-settings-card-body {
        padding: 1rem 1.15rem;
    }

    .api-settings-add-panel {
        display: none;
        margin-bottom: 1rem;
    }

    .api-settings-add-panel--open {
        display: block;
    }

    .api-settings-form-steps {
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

    .api-settings-form-step {
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

    .api-settings-form-step--1 { color: #0891b2; border-color: #a5f3fc; background: #ecfeff; }
    .api-settings-form-step--2 { color: #7c3aed; border-color: #ddd6fe; background: #f5f3ff; }

    .api-settings-form-step-arrow {
        color: #cbd5e1;
        font-size: 0.7rem;
    }

    .api-settings-form-panel {
        height: 100%;
        border: 1px solid #e2e8f0;
        border-radius: 0.95rem;
        background: linear-gradient(180deg, #fafbfc 0%, #fff 100%);
        overflow: hidden;
    }

    .api-settings-form-panel--generate {
        background: linear-gradient(180deg, #faf5ff 0%, #fff 100%);
    }

    .api-settings-form-panel-head {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.9rem 1rem;
        border-bottom: 1px solid #eef2f7;
        background: #fff;
    }

    .api-settings-form-panel-head h4 {
        margin: 0;
        font-size: 0.92rem;
        font-weight: 700;
        color: var(--api-ink);
    }

    .api-settings-form-panel-head p {
        margin: 0.15rem 0 0;
        font-size: 0.78rem;
        color: var(--api-muted);
    }

    .api-settings-form-panel-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.35rem;
        height: 2.35rem;
        border-radius: 0.7rem;
        flex-shrink: 0;
        font-size: 0.95rem;
    }

    .api-settings-form-panel-icon--paste { background: #ecfeff; color: #0891b2; }
    .api-settings-form-panel-icon--generate { background: #f5f3ff; color: #7c3aed; }

    .api-settings-form-panel-body {
        padding: 1rem;
    }

    .api-settings-form-panel-footer {
        padding: 0.85rem 1rem;
        border-top: 1px solid #eef2f7;
        background: #f8fafc;
    }

    .api-settings-form-panel-footer .btn {
        font-weight: 700;
        border: 0;
    }

    .api-settings-field label {
        display: block;
        margin-bottom: 0.35rem;
        color: var(--api-muted);
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .api-settings-input-wrap {
        position: relative;
    }

    .api-settings-input-icon {
        position: absolute;
        left: 0.85rem;
        top: 50%;
        transform: translateY(-50%);
        z-index: 2;
        color: #94a3b8;
        font-size: 0.85rem;
        pointer-events: none;
    }

    .api-settings-input-wrap .form-control {
        min-height: 2.65rem;
        padding-left: 2.45rem;
        border: 1.5px solid #dbe3ed;
        border-radius: 0.7rem;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
    }

    .api-settings-input-wrap--mono .form-control {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 0.8rem;
    }

    .api-settings-input-wrap .form-control:focus {
        border-color: #22d3ee;
        box-shadow: 0 0 0 3px rgba(34, 211, 238, 0.12);
    }

    .api-settings-field + .api-settings-field {
        margin-top: 0.85rem;
    }

    .api-settings-field-hint {
        display: block;
        margin-top: 0.35rem;
        color: var(--api-muted);
        font-size: 0.76rem;
    }

    .api-settings-generate-box {
        padding: 0.85rem;
        border: 1px dashed #c4b5fd;
        border-radius: 0.8rem;
        background: #faf5ff;
    }

    .api-settings-generate-box p {
        margin: 0 0 0.85rem;
        color: #6d28d9;
        font-size: 0.8rem;
        line-height: 1.45;
    }

    .api-settings-table thead th {
        border-top: 0;
        border-bottom: 1px solid #e2e8f0;
        background: #f8fafc;
        color: var(--api-muted);
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .api-settings-table tbody td {
        padding-top: 0.85rem;
        padding-bottom: 0.85rem;
        border-top-color: #eef2f7;
        vertical-align: middle;
    }

    .api-settings-site-name {
        font-weight: 700;
        color: #334155;
    }

    .api-settings-inline-form {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        flex-wrap: wrap;
    }

    .api-settings-inline-form .form-control {
        min-width: 180px;
        border-radius: 0.55rem;
    }

    .api-settings-inline-form .btn {
        font-weight: 700;
        border: 0;
    }

    .api-settings-code {
        display: inline-block;
        max-width: 12rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: 0.72rem;
        background: #f8fafc;
        color: #334155;
    }

    .api-settings-code--muted {
        color: #64748b;
    }

    .api-settings-status {
        display: inline-block;
        padding: 0.25rem 0.6rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .api-settings-status--active { color: #047857; background: #ecfdf5; }
    .api-settings-status--inactive { color: #64748b; background: #f1f5f9; }

    .api-settings-date {
        font-weight: 600;
        color: #334155;
        font-size: 0.82rem;
    }

    .api-settings-empty {
        padding: 2.5rem 1rem;
        text-align: center;
        color: var(--api-muted);
    }

    .api-settings-empty i {
        display: block;
        margin-bottom: 0.75rem;
        font-size: 2rem;
        opacity: 0.45;
    }

    .api-settings-empty strong {
        display: block;
        color: #334155;
        font-size: 1rem;
    }

    .api-settings-empty p {
        margin: 0.35rem 0 0;
        font-size: 0.86rem;
    }

    .api-settings-webhook-preview {
        margin-bottom: 1rem;
        padding: 0.9rem 1rem;
        border: 1px solid #a5f3fc;
        border-radius: 0.85rem;
        background: linear-gradient(135deg, #ecfeff 0%, #f0fdfa 100%);
    }

    .api-settings-webhook-label {
        display: block;
        margin-bottom: 0.45rem;
        color: #0e7490;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .api-settings-webhook-copy {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .api-settings-webhook-copy .form-control {
        flex: 1;
        min-width: 0;
        min-height: 2.65rem;
        border-radius: 0.7rem;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 0.8rem;
        background: #fff;
    }

    .api-settings-webhook-copy .btn {
        font-weight: 700;
        border: 0;
    }

    .api-settings-webhook-form {
        margin-bottom: 1rem;
    }

    .api-settings-webhook-form .btn {
        font-weight: 700;
        border: 0;
    }

    .api-settings-webhook-notes {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 0.65rem;
    }

    .api-settings-note {
        padding: 0.75rem 0.85rem;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        background: #f8fafc;
        font-size: 0.78rem;
    }

    .api-settings-note strong {
        display: block;
        margin-bottom: 0.25rem;
        color: var(--api-muted);
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .api-settings-note span,
    .api-settings-note code {
        color: #475569;
        font-size: 0.76rem;
        background: transparent;
    }

    @media (max-width: 767.98px) {
        .api-settings-hero {
            align-items: flex-start;
            flex-direction: column;
            padding: 1.1rem;
        }

        .api-settings-hero-actions {
            width: 100%;
        }

        .api-settings-hero-actions .btn {
            flex: 1;
        }

        .api-settings-card-head {
            flex-direction: column;
        }
    }
</style>
@endpush

@push('scripts')
<script>
(function () {
    var toggleBtn = document.getElementById('toggle-add-source');
    var addPanel = document.getElementById('add-source-form');
    var copyBtn = document.getElementById('copy-receive-url');
    var receiveUrl = document.getElementById('receive-url');

    if (toggleBtn && addPanel) {
        toggleBtn.addEventListener('click', function () {
            addPanel.classList.toggle('api-settings-add-panel--open');
        });
    }

    if (copyBtn && receiveUrl) {
        copyBtn.addEventListener('click', function () {
            var value = receiveUrl.value;
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(value);
            } else {
                receiveUrl.select();
                document.execCommand('copy');
            }
            var original = copyBtn.innerHTML;
            copyBtn.innerHTML = '<i class="fas fa-check mr-1"></i> Copied';
            setTimeout(function () {
                copyBtn.innerHTML = original;
            }, 1500);
        });
    }
})();
</script>
@endpush
