@extends('layouts.admin')

@section('title', $link->exists ? 'Edit Footer Link' : 'Add Footer Link')
@section('page_title', $link->exists ? 'Edit Footer Link' : 'Add Footer Link')

@section('content')
    <form action="{{ $link->exists ? route('admin.homepage.footer-links.update', $link) : route('admin.homepage.footer-links.store') }}" method="POST" id="footer-link-form">
        @csrf @if ($link->exists) @method('PUT') @endif
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Group *</label>
                            <select name="group" class="form-control" required>
                                @foreach (App\Models\FooterLink::GROUPS as $key => $label)
                                    <option value="{{ $key }}" @selected(old('group', $link->group) === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Label *</label>
                            <input type="text" name="label" class="form-control" value="{{ old('label', $link->label) }}" required placeholder="Contact Us">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $link->sort_order ?? 0) }}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Link to page</label>
                            <select name="page_id" id="page_id" class="form-control">
                                <option value="">— Custom URL (no page) —</option>
                                @foreach ($pages as $page)
                                    <option value="{{ $page->id }}" @selected((string) old('page_id', $link->page_id) === (string) $page->id)>
                                        {{ $page->title }} (/pages/{{ $page->slug }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Select a page created under <a href="{{ route('admin.pages.index') }}">Pages</a> (Contact Us, FAQs, Shipping Info, Returns…).</small>
                        </div>
                    </div>
                    <div class="col-md-12" id="custom-url-wrap">
                        <div class="form-group">
                            <label>Custom URL</label>
                            <input type="text" name="url" id="url" class="form-control" value="{{ old('url', $link->url) }}" placeholder="/deals or https://...">
                            <small class="text-muted">Used only when no page is selected.</small>
                        </div>
                    </div>
                </div>
                <div class="form-check">
                    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="active" @checked(old('is_active', $link->is_active ?? true))>
                    <label class="form-check-label" for="active">Active</label>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('admin.homepage.footer-links.index') }}" class="btn btn-default">Cancel</a>
                <a href="{{ route('admin.pages.create') }}" class="btn btn-outline-info">Create new page</a>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
(function () {
    var pageSelect = document.getElementById('page_id');
    var urlWrap = document.getElementById('custom-url-wrap');
    var urlInput = document.getElementById('url');

    function sync() {
        var hasPage = pageSelect && pageSelect.value;
        if (urlWrap) urlWrap.style.display = hasPage ? 'none' : 'block';
        if (urlInput) urlInput.required = !hasPage;
    }

    if (pageSelect) {
        pageSelect.addEventListener('change', sync);
        sync();
    }
})();
</script>
@endpush
