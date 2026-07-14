@extends('layouts.admin')

@section('title', 'Activity Logs')
@section('page_title', 'Activity Logs')

@section('content')
    <div class="settings-page">
        <section class="settings-hero">
            <div>
                <span class="settings-eyebrow">Users</span>
                <h2>Activity Logs</h2>
                <p>Track who worked in the admin panel — logins, creates, updates, and deletes.</p>
                <div class="settings-hero-meta">
                    <span class="settings-hero-chip"><i class="fas fa-history"></i> {{ number_format($stats['total']) }} total</span>
                    <span class="settings-hero-chip"><i class="fas fa-calendar-day"></i> {{ number_format($stats['today']) }} today</span>
                </div>
            </div>
            <div class="settings-hero-actions">
                <a href="{{ route('admin.users.index') }}" class="btn btn-info">
                    <i class="fas fa-users mr-1"></i> Users
                </a>
            </div>
        </section>

        <section class="row mb-3">
            <div class="col-md-4 mb-3">
                <article class="settings-stat settings-stat--teal">
                    <span class="settings-stat-icon"><i class="fas fa-list"></i></span>
                    <div>
                        <div class="settings-stat-value">{{ number_format($stats['total']) }}</div>
                        <div class="settings-stat-label">All Logs</div>
                    </div>
                </article>
            </div>
            <div class="col-md-4 mb-3">
                <article class="settings-stat settings-stat--green">
                    <span class="settings-stat-icon"><i class="fas fa-bolt"></i></span>
                    <div>
                        <div class="settings-stat-value">{{ number_format($stats['today']) }}</div>
                        <div class="settings-stat-label">Today</div>
                    </div>
                </article>
            </div>
            <div class="col-md-4 mb-3">
                <article class="settings-stat settings-stat--blue">
                    <span class="settings-stat-icon"><i class="fas fa-user-check"></i></span>
                    <div>
                        <div class="settings-stat-value">{{ number_format($stats['users']) }}</div>
                        <div class="settings-stat-label">Active Workers</div>
                    </div>
                </article>
            </div>
        </section>

        @include('admin.users.partials.nav')

        <div class="settings-card mb-3">
            <div class="settings-card-head">
                <div>
                    <h3>Filter Logs</h3>
                    <p class="mb-0 text-muted">Search by user, action, or date range.</p>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.activity-logs.index') }}" class="row">
                    <div class="col-md-3 form-group">
                        <label>Search</label>
                        <input type="text" name="search" class="form-control" value="{{ $filters['search'] }}" placeholder="User, action, IP…">
                    </div>
                    <div class="col-md-3 form-group">
                        <label>User</label>
                        <select name="user_id" class="form-control">
                            <option value="">All users</option>
                            @foreach ($staffUsers as $staff)
                                <option value="{{ $staff->id }}" @selected((string) $filters['user_id'] === (string) $staff->id)>
                                    {{ $staff->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 form-group">
                        <label>Action</label>
                        <select name="action" class="form-control">
                            <option value="">All actions</option>
                            @foreach ($actions as $actionOption)
                                <option value="{{ $actionOption }}" @selected($filters['action'] === $actionOption)>
                                    {{ ucfirst($actionOption) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 form-group">
                        <label>From</label>
                        <input type="date" name="from" class="form-control" value="{{ $filters['from'] }}">
                    </div>
                    <div class="col-md-2 form-group">
                        <label>To</label>
                        <input type="date" name="to" class="form-control" value="{{ $filters['to'] }}">
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search mr-1"></i> Apply</button>
                        <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="settings-card">
            <div class="settings-card-head">
                <div>
                    <h3>All Activity</h3>
                    <p class="mb-0 text-muted">Showing {{ $logs->count() }} of {{ $logs->total() }} entries</p>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>When</th>
                            <th>Who</th>
                            <th>Action</th>
                            <th>What</th>
                            <th>IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td class="text-nowrap">
                                    <div>{{ $log->created_at?->format('d M Y') }}</div>
                                    <small class="text-muted">{{ $log->created_at?->format('h:i A') }}</small>
                                </td>
                                <td>
                                    <div class="font-weight-semibold">{{ $log->actorLabel() }}</div>
                                    @if ($log->user_email)
                                        <small class="text-muted">{{ $log->user_email }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $log->actionBadgeClass() }}">{{ ucfirst($log->action) }}</span>
                                </td>
                                <td>
                                    <div>{{ $log->description }}</div>
                                    @if ($log->route_name)
                                        <small class="text-muted">{{ $log->route_name }}</small>
                                    @endif
                                </td>
                                <td class="text-nowrap">
                                    <code>{{ $log->ip_address ?: '—' }}</code>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">
                                    <i class="fas fa-history fa-2x mb-2 d-block"></i>
                                    No activity logged yet. Admin actions will appear here.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($logs->hasPages())
                <div class="card-footer">{{ $logs->links() }}</div>
            @endif
        </div>
    </div>
@endsection

@push('styles')
    @include('admin.users.partials.page-styles')
@endpush
