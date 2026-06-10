@extends('layouts.admin')

@section('title', 'Footer Links')
@section('page_title', 'Footer Links')

@section('content')
    <div class="mb-3"><a href="{{ route('admin.homepage.index') }}" class="btn btn-default btn-sm"><i class="fas fa-arrow-left"></i> Homepage</a></div>
    <div class="card">
        <div class="card-header">
            <a href="{{ route('admin.homepage.footer-links.create') }}" class="btn btn-primary btn-sm float-right"><i class="fas fa-plus"></i> Add Link</a>
            <h3 class="card-title">Footer Links</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover">
                <thead><tr><th>Group</th><th>Label</th><th>URL</th><th>Order</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse ($links as $link)
                        <tr>
                            <td><span class="badge badge-info">{{ App\Models\FooterLink::GROUPS[$link->group] ?? $link->group }}</span></td>
                            <td>{{ $link->label }}</td>
                            <td><code>{{ Str::limit($link->url, 40) }}</code></td>
                            <td>{{ $link->sort_order }}</td>
                            <td><span class="badge badge-{{ $link->is_active ? 'success' : 'secondary' }}">{{ $link->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td>
                                <a href="{{ route('admin.homepage.footer-links.edit', $link) }}" class="btn btn-xs btn-info"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('admin.homepage.footer-links.destroy', $link) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button></form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">No footer links yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($links->hasPages())<div class="card-footer">{{ $links->links() }}</div>@endif
    </div>
@endsection
