@extends('layouts.admin')

@section('title', 'Profit & Loss')
@section('page_title', 'Profit & Loss')

@section('content')
    @include('admin.reports.partials.nav')

    @include('admin.reports.partials.filters', [
        'action' => route('admin.reports.profit-loss'),
        'exportRoute' => route('admin.reports.profit-loss', array_merge($filters->toQueryArray(), ['export' => 'csv'])),
    ])

    @if (! empty($report['cash_note']))
        <div class="alert alert-info">{{ $report['cash_note'] }}</div>
    @endif

    <div class="row">
        <div class="col-lg-7 mb-3">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title mb-0">Income Statement ({{ ucfirst($report['basis']) }})</h3>
                    <span class="badge badge-light ml-2">{{ $filters->dateFrom }} → {{ $filters->dateTo }}</span>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <tbody>
                            <tr><td>Gross Sales</td><td class="text-right font-weight-bold">{{ money($report['gross_sales']) }}</td></tr>
                            <tr><td class="pl-4 text-muted">Less: Discounts</td><td class="text-right text-danger">({{ money($report['discounts']) }})</td></tr>
                            <tr class="table-light"><td><strong>Net Sales</strong></td><td class="text-right font-weight-bold">{{ money($report['net_sales']) }}</td></tr>
                            <tr><td>Shipping Income</td><td class="text-right">{{ money($report['shipping_income']) }}</td></tr>
                            <tr class="table-primary"><td><strong>Total Revenue</strong></td><td class="text-right font-weight-bold">{{ money($report['total_revenue']) }}</td></tr>
                            <tr><td class="pl-4 text-muted">Less: Cost of Goods Sold (sold)</td><td class="text-right text-danger">({{ money($report['cogs']) }})</td></tr>
                            <tr class="table-light"><td><strong>Gross Profit</strong></td><td class="text-right font-weight-bold">{{ money($report['gross_profit']) }}</td></tr>
                            @if (($report['product_purchases'] ?? 0) > 0)
                                <tr><td class="pl-4 text-muted">Less: Product Purchases (stock added)</td><td class="text-right text-danger">({{ money($report['product_purchases']) }})</td></tr>
                            @endif
                            <tr><td class="pl-4 text-muted">Less: Operating Expenses</td><td class="text-right text-danger">({{ money($report['operating_expenses']) }})</td></tr>
                            <tr class="{{ $report['net_profit'] >= 0 ? 'table-success' : 'table-danger' }}">
                                <td><strong>Net {{ $report['net_profit'] >= 0 ? 'Profit' : 'Loss' }}</strong></td>
                                <td class="text-right font-weight-bold h5 mb-0">{{ money($report['net_profit']) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($report['items_missing_cost'] > 0)
                <div class="alert alert-warning">
                    {{ number_format($report['items_missing_cost']) }} order line(s) have no purchase price set — COGS may be understated. Add purchase price on manual products.
                </div>
            @endif
        </div>

        <div class="col-lg-5 mb-3">
            <div class="card">
                <div class="card-header"><h3 class="card-title mb-0">Summary</h3></div>
                <div class="card-body">
                    <p class="mb-1"><strong>Orders:</strong> {{ number_format($report['order_count']) }}</p>
                    <p class="mb-1"><strong>Margin:</strong>
                        @if ($report['net_sales'] > 0)
                            {{ number_format(($report['gross_profit'] / $report['net_sales']) * 100, 1) }}% gross
                        @else
                            —
                        @endif
                    </p>
                </div>
            </div>

            @if (! empty($report['expense_breakdown']))
                <div class="card">
                    <div class="card-header"><h3 class="card-title mb-0">Expenses by Category</h3></div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-sm mb-0">
                            @foreach ($report['expense_breakdown'] as $row)
                                <tr>
                                    <td>{{ $row['label'] }}</td>
                                    <td class="text-right">{{ money($row['total']) }}</td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
            @endif

            @if (! empty($report['coupon_breakdown']))
                <div class="card">
                    <div class="card-header"><h3 class="card-title mb-0">Coupon Impact</h3></div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Code</th><th>Orders</th><th class="text-right">Discount</th></tr></thead>
                            <tbody>
                                @foreach ($report['coupon_breakdown'] as $row)
                                    <tr>
                                        <td><code>{{ $row['coupon'] }}</code></td>
                                        <td>{{ $row['orders'] }}</td>
                                        <td class="text-right">{{ money($row['total']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
