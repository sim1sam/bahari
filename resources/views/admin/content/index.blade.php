@extends('layouts.admin')

@section('title', 'Content')
@section('page_title', 'Content — Received Images')

@section('content')
    @if (session('generated_credentials'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <strong>API credentials:</strong>
            <code>{{ session('generated_credentials')['api_key'] }}</code> /
            <code>{{ session('generated_credentials')['api_token'] }}</code>
        </div>
    @endif

    @unless ($logoUrl)
        <div class="alert alert-warning">
            <strong>Logo required.</strong> Upload a logo below before clicking Process Selected.
        </div>
    @endunless

    <div class="row mb-3">
        <div class="col-md-8">
            <a href="{{ route('admin.processed.index') }}" class="btn btn-info btn-sm">Processed</a>
            <a href="{{ route('admin.api-settings.index') }}" class="btn btn-outline-secondary btn-sm">API Settings</a>
            <form action="{{ route('admin.content.repair-images') }}" method="POST" class="d-inline" onsubmit="return confirm('Re-download images and sync prices from API payload for pending items?')">
                @csrf
                <button type="submit" class="btn btn-outline-warning btn-sm">
                    <i class="fas fa-sync"></i> Re-download Images & Sync Prices
                </button>
            </form>
        </div>
        <div class="col-md-4 text-md-right">
            <span class="badge badge-warning badge-lg">{{ $pendingCount }} received</span>
        </div>
    </div>

    {{-- Logo + batch process --}}
    <div class="card card-outline card-primary">
        <div class="card-header"><h3 class="card-title">1. Upload Logo → 2. Select Images → 3. Process</h3></div>
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-3 text-center mb-3 mb-md-0">
                    @if ($logoUrl)
                        <img src="{{ $logoUrl }}" alt="Logo" class="img-thumbnail" style="max-height:72px">
                    @else
                        <span class="text-muted d-block">No logo yet</span>
                    @endif
                </div>
                <div class="col-md-5 mb-3 mb-md-0">
                    <form action="{{ route('admin.content.logo') }}" method="POST" enctype="multipart/form-data" class="form-inline mb-2">
                        @csrf
                        <input type="file" name="logo" class="form-control-file mr-2" accept="image/*" required>
                        <button type="submit" class="btn btn-secondary btn-sm">Upload Logo</button>
                    </form>
                    <form action="{{ route('admin.content.logo-scale') }}" method="POST" class="form-inline align-items-center">
                        @csrf
                        @method('PUT')
                        <label class="small text-muted mr-2 mb-0">Logo size on image</label>
                        <input type="number" name="api_logo_scale" class="form-control form-control-sm mr-1" style="width:72px"
                            min="10" max="50" step="1" value="{{ old('api_logo_scale', $logoScale) }}" required>
                        <span class="small text-muted mr-2">%</span>
                        <button type="submit" class="btn btn-outline-secondary btn-sm">Save Size</button>
                    </form>
                    <small class="text-muted d-block mt-1">Default is 28% of image width (was 18%). Increase before processing.</small>
                </div>
                <div class="col-md-4 text-md-right">
                    <button type="button" class="btn btn-primary" id="btn-process-selected" disabled>
                        <i class="fas fa-magic"></i> Process Selected
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
            <h3 class="card-title mb-0">Received Images</h3>
            <div class="d-flex flex-wrap align-items-center mt-2 mt-md-0">
                <form action="{{ route('admin.content.index') }}" method="GET" class="form-inline mr-3 mb-2 mb-md-0">
                    <select name="brand" class="form-control form-control-sm mr-1" style="min-width:160px" aria-label="Brand">
                        <option value="">All brands</option>
                        @foreach ($brands as $brandOption)
                            <option value="{{ $brandOption }}" @selected($brand === $brandOption)>{{ $brandOption }}</option>
                        @endforeach
                    </select>
                    <input type="date" name="date_from" class="form-control form-control-sm mr-1" value="{{ $dateFrom }}" aria-label="From date">
                    <span class="text-muted mx-1">to</span>
                    <input type="date" name="date_to" class="form-control form-control-sm mr-1" value="{{ $dateTo }}" aria-label="To date">
                    <button type="submit" class="btn btn-sm btn-outline-secondary mr-1">Filter</button>
                    @if ($brand || $dateFrom || $dateTo)
                        <a href="{{ route('admin.content.index') }}" class="btn btn-sm btn-link">Clear</a>
                    @endif
                </form>
                <label class="mb-0 mr-2">
                    <input type="checkbox" id="select-page"> This page
                </label>
                <label class="mb-0 mr-3">
                    <input type="checkbox" id="select-all-pages"> All pages
                </label>
                <span id="select-all-status" class="small text-info mr-3 d-none"></span>
            </div>
        </div>
        <form id="batch-form" action="{{ route('admin.content.process-batch') }}" method="POST">
            @csrf
            <div class="card-body">
                @if ($items->isEmpty())
                    <p class="text-center text-muted py-5 mb-0">No received images. Items from API will appear here.</p>
                @else
                    <div class="row">
                        @foreach ($items as $item)
                            <div class="col-6 col-md-3 col-lg-2 mb-4">
                                <div class="card h-100 border {{ $item->imageUrl() ? '' : 'border-danger' }}">
                                    <div class="card-header p-2 text-center">
                                        <input type="checkbox" class="item-check" name="items[]" value="{{ $item->id }}" form="batch-form">
                                    </div>
                                    <a href="{{ route('admin.content.show', $item) }}">
                                        @if ($item->imageUrl())
                                            <img src="{{ $item->imageUrl() }}" alt="" class="card-img-top" style="height:140px;object-fit:cover">
                                        @else
                                            <div class="bg-light d-flex align-items-center justify-content-center" style="height:140px">
                                                <span class="text-muted small">No image</span>
                                            </div>
                                        @endif
                                    </a>
                                    <div class="card-body p-2">
                                        <p class="small font-weight-bold mb-0 text-truncate" title="{{ $item->title }}">{{ $item->title }}</p>
                                        @if ($item->sku)
                                            <code class="small">{{ $item->sku }}</code>
                                        @endif
                                        @if ($item->brand || $item->vendor)
                                            <p class="small text-muted mb-1">
                                                @if ($item->brand)<span>{{ $item->brand }}</span>@endif
                                                @if ($item->brand && $item->vendor)<span> · </span>@endif
                                                @if ($item->vendor)<span>{{ $item->vendor }}</span>@endif
                                            </p>
                                        @endif
                                        <p class="small text-muted mb-1">{{ money($item->price) }}</p>
                                        <a href="{{ route('admin.content.show', $item) }}" class="btn btn-xs btn-outline-primary btn-block">Open</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            @if ($items->hasPages() || $items->total() > 0)
                <div class="card-footer d-flex flex-wrap justify-content-between align-items-center">
                    <span class="text-muted small mb-2 mb-md-0">
                        Showing {{ $items->firstItem() ?? 0 }}–{{ $items->lastItem() ?? 0 }} of {{ $items->total() }}
                    </span>
                    @if ($items->hasPages())
                        <div>{{ $items->links() }}</div>
                    @endif
                </div>
            @endif
        </form>
    </div>

@endsection

@push('scripts')
<script>
(function () {
    var checks = document.querySelectorAll('.item-check');
    var selectPage = document.getElementById('select-page');
    var selectAllPagesCheckbox = document.getElementById('select-all-pages');
    var selectAllStatus = document.getElementById('select-all-status');
    var btn = document.getElementById('btn-process-selected');
    var form = document.getElementById('batch-form');
    var filteredTotal = {{ (int) $items->total() }};
    var pageTotal = checks.length;
    var filterBrand = @json($brand);
    var filterDateFrom = @json($dateFrom);
    var filterDateTo = @json($dateTo);
    var selectAllPages = false;

    function selectedChecks() {
        return Array.from(checks).filter(function (c) { return c.checked; });
    }

    function hasSelection() {
        return selectAllPages || selectedChecks().length > 0;
    }

    function updateSelectAllStatus() {
        if (!selectAllStatus) return;
        if (selectAllPages && filteredTotal > 0) {
            selectAllStatus.textContent = 'All ' + filteredTotal + ' matching items selected (all pages)';
            selectAllStatus.classList.remove('d-none');
        } else if (selectedChecks().length > 0) {
            selectAllStatus.textContent = selectedChecks().length + ' on this page selected';
            selectAllStatus.classList.remove('d-none');
        } else {
            selectAllStatus.textContent = '';
            selectAllStatus.classList.add('d-none');
        }
    }

    function syncPageCheckbox() {
        if (!selectPage || selectAllPages) return;
        selectPage.checked = pageTotal > 0 && selectedChecks().length === pageTotal;
    }

    function clearMasterSelection() {
        selectAllPages = false;
        if (selectAllPagesCheckbox) selectAllPagesCheckbox.checked = false;
        if (selectPage) selectPage.checked = false;
    }

    function selectionCountLabel() {
        if (selectAllPages) {
            return 'all ' + filteredTotal + ' matching';
        }
        if (selectPage && selectPage.checked) {
            return selectedChecks().length + ' on this page';
        }
        return selectedChecks().length + ' selected';
    }

    function updateBtn() {
        if (btn) btn.disabled = !hasSelection();
        updateSelectAllStatus();
    }

    function prepareBatchSubmit() {
        form.querySelectorAll('input[name="select_all"]').forEach(function (el) { el.remove(); });
        form.querySelectorAll('input[name="filter_brand"]').forEach(function (el) { el.remove(); });
        form.querySelectorAll('input[name="filter_date_from"]').forEach(function (el) { el.remove(); });
        form.querySelectorAll('input[name="filter_date_to"]').forEach(function (el) { el.remove(); });

        if (selectAllPages) {
            checks.forEach(function (c) { c.disabled = true; });

            var selectInput = document.createElement('input');
            selectInput.type = 'hidden';
            selectInput.name = 'select_all';
            selectInput.value = '1';
            form.appendChild(selectInput);

            if (filterBrand) {
                var brandInput = document.createElement('input');
                brandInput.type = 'hidden';
                brandInput.name = 'filter_brand';
                brandInput.value = filterBrand;
                form.appendChild(brandInput);
            }
            if (filterDateFrom) {
                var fromInput = document.createElement('input');
                fromInput.type = 'hidden';
                fromInput.name = 'filter_date_from';
                fromInput.value = filterDateFrom;
                form.appendChild(fromInput);
            }
            if (filterDateTo) {
                var toInput = document.createElement('input');
                toInput.type = 'hidden';
                toInput.name = 'filter_date_to';
                toInput.value = filterDateTo;
                form.appendChild(toInput);
            }
        } else {
            checks.forEach(function (c) { c.disabled = false; });
        }
    }

    checks.forEach(function (c) {
        c.addEventListener('change', function () {
            if (!c.checked) clearMasterSelection();
            syncPageCheckbox();
            updateBtn();
        });
    });

    if (selectPage) {
        selectPage.addEventListener('change', function () {
            selectAllPages = false;
            if (selectAllPagesCheckbox) selectAllPagesCheckbox.checked = false;
            checks.forEach(function (check) { check.checked = selectPage.checked; });
            updateBtn();
        });
    }

    if (selectAllPagesCheckbox) {
        selectAllPagesCheckbox.addEventListener('change', function () {
            selectAllPages = selectAllPagesCheckbox.checked && filteredTotal > 0;
            checks.forEach(function (check) { check.checked = selectAllPagesCheckbox.checked; });
            if (selectPage) selectPage.checked = selectAllPagesCheckbox.checked;
            updateBtn();
        });
    }

    if (btn && form) {
        btn.addEventListener('click', function () {
            if (!hasSelection()) {
                alert('Please select at least one received item.');
                return;
            }
            if (!confirm('Apply logo and process ' + selectionCountLabel() + ' images?')) {
                return;
            }
            prepareBatchSubmit();
            form.submit();
        });
    }
})();
</script>
@endpush
