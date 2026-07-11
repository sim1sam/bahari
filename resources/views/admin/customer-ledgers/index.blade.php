@extends('layouts.admin')

@section('title', 'Customer Ledgers')
@section('page_title', 'Customer Ledgers')

@section('content')
    <div class="customer-ledgers-page">
        <section class="customer-ledgers-hero">
            <div>
                <span class="customer-ledgers-eyebrow">Payment settings</span>
                <h2>Customer Ledgers</h2>
                <p>Track order charges, payments, and outstanding balances for every customer.</p>
            </div>
            <div class="customer-ledgers-hero-actions">
                <a href="{{ route('admin.bank-payments.create') }}" class="btn btn-info">
                    <i class="fas fa-plus mr-1"></i> Record Payment
                </a>
                <a href="{{ route('admin.payment-banks.index') }}" class="btn btn-light">
                    <i class="fas fa-university mr-1"></i> Payment Banks
                </a>
            </div>
        </section>

        <section class="row customer-ledgers-stats">
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="customer-ledgers-stat customer-ledgers-stat--customers">
                    <span class="customer-ledgers-stat-icon"><i class="fas fa-users"></i></span>
                    <div>
                        <div class="customer-ledgers-stat-value">{{ number_format($stats['customers']) }}</div>
                        <div class="customer-ledgers-stat-label">Customers</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="customer-ledgers-stat customer-ledgers-stat--orders">
                    <span class="customer-ledgers-stat-icon"><i class="fas fa-shopping-bag"></i></span>
                    <div>
                        <div class="customer-ledgers-stat-value">{{ money($stats['total_orders'], 0) }}</div>
                        <div class="customer-ledgers-stat-label">Total Orders</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="customer-ledgers-stat customer-ledgers-stat--paid">
                    <span class="customer-ledgers-stat-icon"><i class="fas fa-check-circle"></i></span>
                    <div>
                        <div class="customer-ledgers-stat-value">{{ money($stats['total_paid'], 0) }}</div>
                        <div class="customer-ledgers-stat-label">Total Paid</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="customer-ledgers-stat customer-ledgers-stat--due">
                    <span class="customer-ledgers-stat-icon"><i class="fas fa-exclamation-circle"></i></span>
                    <div>
                        <div class="customer-ledgers-stat-value">{{ money($stats['outstanding'], 0) }}</div>
                        <div class="customer-ledgers-stat-label">Outstanding ({{ number_format($stats['with_balance']) }})</div>
                    </div>
                </article>
            </div>
        </section>

        <div class="card customer-ledgers-card">
            <div class="customer-ledgers-card-head">
                <div>
                    <h3 class="mb-0">Customer Balances</h3>
                    <p class="mb-0 text-muted">
                        @if ($search)
                            Showing {{ $summaries->count() }} result{{ $summaries->count() === 1 ? '' : 's' }} for “{{ $search }}”
                        @else
                            {{ $summaries->count() }} {{ Str::plural('customer', $summaries->count()) }} in the ledger
                        @endif
                    </p>
                </div>
                <form action="{{ route('admin.customer-ledgers.index') }}" method="GET" class="customer-ledgers-search">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Search name or email..." value="{{ $search }}">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-info" title="Search">
                                <i class="fas fa-search"></i>
                            </button>
                            @if ($search)
                                <a href="{{ route('admin.customer-ledgers.index') }}" class="btn btn-outline-secondary" title="Clear">
                                    <i class="fas fa-times"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table customer-ledgers-table mb-0">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th class="text-right">Total Orders</th>
                            <th class="text-right">Total Paid</th>
                            <th class="text-right">Balance Due</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($summaries as $summary)
                            @php
                                $customer = $summary['user'];
                                $initials = strtoupper(substr($customer->name, 0, 1));
                                $hasDue = $summary['balance'] > 0;
                            @endphp
                            <tr>
                                <td>
                                    <div class="customer-ledgers-person">
                                        <span class="customer-ledgers-avatar">{{ $initials }}</span>
                                        <div>
                                            <div class="customer-ledgers-name">{{ $customer->name }}</div>
                                            <small class="text-muted">{{ $customer->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-right">
                                    <span class="customer-ledgers-amount">{{ money($summary['total_orders']) }}</span>
                                </td>
                                <td class="text-right">
                                    <span class="customer-ledgers-amount customer-ledgers-amount--paid">{{ money($summary['total_paid']) }}</span>
                                </td>
                                <td class="text-right">
                                    <span class="customer-ledgers-balance {{ $hasDue ? 'customer-ledgers-balance--due' : 'customer-ledgers-balance--clear' }}">
                                        {{ money($summary['balance']) }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <div class="customer-ledgers-actions">
                                        <a href="{{ route('admin.customer-ledgers.show', $customer) }}" class="btn btn-xs btn-outline-info" title="View Ledger">
                                            <i class="fas fa-book"></i>
                                        </a>
                                        <a href="{{ route('admin.bank-payments.create', ['customer_id' => $customer->id]) }}" class="btn btn-xs btn-outline-primary" title="Record Payment">
                                            <i class="fas fa-plus"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="customer-ledgers-empty">
                                    <i class="fas fa-book-open"></i>
                                    <strong>@if ($search) No matching customers @else No customers yet @endif</strong>
                                    <p>
                                        @if ($search)
                                            Try a different name or email, or clear the search.
                                        @else
                                            Customer ledgers will appear here once customers are added.
                                        @endif
                                    </p>
                                    @if ($search)
                                        <a href="{{ route('admin.customer-ledgers.index') }}" class="btn btn-sm btn-outline-secondary mt-2">Clear Search</a>
                                    @else
                                        <a href="{{ route('admin.bank-payments.create') }}" class="btn btn-sm btn-info mt-2">
                                            <i class="fas fa-plus mr-1"></i> Record Payment
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .customer-ledgers-page {
        --cl-ink: #0f172a;
        --cl-muted: #64748b;
        --cl-border: #e2e8f0;
    }

    .customer-ledgers-hero {
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

    .customer-ledgers-eyebrow {
        display: block;
        margin-bottom: 0.2rem;
        color: #a5f3fc;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.09em;
        text-transform: uppercase;
    }

    .customer-ledgers-hero h2 {
        margin: 0;
        font-size: 1.55rem;
        font-weight: 700;
    }

    .customer-ledgers-hero p {
        margin: 0.4rem 0 0;
        color: rgba(255, 255, 255, 0.82);
    }

    .customer-ledgers-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.55rem;
        flex-shrink: 0;
    }

    .customer-ledgers-stat {
        display: flex;
        align-items: center;
        gap: 0.9rem;
        height: 100%;
        padding: 1rem 1.1rem;
        border: 1px solid var(--cl-border);
        border-radius: 0.95rem;
        background: #fff;
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.04);
    }

    .customer-ledgers-stat-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.6rem;
        height: 2.6rem;
        border-radius: 0.75rem;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .customer-ledgers-stat--customers .customer-ledgers-stat-icon { background: #ecfeff; color: #0891b2; }
    .customer-ledgers-stat--orders .customer-ledgers-stat-icon { background: #eff6ff; color: #2563eb; }
    .customer-ledgers-stat--paid .customer-ledgers-stat-icon { background: #ecfdf5; color: #059669; }
    .customer-ledgers-stat--due .customer-ledgers-stat-icon { background: #fef2f2; color: #dc2626; }

    .customer-ledgers-stat-value {
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--cl-ink);
        line-height: 1.1;
    }

    .customer-ledgers-stat-label {
        margin-top: 0.15rem;
        color: var(--cl-muted);
        font-size: 0.82rem;
        font-weight: 500;
    }

    .customer-ledgers-card {
        border: 1px solid var(--cl-border);
        border-radius: 1rem;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }

    .customer-ledgers-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.15rem;
        border-bottom: 1px solid #eef2f7;
        background: #fff;
        flex-wrap: wrap;
    }

    .customer-ledgers-card-head h3 {
        font-size: 1rem;
        font-weight: 700;
        color: var(--cl-ink);
    }

    .customer-ledgers-card-head p {
        font-size: 0.8rem;
    }

    .customer-ledgers-search {
        min-width: min(100%, 18rem);
    }

    .customer-ledgers-search .form-control {
        min-height: 2.4rem;
        border-color: #dbe3ed;
        border-radius: 0.55rem 0 0 0.55rem;
        box-shadow: none;
    }

    .customer-ledgers-search .form-control:focus {
        border-color: #22d3ee;
        box-shadow: 0 0 0 3px rgba(34, 211, 238, 0.12);
    }

    .customer-ledgers-search .btn {
        min-height: 2.4rem;
    }

    .customer-ledgers-table thead th {
        border-top: 0;
        border-bottom: 1px solid #e2e8f0;
        background: #f8fafc;
        color: var(--cl-muted);
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        vertical-align: middle;
        white-space: nowrap;
    }

    .customer-ledgers-table tbody td {
        padding-top: 0.9rem;
        padding-bottom: 0.9rem;
        border-top-color: #eef2f7;
        vertical-align: middle;
    }

    .customer-ledgers-person {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        min-width: 0;
    }

    .customer-ledgers-avatar {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.4rem;
        height: 2.4rem;
        border-radius: 0.7rem;
        background: linear-gradient(135deg, #0e7490, #0891b2);
        color: #fff;
        font-size: 0.9rem;
        font-weight: 700;
        flex-shrink: 0;
    }

    .customer-ledgers-name {
        font-weight: 700;
        color: #334155;
    }

    .customer-ledgers-amount {
        font-weight: 600;
        color: var(--cl-ink);
    }

    .customer-ledgers-amount--paid {
        color: #047857;
    }

    .customer-ledgers-balance {
        display: inline-block;
        padding: 0.25rem 0.6rem;
        border-radius: 999px;
        font-size: 0.82rem;
        font-weight: 700;
    }

    .customer-ledgers-balance--due {
        color: #b91c1c;
        background: #fef2f2;
    }

    .customer-ledgers-balance--clear {
        color: #047857;
        background: #ecfdf5;
    }

    .customer-ledgers-actions {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.25rem;
    }

    .customer-ledgers-actions .btn {
        width: 1.85rem;
        height: 1.85rem;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .customer-ledgers-empty {
        padding: 3rem 1rem !important;
        text-align: center;
        color: var(--cl-muted);
    }

    .customer-ledgers-empty i {
        display: block;
        margin-bottom: 0.75rem;
        font-size: 2rem;
        opacity: 0.45;
    }

    .customer-ledgers-empty strong {
        display: block;
        color: #334155;
        font-size: 1rem;
    }

    .customer-ledgers-empty p {
        margin: 0.35rem 0 0;
        font-size: 0.86rem;
    }

    @media (max-width: 767.98px) {
        .customer-ledgers-hero {
            align-items: flex-start;
            flex-direction: column;
            padding: 1.1rem;
        }

        .customer-ledgers-hero h2 {
            font-size: 1.3rem;
        }

        .customer-ledgers-hero-actions {
            width: 100%;
        }

        .customer-ledgers-hero-actions .btn {
            flex: 1;
        }

        .customer-ledgers-search {
            width: 100%;
        }
    }
</style>
@endpush
