<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\CustomerLedgerService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerLedgerController extends Controller
{
    public function __construct(private CustomerLedgerService $ledger) {}

    public function index(Request $request): View
    {
        $allSummaries = $this->ledger->summariesForCustomers();
        $summaries = $allSummaries;
        $search = trim((string) $request->input('search'));

        if ($search !== '') {
            $summaries = $allSummaries->filter(function (array $summary) use ($search) {
                $customer = $summary['user'];

                return str_contains(strtolower($customer->name), strtolower($search))
                    || str_contains(strtolower($customer->email), strtolower($search));
            })->values();
        }

        return view('admin.customer-ledgers.index', [
            'summaries' => $summaries,
            'search' => $search,
            'stats' => [
                'customers' => $allSummaries->count(),
                'with_balance' => $allSummaries->where('balance', '>', 0)->count(),
                'total_orders' => (float) $allSummaries->sum('total_orders'),
                'total_paid' => (float) $allSummaries->sum('total_paid'),
                'outstanding' => (float) $allSummaries->sum('balance'),
            ],
        ]);
    }

    public function show(User $customer): View
    {
        $this->ensureCustomer($customer);

        return view('admin.customer-ledgers.show', [
            'customer' => $customer,
            'entries' => $this->ledger->entriesForUser($customer),
            'totals' => $this->ledger->totalsForUser($customer),
        ]);
    }

    private function ensureCustomer(User $customer): void
    {
        if (! $customer->hasRole(Role::SLUG_CUSTOMER)) {
            abort(404);
        }
    }
}
