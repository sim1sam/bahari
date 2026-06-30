@extends('layouts.admin')

@section('title', 'Financial Reports')
@section('page_title', 'Financial Reports')

@section('content')
    @include('admin.reports.partials.nav')

    @include('admin.reports.partials.filters', ['action' => route('admin.reports.index')])

    <div class="row">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ money($overview['total_revenue']) }}</h3>
                    <p>Total Revenue</p>
                </div>
                <div class="icon"><i class="fas fa-chart-line"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ money($overview['gross_profit']) }}</h3>
                    <p>Gross Profit</p>
                </div>
                <div class="icon"><i class="fas fa-coins"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="small-box {{ $overview['net_profit'] >= 0 ? 'bg-teal' : 'bg-danger' }}">
                <div class="inner">
                    <h3>{{ money($overview['net_profit']) }}</h3>
                    <p>Net {{ $overview['net_profit'] >= 0 ? 'Profit' : 'Loss' }}</p>
                </div>
                <div class="icon"><i class="fas fa-balance-scale"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ money($overview['cash_collected']) }}</h3>
                    <p>Cash Collected</p>
                </div>
                <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted small">Accounts Receivable</div>
                    <div class="h4 mb-0">{{ money($overview['accounts_receivable']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted small">Inventory Value (at cost)</div>
                    <div class="h4 mb-0">{{ money($overview['inventory_value']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted small">Operating Expenses</div>
                    <div class="h4 mb-0">{{ money($overview['total_expenses']) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title mb-0">Quick Links</h3></div>
        <div class="card-body d-flex flex-wrap" style="gap: 0.5rem;">
            <a href="{{ route('admin.reports.profit-loss', $filters->toQueryArray()) }}" class="btn btn-outline-primary btn-sm">Profit &amp; Loss</a>
            <a href="{{ route('admin.reports.balance-sheet', $filters->toQueryArray()) }}" class="btn btn-outline-primary btn-sm">Balance Sheet</a>
            <a href="{{ route('admin.reports.ledger', $filters->toQueryArray()) }}" class="btn btn-outline-primary btn-sm">Ledger</a>
            <a href="{{ route('admin.reports.expenses.create') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-plus mr-1"></i> Add Expense</a>
        </div>
    </div>
@endsection
