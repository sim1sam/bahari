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
        $quickActionKeys = ['orders', 'products', 'categories', 'customers', 'homepage', 'settings', 'coupons', 'transactions'];
    @endphp

    <div class="admin-dashboard">
        <div class="dash-hero">
            <div class="dash-hero-copy">
                <p class="dash-hero-eyebrow">Admin overview</p>
                <h2 class="dash-hero-title">Welcome back, {{ auth()->user()->name }}</h2>
                <p class="dash-hero-text">Track sales, manage catalog, and stay on top of new orders for {{ $site->siteName() }}.</p>
            </div>
            <div class="dash-hero-actions">
                @if (auth()->user()->canAccessAdminFeature('orders'))
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-shopping-bag mr-1"></i> All Orders
                    </a>
                @endif
            </div>
        </div>

        <div class="row">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="dash-stat-card dash-stat-card--cyan">
                    <div class="dash-stat-icon"><i class="fas fa-shopping-cart"></i></div>
                    <div>
                        <div class="dash-stat-value">{{ number_format($stats['orders']) }}</div>
                        <div class="dash-stat-label">Total Orders</div>
                        <div class="dash-stat-meta">{{ $stats['today_orders'] }} today</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="dash-stat-card dash-stat-card--emerald">
                    <div class="dash-stat-icon"><i class="fas fa-coins"></i></div>
                    <div>
                        <div class="dash-stat-value">{{ money($stats['revenue'], 0) }}</div>
                        <div class="dash-stat-label">Total Revenue</div>
                        <div class="dash-stat-meta">{{ money($stats['today_revenue'], 0) }} today</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="dash-stat-card dash-stat-card--violet">
                    <div class="dash-stat-icon"><i class="fas fa-tshirt"></i></div>
                    <div>
                        <div class="dash-stat-value">{{ number_format($stats['products']) }}</div>
                        <div class="dash-stat-label">Live Products</div>
                        <div class="dash-stat-meta">{{ number_format($stats['categories']) }} categories</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="dash-stat-card dash-stat-card--amber">
                    <div class="dash-stat-icon"><i class="fas fa-users"></i></div>
                    <div>
                        <div class="dash-stat-value">{{ number_format($stats['customers']) }}</div>
                        <div class="dash-stat-label">Customers</div>
                        <div class="dash-stat-meta">{{ money($stats['paid_revenue'], 0) }} collected</div>
                    </div>
                </div>
            </div>
        </div>

        @if ($stats['pending_orders'] > 0 && auth()->user()->canAccessAdminFeature('orders'))
            <div class="dash-alert mb-4">
                <div>
                    <strong>{{ $stats['pending_orders'] }} pending {{ Str::plural('order', $stats['pending_orders']) }}</strong>
                    <span class="text-muted ml-1">need review or approval.</span>
                </div>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-warning">Review orders</a>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="card dash-panel h-100">
                    <div class="card-header border-0 d-flex align-items-center">
                        <h3 class="card-title mb-0">Recent Orders</h3>
                        @if (auth()->user()->canAccessAdminFeature('orders'))
                            <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-primary ml-auto">View all</a>
                        @endif
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover dash-table mb-0">
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
                                            <a href="{{ route('admin.orders.show', $order) }}" class="font-weight-semibold">{{ $order->number }}</a>
                                        </td>
                                        <td>
                                            <div>{{ $order->customer_name }}</div>
                                            <small class="text-muted">{{ $order->customer_email }}</small>
                                        </td>
                                        <td class="font-weight-semibold">{{ money($order->total) }}</td>
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
                                        <td class="text-muted">{{ $order->created_at->format('M d, Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-5">No orders yet. Your first sale will show up here.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-4">
                <div class="card dash-panel mb-4">
                    <div class="card-header border-0">
                        <h3 class="card-title mb-0">Orders by Status</h3>
                    </div>
                    <div class="card-body">
                        @foreach ($statusLabels as $status => $label)
                            @php $count = (int) ($ordersByStatus[$status] ?? 0); @endphp
                            <div class="dash-status-row">
                                <div class="dash-status-label">
                                    <span class="dash-status-dot" style="background: {{ $statusColors[$status] }}"></span>
                                    {{ $label }}
                                </div>
                                <strong>{{ $count }}</strong>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="card dash-panel">
                    <div class="card-header border-0">
                        <h3 class="card-title mb-0">Quick Actions</h3>
                    </div>
                    <div class="card-body">
                        <div class="dash-actions">
                            @foreach ($quickActionKeys as $key)
                                @php $feature = config("admin_features.{$key}"); @endphp
                                @if ($feature && auth()->user()->canAccessAdminFeature($key))
                                    <a href="{{ route($feature['route']) }}" class="dash-action">
                                        <span class="dash-action-icon"><i class="{{ $feature['icon'] }}"></i></span>
                                        <span>{{ $feature['label'] }}</span>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .admin-dashboard .dash-hero {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.5rem;
        padding: 1.5rem 1.75rem;
        border-radius: 1rem;
        background: linear-gradient(135deg, #0f766e 0%, #0891b2 55%, #1d4ed8 100%);
        color: #fff;
        box-shadow: 0 12px 30px rgba(8, 145, 178, 0.22);
    }

    .admin-dashboard .dash-hero-eyebrow {
        margin: 0 0 0.35rem;
        font-size: 0.78rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        opacity: 0.85;
    }

    .admin-dashboard .dash-hero-title {
        margin: 0;
        font-size: 1.65rem;
        font-weight: 700;
    }

    .admin-dashboard .dash-hero-text {
        margin: 0.5rem 0 0;
        max-width: 38rem;
        opacity: 0.92;
    }

    .admin-dashboard .dash-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .admin-dashboard .dash-stat-card {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        height: 100%;
        padding: 1.25rem;
        border-radius: 1rem;
        background: #fff;
        border: 1px solid #e5e7eb;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
    }

    .admin-dashboard .dash-stat-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 3rem;
        height: 3rem;
        border-radius: 0.9rem;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .admin-dashboard .dash-stat-card--cyan .dash-stat-icon { background: #ecfeff; color: #0891b2; }
    .admin-dashboard .dash-stat-card--emerald .dash-stat-icon { background: #ecfdf5; color: #059669; }
    .admin-dashboard .dash-stat-card--violet .dash-stat-icon { background: #f5f3ff; color: #7c3aed; }
    .admin-dashboard .dash-stat-card--amber .dash-stat-icon { background: #fffbeb; color: #d97706; }

    .admin-dashboard .dash-stat-value {
        font-size: 1.55rem;
        font-weight: 700;
        line-height: 1.2;
        color: #111827;
    }

    .admin-dashboard .dash-stat-label {
        margin-top: 0.15rem;
        color: #6b7280;
        font-size: 0.92rem;
    }

    .admin-dashboard .dash-stat-meta {
        margin-top: 0.35rem;
        font-size: 0.8rem;
        color: #9ca3af;
    }

    .admin-dashboard .dash-alert {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.9rem 1.1rem;
        border-radius: 0.85rem;
        background: #fffbeb;
        border: 1px solid #fde68a;
    }

    .admin-dashboard .dash-panel {
        border: 0;
        border-radius: 1rem;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
    }

    .admin-dashboard .dash-panel .card-header {
        background: #fff;
        padding-top: 1.1rem;
    }

    .admin-dashboard .dash-table thead th {
        border-top: 0;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #6b7280;
        background: #f9fafb;
    }

    .admin-dashboard .dash-status-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.55rem 0;
        border-bottom: 1px solid #f3f4f6;
    }

    .admin-dashboard .dash-status-row:last-child {
        border-bottom: 0;
    }

    .admin-dashboard .dash-status-label {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        color: #374151;
    }

    .admin-dashboard .dash-status-dot {
        width: 0.55rem;
        height: 0.55rem;
        border-radius: 999px;
    }

    .admin-dashboard .dash-actions {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.65rem;
    }

    .admin-dashboard .dash-action {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0.8rem 0.85rem;
        border-radius: 0.8rem;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        color: #111827;
        font-weight: 600;
        font-size: 0.88rem;
        transition: all 0.15s ease;
    }

    .admin-dashboard .dash-action:hover {
        color: #0f766e;
        background: #ecfeff;
        border-color: #a5f3fc;
        text-decoration: none;
        transform: translateY(-1px);
    }

    .admin-dashboard .dash-action-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2rem;
        height: 2rem;
        border-radius: 0.55rem;
        background: #fff;
        color: #0891b2;
    }

    @media (max-width: 767.98px) {
        .admin-dashboard .dash-hero {
            padding: 1.15rem;
        }

        .admin-dashboard .dash-hero-title {
            font-size: 1.35rem;
        }

        .admin-dashboard .dash-actions {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush
