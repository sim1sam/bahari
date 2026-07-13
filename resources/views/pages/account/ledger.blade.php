@extends('layouts.account')

@section('title', 'Ledger')
@section('page_title', 'My Ledger')
@section('mobile_title', 'Ledger')
@section('page_subtitle', 'Your order charges and payment history')

@section('breadcrumb')
    <a href="{{ route('account.dashboard') }}" class="hover:text-brand-600">Dashboard</a>
    <span>/</span>
    <span class="text-ink">Ledger</span>
@endsection

@section('content')
    {{-- Mobile --}}
    <div class="lg:hidden px-4 pt-4 space-y-4 pb-4">
        <div class="grid grid-cols-1 gap-3">
            <div class="rounded-2xl bg-gradient-to-br from-brand-600 to-brand-800 p-5 text-white shadow-lg shadow-brand-600/20">
                <p class="text-brand-100 text-sm">Balance Due</p>
                <p class="text-3xl font-bold mt-1">{{ money($totals['balance']) }}</p>
                <p class="text-xs text-brand-100 mt-2">Orders {{ money($totals['total_orders']) }} · Paid {{ money($totals['total_paid']) }}</p>
            </div>
        </div>

        @include('pages.account.partials.ledger-list-mobile', ['entries' => $entries])
    </div>

    {{-- Desktop --}}
    <div class="hidden lg:block px-8 pt-8 space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="account-stat-card">
                <p class="text-sm text-ink-muted">Total Orders</p>
                <p class="text-2xl font-bold text-ink mt-1">{{ money($totals['total_orders']) }}</p>
            </div>
            <div class="account-stat-card">
                <p class="text-sm text-ink-muted">Total Paid</p>
                <p class="text-2xl font-bold text-emerald-600 mt-1">{{ money($totals['total_paid']) }}</p>
            </div>
            <div class="account-stat-card">
                <p class="text-sm text-ink-muted">Balance Due</p>
                <p class="text-2xl font-bold {{ $totals['balance'] > 0 ? 'text-red-600' : 'text-ink' }} mt-1">{{ money($totals['balance']) }}</p>
            </div>
        </div>

        <div class="account-panel">
            <div class="account-panel-header">
                <h2 class="font-semibold text-ink">Ledger Entries</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="account-table w-full">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Reference</th>
                            <th>Description</th>
                            <th class="text-right">Debit</th>
                            <th class="text-right">Credit</th>
                            <th class="text-right">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($entries as $entry)
                            <tr>
                                <td>{{ $entry['date'] }}</td>
                                <td>{{ $entry['type'] }}</td>
                                <td>
                                    @if ($entry['order_id'])
                                        <a href="{{ route('account.orders.show', $entry['order_id']) }}" class="text-brand-600 hover:underline">{{ $entry['reference'] }}</a>
                                    @else
                                        {{ $entry['reference'] }}
                                    @endif
                                </td>
                                <td>{{ $entry['description'] }}</td>
                                <td class="text-right">{{ $entry['debit'] > 0 ? money($entry['debit']) : '—' }}</td>
                                <td class="text-right">{{ $entry['credit'] > 0 ? money($entry['credit']) : '—' }}</td>
                                <td class="text-right font-semibold">{{ money($entry['balance']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-ink-muted py-10">No ledger entries yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
