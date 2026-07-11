@extends('layouts.admin')

@section('title', 'Discount Banners')
@section('page_title', 'Discount Banners')

@section('content')
    @component('admin.homepage.partials.list-layout', [
        'title' => 'Discount Banners',
        'description' => 'Promotional banners displayed on the homepage below the hero slider.',
        'createRoute' => route('admin.homepage.banners.create'),
        'createLabel' => 'Add Banner',
        'items' => $banners,
        'emptyIcon' => 'fas fa-percent',
        'emptyTitle' => 'No banners yet',
        'emptyText' => 'Add discount banners to highlight sales and promotions.',
    ])
        @slot('tableHead')
            <th>Image</th>
            <th>Title</th>
            <th>Order</th>
            <th>Status</th>
            <th class="text-right">Actions</th>
        @endslot

        @forelse ($banners as $banner)
            <tr>
                <td>
                    @if ($banner->imageUrl())
                        <img src="{{ $banner->imageUrl() }}" style="height:40px;width:80px;object-fit:cover" class="rounded">
                    @endif
                </td>
                <td><strong>{{ $banner->title }}</strong></td>
                <td>{{ $banner->sort_order }}</td>
                <td>
                    <span class="settings-status {{ $banner->is_active ? 'settings-status--live' : 'settings-status--hidden' }}">
                        {{ $banner->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td class="text-right">
                    <div class="settings-actions">
                        <a href="{{ route('admin.homepage.banners.edit', $banner) }}" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('admin.homepage.banners.destroy', $banner) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="settings-empty">
                    <i class="fas fa-percent"></i>
                    <strong>No banners yet</strong>
                    <p>Add discount banners to highlight sales and promotions.</p>
                </td>
            </tr>
        @endforelse
    @endcomponent
@endsection
