@php
    use App\Support\FinancialReportFilters;
    use App\Services\FinancialReportService;

    $navFilters = (isset($filters) && $filters instanceof FinancialReportFilters)
        ? $filters
        : FinancialReportFilters::fromRequest(request());

    if (! $navFilters->dateFrom) {
        $navFilters->dateFrom = app(FinancialReportService::class)->defaultDateFrom();
    }

    if (! $navFilters->dateTo) {
        $navFilters->dateTo = app(FinancialReportService::class)->defaultDateTo();
    }

    $query = $navFilters->toQueryArray();
@endphp

<nav class="reports-nav" aria-label="Report sections">
    <a href="{{ route('admin.reports.index', $query) }}" class="reports-nav-link @if (request()->routeIs('admin.reports.index')) active @endif">
        <i class="fas fa-chart-pie"></i> Overview
    </a>
    <a href="{{ route('admin.reports.profit-loss', $query) }}" class="reports-nav-link @if (request()->routeIs('admin.reports.profit-loss')) active @endif">
        <i class="fas fa-file-invoice-dollar"></i> Profit &amp; Loss
    </a>
    <a href="{{ route('admin.reports.sales', $query) }}" class="reports-nav-link @if (request()->routeIs('admin.reports.sales')) active @endif">
        <i class="fas fa-shopping-bag"></i> Sales
    </a>
    <a href="{{ route('admin.reports.balance-sheet', $query) }}" class="reports-nav-link @if (request()->routeIs('admin.reports.balance-sheet')) active @endif">
        <i class="fas fa-balance-scale"></i> Balance Sheet
    </a>
    <a href="{{ route('admin.reports.ledger', $query) }}" class="reports-nav-link @if (request()->routeIs('admin.reports.ledger')) active @endif">
        <i class="fas fa-book"></i> Ledger
    </a>
    <a href="{{ route('admin.reports.bank-balances', $query) }}" class="reports-nav-link @if (request()->routeIs('admin.reports.bank-balances')) active @endif">
        <i class="fas fa-university"></i> Bank Balances
    </a>
</nav>
