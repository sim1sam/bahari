@extends('layouts.admin')

@section('title', 'Customer Ledgers')
@section('page_title', 'Customer Ledgers')

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title mb-0">Customer-wise Ledger</h3>
            <div class="ml-auto">
                <a href="{{ route('admin.bank-payments.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus"></i> Record Bank Payment
                </a>
            </div>
        </div>
        <div class="card-body border-bottom pb-3">
            <form action="{{ route('admin.customer-ledgers.index') }}" method="GET" class="form-inline">
                <div class="input-group input-group-sm">
                    <input type="text" name="search" class="form-control" placeholder="Search customer..." value="{{ $search }}">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-default"><i class="fas fa-search"></i></button>
                        @if ($search)
                            <a href="{{ route('admin.customer-ledgers.index') }}" class="btn btn-default">Clear</a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Email</th>
                        <th class="text-right">Total Orders</th>
                        <th class="text-right">Total Paid</th>
                        <th class="text-right">Balance Due</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($summaries as $summary)
                        @php $customer = $summary['user']; @endphp
                        <tr>
                            <td>{{ $customer->name }}</td>
                            <td>{{ $customer->email }}</td>
                            <td class="text-right">{{ money($summary['total_orders']) }}</td>
                            <td class="text-right">{{ money($summary['total_paid']) }}</td>
                            <td class="text-right font-weight-bold {{ $summary['balance'] > 0 ? 'text-danger' : 'text-success' }}">
                                {{ money($summary['balance']) }}
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.customer-ledgers.show', $customer) }}" class="btn btn-xs btn-info">View Ledger</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No customers found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
