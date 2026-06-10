@extends('layouts.admin')

@section('title', 'Hero Sliders')
@section('page_title', 'Hero Sliders')

@section('content')
    <div class="mb-3">
        <a href="{{ route('admin.homepage.index') }}" class="btn btn-default btn-sm"><i class="fas fa-arrow-left"></i> Homepage</a>
    </div>
    <div class="card">
        <div class="card-header">
            <a href="{{ route('admin.homepage.sliders.create') }}" class="btn btn-primary btn-sm float-right"><i class="fas fa-plus"></i> Add Slide</a>
            <h3 class="card-title">Hero Sliders</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover">
                <thead>
                    <tr><th>Image</th><th>Title</th><th>Order</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @forelse ($sliders as $slider)
                        <tr>
                            <td>@if ($slider->imageUrl())<img src="{{ $slider->imageUrl() }}" style="height:40px;width:60px;object-fit:cover" class="rounded">@endif</td>
                            <td>{{ $slider->title }}</td>
                            <td>{{ $slider->sort_order }}</td>
                            <td><span class="badge badge-{{ $slider->is_active ? 'success' : 'secondary' }}">{{ $slider->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td>
                                <a href="{{ route('admin.homepage.sliders.edit', $slider) }}" class="btn btn-xs btn-info"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('admin.homepage.sliders.destroy', $slider) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button></form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">No slides yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($sliders->hasPages())<div class="card-footer">{{ $sliders->links() }}</div>@endif
    </div>
@endsection
