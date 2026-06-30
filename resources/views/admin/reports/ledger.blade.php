@extends('layouts.admin')

@section('title', 'Ledger')
@section('page_title', 'General Ledger')

@section('content')
    @include('admin.reports.partials.nav')

    @include('admin.reports.partials.filters', [
        'action' => route('admin.reports.ledger'),
        'exportRoute' => route('admin.reports.ledger', array_merge($filters->toQueryArray(), ['export' => 'csv'])),
    ])

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Ledger Entries</h3>
            <div class="text-muted small">
                Debit: {{ money($totals['debit']) }} · Credit: {{ money($totals['credit']) }}
            </div>
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
                        <tr><td colspan="7" class="text-center text-muted py-4">No ledger entries for this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
