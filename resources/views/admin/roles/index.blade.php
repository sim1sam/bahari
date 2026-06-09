@extends('layouts.admin')

@section('title', 'Roles')
@section('page_title', 'Roles')

@section('content')
    <div class="card">
        <div class="card-header">
            <a href="{{ route('admin.roles.create') }}" class="btn btn-primary btn-sm float-right"><i class="fas fa-plus"></i> Add Role</a>
            <h3 class="card-title">All Roles</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Admin Access</th>
                        <th>Features</th>
                        <th>Status</th>
                        <th>Users</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roles as $role)
                        <tr>
                            <td>
                                {{ $role->name }}
                                @if ($role->isSystem())
                                    <span class="badge badge-secondary ml-1">System</span>
                                @endif
                            </td>
                            <td><code>{{ $role->slug }}</code></td>
                            <td>
                                <span class="badge badge-{{ $role->can_access_admin ? 'success' : 'secondary' }}">
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
                                <span class="badge badge-{{ $role->is_active ? 'success' : 'danger' }}">
                                    {{ $role->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>{{ $role->users_count }}</td>
                            <td class="text-nowrap">
                                <form action="{{ route('admin.roles.status', $role) }}" method="POST" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-xs btn-{{ $role->is_active ? 'warning' : 'success' }}" title="{{ $role->is_active ? 'Deactivate' : 'Activate' }}">
                                        <i class="fas fa-{{ $role->is_active ? 'ban' : 'check' }}"></i>
                                    </button>
                                </form>
                                <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-xs btn-info"><i class="fas fa-edit"></i></a>
                                @unless ($role->isSystem())
                                    <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this role?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                @endunless
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted">No roles found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($roles->hasPages())
            <div class="card-footer">{{ $roles->links() }}</div>
        @endif
    </div>
@endsection
