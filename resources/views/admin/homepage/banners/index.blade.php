@extends('layouts.admin')

@section('title', 'Discount Banners')
@section('page_title', 'Discount Banners')

@section('content')
    <div class="mb-3"><a href="{{ route('admin.homepage.index') }}" class="btn btn-default btn-sm"><i class="fas fa-arrow-left"></i> Homepage</a></div>
    <div class="card">
        <div class="card-header">
            <a href="{{ route('admin.homepage.banners.create') }}" class="btn btn-primary btn-sm float-right"><i class="fas fa-plus"></i> Add Banner</a>
            <h3 class="card-title">Discount Banners</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover">
                <thead><tr><th>Image</th><th>Title</th><th>Order</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    @forelse ($banners as $banner)
                        <tr>
                            <td>@if ($banner->imageUrl())<img src="{{ $banner->imageUrl() }}" style="height:40px;width:80px;object-fit:cover" class="rounded">@endif</td>
                            <td>{{ $banner->title }}</td>
                            <td>{{ $banner->sort_order }}</td>
                            <td><span class="badge badge-{{ $banner->is_active ? 'success' : 'secondary' }}">{{ $banner->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td>
                                <a href="{{ route('admin.homepage.banners.edit', $banner) }}" class="btn btn-xs btn-info"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('admin.homepage.banners.destroy', $banner) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button></form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">No banners yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($banners->hasPages())<div class="card-footer">{{ $banners->links() }}</div>@endif
    </div>
@endsection
