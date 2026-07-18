@extends('layouts.admin')

@section('title', 'Sales Report')
@section('page_title', 'Sales Report')

@section('content')
    @include('admin.reports.partials.nav')

    @include('admin.reports.partials.filters', [
        'action' => route('admin.reports.sales'),
        'exportRoute' => route('admin.reports.sales', array_merge($filters->toQueryArray(), ['export' => 'csv'])),
        'paymentBanks' => $paymentBanks ?? collect(),
        'accountHeads' => $accountHeads ?? collect(),
    ])

    <div class="row mb-3">
        <div class="col-md-4 mb-2">
            <div class="card mb-0">
                <div class="card-body py-3">
                    <div class="text-muted small">Sales Price</div>
                    <div class="h4 mb-0">{{ money($report['totals']['sales_price']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-2">
            <div class="card mb-0">
                <div class="card-body py-3">
                    <div class="text-muted small">Procurement Cost</div>
                    <div class="h4 mb-0">{{ money($report['totals']['procurement_cost']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-2">
            <div class="card mb-0">
                <div class="card-body py-3">
                    <div class="text-muted small">Service Charge</div>
                    <div class="h4 mb-0 {{ $report['totals']['service_charge'] >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ money($report['totals']['service_charge']) }}
                    </div>
                    <div class="text-muted small mt-1">Sales Price − Procurement Cost</div>
                </div>
            </div>
        </div>
    </div>

    @if ($report['items_missing_cost'] > 0)
        <div class="alert alert-warning">
            {{ number_format($report['items_missing_cost']) }} order line(s) have no purchase price set — procurement cost may be understated.
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Order-wise Sales</h3>
            <span class="text-muted small">{{ number_format($report['order_count']) }} orders · {{ $filters->dateFrom }} → {{ $filters->dateTo }}</span>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-sm mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Status</th>
                        <th class="text-right">Sales Price</th>
                        <th class="text-right">Procurement Cost</th>
                        <th class="text-right">Service Charge</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($report['rows'] as $row)
                        <tr>
                            <td>{{ $row['date'] }}</td>
                            <td>
                                <a href="{{ route('admin.orders.show', $row['order_id']) }}">{{ $row['number'] }}</a>
                            </td>
                            <td>{{ $row['customer_name'] ?: '—' }}</td>
                            <td>
                                <span class="badge badge-secondary">{{ ucfirst($row['status']) }}</span>
                            </td>
                            <td class="text-right">{{ money($row['sales_price']) }}</td>
                            <td class="text-right">{{ money($row['procurement_cost']) }}</td>
                            <td class="text-right font-weight-bold {{ $row['service_charge'] >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ money($row['service_charge']) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No orders for this period.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($report['order_count'] > 0)
                    <tfoot>
                        <tr class="table-light">
                            <th colspan="4">Total</th>
                            <th class="text-right">{{ money($report['totals']['sales_price']) }}</th>
                            <th class="text-right">{{ money($report['totals']['procurement_cost']) }}</th>
                            <th class="text-right">{{ money($report['totals']['service_charge']) }}</th>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
@endsection

@push('styles')
@include('admin.reports.partials.page-styles')
@endpush
