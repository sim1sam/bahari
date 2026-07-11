@extends('layouts.admin')

@section('title', 'Homepage Features')
@section('page_title', 'Trust Features')

@section('content')
    @component('admin.homepage.partials.list-layout', [
        'title' => 'Trust Features',
        'description' => 'Icon strip below the hero slider highlighting shipping, returns, and support.',
        'createRoute' => route('admin.homepage.features.create'),
        'createLabel' => 'Add Feature',
        'items' => $features,
        'emptyIcon' => 'fas fa-star',
        'emptyTitle' => 'No features yet',
        'emptyText' => 'Add trust features to build customer confidence on the homepage.',
    ])
        @slot('tableHead')
            <th>Title</th>
            <th>Description</th>
            <th>Icon</th>
            <th>Order</th>
            <th>Status</th>
            <th class="text-right">Actions</th>
        @endslot

        @forelse ($features as $feature)
            <tr>
                <td><strong>{{ $feature->title }}</strong></td>
                <td>{{ Str::limit($feature->description, 50) }}</td>
                <td><code>{{ $feature->icon }}</code></td>
                <td>{{ $feature->sort_order }}</td>
                <td>
                    <span class="settings-status {{ $feature->is_active ? 'settings-status--live' : 'settings-status--hidden' }}">
                        {{ $feature->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td class="text-right">
                    <div class="settings-actions">
                        <a href="{{ route('admin.homepage.features.edit', $feature) }}" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('admin.homepage.features.destroy', $feature) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="settings-empty">
                    <i class="fas fa-star"></i>
                    <strong>No features yet</strong>
                    <p>Add trust features to build customer confidence on the homepage.</p>
                </td>
            </tr>
        @endforelse
    @endcomponent
@endsection
