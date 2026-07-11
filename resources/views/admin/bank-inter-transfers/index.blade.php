@extends('layouts.admin')

@section('title', 'Inter Transfer History')
@section('page_title', 'Inter Transfer History')

@section('content')
    <div class="settings-page">
        <section class="settings-hero">
            <div>
                <span class="settings-eyebrow">Account</span>
                <h2>Inter Transfer History</h2>
                <p>Bank-to-bank transfers recorded as transactions only.</p>
            </div>
            <div class="settings-hero-actions">
                <a href="{{ route('admin.bank-inter-transfers.create') }}" class="btn btn-light">
                    <i class="fas fa-plus mr-1"></i> New Transfer
                </a>
            </div>
        </section>

        @include('admin.account.partials.nav')

        <section class="row mb-3">
            <div class="col-md-4 mb-3">
                <article class="settings-stat settings-stat--teal">
                    <span class="settings-stat-icon"><i class="fas fa-exchange-alt"></i></span>
                    <div>
                        <div class="settings-stat-value">{{ number_format($stats['total']) }}</div>
                        <div class="settings-stat-label">Total Transfers</div>
                    </div>
                </article>
            </div>
            <div class="col-md-4 mb-3">
                <article class="settings-stat settings-stat--blue">
                    <span class="settings-stat-icon"><i class="fas fa-calendar-day"></i></span>
                    <div>
                        <div class="settings-stat-value">{{ number_format($stats['today']) }}</div>
                        <div class="settings-stat-label">Today</div>
                    </div>
                </article>
            </div>
            <div class="col-md-4 mb-3">
                <article class="settings-stat settings-stat--green">
                    <span class="settings-stat-icon"><i class="fas fa-coins"></i></span>
                    <div>
                        <div class="settings-stat-value">{{ money($stats['today_amount'], 0) }}</div>
                        <div class="settings-stat-label">Transferred Today</div>
                    </div>
                </article>
            </div>
        </section>

        <div class="settings-card">
            <div class="settings-card-body p-0">
                <div class="table-responsive">
                    <table class="table settings-table mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>From</th>
                                <th>To</th>
                                <th class="text-right">Amount</th>
                                <th>Notes</th>
                                <th>Recorded By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($transfers as $transfer)
                                <tr>
                                    <td>{{ $transfer->transfer_date->format('M d, Y') }}</td>
                                    <td>{{ $transfer->fromBank->displayName() }}</td>
                                    <td>{{ $transfer->toBank->displayName() }}</td>
                                    <td class="text-right"><strong>{{ money($transfer->amount) }}</strong></td>
                                    <td>{{ $transfer->notes ?: '—' }}</td>
                                    <td>{{ $transfer->recorder?->name ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No inter-bank transfers yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($transfers->hasPages())
                <div class="settings-card-footer">{{ $transfers->links() }}</div>
            @endif
        </div>
    </div>
@endsection

@push('styles')
    @include('admin.settings.partials.page-styles')
@endpush
