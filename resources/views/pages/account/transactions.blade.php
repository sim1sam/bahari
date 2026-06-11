@extends('layouts.account')

@section('title', 'Transactions')
@section('page_title', 'Transactions')
@section('mobile_title', 'Transactions')
@section('page_subtitle', 'Your payment submissions and approval status')

@section('breadcrumb')
    <a href="{{ route('account.dashboard') }}" class="hover:text-brand-600">Dashboard</a>
    <span>/</span>
    <span class="text-ink">Transactions</span>
@endsection

@section('content')
    {{-- Mobile --}}
    <div class="lg:hidden px-4 pt-4 space-y-4">
        <div class="rounded-2xl bg-gradient-to-br from-brand-600 to-brand-800 p-5 text-white shadow-lg shadow-brand-600/20">
            <p class="text-brand-100 text-sm">Total approved payments</p>
            <p class="text-3xl font-bold mt-1">{{ money($totalSpent) }}</p>
            <p class="text-xs text-brand-100 mt-2">{{ $transactionsCount }} transaction{{ $transactionsCount === 1 ? '' : 's' }}</p>
        </div>

        @include('pages.account.partials.transactions-list-mobile', ['transactions' => $transactions])
        <div class="pb-2">{{ $transactions->links() }}</div>
    </div>

    {{-- Desktop --}}
    <div class="hidden lg:block px-8 pt-8">
        <div class="grid grid-cols-3 gap-5 mb-8">
            <div class="account-stat-card">
                <p class="text-sm text-ink-muted">Approved Payments</p>
                <p class="text-2xl font-bold text-ink mt-1">{{ money($totalSpent) }}</p>
            </div>
            <div class="account-stat-card col-span-2">
                <p class="text-sm text-ink-muted">Transactions</p>
                <p class="text-2xl font-bold text-ink mt-1">{{ $transactionsCount }}</p>
            </div>
        </div>

        @if ($transactions->isEmpty())
            <div class="account-panel">
                <div class="account-panel-body text-center py-16">
                    <p class="text-lg font-medium text-ink">No transactions yet</p>
                    <p class="text-ink-muted mt-1">Payment screenshots you upload will appear here for admin review</p>
                </div>
            </div>
        @else
            <div class="account-panel">
                <div class="account-panel-header">
                    <h2 class="font-semibold text-ink">Payment Transactions</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="account-table w-full">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Date</th>
                                <th>Bank</th>
                                <th>Status</th>
                                <th class="text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($transactions as $transaction)
                                <tr>
                                    <td>
                                        <a href="{{ route('account.orders.show', $transaction->order) }}" class="font-medium text-brand-600 hover:underline">{{ $transaction->order->number }}</a>
                                    </td>
                                    <td class="text-ink-muted whitespace-nowrap">{{ $transaction->created_at->format('M d, Y g:i A') }}</td>
                                    <td>{{ $transaction->bank_name ?: '—' }}</td>
                                    <td><span class="px-2.5 py-1 rounded-md text-xs font-medium {{ $transaction->statusColor() }}">{{ $transaction->statusLabel() }}</span></td>
                                    <td class="text-right font-semibold text-brand-700">{{ money($transaction->amount) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="account-panel-footer">{{ $transactions->links() }}</div>
            </div>
        @endif
    </div>
@endsection
