<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountExpense;
use App\Services\FinancialReportService;
use App\Support\FinancialReportFilters;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class FinancialReportController extends Controller
{
    public function __construct(private FinancialReportService $reports) {}

    public function index(Request $request): View
    {
        $filters = $this->resolveFilters($request);

        return view('admin.reports.index', [
            'filters' => $filters,
            'overview' => $this->reports->overview($filters),
        ]);
    }

    public function profitLoss(Request $request): View|Response
    {
        $filters = $this->resolveFilters($request);
        $report = $this->reports->profitLoss($filters);

        if ($request->query('export') === 'csv') {
            return $this->exportProfitLossCsv($report, $filters);
        }

        return view('admin.reports.profit-loss', [
            'filters' => $filters,
            'report' => $report,
        ]);
    }

    public function balanceSheet(Request $request): View
    {
        $filters = $this->resolveFilters($request);

        return view('admin.reports.balance-sheet', [
            'filters' => $filters,
            'report' => $this->reports->balanceSheet($filters),
        ]);
    }

    public function ledger(Request $request): View|Response
    {
        $filters = $this->resolveFilters($request);
        $entries = $this->reports->ledger($filters);

        if ($request->query('export') === 'csv') {
            return $this->exportLedgerCsv($entries, $filters);
        }

        return view('admin.reports.ledger', [
            'filters' => $filters,
            'entries' => $entries,
            'totals' => [
                'debit' => $entries->sum('debit'),
                'credit' => $entries->sum('credit'),
            ],
        ]);
    }

    private function resolveFilters(Request $request): FinancialReportFilters
    {
        $filters = FinancialReportFilters::fromRequest($request);

        if (! $filters->dateFrom) {
            $filters->dateFrom = $this->reports->defaultDateFrom();
        }

        if (! $filters->dateTo) {
            $filters->dateTo = $this->reports->defaultDateTo();
        }

        return $filters;
    }

    private function exportProfitLossCsv(array $report, FinancialReportFilters $filters): Response
    {
        $lines = [
            ['Profit & Loss Report'],
            ['Period', $filters->dateFrom.' to '.$filters->dateTo],
            ['Basis', ucfirst($report['basis'])],
            [],
            ['Gross Sales', $report['gross_sales']],
            ['Discounts', -$report['discounts']],
            ['Net Sales', $report['net_sales']],
            ['Shipping Income', $report['shipping_income']],
            ['Total Revenue', $report['total_revenue']],
            ['Cost of Goods Sold', -$report['cogs']],
            ['Gross Profit', $report['gross_profit']],
            ['Operating Expenses', -$report['operating_expenses']],
            ['Net Profit / Loss', $report['net_profit']],
        ];

        return response($this->toCsv($lines), 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="profit-loss-'.$filters->dateFrom.'-'.$filters->dateTo.'.csv"',
        ]);
    }

    private function exportLedgerCsv($entries, FinancialReportFilters $filters): Response
    {
        $lines = [['Date', 'Type', 'Reference', 'Description', 'Debit', 'Credit', 'Balance']];

        foreach ($entries as $entry) {
            $lines[] = [
                $entry['date'],
                $entry['type'],
                $entry['reference'],
                $entry['description'],
                $entry['debit'],
                $entry['credit'],
                $entry['balance'],
            ];
        }

        return response($this->toCsv($lines), 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="ledger-'.$filters->dateFrom.'-'.$filters->dateTo.'.csv"',
        ]);
    }

    /** @param array<int, array<int, mixed>> $lines */
    private function toCsv(array $lines): string
    {
        $handle = fopen('php://temp', 'r+');

        foreach ($lines as $line) {
            fputcsv($handle, $line);
        }

        rewind($handle);
        $csv = stream_get_contents($handle) ?: '';
        fclose($handle);

        return $csv;
    }
}
