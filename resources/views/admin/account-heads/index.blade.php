@extends('layouts.admin')

@section('title', 'Account Heads')
@section('page_title', 'Account Heads')

@section('content')
    <div class="settings-page">
        <section class="settings-hero">
            <div>
                <span class="settings-eyebrow">Account</span>
                <h2>Account Heads</h2>
                <p>Chart of accounts — income, expense, asset, liability, and equity heads.</p>
                <div class="settings-hero-meta">
                    <span class="settings-hero-chip"><i class="fas fa-list"></i> {{ $stats['total'] }} head{{ $stats['total'] === 1 ? '' : 's' }}</span>
                    <span class="settings-hero-chip"><i class="fas fa-check-circle"></i> {{ $stats['active'] }} active</span>
                </div>
            </div>
            <div class="settings-hero-actions">
                <a href="{{ route('admin.account-heads.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus mr-1"></i> Create Account Head
                </a>
            </div>
        </section>

        <section class="row mb-3">
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="settings-stat settings-stat--teal">
                    <span class="settings-stat-icon"><i class="fas fa-list"></i></span>
                    <div>
                        <div class="settings-stat-value">{{ $stats['total'] }}</div>
                        <div class="settings-stat-label">Total Heads</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="settings-stat settings-stat--green">
                    <span class="settings-stat-icon"><i class="fas fa-check-circle"></i></span>
                    <div>
                        <div class="settings-stat-value">{{ $stats['active'] }}</div>
                        <div class="settings-stat-label">Active</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="settings-stat settings-stat--blue">
                    <span class="settings-stat-icon"><i class="fas fa-tags"></i></span>
                    <div>
                        <div class="settings-stat-value">{{ $stats['types'] }}</div>
                        <div class="settings-stat-label">Types</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="settings-stat settings-stat--amber">
                    <span class="settings-stat-icon"><i class="fas fa-ban"></i></span>
                    <div>
                        <div class="settings-stat-value">{{ $stats['inactive'] }}</div>
                        <div class="settings-stat-label">Inactive</div>
                    </div>
                </article>
            </div>
        </section>

        @include('admin.account.partials.nav')

        <div class="settings-card">
            <div class="settings-card-head">
                <div>
                    <h3>All Account Heads</h3>
                    <p>Ledger accounts used for bookkeeping and reports.</p>
                </div>
                <a href="{{ route('admin.account-heads.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus mr-1"></i> Create Account Head
                </a>
            </div>
            <div class="settings-card-body border-bottom">
                <form action="{{ route('admin.account-heads.index') }}" method="GET" class="row align-items-end">
                    <div class="col-md-5">
                        <div class="settings-field mb-0">
                            <label for="search">Search</label>
                            <div class="settings-input-wrap">
                                <span class="settings-input-icon"><i class="fas fa-search"></i></span>
                                <input type="text" name="search" id="search" class="form-control" placeholder="Search name or code..." value="{{ $search }}">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="settings-field mb-0">
                            <label for="type">Type</label>
                            <select name="type" id="type" class="form-control settings-textarea">
                                <option value="">All types</option>
                                @foreach ($accountTypes as $type)
                                    <option value="{{ $type->id }}" @selected((string) $typeFilter === (string) $type->id)>{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search mr-1"></i> Filter</button>
                        @if ($search || $typeFilter)
                            <a href="{{ route('admin.account-heads.index') }}" class="btn btn-secondary">Clear</a>
                        @endif
                    </div>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 settings-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Order</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($accountHeads as $head)
                            <tr>
                                <td>
                                    @if ($head->code)
                                        <code>{{ $head->code }}</code>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $head->name }}</strong>
                                    @if ($head->description)
                                        <div class="text-muted small">{{ Str::limit($head->description, 50) }}</div>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $typeClass = match ($head->accountHeadType?->slug) {
                                            'income' => 'settings-status--public',
                                            'expense' => 'settings-status--private',
                                            'asset' => 'settings-status--live',
                                            default => 'settings-status--hidden',
                                        };
                                    @endphp
                                    <span class="settings-status {{ $typeClass }}">{{ $head->typeLabel() }}</span>
                                </td>
                                <td>{{ $head->sort_order }}</td>
                                <td>
                                    <span class="settings-status {{ $head->is_active ? 'settings-status--live' : 'settings-status--hidden' }}">
                                        {{ $head->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <div class="settings-actions">
                                        <a href="{{ route('admin.account-heads.edit', $head) }}" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                                        <form action="{{ route('admin.account-heads.destroy', $head) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this account head?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="settings-empty">
                                    <i class="fas fa-calculator"></i>
                                    <strong>No account heads yet</strong>
                                    <p>Create your first account head to start building your chart of accounts.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($accountHeads->hasPages())
                <div class="settings-card-footer">{{ $accountHeads->links() }}</div>
            @endif
        </div>
    </div>
@endsection

@push('styles')
    @include('admin.settings.partials.page-styles')
@endpush
