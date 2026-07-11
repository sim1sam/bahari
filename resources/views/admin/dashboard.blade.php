@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')
    @php
        $statusLabels = [
            'pending' => 'Pending',
            'processing' => 'Processing',
            'shipped' => 'Shipped',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];
        $statusColors = [
            'pending' => '#f59e0b',
            'processing' => '#3b82f6',
            'shipped' => '#8b5cf6',
            'completed' => '#10b981',
            'cancelled' => '#ef4444',
        ];
        $totalStatusOrders = max(1, (int) $ordersByStatus->sum());
        $outstanding = max(0, $stats['revenue'] - $stats['paid_revenue']);
        $collectionRate = $stats['revenue'] > 0
            ? round(($stats['paid_revenue'] / $stats['revenue']) * 100)
            : 0;
        $quickActionKeys = ['orders_create', 'orders_list', 'products', 'customers', 'bank_payments', 'reports', 'settings_branding'];
        $user = auth()->user();
        $initials = strtoupper(substr($user->name, 0, 1));
    @endphp

    <div class="admin-dashboard-v2">
        <section class="dash-v2-hero">
            <div class="dash-v2-hero-main">
                <div class="dash-v2-avatar">{{ $initials }}</div>
                <div>
                    <p class="dash-v2-eyebrow">{{ now()->format('l, M d, Y') }}</p>
                    <h2 class="dash-v2-title">Welcome back, {{ $user->name }}</h2>
                    <p class="dash-v2-subtitle">Here is what is happening at <strong>{{ $site->siteName() }}</strong> today.</p>
                </div>
            </div>
            <div class="dash-v2-hero-pills">
                <div class="dash-v2-pill">
                    <span class="dash-v2-pill-label">Today orders</span>
                    <strong>{{ number_format($stats['today_orders']) }}</strong>
                </div>
                <div class="dash-v2-pill">
                    <span class="dash-v2-pill-label">Today revenue</span>
                    <strong>{{ money($stats['today_revenue'], 0) }}</strong>
                </div>
                @if ($user->canAccessAdminFeature('orders') && $stats['pending_orders'] > 0)
                    <a href="{{ route('admin.orders.index') }}" class="dash-v2-pill dash-v2-pill--alert">
                        <span class="dash-v2-pill-label">Needs action</span>
                        <strong>{{ $stats['pending_orders'] }} pending</strong>
                    </a>
                @endif
            </div>
        </section>

        <section class="row dash-v2-stats">
            <div class="col-xl-3 col-sm-6 mb-4">
                <article class="dash-v2-stat dash-v2-stat--orders">
                    <div class="dash-v2-stat-top">
                        <span class="dash-v2-stat-icon"><i class="fas fa-shopping-bag"></i></span>
                        <span class="dash-v2-stat-badge">+{{ $stats['today_orders'] }} today</span>
                    </div>
                    <div class="dash-v2-stat-value">{{ number_format($stats['orders']) }}</div>
                    <div class="dash-v2-stat-label">Total Orders</div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-4">
                <article class="dash-v2-stat dash-v2-stat--revenue">
                    <div class="dash-v2-stat-top">
                        <span class="dash-v2-stat-icon"><i class="fas fa-chart-line"></i></span>
                        <span class="dash-v2-stat-badge">{{ money($stats['today_revenue'], 0) }}</span>
                    </div>
                    <div class="dash-v2-stat-value">{{ money($stats['revenue'], 0) }}</div>
                    <div class="dash-v2-stat-label">Total Revenue</div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-4">
                <article class="dash-v2-stat dash-v2-stat--products">
                    <div class="dash-v2-stat-top">
                        <span class="dash-v2-stat-icon"><i class="fas fa-box-open"></i></span>
                        <span class="dash-v2-stat-badge">{{ number_format($stats['categories']) }} cats</span>
                    </div>
                    <div class="dash-v2-stat-value">{{ number_format($stats['products']) }}</div>
                    <div class="dash-v2-stat-label">Live Products</div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-4">
                <article class="dash-v2-stat dash-v2-stat--customers">
                    <div class="dash-v2-stat-top">
                        <span class="dash-v2-stat-icon"><i class="fas fa-users"></i></span>
                        <span class="dash-v2-stat-badge">{{ $collectionRate }}% collected</span>
                    </div>
                    <div class="dash-v2-stat-value">{{ number_format($stats['customers']) }}</div>
                    <div class="dash-v2-stat-label">Customers</div>
                </article>
            </div>
        </section>

        <section class="row">
            <div class="col-xl-8 mb-4">
                <div class="dash-v2-panel">
                    <div class="dash-v2-panel-head">
                        <div>
                            <h3 class="dash-v2-panel-title">Recent Orders</h3>
                            <p class="dash-v2-panel-sub">Latest activity across your store</p>
                        </div>
                        @if ($user->canAccessAdminFeature('orders'))
                            <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-primary">View all</a>
                        @endif
                    </div>
                    <div class="table-responsive">
                        <table class="table dash-v2-table mb-0">
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Payment</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentOrders as $order)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.orders.show', $order) }}" class="dash-v2-order-link">{{ $order->number }}</a>
                                        </td>
                                        <td>
                                            <div class="dash-v2-customer-name">{{ $order->customer_name }}</div>
                                            <small class="text-muted">{{ $order->customer_email }}</small>
                                        </td>
                                        <td class="font-weight-bold">{{ money($order->total) }}</td>
                                        <td><span class="badge {{ $order->paymentStatusBadgeClass() }}">{{ $order->paymentStatusLabel() }}</span></td>
                                        <td>
                                            @php
                                                $statusBadge = match ($order->status) {
                                                    'processing' => 'badge-info',
                                                    'shipped' => 'badge-primary',
                                                    'completed' => 'badge-success',
                                                    'cancelled' => 'badge-danger',
                                                    default => 'badge-warning',
                                                };
                                            @endphp
                                            <span class="badge {{ $statusBadge }}">{{ ucfirst($order->status) }}</span>
                                        </td>
                                        <td class="text-muted">{{ $order->created_at->format('M d') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-5">
                                            <i class="fas fa-inbox fa-2x mb-2 d-block opacity-50"></i>
                                            No orders yet. Your first sale will appear here.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 mb-4">
                <div class="dash-v2-panel mb-4">
                    <div class="dash-v2-panel-head">
                        <div>
                            <h3 class="dash-v2-panel-title">Collections</h3>
                            <p class="dash-v2-panel-sub">Payment progress overview</p>
                        </div>
                    </div>
                    <div class="dash-v2-collection">
                        <div class="dash-v2-collection-ring" style="--rate: {{ $collectionRate }}">
                            <span>{{ $collectionRate }}%</span>
                        </div>
                        <div class="dash-v2-collection-meta">
                            <div>
                                <small>Collected</small>
                                <strong>{{ money($stats['paid_revenue'], 0) }}</strong>
                            </div>
                            <div>
                                <small>Outstanding</small>
                                <strong class="text-danger">{{ money($outstanding, 0) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="dash-v2-panel mb-4">
                    <div class="dash-v2-panel-head">
                        <div>
                            <h3 class="dash-v2-panel-title">Order Pipeline</h3>
                            <p class="dash-v2-panel-sub">Status breakdown</p>
                        </div>
                    </div>
                    <div class="dash-v2-pipeline">
                        @foreach ($statusLabels as $status => $label)
                            @php
                                $count = (int) ($ordersByStatus[$status] ?? 0);
                                $percent = round(($count / $totalStatusOrders) * 100);
                            @endphp
                            <div class="dash-v2-pipeline-row">
                                <div class="dash-v2-pipeline-label">
                                    <span class="dash-v2-dot" style="background: {{ $statusColors[$status] }}"></span>
                                    {{ $label }}
                                    <span class="dash-v2-pipeline-count">{{ $count }}</span>
                                </div>
                                <div class="dash-v2-progress">
                                    <div class="dash-v2-progress-bar" style="width: {{ $percent }}%; background: {{ $statusColors[$status] }}"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="dash-v2-panel">
                    <div class="dash-v2-panel-head">
                        <div>
                            <h3 class="dash-v2-panel-title">Quick Actions</h3>
                            <p class="dash-v2-panel-sub">Jump to common tasks</p>
                        </div>
                    </div>
                    <div class="dash-v2-actions">
                        @foreach ($quickActionKeys as $key)
                            @php $feature = config("admin_features.{$key}"); @endphp
                            @if ($feature && $user->canAccessAdminFeature(\App\Support\AdminFeatures::permissionFor($key)))
                                <a href="{{ route($feature['route']) }}" class="dash-v2-action">
                                    <span class="dash-v2-action-icon"><i class="{{ $feature['icon'] }}"></i></span>
                                    <span>{{ $feature['label'] }}</span>
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('styles')
<style>
    .admin-dashboard-v2 {
        --dash-bg: #f8fafc;
        --dash-card: #ffffff;
        --dash-border: #e2e8f0;
        --dash-text: #0f172a;
        --dash-muted: #64748b;
        --dash-accent: #0891b2;
        --dash-accent-soft: #ecfeff;
    }

    .dash-v2-hero {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 1.25rem;
        margin-bottom: 1.5rem;
        padding: 1.5rem 1.75rem;
        border-radius: 1.25rem;
        background:
            radial-gradient(circle at top right, rgba(34, 211, 238, 0.28), transparent 42%),
            linear-gradient(135deg, #0c4a6e 0%, #0f766e 45%, #0891b2 100%);
        color: #fff;
        box-shadow: 0 18px 40px rgba(8, 145, 178, 0.22);
    }

    .dash-v2-hero-main {
        display: flex;
        align-items: center;
        gap: 1rem;
        min-width: 0;
    }

    .dash-v2-avatar {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 3.5rem;
        height: 3.5rem;
        border-radius: 1rem;
        background: rgba(255, 255, 255, 0.16);
        border: 1px solid rgba(255, 255, 255, 0.22);
        font-size: 1.35rem;
        font-weight: 700;
        flex-shrink: 0;
    }

    .dash-v2-eyebrow {
        margin: 0 0 0.25rem;
        font-size: 0.78rem;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        opacity: 0.82;
    }

    .dash-v2-title {
        margin: 0;
        font-size: 1.7rem;
        font-weight: 700;
        line-height: 1.2;
    }

    .dash-v2-subtitle {
        margin: 0.45rem 0 0;
        opacity: 0.9;
        max-width: 34rem;
    }

    .dash-v2-hero-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 0.65rem;
    }

    .dash-v2-pill {
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
        min-width: 7.5rem;
        padding: 0.7rem 0.9rem;
        border-radius: 0.9rem;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.18);
        color: #fff;
        text-decoration: none;
        transition: background 0.15s ease, transform 0.15s ease;
    }

    .dash-v2-pill:hover {
        background: rgba(255, 255, 255, 0.18);
        color: #fff;
        text-decoration: none;
        transform: translateY(-1px);
    }

    .dash-v2-pill--alert {
        background: rgba(245, 158, 11, 0.22);
        border-color: rgba(251, 191, 36, 0.45);
    }

    .dash-v2-pill-label {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        opacity: 0.82;
    }

    .dash-v2-stat {
        height: 100%;
        padding: 1.2rem 1.25rem;
        border-radius: 1.1rem;
        background: var(--dash-card);
        border: 1px solid var(--dash-border);
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
        position: relative;
        overflow: hidden;
    }

    .dash-v2-stat::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--dash-accent);
    }

    .dash-v2-stat--orders::before { background: linear-gradient(90deg, #0891b2, #22d3ee); }
    .dash-v2-stat--revenue::before { background: linear-gradient(90deg, #059669, #34d399); }
    .dash-v2-stat--products::before { background: linear-gradient(90deg, #7c3aed, #a78bfa); }
    .dash-v2-stat--customers::before { background: linear-gradient(90deg, #d97706, #fbbf24); }

    .dash-v2-stat-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.85rem;
    }

    .dash-v2-stat-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.6rem;
        height: 2.6rem;
        border-radius: 0.8rem;
        background: var(--dash-accent-soft);
        color: var(--dash-accent);
        font-size: 1rem;
    }

    .dash-v2-stat--revenue .dash-v2-stat-icon { background: #ecfdf5; color: #059669; }
    .dash-v2-stat--products .dash-v2-stat-icon { background: #f5f3ff; color: #7c3aed; }
    .dash-v2-stat--customers .dash-v2-stat-icon { background: #fffbeb; color: #d97706; }

    .dash-v2-stat-badge {
        font-size: 0.72rem;
        font-weight: 600;
        color: var(--dash-muted);
        background: #f1f5f9;
        padding: 0.2rem 0.5rem;
        border-radius: 999px;
    }

    .dash-v2-stat-value {
        font-size: 1.65rem;
        font-weight: 700;
        color: var(--dash-text);
        line-height: 1.15;
    }

    .dash-v2-stat-label {
        margin-top: 0.2rem;
        color: var(--dash-muted);
        font-size: 0.9rem;
        font-weight: 500;
    }

    .dash-v2-panel {
        background: var(--dash-card);
        border: 1px solid var(--dash-border);
        border-radius: 1.1rem;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }

    .dash-v2-panel-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.15rem 1.25rem 0.85rem;
        border-bottom: 1px solid #f1f5f9;
    }

    .dash-v2-panel-title {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--dash-text);
    }

    .dash-v2-panel-sub {
        margin: 0.2rem 0 0;
        font-size: 0.82rem;
        color: var(--dash-muted);
    }

    .dash-v2-table thead th {
        border-top: 0;
        border-bottom: 1px solid #f1f5f9;
        background: #f8fafc;
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--dash-muted);
        font-weight: 600;
        padding-top: 0.85rem;
        padding-bottom: 0.85rem;
    }

    .dash-v2-table tbody td {
        vertical-align: middle;
        border-top-color: #f1f5f9;
        padding-top: 0.9rem;
        padding-bottom: 0.9rem;
    }

    .dash-v2-order-link {
        font-weight: 600;
        color: var(--dash-accent);
    }

    .dash-v2-order-link:hover {
        color: #0e7490;
    }

    .dash-v2-customer-name {
        font-weight: 500;
        color: var(--dash-text);
    }

    .dash-v2-collection {
        display: flex;
        align-items: center;
        gap: 1.25rem;
        padding: 1.25rem;
    }

    .dash-v2-collection-ring {
        --rate: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 5.5rem;
        height: 5.5rem;
        border-radius: 50%;
        background:
            radial-gradient(closest-side, #fff 72%, transparent 73%),
            conic-gradient(#0891b2 calc(var(--rate) * 1%), #e2e8f0 0);
        font-size: 1rem;
        font-weight: 700;
        color: var(--dash-text);
        flex-shrink: 0;
    }

    .dash-v2-collection-meta {
        display: grid;
        gap: 0.85rem;
        flex: 1;
    }

    .dash-v2-collection-meta small {
        display: block;
        color: var(--dash-muted);
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .dash-v2-collection-meta strong {
        font-size: 1.05rem;
        color: var(--dash-text);
    }

    .dash-v2-pipeline {
        padding: 0.85rem 1.25rem 1.15rem;
    }

    .dash-v2-pipeline-row + .dash-v2-pipeline-row {
        margin-top: 0.75rem;
    }

    .dash-v2-pipeline-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.35rem;
        font-size: 0.86rem;
        font-weight: 500;
        color: #334155;
    }

    .dash-v2-dot {
        width: 0.55rem;
        height: 0.55rem;
        border-radius: 999px;
        flex-shrink: 0;
    }

    .dash-v2-pipeline-count {
        margin-left: auto;
        font-size: 0.78rem;
        color: var(--dash-muted);
    }

    .dash-v2-progress {
        height: 0.45rem;
        border-radius: 999px;
        background: #f1f5f9;
        overflow: hidden;
    }

    .dash-v2-progress-bar {
        height: 100%;
        border-radius: inherit;
        transition: width 0.3s ease;
    }

    .dash-v2-actions {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.65rem;
        padding: 1rem 1.25rem 1.25rem;
    }

    .dash-v2-action {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0.8rem 0.85rem;
        border-radius: 0.85rem;
        background: #f8fafc;
        border: 1px solid var(--dash-border);
        color: var(--dash-text);
        font-size: 0.84rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.15s ease;
    }

    .dash-v2-action:hover {
        color: var(--dash-accent);
        background: var(--dash-accent-soft);
        border-color: #a5f3fc;
        text-decoration: none;
        transform: translateY(-1px);
    }

    .dash-v2-action-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2rem;
        height: 2rem;
        border-radius: 0.6rem;
        background: #fff;
        color: var(--dash-accent);
        flex-shrink: 0;
    }

    @media (max-width: 767.98px) {
        .dash-v2-hero {
            padding: 1.15rem;
        }

        .dash-v2-title {
            font-size: 1.35rem;
        }

        .dash-v2-hero-pills {
            width: 100%;
        }

        .dash-v2-pill {
            flex: 1 1 calc(50% - 0.35rem);
            min-width: 0;
        }

        .dash-v2-actions {
            grid-template-columns: 1fr;
        }

        .dash-v2-collection {
            flex-direction: column;
            text-align: center;
        }

        .dash-v2-collection-meta {
            width: 100%;
            grid-template-columns: 1fr 1fr;
        }
    }
</style>
@endpush
