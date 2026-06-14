@extends('layouts.admin')

@section('title', 'API Settings')
@section('page_title', 'API Settings')

@section('content')
    @if (session('generated_credentials'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <strong>Credentials generated — copy now:</strong>
            <div class="mt-2">
                <p class="mb-1"><strong>API Key:</strong> <code id="gen-key">{{ session('generated_credentials')['api_key'] }}</code></p>
                <p class="mb-0"><strong>API Token:</strong> <code id="gen-token">{{ session('generated_credentials')['api_token'] }}</code></p>
            </div>
        </div>
    @endif

    {{-- Source sites — API Key & Token --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Source Sites — API Key & Token</h3>
            <button type="button" class="btn btn-sm btn-primary" data-toggle="collapse" data-target="#add-source-form">
                <i class="fas fa-plus"></i> Add site
            </button>
        </div>
        <div class="card-body">
            <p class="text-muted small">Paste the API Key and Token from the sending site (e.g. kolkata2dhaka), or generate new credentials and give them to the sender.</p>

            <div id="add-source-form" class="collapse {{ $sources->isEmpty() ? 'show' : '' }} mb-4">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card card-outline card-primary">
                            <div class="card-header"><h3 class="card-title">Paste credentials from sender</h3></div>
                            <form action="{{ route('admin.api-settings.sources.store') }}" method="POST">
                                @csrf
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Site name</label>
                                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="kolkata2dhaka" required value="{{ old('name') }}">
                                        @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="form-group">
                                        <label>API Key</label>
                                        <input type="text" name="api_key" class="form-control @error('api_key') is-invalid @enderror" placeholder="ak_..." required value="{{ old('api_key') }}">
                                        @error('api_key')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="form-group">
                                        <label>API Token</label>
                                        <input type="text" name="api_token" class="form-control @error('api_token') is-invalid @enderror" placeholder="at_..." required value="{{ old('api_token') }}">
                                        @error('api_token')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary">Save API Key & Token</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-outline card-secondary">
                            <div class="card-header"><h3 class="card-title">Generate credentials here</h3></div>
                            <form action="{{ route('admin.api-settings.sources.generate') }}" method="POST">
                                @csrf
                                <div class="card-body">
                                    <p class="text-muted small">Generate keys on this site and copy them into the sender's API Transfer settings.</p>
                                    <div class="form-group mb-0">
                                        <label>Site name</label>
                                        <input type="text" name="name" class="form-control" placeholder="kolkata2dhaka" required>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-secondary">Generate API Key & Token</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            @if ($sources->isEmpty())
                <div class="alert alert-info mb-0">No API sources yet. Click <strong>Add site</strong> to paste or generate credentials.</div>
            @else
                <table class="table table-sm table-bordered mb-0">
                    <thead>
                        <tr>
                            <th>Site</th>
                            <th>API Key</th>
                            <th>API Token</th>
                            <th>Status</th>
                            <th>Added</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sources as $source)
                            <tr>
                                <td><strong>{{ $source->name }}</strong></td>
                                <td><code class="small">{{ $source->api_key }}</code></td>
                                <td><code class="small text-muted">{{ Str::limit($source->api_token, 20) }}…</code></td>
                                <td>
                                    @if ($source->is_active)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>{{ $source->created_at->format('M d, Y') }}</td>
                                <td>
                                    <form action="{{ route('admin.api-settings.sources.destroy', $source) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this API source?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-danger">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- Receive API --}}
    <div class="card card-outline card-info">
        <div class="card-header"><h3 class="card-title">Receive API — webhook URL for sender</h3></div>
        <div class="card-body">
            <p class="mb-2"><strong>Webhook URL (copy to sender):</strong></p>
            <div class="input-group mb-3">
                <input type="text" class="form-control" id="receive-url" value="{{ $receiveUrl }}" readonly>
                <div class="input-group-append">
                    <button type="button" class="btn btn-outline-secondary" onclick="navigator.clipboard.writeText(document.getElementById('receive-url').value)">Copy</button>
                </div>
            </div>

            <form action="{{ route('admin.api-settings.webhook') }}" method="POST">
                @csrf @method('PUT')
                <div class="form-group">
                    <label>Site URL (optional)</label>
                    <input type="url" name="api_webhook_url" class="form-control @error('api_webhook_url') is-invalid @enderror"
                        value="{{ old('api_webhook_url', $webhookBaseUrl) }}"
                        placeholder="https://yourdomain.com">
                    @error('api_webhook_url')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    <small class="text-muted">Leave empty to use <code>APP_URL</code>. <code>/api/content/receive</code> is added automatically.</small>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Save</button>
            </form>

            <hr>
            <p class="mb-1 text-muted small">Method: <strong>POST</strong></p>
            <p class="mb-1 text-muted small">Headers: <code>X-API-Key: {api_key}</code> and <code>Authorization: Bearer {api_token}</code></p>
            <p class="mb-0 text-muted small">Or query: <code>?api_key=...&amp;api_token=...</code></p>
        </div>
    </div>

    <div class="text-muted small">
        <a href="{{ route('admin.content.index') }}">Content</a> ·
        <a href="{{ route('admin.processed.index') }}">Processed</a>
    </div>
@endsection
