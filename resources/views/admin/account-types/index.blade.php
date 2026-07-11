@extends('layouts.admin')

@section('title', 'Account Types')
@section('page_title', 'Account Types')

@section('content')
    <div class="settings-page">
        <section class="settings-hero">
            <div>
                <span class="settings-eyebrow">Account</span>
                <h2>Account Types</h2>
                <p>Manage dynamic categories used when creating account heads.</p>
                <div class="settings-hero-meta">
                    <span class="settings-hero-chip"><i class="fas fa-tags"></i> {{ $stats['total'] }} type{{ $stats['total'] === 1 ? '' : 's' }}</span>
                    <span class="settings-hero-chip"><i class="fas fa-check-circle"></i> {{ $stats['active'] }} active</span>
                </div>
            </div>
            <div class="settings-hero-actions">
                <a href="{{ route('admin.account-types.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus mr-1"></i> Create Account Type
                </a>
            </div>
        </section>

        <section class="row mb-3">
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="settings-stat settings-stat--teal">
                    <span class="settings-stat-icon"><i class="fas fa-tags"></i></span>
                    <div>
                        <div class="settings-stat-value">{{ $stats['total'] }}</div>
                        <div class="settings-stat-label">Total Types</div>
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
                    <span class="settings-stat-icon"><i class="fas fa-link"></i></span>
                    <div>
                        <div class="settings-stat-value">{{ $stats['in_use'] }}</div>
                        <div class="settings-stat-label">In Use</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="settings-stat settings-stat--amber">
                    <span class="settings-stat-icon"><i class="fas fa-calculator"></i></span>
                    <div>
                        <div class="settings-stat-value">{{ $stats['heads'] }}</div>
                        <div class="settings-stat-label">Linked Heads</div>
                    </div>
                </article>
            </div>
        </section>

        @include('admin.account.partials.nav')

        <div class="settings-card">
            <div class="settings-card-head">
                <div>
                    <h3>All Account Types</h3>
                    <p>These types appear in the account head form dropdown.</p>
                </div>
                <a href="{{ route('admin.account-types.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus mr-1"></i> Create Account Type
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 settings-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Heads</th>
                            <th>Order</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($accountTypes as $type)
                            <tr>
                                <td>
                                    <strong>{{ $type->name }}</strong>
                                    @if ($type->description)
                                        <div class="text-muted small">{{ Str::limit($type->description, 60) }}</div>
                                    @endif
                                </td>
                                <td><code>{{ $type->slug }}</code></td>
                                <td>{{ $type->account_heads_count }}</td>
                                <td>{{ $type->sort_order }}</td>
                                <td>
                                    <span class="settings-status {{ $type->is_active ? 'settings-status--live' : 'settings-status--hidden' }}">
                                        {{ $type->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <div class="settings-actions">
                                        <a href="{{ route('admin.account-types.edit', $type) }}" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                                        <form action="{{ route('admin.account-types.destroy', $type) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this account type?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-danger" @disabled($type->account_heads_count > 0)><i class="fas fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="settings-empty">
                                    <i class="fas fa-tags"></i>
                                    <strong>No account types yet</strong>
                                    <p>Create your first type to use in account heads.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($accountTypes->hasPages())
                <div class="settings-card-footer">{{ $accountTypes->links() }}</div>
            @endif
        </div>
    </div>
@endsection

@push('styles')
    @include('admin.settings.partials.page-styles')
@endpush
