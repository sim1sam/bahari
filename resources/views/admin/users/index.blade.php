@extends('layouts.admin')

@section('title', 'Users')
@section('page_title', 'Users')

@section('content')
    @php
        use App\Models\User;
        use App\Models\Role;
        $totalUsers = User::staff()->count();
        $activeUsers = User::staff()->whereHas('role', fn ($q) => $q->where('is_active', true))->count();
        $roleCount = Role::query()->where('can_access_admin', true)->count();
    @endphp

    <div class="settings-page">
        <section class="settings-hero">
            <div>
                <span class="settings-eyebrow">Users</span>
                <h2>Staff &amp; Admin Users</h2>
                <p>Manage admin panel accounts, roles, and access permissions.</p>
                <div class="settings-hero-meta">
                    <span class="settings-hero-chip"><i class="fas fa-users"></i> {{ $totalUsers }} user{{ $totalUsers === 1 ? '' : 's' }}</span>
                    <span class="settings-hero-chip"><i class="fas fa-check-circle"></i> {{ $activeUsers }} active</span>
                </div>
            </div>
            <div class="settings-hero-actions">
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus mr-1"></i> Add User
                </a>
                <a href="{{ route('admin.roles.index') }}" class="btn btn-info">
                    <i class="fas fa-user-shield mr-1"></i> Roles
                </a>
            </div>
        </section>

        <section class="row mb-3">
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="settings-stat settings-stat--teal">
                    <span class="settings-stat-icon"><i class="fas fa-users"></i></span>
                    <div>
                        <div class="settings-stat-value">{{ $totalUsers }}</div>
                        <div class="settings-stat-label">Total Users</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="settings-stat settings-stat--green">
                    <span class="settings-stat-icon"><i class="fas fa-user-check"></i></span>
                    <div>
                        <div class="settings-stat-value">{{ $activeUsers }}</div>
                        <div class="settings-stat-label">Active</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="settings-stat settings-stat--purple">
                    <span class="settings-stat-icon"><i class="fas fa-user-shield"></i></span>
                    <div>
                        <div class="settings-stat-value">{{ $roleCount }}</div>
                        <div class="settings-stat-label">Admin Roles</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="settings-stat settings-stat--blue">
                    <span class="settings-stat-icon"><i class="fas fa-filter"></i></span>
                    <div>
                        <div class="settings-stat-value">{{ $users->total() }}</div>
                        <div class="settings-stat-label">Showing</div>
                    </div>
                </article>
            </div>
        </section>

        @include('admin.users.partials.nav')

        <div class="settings-card">
            <div class="settings-card-head">
                <div>
                    <h3>All Users</h3>
                    <p>Staff accounts with admin panel access.</p>
                </div>
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus mr-1"></i> Add User
                </a>
            </div>
            <div class="settings-card-body border-bottom">
                <form action="{{ route('admin.users.index') }}" method="GET" class="users-filter-grid">
                    <div class="settings-field mb-0">
                        <label for="search">Search</label>
                        <div class="settings-input-wrap">
                            <span class="settings-input-icon"><i class="fas fa-search"></i></span>
                            <input type="text" name="search" id="search" class="form-control" placeholder="Search by name or email..." value="{{ $search }}">
                        </div>
                    </div>
                    <div class="settings-field mb-0">
                        <label for="role">Role</label>
                        <select name="role" id="role" class="form-control settings-textarea">
                            <option value="">All roles</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}" @selected($roleFilter == $role->id)>{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search mr-1"></i> Filter</button>
                        @if ($search || $roleFilter)
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Clear</a>
                        @endif
                    </div>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 settings-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Orders</th>
                            <th>Joined</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>
                                    <div class="users-cell">
                                        <span class="users-avatar">
                                            @if ($user->avatarUrl())
                                                <img src="{{ $user->avatarUrl() }}" alt="">
                                            @else
                                                {{ $user->initials() }}
                                            @endif
                                        </span>
                                        <span class="users-cell-name">{{ $user->name }}</span>
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if ($user->role)
                                        <span class="users-role-badge {{ $user->role->can_access_admin ? 'users-role-badge--admin' : 'users-role-badge--other' }}">
                                            {{ $user->role->name }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="settings-status {{ $user->hasActiveRole() ? 'settings-status--live' : 'settings-status--hidden' }}">
                                        {{ $user->hasActiveRole() ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>{{ $user->orders_count }}</td>
                                <td>{{ $user->created_at->format('M d, Y') }}</td>
                                <td class="text-right">
                                    <div class="settings-actions">
                                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                                        @if ($user->id !== auth()->id())
                                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this user?')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="settings-empty">
                                    <i class="fas fa-users"></i>
                                    <strong>No users found</strong>
                                    <p>Try adjusting your search or add a new staff user.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($users->hasPages())
                <div class="settings-card-footer">{{ $users->links() }}</div>
            @endif
        </div>
    </div>
@endsection

@push('styles')
    @include('admin.users.partials.page-styles')
@endpush
