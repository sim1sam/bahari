@extends('layouts.admin')

@section('title', 'Bank Balances')
@section('page_title', 'Bank Balances')

@section('content')
    <div class="reports-page">
        @include('admin.reports.partials.nav')

        @include('admin.reports.partials.filters', [
            'action' => route('admin.reports.bank-balances'),
            'paymentBanks' => $paymentBanks ?? collect(),
            'accountHeads' => $accountHeads ?? collect(),
        ])

        <div class="reports-filters-card">
            <div class="reports-filters-head">
                <h3><i class="fas fa-university mr-1 text-info"></i> Bank Balances as of {{ $report['as_of'] }}</h3>
            </div>
            <div class="reports-filters-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Bank</th>
                                <th class="text-right">Opening</th>
                                <th class="text-right">Payments In</th>
                                <th class="text-right">Transfers In</th>
                                <th class="text-right">Expenses Out</th>
                                <th class="text-right">Transfers Out</th>
                                <th class="text-right">Current Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($report['banks'] as $bank)
                                <tr>
                                    <td><strong>{{ $bank['name'] }}</strong></td>
                                    <td class="text-right">{{ money($bank['opening_balance']) }}</td>
                                    <td class="text-right">{{ money($bank['payments_in']) }}</td>
                                    <td class="text-right">{{ money($bank['transfers_in']) }}</td>
                                    <td class="text-right">{{ money($bank['expenses_out']) }}</td>
                                    <td class="text-right">{{ money($bank['transfers_out']) }}</td>
                                    <td class="text-right"><strong>{{ money($bank['current_balance']) }}</strong></td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Total</th>
                                <th colspan="5"></th>
                                <th class="text-right">{{ money($report['total_balance']) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    @include('admin.reports.partials.page-styles')
@endpush
