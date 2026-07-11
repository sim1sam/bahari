@extends('layouts.admin')

@section('title', 'Transactions')
@section('page_title', 'Transactions')

@section('content')
    <div class="transactions-page">
        <section class="transactions-hero">
            <div>
                <span class="transactions-eyebrow">Payment settings</span>
                <h2>Transactions</h2>
                <p>Review customer payment submissions and verify uploaded bank transfer evidence.</p>
            </div>
            <div class="transactions-hero-actions">
                <a href="{{ route('admin.bank-payments.create') }}" class="btn btn-info">
                    <i class="fas fa-plus mr-1"></i> Record Payment
                </a>
                <a href="{{ route('admin.customer-ledgers.index') }}" class="btn btn-light">
                    <i class="fas fa-book mr-1"></i> Customer Ledgers
                </a>
            </div>
        </section>

        <section class="row transactions-stats">
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="transactions-stat transactions-stat--total">
                    <span class="transactions-stat-icon"><i class="fas fa-receipt"></i></span>
                    <div>
                        <div class="transactions-stat-value">{{ number_format($stats['total']) }}</div>
                        <div class="transactions-stat-label">All Transactions</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="transactions-stat transactions-stat--pending">
                    <span class="transactions-stat-icon"><i class="fas fa-clock"></i></span>
                    <div>
                        <div class="transactions-stat-value">{{ number_format($stats['pending']) }}</div>
                        <div class="transactions-stat-label">Pending Review</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="transactions-stat transactions-stat--approved">
                    <span class="transactions-stat-icon"><i class="fas fa-check-circle"></i></span>
                    <div>
                        <div class="transactions-stat-value">{{ number_format($stats['approved']) }}</div>
                        <div class="transactions-stat-label">Approved</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="transactions-stat transactions-stat--amount">
                    <span class="transactions-stat-icon"><i class="fas fa-coins"></i></span>
                    <div>
                        <div class="transactions-stat-value">{{ money($stats['approved_amount'], 0) }}</div>
                        <div class="transactions-stat-label">Approved Value</div>
                    </div>
                </article>
            </div>
        </section>

        <div class="card transactions-card">
            <div class="transactions-card-head">
                <div>
                    <h3 class="mb-0">Payment Submissions</h3>
                    <p class="mb-0 text-muted">Showing {{ $transactions->count() }} of {{ $transactions->total() }} transactions</p>
                </div>
                <nav class="transactions-filters" aria-label="Transaction status filters">
                    <a href="{{ route('admin.transactions.index', ['status' => 'pending']) }}" class="{{ $status === 'pending' ? 'active transactions-filter--pending' : '' }}">
                        Pending <span>{{ $stats['pending'] }}</span>
                    </a>
                    <a href="{{ route('admin.transactions.index', ['status' => 'approved']) }}" class="{{ $status === 'approved' ? 'active transactions-filter--approved' : '' }}">
                        Approved <span>{{ $stats['approved'] }}</span>
                    </a>
                    <a href="{{ route('admin.transactions.index', ['status' => 'rejected']) }}" class="{{ $status === 'rejected' ? 'active transactions-filter--rejected' : '' }}">
                        Rejected <span>{{ $stats['rejected'] }}</span>
                    </a>
                    <a href="{{ route('admin.transactions.index', ['status' => 'all']) }}" class="{{ $status === 'all' ? 'active transactions-filter--all' : '' }}">
                        All <span>{{ $stats['total'] }}</span>
                    </a>
                </nav>
            </div>

            <div class="table-responsive">
                <table class="table transactions-table mb-0">
                <thead>
                    <tr>
                        <th>Transaction</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Bank</th>
                        <th>Evidence</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $transaction)
                        <tr>
                            <td>
                                <a href="{{ route('admin.transactions.show', $transaction) }}" class="transactions-id">#{{ $transaction->id }}</a>
                                <a href="{{ route('admin.orders.show', $transaction->order) }}" class="transactions-order">
                                    {{ $transaction->order->number }}
                                </a>
                            </td>
                            <td>
                                <div class="transactions-customer">{{ $transaction->order->customer_name }}</div>
                                <small class="text-muted">{{ $transaction->order->customer_email }}</small>
                            </td>
                            <td><strong class="transactions-amount">{{ money($transaction->amount) }}</strong></td>
                            <td>
                                <span class="transactions-bank">
                                    <i class="fas fa-university"></i>
                                    {{ $transaction->bank_name ?: 'Not specified' }}
                                </span>
                            </td>
                            <td>
                                @if ($transaction->screenshotUrl())
                                    <a href="{{ $transaction->screenshotUrl() }}" target="_blank" rel="noopener" class="transactions-evidence" title="Open payment screenshot">
                                        <img src="{{ $transaction->screenshotUrl() }}" alt="Payment evidence">
                                        <span><i class="fas fa-expand-alt"></i></span>
                                    </a>
                                @else
                                    <span class="transactions-no-evidence"><i class="far fa-image"></i> None</span>
                                @endif
                            </td>
                            <td>
                                <span class="transactions-status transactions-status--{{ $transaction->status }}">
                                    <i class="fas fa-circle"></i> {{ $transaction->statusLabel() }}
                                </span>
                            </td>
                            <td>
                                <div class="transactions-date">{{ $transaction->created_at->format('M d, Y') }}</div>
                                <small class="text-muted">{{ $transaction->created_at->format('h:i A') }}</small>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.transactions.show', $transaction) }}" class="btn btn-sm {{ $transaction->isPending() ? 'btn-info' : 'btn-outline-secondary' }}">
                                    <i class="fas {{ $transaction->isPending() ? 'fa-search' : 'fa-eye' }} mr-1"></i>
                                    {{ $transaction->isPending() ? 'Review' : 'View' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="transactions-empty">
                                <i class="fas fa-file-invoice-dollar"></i>
                                <strong>
                                @if ($status === 'pending')
                                    No pending transactions
                                @else
                                    No transactions found
                                @endif
                                </strong>
                                <p>
                                    @if ($status === 'pending')
                                        All submitted payment evidence has been reviewed.
                                    @else
                                        There are no transactions matching this status.
                                    @endif
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>

            @if ($transactions->hasPages())
                <div class="transactions-card-footer">{{ $transactions->links() }}</div>
            @endif
        </div>
    </div>
@endsection

@push('styles')
<style>
    .transactions-page {
        --tx-ink: #0f172a;
        --tx-muted: #64748b;
        --tx-border: #e2e8f0;
    }

    .transactions-hero {
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

    .transactions-eyebrow {
        display: block;
        margin-bottom: 0.2rem;
        color: #a5f3fc;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.09em;
        text-transform: uppercase;
    }

    .transactions-hero h2 {
        margin: 0;
        font-size: 1.55rem;
        font-weight: 700;
    }

    .transactions-hero p {
        margin: 0.4rem 0 0;
        color: rgba(255, 255, 255, 0.82);
    }

    .transactions-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.55rem;
        flex-shrink: 0;
    }

    .transactions-stat {
        display: flex;
        align-items: center;
        gap: 0.9rem;
        height: 100%;
        padding: 1rem 1.1rem;
        border: 1px solid var(--tx-border);
        border-radius: 0.95rem;
        background: #fff;
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.04);
    }

    .transactions-stat-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.6rem;
        height: 2.6rem;
        border-radius: 0.75rem;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .transactions-stat--total .transactions-stat-icon { background: #ecfeff; color: #0891b2; }
    .transactions-stat--pending .transactions-stat-icon { background: #fff7ed; color: #d97706; }
    .transactions-stat--approved .transactions-stat-icon { background: #ecfdf5; color: #059669; }
    .transactions-stat--amount .transactions-stat-icon { background: #eff6ff; color: #2563eb; }

    .transactions-stat-value {
        color: var(--tx-ink);
        font-size: 1.2rem;
        font-weight: 700;
        line-height: 1.1;
    }

    .transactions-stat-label {
        margin-top: 0.15rem;
        color: var(--tx-muted);
        font-size: 0.82rem;
        font-weight: 500;
    }

    .transactions-card {
        border: 1px solid var(--tx-border);
        border-radius: 1rem;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }

    .transactions-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.15rem;
        border-bottom: 1px solid #eef2f7;
        background: #fff;
        flex-wrap: wrap;
    }

    .transactions-card-head h3 {
        color: var(--tx-ink);
        font-size: 1rem;
        font-weight: 700;
    }

    .transactions-card-head p { font-size: 0.8rem; }

    .transactions-filters {
        display: flex;
        gap: 0.3rem;
        padding: 0.25rem;
        border-radius: 0.75rem;
        background: #f1f5f9;
        flex-wrap: wrap;
    }

    .transactions-filters a {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.45rem 0.65rem;
        border-radius: 0.55rem;
        color: #64748b;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .transactions-filters a:hover { color: #0f172a; text-decoration: none; }
    .transactions-filters a.active { background: #fff; box-shadow: 0 2px 7px rgba(15, 23, 42, 0.08); }
    .transactions-filters a span { color: #94a3b8; font-size: 0.68rem; }
    .transactions-filters .transactions-filter--pending { color: #b45309; }
    .transactions-filters .transactions-filter--approved { color: #047857; }
    .transactions-filters .transactions-filter--rejected { color: #b91c1c; }
    .transactions-filters .transactions-filter--all { color: #0891b2; }

    .transactions-table thead th {
        border-top: 0;
        border-bottom: 1px solid #e2e8f0;
        background: #f8fafc;
        color: var(--tx-muted);
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        vertical-align: middle;
        white-space: nowrap;
    }

    .transactions-table tbody td {
        padding-top: 0.85rem;
        padding-bottom: 0.85rem;
        border-top-color: #eef2f7;
        vertical-align: middle;
    }

    .transactions-id {
        display: block;
        color: #0891b2;
        font-weight: 700;
    }

    .transactions-order {
        display: block;
        margin-top: 0.1rem;
        color: #64748b;
        font-size: 0.74rem;
    }

    .transactions-id:hover,
    .transactions-order:hover { color: #0e7490; text-decoration: none; }
    .transactions-customer { color: #334155; font-weight: 600; }
    .transactions-amount { color: var(--tx-ink); white-space: nowrap; }

    .transactions-bank {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        color: #475569;
        font-size: 0.82rem;
        white-space: nowrap;
    }

    .transactions-bank i { color: #94a3b8; }

    .transactions-evidence {
        position: relative;
        display: inline-block;
        width: 3rem;
        height: 2.4rem;
        overflow: hidden;
        border: 1px solid #dbe3ed;
        border-radius: 0.55rem;
        background: #f8fafc;
    }

    .transactions-evidence img { width: 100%; height: 100%; object-fit: cover; }

    .transactions-evidence span {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        background: rgba(15, 23, 42, 0.55);
        opacity: 0;
        transition: opacity 0.15s ease;
    }

    .transactions-evidence:hover span { opacity: 1; }

    .transactions-no-evidence {
        color: #94a3b8;
        font-size: 0.78rem;
        white-space: nowrap;
    }

    .transactions-status {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.25rem 0.6rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .transactions-status i { font-size: 0.4rem; }
    .transactions-status--pending { color: #b45309; background: #fff7ed; }
    .transactions-status--approved { color: #047857; background: #ecfdf5; }
    .transactions-status--rejected { color: #b91c1c; background: #fef2f2; }
    .transactions-date { color: #334155; font-size: 0.84rem; font-weight: 600; white-space: nowrap; }

    .transactions-empty {
        padding: 3rem 1rem !important;
        color: var(--tx-muted);
        text-align: center;
    }

    .transactions-empty > i {
        display: block;
        margin-bottom: 0.75rem;
        font-size: 2rem;
        opacity: 0.4;
    }

    .transactions-empty strong { display: block; color: #334155; font-size: 1rem; }
    .transactions-empty p { margin: 0.35rem 0 0; font-size: 0.86rem; }

    .transactions-card-footer {
        padding: 0.85rem 1rem;
        border-top: 1px solid #eef2f7;
        background: #f8fafc;
    }

    @media (max-width: 767.98px) {
        .transactions-hero {
            align-items: flex-start;
            flex-direction: column;
            padding: 1.1rem;
        }

        .transactions-hero h2 { font-size: 1.3rem; }
        .transactions-hero-actions { width: 100%; }
        .transactions-hero-actions .btn { flex: 1; }
        .transactions-filters { width: 100%; }
        .transactions-filters a { flex: 1; justify-content: center; }
    }
</style>
@endpush
