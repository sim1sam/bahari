@extends('layouts.admin')

@section('title', 'Balance Sheet')
@section('page_title', 'Balance Sheet')

@section('content')
    @include('admin.reports.partials.nav')

    @include('admin.reports.partials.filters', ['action' => route('admin.reports.balance-sheet')])

    <div class="alert alert-light border">
        Snapshot as of <strong>{{ $report['as_of'] }}</strong>. Inventory uses current stock × purchase price. Retained earnings = cumulative net profit to date.
    </div>

    <div class="row">
        <div class="col-lg-6 mb-3">
            <div class="card card-outline card-success">
                <div class="card-header"><h3 class="card-title mb-0">Assets</h3></div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <tbody>
                            <tr><td>Cash &amp; Bank (collected)</td><td class="text-right">{{ money($report['cash']) }}</td></tr>
                            <tr><td>Accounts Receivable (due from customers)</td><td class="text-right">{{ money($report['accounts_receivable']) }}</td></tr>
                            <tr><td>Inventory ({{ number_format($report['inventory_units']) }} units, {{ $report['products_with_cost'] }} products with cost)</td><td class="text-right">{{ money($report['inventory']) }}</td></tr>
                            <tr class="table-success font-weight-bold"><td>Total Assets</td><td class="text-right">{{ money($report['total_assets']) }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-3">
            <div class="card card-outline card-info">
                <div class="card-header"><h3 class="card-title mb-0">Liabilities &amp; Equity</h3></div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <tbody>
                            <tr class="table-light"><td colspan="2"><strong>Liabilities</strong></td></tr>
                            <tr><td class="pl-4">Accounts Payable</td><td class="text-right">{{ money($report['accounts_payable']) }}</td></tr>
                            <tr class="font-weight-bold"><td>Total Liabilities</td><td class="text-right">{{ money($report['total_liabilities']) }}</td></tr>
                            <tr class="table-light"><td colspan="2"><strong>Equity</strong></td></tr>
                            <tr><td class="pl-4">Retained Earnings (net profit to date)</td><td class="text-right">{{ money($report['retained_earnings']) }}</td></tr>
                            <tr class="font-weight-bold"><td>Total Equity</td><td class="text-right">{{ money($report['total_equity']) }}</td></tr>
                            <tr class="table-info font-weight-bold"><td>Total Liabilities &amp; Equity</td><td class="text-right">{{ money($report['total_liabilities_equity']) }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            @if (! $report['is_balanced'])
                <div class="alert alert-warning mb-0">
                    Balance variance: {{ money($report['variance']) }}. This is expected when inventory or purchase costs are incomplete.
                </div>
            @else
                <div class="alert alert-success mb-0">Assets equal liabilities + equity.</div>
            @endif
        </div>
    </div>
@endsection
