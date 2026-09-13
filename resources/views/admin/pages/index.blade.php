@extends('layouts.admin')

@section('title', 'Pages')
@section('page_title', 'Pages')

@section('content')
    @component('admin.homepage.partials.list-layout', [
        'title' => 'Pages',
        'description' => 'Create Contact Us, FAQs, Shipping Info, Returns and other content pages. Link them from Footer Links.',
        'createRoute' => route('admin.pages.create'),
        'createLabel' => 'Add Page',
        'items' => $pages,
        'emptyIcon' => 'fas fa-file-alt',
        'emptyTitle' => 'No pages yet',
        'emptyText' => 'Create pages for Contact Us, FAQs, Shipping Info, and Returns.',
    ])
        @slot('tableHead')
            <th>Title</th>
            <th>Slug / URL</th>
            <th>Order</th>
            <th>Status</th>
            <th class="text-right">Actions</th>
        @endslot

        @forelse ($pages as $page)
            <tr>
                <td><strong>{{ $page->title }}</strong></td>
                <td>
                    <code>/pages/{{ $page->slug }}</code>
                    @if ($page->is_active)
                        <a href="{{ $page->url() }}" target="_blank" rel="noopener" class="btn btn-xs btn-outline-secondary ml-1" title="View"><i class="fas fa-external-link-alt"></i></a>
                    @endif
                </td>
                <td>{{ $page->sort_order }}</td>
                <td>
                    <span class="settings-status {{ $page->is_active ? 'settings-status--live' : 'settings-status--hidden' }}">
                        {{ $page->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td class="text-right">
                    <div class="settings-actions">
                        <a href="{{ route('admin.pages.edit', $page) }}" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('admin.pages.destroy', $page) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this page?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="settings-empty">
                    <i class="fas fa-file-alt"></i>
                    <strong>No pages yet</strong>
                    <p>Create pages for Contact Us, FAQs, Shipping Info, and Returns.</p>
                </td>
            </tr>
        @endforelse
    @endcomponent
@endsection
