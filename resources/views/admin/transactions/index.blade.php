@extends('layouts.admin')

@section('title', 'Transactions')
@section('page_title', 'Transactions')

@section('content')
    <div class="mb-3">
        <div class="btn-group">
            <a href="{{ route('admin.transactions.index', ['status' => 'pending']) }}" class="btn btn-sm {{ $status === 'pending' ? 'btn-warning' : 'btn-outline-warning' }}">
                Pending
                @if ($pendingCount > 0)
                    <span class="badge badge-light ml-1">{{ $pendingCount }}</span>
                @endif
            </a>
            <a href="{{ route('admin.transactions.index', ['status' => 'approved']) }}" class="btn btn-sm {{ $status === 'approved' ? 'btn-success' : 'btn-outline-success' }}">Approved</a>
            <a href="{{ route('admin.transactions.index', ['status' => 'rejected']) }}" class="btn btn-sm {{ $status === 'rejected' ? 'btn-danger' : 'btn-outline-danger' }}">Rejected</a>
            <a href="{{ route('admin.transactions.index', ['status' => 'all']) }}" class="btn btn-sm {{ $status === 'all' ? 'btn-primary' : 'btn-outline-primary' }}">All</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Bank</th>
                        <th>Screenshot</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $transaction)
                        <tr>
                            <td>{{ $transaction->id }}</td>
                            <td>
                                <a href="{{ route('admin.orders.show', $transaction->order) }}">{{ $transaction->order->number }}</a>
                            </td>
                            <td>
                                {{ $transaction->order->customer_name }}<br>
                                <small class="text-muted">{{ $transaction->order->customer_email }}</small>
                            </td>
                            <td><strong>{{ money($transaction->amount) }}</strong></td>
                            <td>{{ $transaction->bank_name ?: '—' }}</td>
                            <td>
                                @if ($transaction->screenshotUrl())
                                    <a href="{{ $transaction->screenshotUrl() }}" target="_blank" rel="noopener">
                                        <img src="{{ $transaction->screenshotUrl() }}" alt="Screenshot" class="rounded border" style="max-height:40px">
                                    </a>
                                @else
                                    —
                                @endif
                            </td>
                            <td><span class="badge {{ $transaction->statusBadgeClass() }}">{{ $transaction->statusLabel() }}</span></td>
                            <td class="text-nowrap">{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                            <td class="text-nowrap">
                                <a href="{{ route('admin.transactions.show', $transaction) }}" class="btn btn-xs btn-primary">Review</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                @if ($status === 'pending')
                                    No pending payment screenshots to review.
                                @else
                                    No transactions found.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($transactions->hasPages())
            <div class="card-footer">{{ $transactions->links() }}</div>
        @endif
    </div>
@endsection
