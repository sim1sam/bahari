@extends('layouts.admin')

@section('title', 'Customers')
@section('page_title', 'Customers')

@section('content')
    <div class="customers-page">
        <section class="customers-hero">
            <div>
                <span class="customers-eyebrow">Customer management</span>
                <h2>Customers</h2>
                <p>Manage registered shoppers, view order activity, and access ledgers or payments.</p>
            </div>
            <div class="customers-hero-actions">
                <a href="{{ route('admin.customers.create') }}" class="btn btn-info">
                    <i class="fas fa-plus mr-1"></i> Add Customer
                </a>
                <a href="{{ route('admin.customer-ledgers.index') }}" class="btn btn-light">
                    <i class="fas fa-book mr-1"></i> Customer Ledgers
                </a>
            </div>
        </section>

        <section class="row customers-stats">
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="customers-stat customers-stat--total">
                    <span class="customers-stat-icon"><i class="fas fa-users"></i></span>
                    <div>
                        <div class="customers-stat-value">{{ number_format($stats['total']) }}</div>
                        <div class="customers-stat-label">Total Customers</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="customers-stat customers-stat--active">
                    <span class="customers-stat-icon"><i class="fas fa-user-check"></i></span>
                    <div>
                        <div class="customers-stat-value">{{ number_format($stats['active']) }}</div>
                        <div class="customers-stat-label">Active</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="customers-stat customers-stat--orders">
                    <span class="customers-stat-icon"><i class="fas fa-shopping-bag"></i></span>
                    <div>
                        <div class="customers-stat-value">{{ number_format($stats['with_orders']) }}</div>
                        <div class="customers-stat-label">With Orders</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="customers-stat customers-stat--new">
                    <span class="customers-stat-icon"><i class="fas fa-user-plus"></i></span>
                    <div>
                        <div class="customers-stat-value">{{ number_format($stats['new_month']) }}</div>
                        <div class="customers-stat-label">New This Month</div>
                    </div>
                </article>
            </div>
        </section>

        <div class="card customers-card">
            <div class="customers-card-head">
                <div>
                    <h3 class="mb-0">All Customers</h3>
                    <p class="mb-0 text-muted">
                        @if ($search)
                            Showing {{ $customers->count() }} result{{ $customers->count() === 1 ? '' : 's' }} for “{{ $search }}”
                        @else
                            {{ $customers->total() }} {{ Str::plural('customer', $customers->total()) }} registered
                        @endif
                    </p>
                </div>
                <form action="{{ route('admin.customers.index') }}" method="GET" class="customers-search">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Search name or email..." value="{{ $search }}">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-info" title="Search">
                                <i class="fas fa-search"></i>
                            </button>
                            @if ($search)
                                <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary" title="Clear">
                                    <i class="fas fa-times"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table customers-table mb-0">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Status</th>
                            <th>Orders</th>
                            <th>Joined</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($customers as $customer)
                            <tr>
                                <td>
                                    <div class="customers-person">
                                        @if ($customer->avatarUrl())
                                            <img src="{{ $customer->avatarUrl() }}" alt="" class="customers-avatar customers-avatar--image">
                                        @else
                                            <span class="customers-avatar">{{ $customer->initials() }}</span>
                                        @endif
                                        <div>
                                            <div class="customers-name">{{ $customer->name }}</div>
                                            <small class="text-muted">{{ $customer->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="customers-status {{ $customer->hasActiveRole() ? 'customers-status--active' : 'customers-status--inactive' }}">
                                        {{ $customer->hasActiveRole() ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="customers-orders-count">
                                        <i class="fas fa-shopping-bag"></i>
                                        {{ number_format($customer->orders_count) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="customers-date">{{ $customer->created_at->format('M d, Y') }}</div>
                                    <small class="text-muted">{{ $customer->created_at->diffForHumans() }}</small>
                                </td>
                                <td class="text-right">
                                    <div class="customers-actions">
                                        <a href="{{ route('admin.customer-ledgers.show', $customer) }}" class="btn btn-xs btn-outline-secondary" title="View Ledger">
                                            <i class="fas fa-book"></i>
                                        </a>
                                        <a href="{{ route('admin.bank-payments.create', ['customer_id' => $customer->id]) }}" class="btn btn-xs btn-outline-success" title="Record Payment">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </a>
                                        <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-xs btn-outline-info" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.customers.destroy', $customer) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this customer?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-outline-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="customers-empty">
                                    <i class="fas fa-users"></i>
                                    <strong>@if ($search) No matching customers @else No customers yet @endif</strong>
                                    <p>
                                        @if ($search)
                                            Try a different name or email, or clear the search.
                                        @else
                                            Add your first customer to start managing accounts here.
                                        @endif
                                    </p>
                                    @if ($search)
                                        <a href="{{ route('admin.customers.index') }}" class="btn btn-sm btn-outline-secondary mt-2">Clear Search</a>
                                    @else
                                        <a href="{{ route('admin.customers.create') }}" class="btn btn-sm btn-info mt-2">
                                            <i class="fas fa-plus mr-1"></i> Add Customer
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($customers->hasPages())
                <div class="customers-card-footer">{{ $customers->links() }}</div>
            @endif
        </div>
    </div>
@endsection

@push('styles')
<style>
    .customers-page {
        --cu-ink: #0f172a;
        --cu-muted: #64748b;
        --cu-border: #e2e8f0;
    }

    .customers-hero {
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

    .customers-eyebrow {
        display: block;
        margin-bottom: 0.2rem;
        color: #a5f3fc;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.09em;
        text-transform: uppercase;
    }

    .customers-hero h2 {
        margin: 0;
        font-size: 1.55rem;
        font-weight: 700;
    }

    .customers-hero p {
        margin: 0.4rem 0 0;
        color: rgba(255, 255, 255, 0.82);
    }

    .customers-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.55rem;
        flex-shrink: 0;
    }

    .customers-stat {
        display: flex;
        align-items: center;
        gap: 0.9rem;
        height: 100%;
        padding: 1rem 1.1rem;
        border: 1px solid var(--cu-border);
        border-radius: 0.95rem;
        background: #fff;
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.04);
    }

    .customers-stat-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.6rem;
        height: 2.6rem;
        border-radius: 0.75rem;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .customers-stat--total .customers-stat-icon { background: #ecfeff; color: #0891b2; }
    .customers-stat--active .customers-stat-icon { background: #ecfdf5; color: #059669; }
    .customers-stat--orders .customers-stat-icon { background: #eff6ff; color: #2563eb; }
    .customers-stat--new .customers-stat-icon { background: #f5f3ff; color: #7c3aed; }

    .customers-stat-value {
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--cu-ink);
        line-height: 1.1;
    }

    .customers-stat-label {
        margin-top: 0.15rem;
        color: var(--cu-muted);
        font-size: 0.82rem;
        font-weight: 500;
    }

    .customers-card {
        border: 1px solid var(--cu-border);
        border-radius: 1rem;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }

    .customers-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.15rem;
        border-bottom: 1px solid #eef2f7;
        background: #fff;
        flex-wrap: wrap;
    }

    .customers-card-head h3 {
        font-size: 1rem;
        font-weight: 700;
        color: var(--cu-ink);
    }

    .customers-card-head p {
        font-size: 0.8rem;
    }

    .customers-search {
        min-width: min(100%, 18rem);
    }

    .customers-search .form-control {
        min-height: 2.4rem;
        border-color: #dbe3ed;
        border-radius: 0.55rem 0 0 0.55rem;
        box-shadow: none;
    }

    .customers-search .form-control:focus {
        border-color: #22d3ee;
        box-shadow: 0 0 0 3px rgba(34, 211, 238, 0.12);
    }

    .customers-search .btn {
        min-height: 2.4rem;
    }

    .customers-table thead th {
        border-top: 0;
        border-bottom: 1px solid #e2e8f0;
        background: #f8fafc;
        color: var(--cu-muted);
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        vertical-align: middle;
        white-space: nowrap;
    }

    .customers-table tbody td {
        padding-top: 0.9rem;
        padding-bottom: 0.9rem;
        border-top-color: #eef2f7;
        vertical-align: middle;
    }

    .customers-person {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        min-width: 0;
    }

    .customers-avatar {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 0.7rem;
        background: linear-gradient(135deg, #0e7490, #0891b2);
        color: #fff;
        font-size: 0.9rem;
        font-weight: 700;
        flex-shrink: 0;
    }

    .customers-avatar--image {
        object-fit: cover;
        background: #f1f5f9;
    }

    .customers-name {
        font-weight: 700;
        color: #334155;
    }

    .customers-status {
        display: inline-block;
        padding: 0.25rem 0.6rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .customers-status--active {
        color: #047857;
        background: #ecfdf5;
    }

    .customers-status--inactive {
        color: #b91c1c;
        background: #fef2f2;
    }

    .customers-orders-count {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        color: #475569;
        font-weight: 600;
        font-size: 0.88rem;
    }

    .customers-orders-count i {
        color: #94a3b8;
        font-size: 0.75rem;
    }

    .customers-date {
        font-weight: 600;
        color: #334155;
        font-size: 0.86rem;
    }

    .customers-actions {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.25rem;
        flex-wrap: wrap;
    }

    .customers-actions .btn {
        width: 1.85rem;
        height: 1.85rem;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .customers-empty {
        padding: 3rem 1rem !important;
        text-align: center;
        color: var(--cu-muted);
    }

    .customers-empty i {
        display: block;
        margin-bottom: 0.75rem;
        font-size: 2rem;
        opacity: 0.45;
    }

    .customers-empty strong {
        display: block;
        color: #334155;
        font-size: 1rem;
    }

    .customers-empty p {
        margin: 0.35rem 0 0;
        font-size: 0.86rem;
    }

    .customers-card-footer {
        padding: 0.85rem 1rem;
        border-top: 1px solid #eef2f7;
        background: #f8fafc;
    }

    @media (max-width: 767.98px) {
        .customers-hero {
            align-items: flex-start;
            flex-direction: column;
            padding: 1.1rem;
        }

        .customers-hero h2 {
            font-size: 1.3rem;
        }

        .customers-hero-actions {
            width: 100%;
        }

        .customers-hero-actions .btn {
            flex: 1;
        }

        .customers-search {
            width: 100%;
        }
    }
</style>
@endpush
