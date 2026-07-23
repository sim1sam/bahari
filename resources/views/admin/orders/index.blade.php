@extends('layouts.admin')

@section('title', 'All Orders')
@section('page_title', 'All Orders')

@section('content')
    @php
        $outstanding = max(0, $stats['revenue'] - $stats['collected']);
        $statusStyles = [
            'pending' => ['bg' => '#fff7ed', 'text' => '#c2410c', 'dot' => '#f59e0b'],
            'processing' => ['bg' => '#eff6ff', 'text' => '#1d4ed8', 'dot' => '#3b82f6'],
            'shipped' => ['bg' => '#f5f3ff', 'text' => '#6d28d9', 'dot' => '#8b5cf6'],
            'completed' => ['bg' => '#ecfdf5', 'text' => '#047857', 'dot' => '#10b981'],
            'cancelled' => ['bg' => '#fef2f2', 'text' => '#b91c1c', 'dot' => '#ef4444'],
        ];
        $transferStyles = [
            'sent' => 'success',
            'failed' => 'danger',
            'skipped' => 'secondary',
            'pending' => 'warning',
        ];
    @endphp

    <div class="orders-index-page">
        <section class="orders-index-hero">
            <div>
                <span class="orders-index-eyebrow">Order management</span>
                <h2>All Orders</h2>
                <p>Track customer orders, payment status, and workflow.</p>
            </div>
            <div class="orders-index-hero-actions">
                <a href="{{ route('admin.orders.create') }}" class="btn btn-info">
                    <i class="fas fa-plus mr-1"></i> Create Order
                </a>
            </div>
        </section>

        <section class="row orders-index-stats">
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="orders-stat-card orders-stat-card--total">
                    <span class="orders-stat-icon"><i class="fas fa-shopping-bag"></i></span>
                    <div>
                        <div class="orders-stat-value">{{ number_format($stats['total']) }}</div>
                        <div class="orders-stat-label">Total Orders</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="orders-stat-card orders-stat-card--pending">
                    <span class="orders-stat-icon"><i class="fas fa-clock"></i></span>
                    <div>
                        <div class="orders-stat-value">{{ number_format($stats['pending']) }}</div>
                        <div class="orders-stat-label">Pending</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="orders-stat-card orders-stat-card--processing">
                    <span class="orders-stat-icon"><i class="fas fa-cog"></i></span>
                    <div>
                        <div class="orders-stat-value">{{ number_format($stats['processing']) }}</div>
                        <div class="orders-stat-label">Processing</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="orders-stat-card orders-stat-card--today">
                    <span class="orders-stat-icon"><i class="fas fa-calendar-day"></i></span>
                    <div>
                        <div class="orders-stat-value">{{ number_format($stats['today']) }}</div>
                        <div class="orders-stat-label">Today</div>
                    </div>
                </article>
            </div>
        </section>

        <section class="orders-index-summary">
            <div class="orders-summary-item">
                <small>Total revenue</small>
                <strong>{{ money($stats['revenue'], 0) }}</strong>
            </div>
            <div class="orders-summary-item">
                <small>Collected</small>
                <strong class="text-success">{{ money($stats['collected'], 0) }}</strong>
            </div>
            <div class="orders-summary-item">
                <small>Outstanding</small>
                <strong class="text-danger">{{ money($outstanding, 0) }}</strong>
            </div>
        </section>

        <div class="card orders-index-card">
            <div class="orders-index-card-head">
                <div>
                    <h3 class="mb-0">Order List</h3>
                    <p class="mb-0 text-muted">Showing {{ $orders->count() }} of {{ $orders->total() }} orders</p>
                </div>
            </div>

            <div class="orders-app-list d-md-none">
                @forelse ($orders as $order)
                    @php
                        $statusKey = $order->adminStatusStyleKey();
                        $statusStyle = $statusStyles[$statusKey] ?? $statusStyles['pending'];
                        $transferKey = $order->external_transfer_status ?? 'pending';
                    @endphp
                    <article class="orders-app-card">
                        <div class="orders-app-card-head">
                            <div>
                                <a href="{{ route('admin.orders.show', $order) }}" class="orders-app-card-number">{{ $order->number }}</a>
                                @if ($order->isCustom())
                                    <span class="orders-tag orders-tag--custom">Custom</span>
                                @endif
                                <div class="orders-app-card-customer">{{ $order->customer_name }}</div>
                                <small class="text-muted">{{ $order->customer_email }}</small>
                            </div>
                            <div class="orders-app-card-total-wrap">
                                <strong class="orders-app-card-total">{{ money($order->total) }}</strong>
                                @if ($order->amountDue() > 0)
                                    <small class="text-danger">Due {{ money($order->amountDue()) }}</small>
                                @endif
                            </div>
                        </div>

                        <div class="orders-app-card-chips">
                            <span class="orders-pill {{ $order->paymentStatusBadgeClass() }}">{{ $order->paymentStatusLabel() }}</span>
                            <span class="orders-pill badge-{{ $transferStyles[$transferKey] ?? 'light' }}">API {{ ucfirst($transferKey) }}</span>
                            <span class="orders-app-status-pill" style="background:{{ $statusStyle['bg'] }};color:{{ $statusStyle['text'] }}">Receiver: {{ $order->adminStatusLabel() }}</span>
                            <span class="orders-pill badge-light">Customer: {{ $order->statusLabel() }}</span>
                        </div>

                        <div class="orders-app-card-field">
                            <label>Customer status (manual)</label>
                            <form action="{{ route('admin.orders.status', $order) }}" method="POST" class="orders-status-form">
                                @csrf
                                @method('PATCH')
                                <select name="status" class="form-control orders-status-select orders-status-select--app" onchange="this.form.submit()" style="--status-bg: {{ $statusStyle['bg'] }}; --status-text: {{ $statusStyle['text'] }};">
                                    @foreach (['pending','processing','shipped','completed','cancelled'] as $status)
                                        <option value="{{ $status }}" @selected($order->status === $status)>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </div>

                        @if ($order->external_transfer_message)
                            <p class="orders-app-card-note" title="{{ $order->external_transfer_message }}">{{ Str::limit($order->external_transfer_message, 60) }}</p>
                        @endif

                        <div class="orders-app-card-foot">
                            <span class="orders-app-card-date">
                                <i class="far fa-clock"></i>
                                {{ $order->created_at->format('M d, Y · h:i A') }}
                            </span>
                            <div class="orders-app-card-actions">
                                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('admin.orders.invoice', $order) }}" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener"><i class="fas fa-file-invoice"></i></a>
                                <a href="{{ route('admin.orders.edit', $order) }}" class="btn btn-sm btn-outline-info"><i class="fas fa-edit"></i></a>
                                @if ($order->canBeDeleted())
                                    <form action="{{ route('admin.orders.destroy', $order) }}" method="POST" onsubmit="return confirm('Delete this order?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="orders-empty orders-app-empty">
                        <i class="fas fa-inbox"></i>
                        <strong>No orders yet</strong>
                        <p>Create your first order to start tracking sales here.</p>
                        <a href="{{ route('admin.orders.create') }}" class="btn btn-sm btn-info mt-2">
                            <i class="fas fa-plus mr-1"></i> Create Order
                        </a>
                    </div>
                @endforelse
            </div>

            <div class="table-responsive d-none d-md-block">
                <table class="table orders-index-table mb-0">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>API Transfer</th>
                            <th>Date</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            @php
                                $statusKey = $order->adminStatusStyleKey();
                                $statusStyle = $statusStyles[$statusKey] ?? $statusStyles['pending'];
                                $transferKey = $order->external_transfer_status ?? 'pending';
                            @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('admin.orders.show', $order) }}" class="orders-number-link">{{ $order->number }}</a>
                                    @if ($order->isCustom())
                                        <span class="orders-tag orders-tag--custom">Custom</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="orders-customer-name">{{ $order->customer_name }}</div>
                                    <small class="text-muted">{{ $order->customer_email }}</small>
                                </td>
                                <td>
                                    <div class="orders-amount">{{ money($order->total) }}</div>
                                    @if ($order->amountDue() > 0)
                                        <small class="text-danger">Due {{ money($order->amountDue()) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="orders-pill {{ $order->paymentStatusBadgeClass() }}">
                                        {{ $order->paymentStatusLabel() }}
                                    </span>
                                </td>
                                <td>
                                    <div class="mb-1">
                                        <span class="orders-app-status-pill" style="background:{{ $statusStyle['bg'] }};color:{{ $statusStyle['text'] }}">{{ $order->adminStatusLabel() }}</span>
                                        <div class="small text-muted mt-1">Customer: {{ $order->statusLabel() }}</div>
                                    </div>
                                    <form action="{{ route('admin.orders.status', $order) }}" method="POST" class="orders-status-form">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status" class="form-control form-control-sm orders-status-select" onchange="this.form.submit()" style="--status-bg: {{ $statusStyle['bg'] }}; --status-text: {{ $statusStyle['text'] }};" title="Customer status (manual)">
                                            @foreach (['pending','processing','shipped','completed','cancelled'] as $status)
                                                <option value="{{ $status }}" @selected($order->status === $status)>{{ ucfirst($status) }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    <span class="orders-pill badge-{{ $transferStyles[$transferKey] ?? 'light' }}">
                                        {{ ucfirst($transferKey) }}
                                    </span>
                                    @if ($order->external_transfer_message)
                                        <div class="orders-transfer-note" title="{{ $order->external_transfer_message }}">
                                            {{ Str::limit($order->external_transfer_message, 28) }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="orders-date">{{ $order->created_at->format('M d, Y') }}</div>
                                    <small class="text-muted">{{ $order->created_at->format('h:i A') }}</small>
                                </td>
                                <td class="text-right">
                                    <div class="orders-actions">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-xs btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.orders.invoice', $order) }}" class="btn btn-xs btn-outline-secondary" target="_blank" rel="noopener" title="Invoice">
                                            <i class="fas fa-file-invoice"></i>
                                        </a>
                                        <a href="{{ route('admin.orders.edit', $order) }}" class="btn btn-xs btn-outline-info" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if ($order->canBeDeleted())
                                            <form action="{{ route('admin.orders.destroy', $order) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this order?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-xs btn-outline-danger" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="orders-empty">
                                    <i class="fas fa-inbox"></i>
                                    <strong>No orders yet</strong>
                                    <p>Create your first order to start tracking sales here.</p>
                                    <a href="{{ route('admin.orders.create') }}" class="btn btn-sm btn-info mt-2">
                                        <i class="fas fa-plus mr-1"></i> Create Order
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($orders->hasPages())
                <div class="orders-index-footer">{{ $orders->links() }}</div>
            @endif
        </div>
    </div>
@endsection

@push('styles')
<style>
    .orders-index-page {
        --orders-ink: #0f172a;
        --orders-muted: #64748b;
        --orders-border: #e2e8f0;
    }

    .orders-index-hero {
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

    .orders-index-eyebrow {
        display: block;
        margin-bottom: 0.2rem;
        color: #a5f3fc;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.09em;
        text-transform: uppercase;
    }

    .orders-index-hero h2 {
        margin: 0;
        font-size: 1.55rem;
        font-weight: 700;
    }

    .orders-index-hero p {
        margin: 0.4rem 0 0;
        color: rgba(255, 255, 255, 0.82);
    }

    .orders-index-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.55rem;
        flex-shrink: 0;
    }

    .orders-stat-card {
        display: flex;
        align-items: center;
        gap: 0.9rem;
        height: 100%;
        padding: 1rem 1.1rem;
        border: 1px solid var(--orders-border);
        border-radius: 0.95rem;
        background: #fff;
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.04);
    }

    .orders-stat-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.6rem;
        height: 2.6rem;
        border-radius: 0.75rem;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .orders-stat-card--total .orders-stat-icon { background: #ecfeff; color: #0891b2; }
    .orders-stat-card--pending .orders-stat-icon { background: #fff7ed; color: #d97706; }
    .orders-stat-card--processing .orders-stat-icon { background: #eff6ff; color: #2563eb; }
    .orders-stat-card--today .orders-stat-icon { background: #f5f3ff; color: #7c3aed; }

    .orders-stat-value {
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--orders-ink);
        line-height: 1.1;
    }

    .orders-stat-label {
        margin-top: 0.15rem;
        color: var(--orders-muted);
        font-size: 0.82rem;
        font-weight: 500;
    }

    .orders-index-summary {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.75rem;
        margin-bottom: 1.25rem;
    }

    .orders-summary-item {
        padding: 0.85rem 1rem;
        border: 1px solid var(--orders-border);
        border-radius: 0.85rem;
        background: #fff;
    }

    .orders-summary-item small {
        display: block;
        color: var(--orders-muted);
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .orders-summary-item strong {
        display: block;
        margin-top: 0.2rem;
        font-size: 1.05rem;
        color: var(--orders-ink);
    }

    .orders-index-card {
        border: 1px solid var(--orders-border);
        border-radius: 1rem;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }

    .orders-index-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.15rem;
        border-bottom: 1px solid #eef2f7;
        background: #fff;
    }

    .orders-index-card-head h3 {
        font-size: 1rem;
        font-weight: 700;
        color: var(--orders-ink);
    }

    .orders-index-card-head p {
        font-size: 0.8rem;
    }

    .orders-index-table thead th {
        border-top: 0;
        border-bottom: 1px solid #e2e8f0;
        background: #f8fafc;
        color: var(--orders-muted);
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        vertical-align: middle;
        white-space: nowrap;
    }

    .orders-index-table tbody td {
        padding-top: 0.85rem;
        padding-bottom: 0.85rem;
        border-top-color: #eef2f7;
        vertical-align: middle;
    }

    .orders-number-link {
        font-weight: 700;
        color: #0891b2;
    }

    .orders-number-link:hover {
        color: #0e7490;
        text-decoration: none;
    }

    .orders-tag {
        display: inline-block;
        margin-left: 0.35rem;
        padding: 0.1rem 0.4rem;
        border-radius: 999px;
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        vertical-align: middle;
    }

    .orders-tag--custom {
        color: #475569;
        background: #f1f5f9;
    }

    .orders-customer-name {
        font-weight: 600;
        color: #334155;
    }

    .orders-amount {
        font-weight: 700;
        color: var(--orders-ink);
    }

    .orders-pill {
        display: inline-block;
        padding: 0.2rem 0.55rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .orders-status-select {
        min-width: 7.5rem;
        border-radius: 999px !important;
        border-color: transparent !important;
        background: var(--status-bg, #f8fafc) !important;
        color: var(--status-text, #334155) !important;
        font-size: 0.75rem !important;
        font-weight: 700;
        text-transform: capitalize;
    }

    .orders-status-select:focus {
        box-shadow: 0 0 0 3px rgba(34, 211, 238, 0.15) !important;
    }

    .orders-transfer-note {
        margin-top: 0.2rem;
        color: var(--orders-muted);
        font-size: 0.72rem;
        max-width: 10rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .orders-date {
        font-weight: 600;
        color: #334155;
        font-size: 0.86rem;
    }

    .orders-actions {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.25rem;
        flex-wrap: wrap;
    }

    .orders-actions .btn {
        width: 1.85rem;
        height: 1.85rem;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .orders-empty {
        padding: 3rem 1rem !important;
        text-align: center;
        color: var(--orders-muted);
    }

    .orders-empty i {
        display: block;
        margin-bottom: 0.75rem;
        font-size: 2rem;
        opacity: 0.45;
    }

    .orders-empty strong {
        display: block;
        color: #334155;
        font-size: 1rem;
    }

    .orders-empty p {
        margin: 0.35rem 0 0;
        font-size: 0.86rem;
    }

    .orders-index-footer {
        padding: 0.85rem 1rem;
        border-top: 1px solid #eef2f7;
        background: #f8fafc;
    }

    .orders-app-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        padding: 0.85rem;
        background: #f8fafc;
    }

    .orders-app-card {
        padding: 1rem;
        border: 1px solid var(--orders-border);
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.05);
    }

    .orders-app-card-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.75rem;
    }

    .orders-app-card-number {
        display: inline-block;
        font-size: 1rem;
        font-weight: 800;
        color: #0891b2;
    }

    .orders-app-card-number:hover {
        color: #0e7490;
        text-decoration: none;
    }

    .orders-app-card-customer {
        margin-top: 0.35rem;
        font-weight: 700;
        color: #334155;
        font-size: 0.9rem;
    }

    .orders-app-card-total-wrap {
        text-align: right;
        flex-shrink: 0;
    }

    .orders-app-card-total {
        display: block;
        font-size: 1.05rem;
        color: var(--orders-ink);
    }

    .orders-app-card-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        margin-bottom: 0.75rem;
    }

    .orders-app-status-pill {
        display: inline-block;
        padding: 0.2rem 0.55rem;
        border-radius: 999px;
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .orders-app-card-field label {
        display: block;
        margin-bottom: 0.35rem;
        color: var(--orders-muted);
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .orders-status-select--app {
        width: 100%;
        min-width: 0;
    }

    .orders-app-card-note {
        margin: 0 0 0.75rem;
        padding: 0.55rem 0.65rem;
        border-radius: 0.6rem;
        background: #f8fafc;
        color: var(--orders-muted);
        font-size: 0.76rem;
        line-height: 1.35;
    }

    .orders-app-card-foot {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.65rem;
        padding-top: 0.75rem;
        border-top: 1px solid #eef2f7;
        flex-wrap: wrap;
    }

    .orders-app-card-date {
        color: var(--orders-muted);
        font-size: 0.74rem;
        font-weight: 600;
    }

    .orders-app-card-actions {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        margin-left: auto;
    }

    .orders-app-card-actions .btn {
        width: 2.15rem;
        height: 2.15rem;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .orders-app-empty {
        border: 1px dashed var(--orders-border);
        border-radius: 1rem;
        background: #fff;
    }

    @media (max-width: 767.98px) {
        .orders-index-hero {
            align-items: flex-start;
            flex-direction: column;
            padding: 1.1rem;
        }

        .orders-index-hero h2 {
            font-size: 1.3rem;
        }

        .orders-index-hero-actions {
            width: 100%;
        }

        .orders-index-hero-actions .btn {
            flex: 1;
        }

        .orders-index-summary {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush
