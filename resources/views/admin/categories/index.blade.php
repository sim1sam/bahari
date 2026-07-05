@extends('layouts.admin')

@section('title', 'Categories')
@section('page_title', 'Categories')

@section('content')
    <div class="mb-3 d-flex flex-wrap align-items-center gap-2">
        <a href="{{ route('admin.content.index') }}" class="btn btn-default btn-sm">API Content</a>
        @if ($canSyncReceived ?? false)
            <form action="{{ route('admin.categories.sync-received') }}" method="POST" class="d-inline" onsubmit="return confirm('Create categories from all received API items?')">
                @csrf
                <button type="submit" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-sync"></i> Sync from API Received
                </button>
            </form>
        @endif
    </div>

    <div class="card">
        <div class="card-header">
            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm float-right"><i class="fas fa-plus"></i> Add Category</a>
            <h3 class="card-title">All Categories</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Products</th>
                        @if ($canSyncReceived ?? false)
                            <th>API Received</th>
                        @endif
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($categories as $category)
                        <tr>
                            <td>{{ $category->name }}</td>
                            <td>{{ $category->slug }}</td>
                            <td>{{ $category->products_count }}</td>
                            @if ($canSyncReceived ?? false)
                                <td>{{ $category->received_items_count ?? 0 }}</td>
                            @endif
                            <td>
                                <span class="badge badge-{{ $category->is_active ? 'success' : 'secondary' }}">
                                    {{ $category->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-xs btn-info"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this category?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($categories->hasPages())
            <div class="card-footer">{{ $categories->links() }}</div>
        @endif
    </div>
@endsection
