@extends('layouts.admin')

@section('title', 'Homepage Features')
@section('page_title', 'Trust Features')

@section('content')
    <div class="mb-3"><a href="{{ route('admin.homepage.index') }}" class="btn btn-default btn-sm"><i class="fas fa-arrow-left"></i> Homepage</a></div>
    <div class="card">
        <div class="card-header">
            <a href="{{ route('admin.homepage.features.create') }}" class="btn btn-primary btn-sm float-right"><i class="fas fa-plus"></i> Add Feature</a>
            <h3 class="card-title">Trust Features Strip</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover">
                <thead><tr><th>Title</th><th>Description</th><th>Icon</th><th>Order</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse ($features as $feature)
                        <tr>
                            <td>{{ $feature->title }}</td>
                            <td>{{ Str::limit($feature->description, 50) }}</td>
                            <td>{{ $feature->icon }}</td>
                            <td>{{ $feature->sort_order }}</td>
                            <td><span class="badge badge-{{ $feature->is_active ? 'success' : 'secondary' }}">{{ $feature->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td>
                                <a href="{{ route('admin.homepage.features.edit', $feature) }}" class="btn btn-xs btn-info"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('admin.homepage.features.destroy', $feature) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button></form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">No features yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($features->hasPages())<div class="card-footer">{{ $features->links() }}</div>@endif
    </div>
@endsection
