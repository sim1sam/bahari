@extends('layouts.admin')

@section('title', 'Processed')
@section('page_title', 'Processed — Ready to Go Live')

@section('content')
    <div class="mb-3 d-flex flex-wrap align-items-center gap-2">
        <a href="{{ route('admin.content.index') }}" class="btn btn-default btn-sm">Content</a>
        <a href="{{ route('admin.api-settings.index') }}" class="btn btn-outline-secondary btn-sm">API Settings</a>
        <a href="{{ route('admin.processed.live') }}" class="btn btn-success btn-sm">Live on Site</a>
        <form action="{{ route('admin.processed.purge-manual-products') }}" method="POST" class="d-inline" onsubmit="return confirm('Delete all old products that were NOT published from Processed? API Go Live products will stay.')">
            @csrf
            <button type="submit" class="btn btn-outline-danger btn-sm">
                <i class="fas fa-broom"></i> Remove Old Products
            </button>
        </form>
        <span class="badge badge-info badge-lg ml-auto">{{ $processedCount }} awaiting go live</span>
    </div>

    <form id="delete-batch-form" action="{{ route('admin.processed.destroy-batch') }}" method="POST" class="d-none">
        @csrf
        @method('DELETE')
    </form>

    <form id="batch-form" action="{{ route('admin.processed.live-batch') }}" method="POST" class="d-none">
        @csrf
    </form>

    <form id="download-form" action="{{ route('admin.processed.download-images') }}" method="POST" class="d-none">
        @csrf
        <input type="hidden" name="layout" id="download-layout" value="brand">
    </form>

    <div class="card card-outline card-secondary mb-3">
        <div class="card-body py-3">
            <form action="{{ route('admin.processed.index') }}" method="GET" class="form-inline flex-wrap">
                <label class="mr-2 mb-2 mb-md-0 text-muted small">Filter:</label>
                <select name="brand" class="form-control form-control-sm mr-2 mb-2 mb-md-0" style="min-width:180px">
                    <option value="">All brands</option>
                    @foreach ($brands as $brandOption)
                        <option value="{{ $brandOption }}" @selected($brand === $brandOption)>{{ $brandOption }}</option>
                    @endforeach
                </select>
                <input type="date" name="date" class="form-control form-control-sm mr-2 mb-2 mb-md-0" value="{{ $date }}" aria-label="Processed date">
                <button type="submit" class="btn btn-sm btn-outline-secondary mr-2 mb-2 mb-md-0">Apply</button>
                @if ($brand || $date)
                    <a href="{{ route('admin.processed.index') }}" class="btn btn-sm btn-link mb-2 mb-md-0">Clear</a>
                @endif
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
            <h3 class="card-title mb-0">Processed Products</h3>
            <div class="mt-2 mt-md-0 d-flex flex-wrap align-items-center">
                <select id="live-category-id" class="form-control form-control-sm mr-2 mb-2 mb-md-0" style="min-width:180px" required>
                    <option value="">Select category</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                <label class="mb-0 mr-3">
                    <input type="checkbox" id="select-all"> Select all
                </label>
                <button type="button" class="btn btn-outline-primary btn-sm mr-1 mb-2 mb-md-0" id="btn-download-flat" disabled>
                    <i class="fas fa-download"></i> Download Selected
                </button>
                <button type="button" class="btn btn-primary btn-sm mr-1 mb-2 mb-md-0" id="btn-download-brand" disabled>
                    <i class="fas fa-file-archive"></i> ZIP by Brand
                </button>
                <button type="button" class="btn btn-danger btn-sm mr-1 mb-2 mb-md-0" id="btn-delete-selected" disabled>
                    <i class="fas fa-trash"></i> Delete Selected
                </button>
                <button type="button" class="btn btn-success btn-sm mb-2 mb-md-0" id="btn-live-selected" disabled>
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
                        <th>Brand</th>
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
                                @if ($url = $item->displayImageUrl())
                                    <img src="{{ $url }}" alt="" class="rounded border" style="height:56px;width:56px;object-fit:cover">
                                @endif
                            </td>
                            <td>
                                @if ($item->sku)<code class="small">{{ $item->sku }}</code><br>@endif
                                <strong>{{ $item->title }}</strong>
                                @if ($item->vendor)
                                    <br><span class="small text-muted">{{ $item->vendor }}</span>
                                @endif
                            </td>
                            <td>{{ $item->brand ?: '—' }}</td>
                            <td>{{ money($item->price) }}</td>
                            <td>{{ $item->category_name ?: '—' }}</td>
                            <td class="text-nowrap small text-muted">{{ $item->updated_at->format('M d, Y H:i') }}</td>
                            <td class="text-nowrap">
                                <a href="{{ route('admin.processed.download-image', $item) }}" class="btn btn-xs btn-outline-secondary" title="Download image">
                                    <i class="fas fa-download"></i>
                                </a>
                                <a href="{{ route('admin.processed.show', $item) }}" class="btn btn-xs btn-primary">Review</a>
                                <form action="{{ route('admin.processed.live-item', $item) }}" method="POST" class="d-inline live-item-form" onsubmit="return submitLiveItem(this)">
                                    @csrf
                                    <input type="hidden" name="category_id" class="live-category-input" value="">
                                    <button type="submit" class="btn btn-xs btn-success">Go Live</button>
                                </form>
                                <form action="{{ route('admin.processed.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this processed item permanently?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
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
@endsection

@push('scripts')
<script>
(function () {
    var checks = document.querySelectorAll('.item-check');
    var selectAll = document.getElementById('select-all');
    var liveBtn = document.getElementById('btn-live-selected');
    var deleteBtn = document.getElementById('btn-delete-selected');
    var downloadFlatBtn = document.getElementById('btn-download-flat');
    var downloadBrandBtn = document.getElementById('btn-download-brand');
    var liveForm = document.getElementById('batch-form');
    var deleteForm = document.getElementById('delete-batch-form');
    var downloadForm = document.getElementById('download-form');
    var downloadLayout = document.getElementById('download-layout');
    var categorySelect = document.getElementById('live-category-id');

    function selectedCategoryId() {
        return categorySelect ? categorySelect.value : '';
    }

    window.submitLiveItem = function (form) {
        var categoryId = selectedCategoryId();
        if (!categoryId) {
            alert('Please select a category first.');
            if (categorySelect) categorySelect.focus();
            return false;
        }
        var input = form.querySelector('.live-category-input');
        if (input) input.value = categoryId;
        return confirm('Publish this product in the selected category?');
    };

    function selectedChecks() {
        return Array.from(checks).filter(function (c) { return c.checked; });
    }

    function updateBtns() {
        var any = selectedChecks().length > 0;
        if (liveBtn) liveBtn.disabled = !any;
        if (deleteBtn) deleteBtn.disabled = !any;
        if (downloadFlatBtn) downloadFlatBtn.disabled = !any;
        if (downloadBrandBtn) downloadBrandBtn.disabled = !any;
    }

    function submitDownload(layout) {
        var selected = selectedChecks();
        if (!selected.length) {
            alert('Please select at least one processed item.');
            return;
        }

        downloadForm.querySelectorAll('input[name="items[]"]').forEach(function (el) { el.remove(); });
        selected.forEach(function (c) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'items[]';
            input.value = c.value;
            downloadForm.appendChild(input);
        });

        if (downloadLayout) downloadLayout.value = layout;
        downloadForm.submit();
    }

    checks.forEach(function (c) { c.addEventListener('change', updateBtns); });
    if (selectAll) {
        selectAll.addEventListener('change', function () {
            checks.forEach(function (c) { c.checked = selectAll.checked; });
            updateBtns();
        });
    }
    if (downloadFlatBtn) {
        downloadFlatBtn.addEventListener('click', function () {
            submitDownload('flat');
        });
    }
    if (downloadBrandBtn) {
        downloadBrandBtn.addEventListener('click', function () {
            submitDownload('brand');
        });
    }
    if (liveBtn && liveForm) {
        liveBtn.addEventListener('click', function () {
            var categoryId = selectedCategoryId();
            if (!categoryId) {
                alert('Please select a category first.');
                if (categorySelect) categorySelect.focus();
                return;
            }
            if (!confirm('Publish selected products in this category?')) {
                return;
            }
            liveForm.querySelectorAll('input[name="items[]"]').forEach(function (el) { el.remove(); });
            liveForm.querySelectorAll('input[name="category_id"]').forEach(function (el) { el.remove(); });
            selectedChecks().forEach(function (c) {
                var itemInput = document.createElement('input');
                itemInput.type = 'hidden';
                itemInput.name = 'items[]';
                itemInput.value = c.value;
                liveForm.appendChild(itemInput);
            });
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'category_id';
            input.value = categoryId;
            liveForm.appendChild(input);
            liveForm.submit();
        });
    }
    if (deleteBtn && deleteForm) {
        deleteBtn.addEventListener('click', function () {
            if (!confirm('Delete selected processed items permanently?')) {
                return;
            }
            deleteForm.querySelectorAll('input[name="items[]"]').forEach(function (el) { el.remove(); });
            selectedChecks().forEach(function (c) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'items[]';
                input.value = c.value;
                deleteForm.appendChild(input);
            });
            deleteForm.submit();
        });
    }
})();
</script>
@endpush
