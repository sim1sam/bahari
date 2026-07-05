@extends('layouts.admin')

@section('title', 'API Brands')
@section('page_title', 'API Received Brands')

@section('content')
    <div class="mb-3 d-flex flex-wrap align-items-center gap-2">
        <a href="{{ route('admin.content.index') }}" class="btn btn-default btn-sm">Content</a>
        <a href="{{ route('admin.processed.index') }}" class="btn btn-default btn-sm">Processed</a>
        <form action="{{ route('admin.api-brands.sync') }}" method="POST" class="d-inline" onsubmit="return confirm('Import brands from all received API items?')">
            @csrf
            <button type="submit" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-sync"></i> Sync from Received
            </button>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <a href="{{ route('admin.api-brands.create') }}" class="btn btn-primary btn-sm float-right">
                <i class="fas fa-plus"></i> Add Brand
            </a>
            <h3 class="card-title">All Received Brands</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Brand</th>
                        <th>Slug</th>
                        <th>Received Items</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($brands as $brand)
                        <tr>
                            <td><strong>{{ $brand->name }}</strong></td>
                            <td><code>{{ $brand->slug }}</code></td>
                            <td>{{ $brand->received_items_count }}</td>
                            <td>
                                <span class="badge badge-{{ $brand->is_active ? 'success' : 'secondary' }}">
                                    {{ $brand->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-nowrap">
                                <a href="{{ route('admin.content.index', ['brand' => $brand->name]) }}" class="btn btn-xs btn-outline-warning" title="View received">
                                    <i class="fas fa-images"></i>
                                </a>
                                <a href="{{ route('admin.processed.index', ['brand' => $brand->name]) }}" class="btn btn-xs btn-outline-info" title="View processed">
                                    <i class="fas fa-check-circle"></i>
                                </a>
                                <a href="{{ route('admin.api-brands.edit', $brand) }}" class="btn btn-xs btn-info"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('admin.api-brands.destroy', $brand) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this brand? Received items will keep their brand name.')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                No brands saved yet. Brands are saved automatically when API sends products, or click
                                <strong>Sync from Received</strong> / <strong>Add Brand</strong>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($brands->hasPages())
            <div class="card-footer">{{ $brands->links() }}</div>
        @endif
    </div>
@endsection
