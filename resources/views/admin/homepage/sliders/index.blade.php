@extends('layouts.admin')

@section('title', 'Hero Sliders')
@section('page_title', 'Hero Sliders')

@section('content')
    @component('admin.homepage.partials.list-layout', [
        'title' => 'Hero Sliders',
        'description' => 'Manage homepage hero carousel slides with images, titles, and sort order.',
        'createRoute' => route('admin.homepage.sliders.create'),
        'createLabel' => 'Add Slide',
        'items' => $sliders,
        'emptyIcon' => 'fas fa-images',
        'emptyTitle' => 'No slides yet',
        'emptyText' => 'Add your first hero slide to showcase promotions on the homepage.',
    ])
        @slot('tableHead')
            <th>Image</th>
            <th>Title</th>
            <th>Order</th>
            <th>Status</th>
            <th class="text-right">Actions</th>
        @endslot

        @forelse ($sliders as $slider)
            <tr>
                <td>
                    @if ($slider->imageUrl())
                        <img src="{{ $slider->imageUrl() }}" style="height:40px;width:60px;object-fit:cover" class="rounded">
                    @endif
                </td>
                <td><strong>{{ $slider->title }}</strong></td>
                <td>{{ $slider->sort_order }}</td>
                <td>
                    <span class="settings-status {{ $slider->is_active ? 'settings-status--live' : 'settings-status--hidden' }}">
                        {{ $slider->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td class="text-right">
                    <div class="settings-actions">
                        <a href="{{ route('admin.homepage.sliders.edit', $slider) }}" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('admin.homepage.sliders.destroy', $slider) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="settings-empty">
                    <i class="fas fa-images"></i>
                    <strong>No slides yet</strong>
                    <p>Add your first hero slide to showcase promotions on the homepage.</p>
                </td>
            </tr>
        @endforelse
    @endcomponent
@endsection
