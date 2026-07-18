<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountHead;
use App\Models\PaymentBank;
use App\Services\FinancialReportService;
use App\Support\FinancialReportFilters;
use App\Support\ReportSpreadsheet;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinancialReportController extends Controller
{
    public function __construct(private FinancialReportService $reports) {}

    public function index(Request $request): View|Response|StreamedResponse
    {
        $filters = $this->resolveFilters($request);
        $overview = $this->reports->overview($filters);

        if ($export = $this->requestedExport($request)) {
            return $this->downloadRows($export, 'overview', 'Overview', $this->overviewRows($overview, $filters), $filters);
        }

        return view('admin.reports.index', [
            'filters' => $filters,
            'overview' => $overview,
            'paymentBanks' => PaymentBank::query()->orderBy('name')->get(),
            'accountHeads' => AccountHead::query()->orderBy('name')->get(),
        ]);
    }

    public function profitLoss(Request $request): View|Response|StreamedResponse
    {
        $filters = $this->resolveFilters($request);
        $report = $this->reports->profitLoss($filters);

        if ($export = $this->requestedExport($request)) {
            return $this->downloadRows($export, 'profit-loss', 'Profit & Loss', $this->profitLossRows($report, $filters), $filters);
        }

        return view('admin.reports.profit-loss', [
            'filters' => $filters,
            'report' => $report,
            'paymentBanks' => PaymentBank::query()->orderBy('name')->get(),
            'accountHeads' => AccountHead::query()->orderBy('name')->get(),
        ]);
    }

    public function balanceSheet(Request $request): View|Response|StreamedResponse
    {
        $filters = $this->resolveFilters($request);
        $report = $this->reports->balanceSheet($filters);

        if ($export = $this->requestedExport($request)) {
            return $this->downloadRows($export, 'balance-sheet', 'Balance Sheet', $this->balanceSheetRows($report), $filters);
        }

        return view('admin.reports.balance-sheet', [
            'filters' => $filters,
            'report' => $report,
            'paymentBanks' => PaymentBank::query()->orderBy('name')->get(),
            'accountHeads' => AccountHead::query()->orderBy('name')->get(),
        ]);
    }

    public function ledger(Request $request): View|Response|StreamedResponse
    {
        $filters = $this->resolveFilters($request);
        $entries = $this->reports->ledger($filters);

        if ($export = $this->requestedExport($request)) {
            return $this->downloadRows($export, 'ledger', 'Ledger', $this->ledgerRows($entries, $filters), $filters);
        }

        return view('admin.reports.ledger', [
            'filters' => $filters,
            'entries' => $entries,
            'paymentBanks' => PaymentBank::query()->orderBy('name')->get(),
            'accountHeads' => AccountHead::query()->orderBy('name')->get(),
            'totals' => [
                'debit' => $entries->sum('debit'),
                'credit' => $entries->sum('credit'),
            ],
        ]);
    }

    public function bankBalances(Request $request): View|Response|StreamedResponse
    {
        $filters = $this->resolveFilters($request);
        $report = $this->reports->bankBalancesReport($filters->dateTo);

        if ($export = $this->requestedExport($request)) {
            return $this->downloadRows($export, 'bank-balances', 'Bank Balances', $this->bankBalancesRows($report), $filters);
        }

        return view('admin.reports.bank-balances', [
            'filters' => $filters,
            'report' => $report,
            'paymentBanks' => PaymentBank::query()->orderBy('name')->get(),
            'accountHeads' => AccountHead::query()->orderBy('name')->get(),
        ]);
    }

    public function sales(Request $request): View|Response|StreamedResponse
    {
        $filters = $this->resolveFilters($request);
        $report = $this->reports->salesReport($filters);

        if ($export = $this->requestedExport($request)) {
            return $this->downloadRows($export, 'sales', 'Sales', $this->salesRows($report, $filters), $filters);
        }

        return view('admin.reports.sales', [
            'filters' => $filters,
            'report' => $report,
            'paymentBanks' => PaymentBank::query()->orderBy('name')->get(),
            'accountHeads' => AccountHead::query()->orderBy('name')->get(),
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

    private function requestedExport(Request $request): ?string
    {
        $export = $request->query('export');

        return in_array($export, ['csv', 'excel'], true) ? $export : null;
    }

    /** @param array{title: string, meta?: array<int, array<int, mixed>>, headers: array<int, string>, rows?: array<int, array<int, mixed>>, footer?: array<int, mixed>|null} $report */
    private function downloadRows(
        string $format,
        string $basename,
        string $sheetTitle,
        array $report,
        FinancialReportFilters $filters,
    ): Response|StreamedResponse {
        $filename = $basename.'-'.$filters->dateFrom.'-'.$filters->dateTo;

        if ($format === 'excel') {
            return ReportSpreadsheet::download($filename.'.xlsx', $report, $sheetTitle);
        }

        return response($this->toCsv(ReportSpreadsheet::toPlainRows($report)), 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'.csv"',
        ]);
    }

    /** @param array<string, mixed> $overview */
    private function overviewRows(array $overview, FinancialReportFilters $filters): array
    {
        return [
            'title' => 'Overview Report',
            'subtitle' => 'Period: '.$filters->dateFrom.' to '.$filters->dateTo,
            'meta' => [
                ['Basis', ucfirst($filters->basis)],
            ],
            'headers' => ['Metric', 'Amount'],
            'rows' => [
                ['Total Revenue', $overview['total_revenue']],
                ['Gross Profit', $overview['gross_profit']],
                ['Net Profit / Loss', $overview['net_profit']],
                ['Cash Collected', $overview['cash_collected']],
                ['Bank Charges Collected', $overview['bank_charges_collected']],
                ['Accounts Receivable', $overview['accounts_receivable']],
                ['Inventory Value', $overview['inventory_value']],
                ['Operating Expenses', $overview['total_expenses']],
                ['Orders', $overview['order_count']],
            ],
        ];
    }

    /** @param array<string, mixed> $report */
    private function profitLossRows(array $report, FinancialReportFilters $filters): array
    {
        return [
            'title' => 'Profit & Loss Report',
            'subtitle' => 'Period: '.$filters->dateFrom.' to '.$filters->dateTo,
            'meta' => [
                ['Basis', ucfirst($report['basis'])],
            ],
            'headers' => ['Line Item', 'Amount'],
            'rows' => [
                ['Gross Sales', $report['gross_sales']],
                ['Discounts', -$report['discounts']],
                ['Net Sales', $report['net_sales']],
                ['Shipping Income', $report['shipping_income']],
                ['Total Revenue', $report['total_revenue']],
                ['Cost of Goods Sold', -$report['cogs']],
                ['Gross Profit', $report['gross_profit']],
                ['Operating Expenses', -$report['operating_expenses']],
                ['Net Profit / Loss', $report['net_profit']],
            ],
        ];
    }

    /** @param array<string, mixed> $report */
    private function balanceSheetRows(array $report): array
    {
        return [
            'title' => 'Balance Sheet',
            'subtitle' => 'As of: '.$report['as_of'],
            'headers' => ['Section', 'Item', 'Amount'],
            'rows' => [
                ['Assets', 'Cash & Bank', $report['cash']],
                ['Assets', 'Accounts Receivable', $report['accounts_receivable']],
                ['Assets', 'Inventory', $report['inventory']],
                ['Assets', 'Total Assets', $report['total_assets']],
                ['Liabilities & Equity', 'Accounts Payable', $report['accounts_payable']],
                ['Liabilities & Equity', 'Total Liabilities', $report['total_liabilities']],
                ['Liabilities & Equity', 'Retained Earnings', $report['retained_earnings']],
                ['Liabilities & Equity', 'Total Equity', $report['total_equity']],
                ['Liabilities & Equity', 'Total Liabilities & Equity', $report['total_liabilities_equity']],
            ],
        ];
    }

    /** @param Collection<int, array<string, mixed>> $entries */
    private function ledgerRows(Collection $entries, FinancialReportFilters $filters): array
    {
        $rows = [];

        foreach ($entries as $entry) {
            $rows[] = [
                $entry['date'],
                $entry['type'],
                $entry['reference'],
                $entry['description'],
                $entry['debit'],
                $entry['credit'],
                $entry['balance'],
            ];
        }

        return [
            'title' => 'General Ledger',
            'subtitle' => 'Period: '.$filters->dateFrom.' to '.$filters->dateTo,
            'headers' => ['Date', 'Type', 'Reference', 'Description', 'Debit', 'Credit', 'Balance'],
            'rows' => $rows,
        ];
    }

    /** @param array{rows: Collection, totals: array<string, float>} $report */
    private function salesRows(array $report, FinancialReportFilters $filters): array
    {
        $rows = [];

        foreach ($report['rows'] as $row) {
            $rows[] = [
                $row['date'],
                $row['number'],
                $row['customer_name'],
                $row['status'],
                $row['sales_price'],
                $row['procurement_cost'],
                $row['service_charge'],
            ];
        }

        return [
            'title' => 'Sales Report (Order-wise)',
            'subtitle' => 'Period: '.$filters->dateFrom.' to '.$filters->dateTo,
            'headers' => ['Date', 'Order', 'Customer', 'Status', 'Sales Price', 'Procurement Cost', 'Service Charge'],
            'rows' => $rows,
            'footer' => [
                'Total',
                '',
                '',
                '',
                $report['totals']['sales_price'],
                $report['totals']['procurement_cost'],
                $report['totals']['service_charge'],
            ],
        ];
    }

    /** @param array<string, mixed> $report */
    private function bankBalancesRows(array $report): array
    {
        $rows = [];

        foreach ($report['banks'] as $bank) {
            $rows[] = [
                $bank['name'],
                $bank['opening_balance'],
                $bank['payments_in'],
                $bank['transfers_in'],
                $bank['expenses_out'],
                $bank['transfers_out'],
                $bank['current_balance'],
            ];
        }

        return [
            'title' => 'Bank Balances',
            'subtitle' => 'As of: '.$report['as_of'],
            'headers' => ['Bank', 'Opening', 'Payments In', 'Transfers In', 'Expenses Out', 'Transfers Out', 'Current Balance'],
            'rows' => $rows,
            'footer' => ['Total Balance', '', '', '', '', '', $report['total_balance']],
        ];
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
