@extends('layouts.admin')

@section('title', 'Processed')
@section('page_title', 'Processed — Ready to Go Live')

@section('content')
    <div class="mb-3 d-flex flex-wrap align-items-center gap-2">
        <a href="{{ route('admin.content.index') }}" class="btn btn-default btn-sm">Content</a>
        <a href="{{ route('admin.api-settings.index') }}" class="btn btn-outline-secondary btn-sm">API Settings</a>
        <a href="{{ route('admin.processed.live') }}" class="btn btn-success btn-sm">Live on Site</a>
        <span class="badge badge-info badge-lg ml-auto">{{ $processedCount }} awaiting go live</span>
    </div>

    <form id="batch-form" action="{{ route('admin.processed.live-batch') }}" method="POST">
        @csrf
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">Processed Products</h3>
                <div>
                    <label class="mb-0 mr-3">
                        <input type="checkbox" id="select-all"> Select all
                    </label>
                    <button type="button" class="btn btn-success btn-sm" id="btn-live-selected" disabled>
                        <i class="fas fa-globe"></i> Go Live (Selected)
                    </button>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="40"></th>
                            <th>Image</th>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Category</th>
                            <th>Processed</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            <tr>
                                <td><input type="checkbox" class="item-check" name="items[]" value="{{ $item->id }}"></td>
                                <td>
                                    @if ($item->processedImageUrl())
                                        <img src="{{ $item->processedImageUrl() }}" alt="" class="rounded border" style="height:56px;width:56px;object-fit:cover">
                                    @endif
                                </td>
                                <td>
                                    @if ($item->sku)<code class="small">{{ $item->sku }}</code><br>@endif
                                    <strong>{{ $item->title }}</strong>
                                </td>
                                <td>{{ money($item->price) }}</td>
                                <td>{{ $item->category_name ?: '—' }}</td>
                                <td class="text-nowrap small text-muted">{{ $item->updated_at->format('M d, Y H:i') }}</td>
                                <td class="text-nowrap">
                                    <a href="{{ route('admin.processed.show', $item) }}" class="btn btn-xs btn-primary">Review</a>
                                    <form action="{{ route('admin.processed.live-item', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Publish on storefront?')">
                                        @csrf
                                        <button type="submit" class="btn btn-xs btn-success">Go Live</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    No processed items yet. Process images from the <a href="{{ route('admin.content.index') }}">Content</a> menu.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($items->hasPages())
                <div class="card-footer">{{ $items->links() }}</div>
            @endif
        </div>
    </form>
@endsection

@push('scripts')
<script>
(function () {
    var checks = document.querySelectorAll('.item-check');
    var selectAll = document.getElementById('select-all');
    var btn = document.getElementById('btn-live-selected');
    var form = document.getElementById('batch-form');
    function updateBtn() {
        if (btn) btn.disabled = !Array.from(checks).some(function (c) { return c.checked; });
    }
    checks.forEach(function (c) { c.addEventListener('change', updateBtn); });
    if (selectAll) selectAll.addEventListener('change', function () {
        checks.forEach(function (c) { c.checked = selectAll.checked; });
        updateBtn();
    });
    if (btn && form) btn.addEventListener('click', function () {
        if (confirm('Publish selected products on the storefront?')) form.submit();
    });
})();
</script>
@endpush
