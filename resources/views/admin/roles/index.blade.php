@extends('layouts.admin')

@section('title', 'Roles')
@section('page_title', 'Roles')

@section('content')
    @php
        use App\Models\Role;
        $totalRoles = Role::query()->count();
        $activeRoles = Role::query()->where('is_active', true)->count();
        $adminRoles = Role::query()->where('can_access_admin', true)->count();
        $assignedUsers = Role::query()->withCount('users')->get()->sum('users_count');
    @endphp

    <div class="settings-page">
        <section class="settings-hero">
            <div>
                <span class="settings-eyebrow">Users</span>
                <h2>Roles</h2>
                <p>Define access levels and admin feature permissions for staff accounts.</p>
                <div class="settings-hero-meta">
                    <span class="settings-hero-chip"><i class="fas fa-user-shield"></i> {{ $totalRoles }} role{{ $totalRoles === 1 ? '' : 's' }}</span>
                    <span class="settings-hero-chip"><i class="fas fa-check-circle"></i> {{ $activeRoles }} active</span>
                </div>
            </div>
            <div class="settings-hero-actions">
                <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus mr-1"></i> Add Role
                </a>
                <a href="{{ route('admin.users.index') }}" class="btn btn-info">
                    <i class="fas fa-users mr-1"></i> Users
                </a>
            </div>
        </section>

        <section class="row mb-3">
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="settings-stat settings-stat--teal">
                    <span class="settings-stat-icon"><i class="fas fa-user-shield"></i></span>
                    <div>
                        <div class="settings-stat-value">{{ $totalRoles }}</div>
                        <div class="settings-stat-label">Total Roles</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="settings-stat settings-stat--green">
                    <span class="settings-stat-icon"><i class="fas fa-check-circle"></i></span>
                    <div>
                        <div class="settings-stat-value">{{ $activeRoles }}</div>
                        <div class="settings-stat-label">Active</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="settings-stat settings-stat--blue">
                    <span class="settings-stat-icon"><i class="fas fa-door-open"></i></span>
                    <div>
                        <div class="settings-stat-value">{{ $adminRoles }}</div>
                        <div class="settings-stat-label">Admin Access</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="settings-stat settings-stat--purple">
                    <span class="settings-stat-icon"><i class="fas fa-users"></i></span>
                    <div>
                        <div class="settings-stat-value">{{ $assignedUsers }}</div>
                        <div class="settings-stat-label">Assigned Users</div>
                    </div>
                </article>
            </div>
        </section>

        @include('admin.users.partials.nav')

        <div class="settings-card">
            <div class="settings-card-head">
                <div>
                    <h3>All Roles</h3>
                    <p>Role definitions with admin access and feature permissions.</p>
                </div>
                <a href="{{ route('admin.roles.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus mr-1"></i> Add Role
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 settings-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Admin Access</th>
                            <th>Features</th>
                            <th>Status</th>
                            <th>Users</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($roles as $role)
                            <tr>
                                <td>
                                    <strong>{{ $role->name }}</strong>
                                    @if ($role->isSystem())
                                        <span class="users-role-badge users-role-badge--system ml-1">System</span>
                                    @endif
                                </td>
                                <td><code>{{ $role->slug }}</code></td>
                                <td>
                                    <span class="settings-status {{ $role->can_access_admin ? 'settings-status--live' : 'settings-status--hidden' }}">
                                        {{ $role->can_access_admin ? 'Yes' : 'No' }}
                                    </span>
                                </td>
                                <td>
                                    @if ($role->can_access_admin)
                                        {{ count($role->permissions ?? []) }} / {{ count(config('admin_features', [])) }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="settings-status {{ $role->is_active ? 'settings-status--live' : 'settings-status--hidden' }}">
                                        {{ $role->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>{{ $role->users_count }}</td>
                                <td class="text-right">
                                    <div class="settings-actions">
                                        <form action="{{ route('admin.roles.status', $role) }}" method="POST" class="d-inline">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-{{ $role->is_active ? 'warning' : 'success' }}" title="{{ $role->is_active ? 'Deactivate' : 'Activate' }}">
                                                <i class="fas fa-{{ $role->is_active ? 'ban' : 'check' }}"></i>
                                            </button>
                                        </form>
                                        <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                                        @unless ($role->isSystem())
                                            <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this role?')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                            </form>
                                        @endunless
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="settings-empty">
                                    <i class="fas fa-user-shield"></i>
                                    <strong>No roles found</strong>
                                    <p>Create a role to assign permissions to staff users.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($roles->hasPages())
                <div class="settings-card-footer">{{ $roles->links() }}</div>
            @endif
        </div>
    </div>
@endsection

@push('styles')
    @include('admin.users.partials.page-styles')
@endpush
