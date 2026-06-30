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

<ul class="nav nav-pills mb-3 flex-wrap">
    <li class="nav-item">
        <a href="{{ route('admin.reports.index', $query) }}" class="nav-link @if (request()->routeIs('admin.reports.index')) active @endif">Overview</a>
    </li>
    <li class="nav-item">
        <a href="{{ route('admin.reports.profit-loss', $query) }}" class="nav-link @if (request()->routeIs('admin.reports.profit-loss')) active @endif">Profit &amp; Loss</a>
    </li>
    <li class="nav-item">
        <a href="{{ route('admin.reports.balance-sheet', $query) }}" class="nav-link @if (request()->routeIs('admin.reports.balance-sheet')) active @endif">Balance Sheet</a>
    </li>
    <li class="nav-item">
        <a href="{{ route('admin.reports.ledger', $query) }}" class="nav-link @if (request()->routeIs('admin.reports.ledger')) active @endif">Ledger</a>
    </li>
    <li class="nav-item">
        <a href="{{ route('admin.reports.expenses.index') }}" class="nav-link @if (request()->routeIs('admin.reports.expenses.*')) active @endif">Expenses</a>
    </li>
</ul>
