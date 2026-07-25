@extends('layouts.admin')

@section('title', 'Processed Product')
@section('page_title', 'Processed — Ready to Go Live')

@section('content')
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

    <form id="download-filtered-form" action="{{ route('admin.processed.download-filtered') }}" method="POST" class="d-none">
        @csrf
        <input type="hidden" name="brand" value="{{ $brand }}">
        <input type="hidden" name="date_from" value="{{ $dateFrom }}">
        <input type="hidden" name="date_to" value="{{ $dateTo }}">
    </form>

    <div class="ecom-page processed-page">
        <section class="ecom-hero">
            <div>
                <span class="ecom-eyebrow">Ecommerce</span>
                <h2>Processed Product</h2>
                <p>Review logo-applied images, download batches, and publish ready items to your storefront.</p>
            </div>
            <div class="ecom-hero-actions">
                <a href="{{ route('admin.content.index') }}" class="btn btn-primary">
                    <i class="fas fa-cloud-download-alt mr-1"></i> Import Product
                </a>
                <a href="{{ route('admin.processed.live') }}" class="btn btn-success">
                    <i class="fas fa-globe mr-1"></i> Live on Site
                </a>
                <a href="{{ route('admin.api-settings.index') }}" class="btn btn-info">
                    <i class="fas fa-cog mr-1"></i> Content API Settings
                </a>
                <form action="{{ route('admin.processed.purge-manual-products') }}" method="POST" class="d-inline" onsubmit="return confirm('Delete all old products that were NOT published from Processed? API Go Live products will stay.')">
                    @csrf
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-broom mr-1"></i> Remove Old Products
                    </button>
                </form>
            </div>
        </section>

        <section class="row ecom-stats">
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="ecom-stat ecom-stat--manual">
                    <span class="ecom-stat-icon"><i class="fas fa-magic"></i></span>
                    <div>
                        <div class="ecom-stat-value">{{ number_format($processedCount) }}</div>
                        <div class="ecom-stat-label">Awaiting Go Live</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="ecom-stat ecom-stat--live">
                    <span class="ecom-stat-icon"><i class="fas fa-store"></i></span>
                    <div>
                        <div class="ecom-stat-value">{{ number_format($liveCount) }}</div>
                        <div class="ecom-stat-label">Published</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="ecom-stat ecom-stat--total">
                    <span class="ecom-stat-icon"><i class="fas fa-list"></i></span>
                    <div>
                        <div class="ecom-stat-value">{{ number_format($items->total()) }}</div>
                        <div class="ecom-stat-label">Filtered Results</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="ecom-stat ecom-stat--api">
                    <span class="ecom-stat-icon"><i class="fas fa-tags"></i></span>
                    <div>
                        <div class="ecom-stat-value">{{ number_format(count($brands)) }}</div>
                        <div class="ecom-stat-label">Active Brands</div>
                    </div>
                </article>
            </div>
        </section>

        <div class="card ecom-card processed-card">
            <div class="processed-toolbar">
                <div class="processed-toolbar-top">
                    <div>
                        <h3 class="mb-0">Processed Products</h3>
                        <p class="mb-0 text-muted">
                            Showing {{ $items->firstItem() ?? 0 }}–{{ $items->lastItem() ?? 0 }} of {{ $items->total() }}
                        </p>
                    </div>
                    <div class="processed-select-toolbar">
                        <label class="processed-select-chip processed-select-chip--page">
                            <input type="checkbox" id="select-page">
                            <span><i class="fas fa-check"></i> This page</span>
                        </label>
                        <label class="processed-select-chip processed-select-chip--all">
                            <input type="checkbox" id="select-all-pages">
                            <span><i class="fas fa-layer-group"></i> All pages</span>
                        </label>
                        <span id="select-all-status" class="processed-select-status d-none"></span>
                    </div>
                </div>

                <form action="{{ route('admin.processed.index') }}" method="GET" class="processed-filter-bar">
                    <div class="processed-filter-field">
                        <label>Brand</label>
                        <select name="brand" class="form-control" aria-label="Brand">
                            <option value="">All brands</option>
                            @foreach ($brands as $brandOption)
                                <option value="{{ $brandOption }}" @selected($brand === $brandOption)>{{ $brandOption }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="processed-filter-field">
                        <label>From date</label>
                        <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}" aria-label="From date">
                    </div>
                    <div class="processed-filter-field">
                        <label>To date</label>
                        <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}" aria-label="To date">
                    </div>
                    <div class="processed-filter-field">
                        <label>Per Page</label>
                        <select name="per_page" class="form-control" aria-label="Items per page">
                            @foreach ([20, 50, 100] as $size)
                                <option value="{{ $size }}" @selected($perPage === $size)>{{ $size }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="processed-filter-actions">
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-filter mr-1"></i> Apply
                        </button>
                        @if ($brand || $dateFrom || $dateTo || $perPage !== 20)
                            <a href="{{ route('admin.processed.index') }}" class="btn btn-secondary">Clear</a>
                        @endif
                        @if ($brand || $dateFrom || $dateTo)
                            <button type="button" class="btn btn-info" id="btn-download-filtered">
                                <i class="fas fa-file-archive mr-1"></i> ZIP Filtered
                            </button>
                        @endif
                    </div>
                </form>

                @if ($brand || $dateFrom || $dateTo || $perPage !== 20)
                    <div class="processed-active-filters">
                        <span class="processed-active-filters-label">Active filters:</span>
                        @if ($brand)
                            <span class="processed-filter-chip"><i class="fas fa-tag"></i> {{ $brand }}</span>
                        @endif
                        @if ($dateFrom || $dateTo)
                            <span class="processed-filter-chip">
                                <i class="fas fa-calendar"></i>
                                {{ $dateFrom ?: '…' }} → {{ $dateTo ?: '…' }}
                            </span>
                        @endif
                        @if ($perPage !== 20)
                            <span class="processed-filter-chip"><i class="fas fa-list-ol"></i> {{ $perPage }} per page</span>
                        @endif
                    </div>
                @endif

                <div class="processed-bulk-bar">
                    <div class="processed-category-field">
                        <label for="live-category-id">Publish Category</label>
                        <select id="live-category-id" class="form-control form-control-sm" title="Optional — uses API category when empty">
                            <option value="">Use API category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="processed-bulk-actions">
                        <button type="button" class="btn btn-sm btn-secondary" id="btn-download-flat" disabled>
                            <i class="fas fa-download mr-1"></i> Download Selected
                        </button>
                        <button type="button" class="btn btn-sm btn-info" id="btn-download-brand" disabled>
                            <i class="fas fa-file-archive mr-1"></i> ZIP by Brand
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" id="btn-delete-selected" disabled>
                            <i class="fas fa-trash mr-1"></i> Delete Selected
                        </button>
                        <button type="button" class="btn btn-sm btn-success" id="btn-live-selected" disabled>
                            <i class="fas fa-globe mr-1"></i> Go Live (Selected)
                        </button>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table ecom-table processed-table mb-0">
                    <thead>
                        <tr>
                            <th width="40"></th>
                            <th>Product</th>
                            <th>Brand</th>
                            <th>Price</th>
                            <th>Category</th>
                            <th>Processed</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            <tr class="processed-row" data-item-id="{{ $item->id }}">
                                <td>
                                    <input type="checkbox" class="item-check" name="items[]" value="{{ $item->id }}">
                                </td>
                                <td>
                                    <div class="ecom-product-cell">
                                        @if ($url = $item->displayImageUrl())
                                            <img src="{{ $url }}" alt="" class="ecom-product-thumb">
                                        @else
                                            <span class="ecom-product-thumb ecom-product-thumb--empty"><i class="fas fa-image"></i></span>
                                        @endif
                                        <div>
                                            <div class="ecom-product-name">{{ $item->title }}</div>
                                            @if ($item->sku)
                                                <code class="ecom-product-slug">{{ $item->sku }}</code>
                                            @endif
                                            @if ($item->vendor)
                                                <small class="text-muted d-block">{{ $item->vendor }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $item->brand ?: '—' }}</td>
                                <td><span class="ecom-price">{{ money($item->price) }}</span></td>
                                <td>{{ $item->category_name ?: '—' }}</td>
                                <td>
                                    <div class="processed-date">{{ $item->updated_at->format('M d, Y') }}</div>
                                    <small class="text-muted">{{ $item->updated_at->format('h:i A') }}</small>
                                </td>
                                <td>
                                    <span class="processed-status">Ready</span>
                                </td>
                                <td class="text-right">
                                    <div class="ecom-actions processed-actions">
                                        <a href="{{ route('admin.processed.download-image', $item) }}" class="btn btn-xs btn-secondary" title="Download image">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <a href="{{ route('admin.processed.show', $item) }}" class="btn btn-xs btn-info" title="Review">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <form action="{{ route('admin.processed.live-item', $item) }}" method="POST" class="d-inline live-item-form" data-api-category="{{ $item->category_name }}" onsubmit="return submitLiveItem(this)">
                                            @csrf
                                            <input type="hidden" name="category_id" class="live-category-input" value="">
                                            <button type="submit" class="btn btn-xs btn-success" title="Go Live">
                                                <i class="fas fa-globe"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.content.reimport', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Re-import this item back to Import Product list?')">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-warning" title="Re-import">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.processed.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this processed item permanently?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="ecom-empty">
                                    <i class="fas fa-magic"></i>
                                    <strong>No processed items yet</strong>
                                    <p>Process images from the Import Product page to see them here.</p>
                                    <a href="{{ route('admin.content.index') }}" class="btn btn-sm btn-info mt-2">
                                        <i class="fas fa-cloud-download-alt mr-1"></i> Import Product
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($items->hasPages())
                <div class="ecom-card-footer">{{ $items->links() }}</div>
            @endif
        </div>
    </div>
@endsection

@push('styles')
@include('admin.partials.ecom-page-styles')
<style>
    .processed-toolbar {
        padding: 1rem 1.15rem 0.85rem;
        border-bottom: 1px solid #eef2f7;
        background: #fff;
    }

    .processed-toolbar-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 0.9rem;
        flex-wrap: wrap;
    }

    .processed-toolbar-top h3 {
        font-size: 1rem;
        font-weight: 700;
        color: #0f172a;
    }

    .processed-toolbar-top p {
        font-size: 0.8rem;
    }

    .processed-select-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.45rem;
    }

    .processed-select-chip {
        margin: 0;
        cursor: pointer;
    }

    .processed-select-chip input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .processed-select-chip span {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.45rem 0.85rem;
        border: 2px solid transparent;
        border-radius: 999px;
        font-size: 0.76rem;
        font-weight: 700;
        transition: all 0.15s ease;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
    }

    .processed-select-chip--page span {
        background: #eff6ff;
        border-color: #93c5fd;
        color: #1d4ed8;
    }

    .processed-select-chip--page:hover span {
        background: #dbeafe;
        border-color: #60a5fa;
    }

    .processed-select-chip--page input:checked + span {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        border-color: #1d4ed8;
        color: #fff;
        box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);
    }

    .processed-select-chip--all span {
        background: #f5f3ff;
        border-color: #c4b5fd;
        color: #6d28d9;
    }

    .processed-select-chip--all:hover span {
        background: #ede9fe;
        border-color: #a78bfa;
    }

    .processed-select-chip--all input:checked + span {
        background: linear-gradient(135deg, #7c3aed, #6d28d9);
        border-color: #6d28d9;
        color: #fff;
        box-shadow: 0 4px 14px rgba(124, 58, 237, 0.35);
    }

    .processed-select-chip input:checked + span i {
        color: inherit;
    }

    .processed-select-status {
        color: #0891b2;
        font-size: 0.76rem;
        font-weight: 700;
    }

    .processed-filter-bar {
        display: grid;
        grid-template-columns: minmax(140px, 1.2fr) minmax(120px, 0.9fr) minmax(120px, 0.9fr) minmax(100px, 0.7fr) auto;
        gap: 0.65rem;
        align-items: end;
        padding: 0.85rem;
        border: 1px solid #e2e8f0;
        border-radius: 0.85rem;
        background: #f8fafc;
    }

    .processed-filter-field label {
        display: block;
        margin-bottom: 0.3rem;
        color: #64748b;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .processed-filter-field .form-control {
        min-height: 2.45rem;
        border-color: #dbe3ed;
        border-radius: 0.6rem;
        background: #fff;
        box-shadow: none;
    }

    .processed-filter-field .form-control:focus {
        border-color: #22d3ee;
        box-shadow: 0 0 0 3px rgba(34, 211, 238, 0.12);
    }

    .processed-filter-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
        align-items: center;
        padding-bottom: 0.05rem;
    }

    .processed-active-filters {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.45rem;
        margin-top: 0.75rem;
    }

    .processed-active-filters-label {
        color: #64748b;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .processed-filter-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.25rem 0.6rem;
        border-radius: 999px;
        background: #ecfeff;
        color: #0e7490;
        font-size: 0.74rem;
        font-weight: 600;
    }

    .processed-bulk-bar {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 1rem;
        margin-top: 0.85rem;
        padding: 0.85rem;
        border: 1px solid #e2e8f0;
        border-radius: 0.85rem;
        background: linear-gradient(180deg, #fafbfc 0%, #fff 100%);
        flex-wrap: wrap;
    }

    .processed-category-field {
        flex: 1;
        min-width: 200px;
        max-width: 280px;
    }

    .processed-category-field label {
        display: block;
        margin-bottom: 0.3rem;
        color: #64748b;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .processed-category-field .form-control {
        border-color: #dbe3ed;
        border-radius: 0.6rem;
        min-height: 2.2rem;
    }

    .processed-bulk-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
        align-items: center;
    }

    .processed-date {
        font-weight: 600;
        color: #334155;
        font-size: 0.82rem;
    }

    .processed-status {
        display: inline-block;
        padding: 0.25rem 0.6rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #b45309;
        background: #fffbeb;
    }

    .processed-row--selected {
        background: #ecfeff !important;
    }

    .processed-row--selected td {
        border-top-color: #a5f3fc !important;
    }

    .processed-page .ecom-hero-actions .btn {
        font-weight: 700;
        border: 0;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.18);
    }

    .processed-page .processed-filter-actions .btn,
    .processed-page .processed-bulk-actions .btn {
        font-weight: 700;
        border: 0;
    }

    .processed-actions .btn {
        border: 0;
        color: #fff;
    }

    .processed-actions .btn-secondary { background: #64748b; }
    .processed-actions .btn-info { background: #0891b2; }
    .processed-actions .btn-success { background: #059669; }
    .processed-actions .btn-warning { background: #d97706; color: #fff; }
    .processed-actions .btn-danger { background: #dc2626; }

    .processed-actions .btn:hover {
        color: #fff;
        filter: brightness(1.08);
    }

    @media (max-width: 991.98px) {
        .processed-filter-bar {
            grid-template-columns: 1fr 1fr;
        }

        .processed-filter-actions {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 767.98px) {
        .processed-toolbar-top {
            flex-direction: column;
        }

        .processed-select-toolbar {
            width: 100%;
        }

        .processed-filter-bar {
            grid-template-columns: 1fr;
        }

        .processed-bulk-bar {
            flex-direction: column;
            align-items: stretch;
        }

        .processed-category-field {
            max-width: none;
        }

        .processed-bulk-actions .btn {
            flex: 1;
        }
    }
</style>
@endpush

@push('scripts')
<script>
(function () {
    var checks = document.querySelectorAll('.item-check');
    var selectPage = document.getElementById('select-page');
    var selectAllPagesCheckbox = document.getElementById('select-all-pages');
    var selectAllStatus = document.getElementById('select-all-status');
    var liveBtn = document.getElementById('btn-live-selected');
    var deleteBtn = document.getElementById('btn-delete-selected');
    var downloadFlatBtn = document.getElementById('btn-download-flat');
    var downloadBrandBtn = document.getElementById('btn-download-brand');
    var downloadFilteredBtn = document.getElementById('btn-download-filtered');
    var liveForm = document.getElementById('batch-form');
    var deleteForm = document.getElementById('delete-batch-form');
    var downloadForm = document.getElementById('download-form');
    var downloadFilteredForm = document.getElementById('download-filtered-form');
    var downloadLayout = document.getElementById('download-layout');
    var categorySelect = document.getElementById('live-category-id');
    var filteredTotal = {{ (int) $items->total() }};
    var pageTotal = checks.length;
    var filterBrand = @json($brand);
    var filterDateFrom = @json($dateFrom);
    var filterDateTo = @json($dateTo);
    var selectAllPages = false;

    function selectedCategoryId() {
        return categorySelect ? categorySelect.value : '';
    }

    window.submitLiveItem = function (form) {
        var categoryId = selectedCategoryId();
        var apiCategory = form.getAttribute('data-api-category') || '';
        if (!categoryId && !apiCategory) {
            alert('No API category on this item. Select an override category or set ecommerce_category_name in the API payload.');
            if (categorySelect) categorySelect.focus();
            return false;
        }
        var input = form.querySelector('.live-category-input');
        if (input) input.value = categoryId;
        var label = categoryId ? 'the selected category' : ('API category "' + apiCategory + '"');
        return confirm('Publish this product using ' + label + '?');
    };

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

    function updateRowStates() {
        document.querySelectorAll('.processed-row').forEach(function (row) {
            var check = row.querySelector('.item-check');
            if (!check) return;
            row.classList.toggle('processed-row--selected', check.checked);
        });
    }

    function updateBtns() {
        var any = hasSelection();
        if (liveBtn) liveBtn.disabled = !any;
        if (deleteBtn) deleteBtn.disabled = !any;
        if (downloadFlatBtn) downloadFlatBtn.disabled = !any;
        if (downloadBrandBtn) downloadBrandBtn.disabled = !any;
        updateSelectAllStatus();
        updateRowStates();
    }

    function clearBatchInputs(form) {
        form.querySelectorAll('input[name="items[]"]').forEach(function (el) { el.remove(); });
        form.querySelectorAll('input[name="select_all"]').forEach(function (el) { el.remove(); });
        form.querySelectorAll('input[name="filter_brand"]').forEach(function (el) { el.remove(); });
        form.querySelectorAll('input[name="filter_date"]').forEach(function (el) { el.remove(); });
        form.querySelectorAll('input[name="filter_date_from"]').forEach(function (el) { el.remove(); });
        form.querySelectorAll('input[name="filter_date_to"]').forEach(function (el) { el.remove(); });
        form.querySelectorAll('input[name="category_id"]').forEach(function (el) { el.remove(); });
    }

    function appendFilterInputs(form) {
        if (!selectAllPages) return;

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
            var dateFromInput = document.createElement('input');
            dateFromInput.type = 'hidden';
            dateFromInput.name = 'filter_date_from';
            dateFromInput.value = filterDateFrom;
            form.appendChild(dateFromInput);
        }

        if (filterDateTo) {
            var dateToInput = document.createElement('input');
            dateToInput.type = 'hidden';
            dateToInput.name = 'filter_date_to';
            dateToInput.value = filterDateTo;
            form.appendChild(dateToInput);
        }
    }

    function submitBatchForm(form, itemChecks) {
        clearBatchInputs(form);
        appendFilterInputs(form);

        if (!selectAllPages) {
            itemChecks.forEach(function (c) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'items[]';
                input.value = c.value;
                form.appendChild(input);
            });
        }
    }

    function submitDownload(layout) {
        if (!hasSelection()) {
            alert('Please select at least one processed item.');
            return;
        }

        if (selectAllPages && layout === 'flat') {
            if (!confirm('Download all ' + filteredTotal + ' matching items as a flat ZIP?')) {
                return;
            }
        }

        submitBatchForm(downloadForm, selectedChecks());
        if (downloadLayout) downloadLayout.value = (selectAllPages || layout === 'brand') ? 'brand' : layout;
        downloadForm.submit();
    }

    checks.forEach(function (c) {
        c.addEventListener('change', function () {
            if (!c.checked) clearMasterSelection();
            syncPageCheckbox();
            updateBtns();
        });
    });

    if (selectPage) {
        selectPage.addEventListener('change', function () {
            selectAllPages = false;
            if (selectAllPagesCheckbox) selectAllPagesCheckbox.checked = false;
            checks.forEach(function (check) { check.checked = selectPage.checked; });
            updateBtns();
        });
    }

    if (selectAllPagesCheckbox) {
        selectAllPagesCheckbox.addEventListener('change', function () {
            selectAllPages = selectAllPagesCheckbox.checked && filteredTotal > 0;
            checks.forEach(function (check) { check.checked = selectAllPagesCheckbox.checked; });
            if (selectPage) selectPage.checked = selectAllPagesCheckbox.checked;
            updateBtns();
        });
    }

    if (downloadFilteredBtn && downloadFilteredForm) {
        downloadFilteredBtn.addEventListener('click', function () {
            downloadFilteredForm.submit();
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
            var countLabel = selectionCountLabel();
            if (categoryId) {
                if (!confirm('Publish ' + countLabel + ' products in the selected category?')) {
                    return;
                }
            } else if (!confirm('No override category selected. Each item will use its API category (ecommerce_category_name). Publish ' + countLabel + ' products?')) {
                return;
            }
            submitBatchForm(liveForm, selectedChecks());
            if (categoryId) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'category_id';
                input.value = categoryId;
                liveForm.appendChild(input);
            }
            liveForm.submit();
        });
    }
    if (deleteBtn && deleteForm) {
        deleteBtn.addEventListener('click', function () {
            var countLabel = selectionCountLabel();
            if (!confirm('Delete ' + countLabel + ' processed items permanently?')) {
                return;
            }
            submitBatchForm(deleteForm, selectedChecks());
            deleteForm.submit();
        });
    }

    updateRowStates();
})();
</script>
@endpush
