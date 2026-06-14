@extends('layouts.admin')

@section('title', 'API Received')
@section('page_title', 'API Received')

@section('content')
    @if (session('generated_credentials'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <strong>Credentials generated — copy now:</strong>
            <div class="mt-2">
                <p class="mb-1"><strong>API Key:</strong> <code>{{ session('generated_credentials')['api_key'] }}</code></p>
                <p class="mb-0"><strong>API Token:</strong> <code>{{ session('generated_credentials')['api_token'] }}</code></p>
            </div>
        </div>
    @endif

    {{-- Source sites --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Source Sites — API Key & Token</h3>
            <button type="button" class="btn btn-sm btn-primary" data-toggle="collapse" data-target="#add-source-form">
                <i class="fas fa-plus"></i> Add site
            </button>
        </div>
        <div class="card-body">
            <p class="text-muted small">Register API credentials from the sending site (e.g. kolkata2dhaka). They push products to your receive URL below.</p>

            <div id="add-source-form" class="collapse {{ $sources->isEmpty() ? 'show' : '' }} mb-4">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card card-outline card-primary">
                            <div class="card-header"><h3 class="card-title">Paste credentials from sender</h3></div>
                            <form action="{{ route('admin.api-received.sources.store') }}" method="POST">
                                @csrf
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Site name</label>
                                        <input type="text" name="name" class="form-control" placeholder="kolkata2dhaka" required value="{{ old('name') }}">
                                    </div>
                                    <div class="form-group">
                                        <label>API Key</label>
                                        <input type="text" name="api_key" class="form-control" placeholder="ak_..." required value="{{ old('api_key') }}">
                                    </div>
                                    <div class="form-group">
                                        <label>API Token</label>
                                        <input type="text" name="api_token" class="form-control" placeholder="at_..." required value="{{ old('api_token') }}">
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary">Save API Credentials</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-outline card-secondary">
                            <div class="card-header"><h3 class="card-title">Or generate credentials here</h3></div>
                            <form action="{{ route('admin.api-received.sources.generate') }}" method="POST">
                                @csrf
                                <div class="card-body">
                                    <p class="text-muted small">Generate keys on this site and give them to the sender's webhook configuration.</p>
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
                <div class="alert alert-info mb-0">No API sources yet. Click <strong>Add site</strong> to register credentials.</div>
            @else
                <table class="table table-sm table-bordered mb-0">
                    <thead>
                        <tr>
                            <th>Site</th>
                            <th>API Key</th>
                            <th>Status</th>
                            <th>Added</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sources as $source)
                            <tr>
                                <td><strong>{{ $source->name }}</strong></td>
                                <td><code>{{ $source->api_key }}</code></td>
                                <td>
                                    @if ($source->is_active)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>{{ $source->created_at->format('M d, Y') }}</td>
                                <td>
                                    <form action="{{ route('admin.api-received.sources.destroy', $source) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this API source?')">
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

    {{-- Receive API info --}}
    <div class="card card-outline card-info">
        <div class="card-header"><h3 class="card-title">Receive API — give this URL to the sending site</h3></div>
        <div class="card-body">
            @if ($isLocalWebhook)
                <div class="alert alert-warning">
                    <strong>Remote senders cannot use <code>{{ $appUrl }}</code></strong><br>
                    <code>ecommerce.test</code> only works on your PC. kolkata2dhaka runs on a remote server and gets
                    <em>cURL error 6: Could not resolve host</em> if you use a local URL.<br><br>
                    <strong>Fix:</strong> Expose this site with a public URL (ngrok, Cloudflare Tunnel, or deploy to a real domain), then set it below and update the webhook on kolkata2dhaka.
                </div>
            @endif

            <p class="mb-2"><strong>Webhook URL (copy to kolkata2dhaka):</strong></p>
            <div class="input-group mb-3">
                <input type="text" class="form-control" id="receive-url" value="{{ $receiveUrl }}" readonly>
                <div class="input-group-append">
                    <button type="button" class="btn btn-outline-secondary" onclick="navigator.clipboard.writeText(document.getElementById('receive-url').value)">Copy</button>
                </div>
            </div>

            <form action="{{ route('admin.api-received.webhook') }}" method="POST" class="mb-3">
                @csrf
                @method('PUT')
                <div class="form-group mb-2">
                    <label>Public site URL (for remote senders)</label>
                    <input type="url" name="api_webhook_url" class="form-control @error('api_webhook_url') is-invalid @enderror"
                        value="{{ old('api_webhook_url', $webhookBaseUrl) }}"
                        placeholder="https://your-ngrok-url.ngrok-free.app or https://shop.yourdomain.com">
                    @error('api_webhook_url')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    <small class="text-muted">Base URL only — <code>/api/content/receive</code> is added automatically. Example: run <code>ngrok http 80 --host-header=ecommerce.test</code> then paste the https URL here.</small>
                </div>
                <button type="submit" class="btn btn-sm btn-primary">Save Public URL</button>
            </form>

            <p class="mb-1 text-muted small">Method: <strong>POST</strong></p>
            <p class="mb-1 text-muted small">Headers: <code>X-API-Key: {api_key}</code> and <code>Authorization: Bearer {api_token}</code></p>
            <p class="mb-0 text-muted small">Or query: <code>?api_key=...&amp;api_token=...</code></p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="mb-3 d-flex flex-wrap align-items-center gap-2">
        <div class="btn-group">
            <a href="{{ route('admin.api-received.index', ['status' => 'pending', 'date' => $date]) }}" class="btn btn-sm {{ $status === 'pending' ? 'btn-warning' : 'btn-outline-warning' }}">
                Pending
                @if ($pendingCount > 0)
                    <span class="badge badge-light ml-1">{{ $pendingCount }}</span>
                @endif
            </a>
            <a href="{{ route('admin.api-received.index', ['status' => 'imported', 'date' => $date]) }}" class="btn btn-sm {{ $status === 'imported' ? 'btn-success' : 'btn-outline-success' }}">Imported</a>
            <a href="{{ route('admin.api-received.index', ['status' => 'rejected', 'date' => $date]) }}" class="btn btn-sm {{ $status === 'rejected' ? 'btn-danger' : 'btn-outline-danger' }}">Rejected</a>
            <a href="{{ route('admin.api-received.index', ['status' => 'all', 'date' => $date]) }}" class="btn btn-sm {{ $status === 'all' ? 'btn-primary' : 'btn-outline-primary' }}">All</a>
        </div>
        <form action="{{ route('admin.api-received.index') }}" method="GET" class="form-inline ml-auto">
            <input type="hidden" name="status" value="{{ $status }}">
            <input type="date" name="date" class="form-control form-control-sm mr-2" value="{{ $date }}">
            <button type="submit" class="btn btn-sm btn-outline-secondary mr-1">Filter</button>
            <a href="{{ route('admin.api-received.index', ['status' => $status]) }}" class="btn btn-sm btn-outline-secondary">Reset</a>
        </form>
    </div>

    {{-- Received items --}}
    <div class="card">
        <div class="card-header"><h3 class="card-title">Items Received via API</h3></div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>SKU / Title</th>
                        <th>Source</th>
                        <th>Price (BDT)</th>
                        <th>Created</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr>
                            <td>
                                @if ($item->imageUrl())
                                    <a href="{{ $item->imageUrl() }}" target="_blank" rel="noopener">
                                        <img src="{{ $item->imageUrl() }}" alt="" class="rounded border" style="max-height:48px">
                                    </a>
                                @else — @endif
                            </td>
                            <td>
                                @if ($item->sku)
                                    <code class="small">{{ $item->sku }}</code><br>
                                @endif
                                <strong>{{ $item->title }}</strong>
                            </td>
                            <td>{{ $item->source?->name ?: '—' }}</td>
                            <td>{{ money($item->price) }}</td>
                            <td class="text-nowrap">{{ $item->created_at->format('d M Y') }}</td>
                            <td><span class="badge {{ $item->statusBadgeClass() }}">{{ $item->statusLabel() }}</span></td>
                            <td class="text-nowrap">
                                @if ($item->isPending())
                                    <form action="{{ route('admin.api-received.approve', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Import as product?')">
                                        @csrf
                                        <button type="submit" class="btn btn-xs btn-success">Import</button>
                                    </form>
                                    <button type="button" class="btn btn-xs btn-danger" data-toggle="modal" data-target="#reject-{{ $item->id }}">Reject</button>
                                @elseif ($item->product_id)
                                    <a href="{{ route('admin.products.edit', $item->product_id) }}" class="btn btn-xs btn-info">View Product</a>
                                @endif
                            </td>
                        </tr>

                        @if ($item->isPending())
                            <div class="modal fade" id="reject-{{ $item->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('admin.api-received.reject', $item) }}" method="POST">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Reject Item</h5>
                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Reject <strong>{{ $item->title }}</strong>?</p>
                                                <div class="form-group mb-0">
                                                    <label>Notes (optional)</label>
                                                    <textarea name="admin_notes" class="form-control" rows="2"></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger">Reject</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No items received yet. Configure a source site and push from the sender.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($items->hasPages())
            <div class="card-footer">{{ $items->links() }}</div>
        @endif
    </div>
@endsection
