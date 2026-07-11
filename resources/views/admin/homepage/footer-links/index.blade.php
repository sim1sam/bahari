@extends('layouts.admin')

@section('title', 'Footer Links')
@section('page_title', 'Footer Links')

@section('content')
    @component('admin.homepage.partials.list-layout', [
        'title' => 'Footer Links',
        'description' => 'Navigation links grouped under Shop and Support columns in the storefront footer.',
        'createRoute' => route('admin.homepage.footer-links.create'),
        'createLabel' => 'Add Link',
        'items' => $links,
        'emptyIcon' => 'fas fa-link',
        'emptyTitle' => 'No footer links yet',
        'emptyText' => 'Add links to populate the Shop and Support columns in the footer.',
    ])
        @slot('tableHead')
            <th>Group</th>
            <th>Label</th>
            <th>URL</th>
            <th>Order</th>
            <th>Status</th>
            <th class="text-right">Actions</th>
        @endslot

        @forelse ($links as $link)
            <tr>
                <td><span class="settings-status settings-status--public">{{ App\Models\FooterLink::GROUPS[$link->group] ?? $link->group }}</span></td>
                <td><strong>{{ $link->label }}</strong></td>
                <td><code>{{ Str::limit($link->url, 40) }}</code></td>
                <td>{{ $link->sort_order }}</td>
                <td>
                    <span class="settings-status {{ $link->is_active ? 'settings-status--live' : 'settings-status--hidden' }}">
                        {{ $link->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td class="text-right">
                    <div class="settings-actions">
                        <a href="{{ route('admin.homepage.footer-links.edit', $link) }}" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('admin.homepage.footer-links.destroy', $link) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="settings-empty">
                    <i class="fas fa-link"></i>
                    <strong>No footer links yet</strong>
                    <p>Add links to populate the Shop and Support columns in the footer.</p>
                </td>
            </tr>
        @endforelse
    @endcomponent
@endsection
