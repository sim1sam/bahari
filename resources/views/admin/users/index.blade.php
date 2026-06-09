@extends('layouts.admin')

@section('title', 'Users')
@section('page_title', 'Users')

@section('content')
    <div class="card">
        <div class="card-header">
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm float-right"><i class="fas fa-plus"></i> Add User</a>
            <h3 class="card-title">Staff & Admin Users</h3>
        </div>
        <div class="card-body border-bottom pb-3">
            <form action="{{ route('admin.users.index') }}" method="GET" class="form-inline">
                <div class="input-group input-group-sm mr-2">
                    <input type="text" name="search" class="form-control" placeholder="Search by name or email..." value="{{ $search }}">
                </div>
                <select name="role" class="form-control form-control-sm mr-2">
                    <option value="">All roles</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" @selected($roleFilter == $role->id)>{{ $role->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-default btn-sm"><i class="fas fa-search"></i> Filter</button>
                @if ($search || $roleFilter)
                    <a href="{{ route('admin.users.index') }}" class="btn btn-default btn-sm ml-1">Clear</a>
                @endif
            </form>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Orders</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if ($user->avatarUrl())
                                        <img src="{{ $user->avatarUrl() }}" alt="" class="img-circle mr-2" style="width:36px;height:36px;object-fit:cover">
                                    @else
                                        <span class="img-circle bg-info d-inline-flex align-items-center justify-content-center text-white font-weight-bold mr-2" style="width:36px;height:36px;font-size:14px">{{ $user->initials() }}</span>
                                    @endif
                                    {{ $user->name }}
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @if ($user->role)
                                    <span class="badge badge-{{ $user->role->can_access_admin ? 'primary' : 'secondary' }}">{{ $user->role->name }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $user->hasActiveRole() ? 'success' : 'danger' }}">
                                    {{ $user->hasActiveRole() ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>{{ $user->orders_count }}</td>
                            <td>{{ $user->created_at->format('M d, Y') }}</td>
                            <td>
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-xs btn-info"><i class="fas fa-edit"></i></a>
                                @if ($user->id !== auth()->id())
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this user?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted">No users found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($users->hasPages())
            <div class="card-footer">{{ $users->links() }}</div>
        @endif
    </div>
@endsection
