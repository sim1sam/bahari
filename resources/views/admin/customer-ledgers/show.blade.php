@extends('layouts.admin')

@section('title', 'Customer Ledger')
@section('page_title', 'Ledger — '.$customer->name)

@section('content')
    <div class="mb-3">
        <a href="{{ route('admin.customer-ledgers.index') }}" class="btn btn-default btn-sm">Back to Customer Ledgers</a>
        <a href="{{ route('admin.bank-payments.create', ['customer_id' => $customer->id]) }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Make Payment
        </a>
    </div>

    <div class="row mb-3">
        <div class="col-md-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ money($totals['total_orders']) }}</h3>
                    <p>Total Orders</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ money($totals['total_paid']) }}</h3>
                    <p>Total Paid</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box {{ $totals['balance'] > 0 ? 'bg-danger' : 'bg-secondary' }}">
                <div class="inner">
                    <h3>{{ money($totals['balance']) }}</h3>
                    <p>Balance Due</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title mb-0">{{ $customer->name }} <span class="text-muted">({{ $customer->email }})</span></h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-sm mb-0">
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
                            <td><span class="badge badge-secondary">{{ $entry['type'] }}</span></td>
                            <td>
                                @if ($entry['order_id'])
                                    <a href="{{ route('admin.orders.show', $entry['order_id']) }}">{{ $entry['reference'] }}</a>
                                @else
                                    {{ $entry['reference'] }}
                                @endif
                            </td>
                            <td>{{ $entry['description'] }}</td>
                            <td class="text-right">{{ $entry['debit'] > 0 ? money($entry['debit']) : '—' }}</td>
                            <td class="text-right">{{ $entry['credit'] > 0 ? money($entry['credit']) : '—' }}</td>
                            <td class="text-right font-weight-bold">{{ money($entry['balance']) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No ledger entries yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
