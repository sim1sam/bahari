@extends('layouts.admin')

@section('title', 'Reports')
@section('page_title', 'Reports')

@section('content')
    <div class="reports-page">
        <section class="reports-hero">
            <div>
                <span class="reports-eyebrow">Finance</span>
                <h2>Financial Reports</h2>
                <p>Track revenue, profit, cash flow, and operating performance across your store.</p>
                <div class="reports-hero-meta">
                    <span class="reports-hero-chip">
                        <i class="fas fa-calendar"></i> {{ $filters->dateFrom }} → {{ $filters->dateTo }}
                    </span>
                    <span class="reports-hero-chip">
                        <i class="fas fa-sliders-h"></i> {{ ucfirst($filters->basis) }} basis
                    </span>
                    <span class="reports-hero-chip">
                        <i class="fas fa-shopping-cart"></i> {{ number_format($overview['order_count']) }} orders
                    </span>
                </div>
            </div>
            <div class="reports-hero-actions">
                <a href="{{ route('admin.reports.profit-loss', $filters->toQueryArray()) }}" class="btn btn-primary">
                    <i class="fas fa-file-invoice-dollar mr-1"></i> Profit &amp; Loss
                </a>
                <a href="{{ route('admin.account-expenses.create') }}" class="btn btn-warning">
                    <i class="fas fa-plus mr-1"></i> Add Expense
                </a>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-info">
                    <i class="fas fa-list mr-1"></i> All Orders
                </a>
            </div>
        </section>

        @include('admin.reports.partials.nav')

        @include('admin.reports.partials.filters', ['action' => route('admin.reports.index')])

        <section class="row">
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="reports-stat reports-stat--revenue">
                    <span class="reports-stat-icon"><i class="fas fa-chart-line"></i></span>
                    <div>
                        <div class="reports-stat-value">{{ money($overview['total_revenue']) }}</div>
                        <div class="reports-stat-label">Total Revenue</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="reports-stat reports-stat--profit">
                    <span class="reports-stat-icon"><i class="fas fa-coins"></i></span>
                    <div>
                        <div class="reports-stat-value">{{ money($overview['gross_profit']) }}</div>
                        <div class="reports-stat-label">Gross Profit</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="reports-stat {{ $overview['net_profit'] >= 0 ? 'reports-stat--profit' : 'reports-stat--loss' }}">
                    <span class="reports-stat-icon"><i class="fas fa-balance-scale"></i></span>
                    <div>
                        <div class="reports-stat-value">{{ money($overview['net_profit']) }}</div>
                        <div class="reports-stat-label">Net {{ $overview['net_profit'] >= 0 ? 'Profit' : 'Loss' }}</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="reports-stat reports-stat--cash">
                    <span class="reports-stat-icon"><i class="fas fa-money-bill-wave"></i></span>
                    <div>
                        <div class="reports-stat-value">{{ money($overview['cash_collected']) }}</div>
                        <div class="reports-stat-label">Cash Collected</div>
                    </div>
                </article>
            </div>
        </section>

        <section class="row">
            <div class="col-md-4 mb-3">
                <article class="reports-metric-card">
                    <div class="reports-metric-label">Accounts Receivable</div>
                    <div class="reports-metric-value">{{ money($overview['accounts_receivable']) }}</div>
                </article>
            </div>
            <div class="col-md-4 mb-3">
                <article class="reports-metric-card">
                    <div class="reports-metric-label">Inventory Value (at cost)</div>
                    <div class="reports-metric-value">{{ money($overview['inventory_value']) }}</div>
                </article>
            </div>
            <div class="col-md-4 mb-3">
                <article class="reports-metric-card">
                    <div class="reports-metric-label">Operating Expenses</div>
                    <div class="reports-metric-value">{{ money($overview['total_expenses']) }}</div>
                </article>
            </div>
        </section>

        <div class="reports-card">
            <div class="reports-card-head">
                <h3>Report Sections</h3>
                <p>Open detailed financial statements and review operating performance.</p>
            </div>
            <div class="reports-quick-links">
                <a href="{{ route('admin.reports.profit-loss', $filters->toQueryArray()) }}" class="reports-quick-link reports-quick-link--pl">
                    <span class="reports-quick-link-icon"><i class="fas fa-file-invoice-dollar"></i></span>
                    <span>
                        <strong>Profit &amp; Loss</strong>
                        <span>Income statement for the selected period</span>
                    </span>
                </a>
                <a href="{{ route('admin.reports.balance-sheet', $filters->toQueryArray()) }}" class="reports-quick-link reports-quick-link--bs">
                    <span class="reports-quick-link-icon"><i class="fas fa-balance-scale"></i></span>
                    <span>
                        <strong>Balance Sheet</strong>
                        <span>Assets, liabilities, and equity snapshot</span>
                    </span>
                </a>
                <a href="{{ route('admin.reports.ledger', $filters->toQueryArray()) }}" class="reports-quick-link reports-quick-link--ledger">
                    <span class="reports-quick-link-icon"><i class="fas fa-book"></i></span>
                    <span>
                        <strong>General Ledger</strong>
                        <span>Debit and credit entries by date</span>
                    </span>
                </a>
                <a href="{{ route('admin.account-expenses.index') }}" class="reports-quick-link reports-quick-link--expense">
                    <span class="reports-quick-link-icon"><i class="fas fa-receipt"></i></span>
                    <span>
                        <strong>Expenses</strong>
                        <span>View and manage operating expenses</span>
                    </span>
                </a>
            </div>
        </div>
    </div>
@endsection

@push('styles')
@include('admin.reports.partials.page-styles')
@endpush
