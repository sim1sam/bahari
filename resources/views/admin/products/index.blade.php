@extends('layouts.admin')

@section('title', 'Products')
@section('page_title', 'Storefront Products')

@section('content')
    <form id="delete-batch-form" action="{{ route('admin.products.destroy-batch') }}" method="POST" class="d-none">
        @csrf
        @method('DELETE')
    </form>

    <div class="ecom-page products-page">
        <section class="ecom-hero">
            <div>
                <span class="ecom-eyebrow">Ecommerce</span>
                <h2>Storefront Products</h2>
                <p>Manage live products on your store — manual entries and API imports.</p>
            </div>
            <div class="ecom-hero-actions">
                <a href="{{ route('admin.products.create') }}" class="btn btn-info">
                    <i class="fas fa-plus mr-1"></i> Create Product
                </a>
                <a href="{{ route('admin.processed.index') }}" class="btn btn-light">
                    <i class="fas fa-check-circle mr-1"></i> Processed Product
                </a>
                <a href="{{ route('admin.content.index') }}" class="btn btn-light">
                    <i class="fas fa-cloud-download-alt mr-1"></i> Import Product
                </a>
            </div>
        </section>

        <section class="row ecom-stats">
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="ecom-stat ecom-stat--total">
                    <span class="ecom-stat-icon"><i class="fas fa-box"></i></span>
                    <div>
                        <div class="ecom-stat-value">{{ number_format($stats['total']) }}</div>
                        <div class="ecom-stat-label">Total Products</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="ecom-stat ecom-stat--live">
                    <span class="ecom-stat-icon"><i class="fas fa-eye"></i></span>
                    <div>
                        <div class="ecom-stat-value">{{ number_format($stats['live']) }}</div>
                        <div class="ecom-stat-label">Live on Store</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="ecom-stat ecom-stat--manual">
                    <span class="ecom-stat-icon"><i class="fas fa-pen"></i></span>
                    <div>
                        <div class="ecom-stat-value">{{ number_format($stats['manual']) }}</div>
                        <div class="ecom-stat-label">Manual</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="ecom-stat ecom-stat--api">
                    <span class="ecom-stat-icon"><i class="fas fa-plug"></i></span>
                    <div>
                        <div class="ecom-stat-value">{{ number_format($stats['api']) }}</div>
                        <div class="ecom-stat-label">From API</div>
                    </div>
                </article>
            </div>
        </section>

        <div class="card ecom-card">
            <div class="ecom-card-head">
                <div>
                    <h3 class="mb-0">All Products</h3>
                    <p class="mb-0 text-muted">Showing {{ $products->count() }} of {{ $products->total() }} products</p>
                </div>
                @if ($products->count() > 0)
                    <div class="ecom-bulk-actions products-select-toolbar">
                        <label class="ecom-select-all mb-0">
                            <input type="checkbox" id="select-page"> This page
                        </label>
                        <label class="ecom-select-all mb-0">
                            <input type="checkbox" id="select-all-pages"> All pages
                        </label>
                        <span id="select-all-status" class="products-select-status d-none"></span>
                        <button type="button" class="btn btn-sm btn-outline-danger" id="btn-delete-selected" disabled>
                            <i class="fas fa-trash mr-1"></i> Delete Selected
                        </button>
                    </div>
                @endif
            </div>

            <form action="{{ route('admin.products.index') }}" method="GET" class="products-filter-bar">
                <div class="products-filter-field">
                    <label>Search</label>
                    <input type="text" name="q" class="form-control" value="{{ $q }}" placeholder="Name, slug, brand" aria-label="Search">
                </div>
                <div class="products-filter-field">
                    <label>Brand</label>
                    <select name="brand" class="form-control" aria-label="Brand">
                        <option value="">All brands</option>
                        @foreach ($brands as $brandOption)
                            <option value="{{ $brandOption }}" @selected($brand === $brandOption)>{{ $brandOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="products-filter-field">
                    <label>Category</label>
                    <select name="category_id" class="form-control" aria-label="Category">
                        <option value="">All categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) $categoryId === (string) $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="products-filter-field">
                    <label>Source</label>
                    <select name="source" class="form-control" aria-label="Source">
                        <option value="">All sources</option>
                        <option value="api" @selected($source === 'api')>API</option>
                        <option value="manual" @selected($source === 'manual')>Manual</option>
                    </select>
                </div>
                <div class="products-filter-field">
                    <label>From date</label>
                    <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}" aria-label="From date">
                </div>
                <div class="products-filter-field">
                    <label>To date</label>
                    <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}" aria-label="To date">
                </div>
                <div class="products-filter-field">
                    <label>Per page</label>
                    <select name="per_page" class="form-control" aria-label="Items per page">
                        @foreach ([20, 50, 100, 'all'] as $size)
                            <option value="{{ $size }}" @selected((string) $perPage === (string) $size)>{{ $size === 'all' ? 'All' : $size }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="products-filter-actions">
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-filter mr-1"></i> Apply
                    </button>
                    @if ($q || $brand || $categoryId || $source || $dateFrom || $dateTo || $perPage !== 20)
                        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Clear</a>
                    @endif
                </div>
            </form>

            @if ($q || $brand || $categoryId || $source || $dateFrom || $dateTo || $perPage !== 20)
                <div class="products-active-filters">
                    <span class="products-active-filters-label">Active filters:</span>
                    @if ($q)
                        <span class="products-filter-chip"><i class="fas fa-search"></i> {{ $q }}</span>
                    @endif
                    @if ($brand)
                        <span class="products-filter-chip"><i class="fas fa-tag"></i> {{ $brand }}</span>
                    @endif
                    @if ($categoryId)
                        <span class="products-filter-chip"><i class="fas fa-folder"></i> {{ $categories->firstWhere('id', (int) $categoryId)?->name ?? 'Category' }}</span>
                    @endif
                    @if ($source)
                        <span class="products-filter-chip"><i class="fas fa-plug"></i> {{ strtoupper($source) }}</span>
                    @endif
                    @if ($dateFrom || $dateTo)
                        <span class="products-filter-chip">
                            <i class="fas fa-calendar"></i>
                            {{ $dateFrom ?: '…' }} → {{ $dateTo ?: '…' }}
                        </span>
                    @endif
                    @if ($perPage !== 20)
                        <span class="products-filter-chip"><i class="fas fa-list-ol"></i> {{ $perPage === 'all' ? 'All' : $perPage.' per page' }}</span>
                    @endif
                </div>
            @endif

            <div class="ecom-app-list d-md-none">
                @forelse ($products as $product)
                    <article class="ecom-app-card">
                        <div class="ecom-app-card-top">
                            <label class="ecom-app-select">
                                <input type="checkbox" class="product-check" name="products[]" value="{{ $product->id }}">
                                <span class="ecom-app-select-mark"><i class="fas fa-check"></i></span>
                            </label>
                            @if ($url = $product->imageUrl())
                                <img src="{{ $url }}" alt="" class="ecom-app-card-thumb">
                            @else
                                <span class="ecom-app-card-thumb ecom-app-card-thumb--empty"><i class="fas fa-image"></i></span>
                            @endif
                            <div class="ecom-app-card-info">
                                <div class="ecom-app-card-name">{{ $product->name }}</div>
                                <code class="ecom-product-slug">{{ $product->slug }}</code>
                                <div class="ecom-app-card-chips">
                                    <span class="ecom-pill {{ $product->isManualProduct() ? 'ecom-pill--manual' : 'ecom-pill--api' }}">
                                        {{ $product->isManualProduct() ? 'Manual' : 'API' }}
                                    </span>
                                    <span class="ecom-status {{ $product->is_active ? 'ecom-status--live' : 'ecom-status--hidden' }}">
                                        {{ $product->is_active ? 'Live' : 'Hidden' }}
                                    </span>
                                </div>
                            </div>
                            <div class="ecom-app-card-price">
                                <strong>{{ money($product->price) }}</strong>
                                @if ($product->original_price)
                                    <small><s>{{ money($product->original_price) }}</s></small>
                                @endif
                            </div>
                        </div>

                        <div class="ecom-app-card-meta">
                            <span><i class="fas fa-tag"></i> {{ $product->brand ?: 'No brand' }}</span>
                            <span><i class="fas fa-folder"></i> {{ $product->category?->name ?? 'No category' }}</span>
                            <span class="{{ $product->stock > 0 ? '' : 'text-danger' }}">
                                <i class="fas fa-boxes"></i> Stock {{ $product->stock }}
                            </span>
                        </div>

                        <div class="ecom-app-card-foot">
                            @if ($product->is_active)
                                <a href="{{ route('products.show', $product->slug) }}" class="btn btn-sm btn-outline-success" target="_blank" rel="noopener">
                                    <i class="fas fa-external-link-alt"></i> View
                                </a>
                            @endif
                            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-outline-info">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="ecom-app-card-delete" onsubmit="return confirm('Remove this product from the storefront?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="ecom-empty ecom-app-empty">
                        <i class="fas fa-box-open"></i>
                        <strong>No products yet</strong>
                        <p>Create a product manually or publish from API processed items.</p>
                        <a href="{{ route('admin.products.create') }}" class="btn btn-sm btn-info mt-2 mr-1">
                            <i class="fas fa-plus mr-1"></i> Create Product
                        </a>
                        <a href="{{ route('admin.processed.index') }}" class="btn btn-sm btn-outline-secondary mt-2">
                            API Processed
                        </a>
                    </div>
                @endforelse
            </div>

            <div class="table-responsive d-none d-md-block">
                <table class="table ecom-table mb-0">
                    <thead>
                        <tr>
                            <th width="40"></th>
                            <th>Product</th>
                            <th>Source</th>
                            <th>Brand</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                            <tr>
                                <td>
                                    <input type="checkbox" class="product-check" name="products[]" value="{{ $product->id }}">
                                </td>
                                <td>
                                    <div class="ecom-product-cell">
                                        @if ($url = $product->imageUrl())
                                            <img src="{{ $url }}" alt="" class="ecom-product-thumb">
                                        @else
                                            <span class="ecom-product-thumb ecom-product-thumb--empty"><i class="fas fa-image"></i></span>
                                        @endif
                                        <div>
                                            <div class="ecom-product-name">{{ $product->name }}</div>
                                            <code class="ecom-product-slug">{{ $product->slug }}</code>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="ecom-pill {{ $product->isManualProduct() ? 'ecom-pill--manual' : 'ecom-pill--api' }}">
                                        {{ $product->isManualProduct() ? 'Manual' : 'API' }}
                                    </span>
                                </td>
                                <td>{{ $product->brand ?: '—' }}</td>
                                <td>{{ $product->category?->name ?? '—' }}</td>
                                <td>
                                    <div class="ecom-price">{{ money($product->price) }}</div>
                                    @if ($product->original_price)
                                        <small class="text-muted"><s>{{ money($product->original_price) }}</s></small>
                                    @endif
                                </td>
                                <td>
                                    <span class="ecom-stock {{ $product->stock > 0 ? '' : 'ecom-stock--empty' }}">
                                        {{ $product->stock }}
                                    </span>
                                </td>
                                <td>
                                    <span class="ecom-status {{ $product->is_active ? 'ecom-status--live' : 'ecom-status--hidden' }}">
                                        {{ $product->is_active ? 'Live' : 'Hidden' }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <div class="ecom-actions">
                                        @if ($product->is_active)
                                            <a href="{{ route('products.show', $product->slug) }}" class="btn btn-xs btn-outline-success" target="_blank" rel="noopener" title="View on storefront">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        @endif
                                        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-xs btn-outline-info" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this product from the storefront?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-outline-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="ecom-empty">
                                    <i class="fas fa-box-open"></i>
                                    <strong>No products yet</strong>
                                    <p>Create a product manually or publish from API processed items.</p>
                                    <a href="{{ route('admin.products.create') }}" class="btn btn-sm btn-info mt-2 mr-1">
                                        <i class="fas fa-plus mr-1"></i> Create Product
                                    </a>
                                    <a href="{{ route('admin.processed.index') }}" class="btn btn-sm btn-outline-secondary mt-2">
                                        API Processed
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($products->hasPages())
                <div class="ecom-card-footer">{{ $products->links() }}</div>
            @endif
        </div>
    </div>
@endsection

@push('styles')
@include('admin.partials.ecom-page-styles')
<style>
    .products-filter-bar {
        display: grid;
        grid-template-columns: minmax(140px, 1.3fr) minmax(120px, 1fr) minmax(120px, 1fr) minmax(100px, 0.8fr) minmax(110px, 0.9fr) minmax(110px, 0.9fr) minmax(90px, 0.7fr) auto;
        gap: 0.65rem;
        align-items: end;
        margin: 0 1rem 0.85rem;
        padding: 0.85rem;
        border: 1px solid #e2e8f0;
        border-radius: 0.85rem;
        background: #f8fafc;
    }

    .products-filter-field label {
        display: block;
        margin-bottom: 0.3rem;
        color: #64748b;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .products-filter-field .form-control {
        min-height: 2.45rem;
        border-color: #dbe3ed;
        border-radius: 0.6rem;
        background: #fff;
        box-shadow: none;
    }

    .products-filter-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
        align-items: center;
    }

    .products-active-filters {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.45rem;
        margin: 0 1rem 0.85rem;
    }

    .products-active-filters-label {
        color: #64748b;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .products-filter-chip {
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

    .products-select-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.75rem;
    }

    .products-select-status {
        color: #0891b2;
        font-size: 0.76rem;
        font-weight: 700;
    }

    @media (max-width: 1200px) {
        .products-filter-bar {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 576px) {
        .products-filter-bar {
            grid-template-columns: 1fr;
            margin: 0 0.75rem 0.85rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
(function () {
    var checks = document.querySelectorAll('.product-check');
    var selectPage = document.getElementById('select-page');
    var selectAllPagesCheckbox = document.getElementById('select-all-pages');
    var selectAllStatus = document.getElementById('select-all-status');
    var deleteBtn = document.getElementById('btn-delete-selected');
    var deleteForm = document.getElementById('delete-batch-form');
    var filteredTotal = {{ (int) $products->total() }};
    var pageTotal = checks.length;
    var selectAllPages = false;
    var filterBrand = @json($brand);
    var filterCategoryId = @json($categoryId);
    var filterSource = @json($source);
    var filterDateFrom = @json($dateFrom);
    var filterDateTo = @json($dateTo);
    var filterQ = @json($q);

    if (!deleteBtn || !deleteForm) {
        return;
    }

    function visibleChecks() {
        return Array.from(checks).filter(function (check) {
            return check.offsetParent !== null;
        });
    }

    function selectedChecks() {
        return visibleChecks().filter(function (check) {
            return check.checked;
        });
    }

    function updateStatus() {
        if (!selectAllStatus) return;

        if (selectAllPages && filteredTotal > 0) {
            selectAllStatus.textContent = 'All ' + filteredTotal + ' matching products selected (all pages)';
            selectAllStatus.classList.remove('d-none');
        } else {
            var count = selectedChecks().length;
            if (count > 0) {
                selectAllStatus.textContent = count + ' on this page selected';
                selectAllStatus.classList.remove('d-none');
            } else {
                selectAllStatus.classList.add('d-none');
            }
        }
    }

    function updateDeleteBtn() {
        var any = selectAllPages ? filteredTotal > 0 : selectedChecks().length > 0;
        deleteBtn.disabled = !any;

        if (selectPage) {
            var visible = visibleChecks();
            selectPage.checked = !selectAllPages && visible.length > 0 && visible.every(function (c) { return c.checked; });
        }

        updateStatus();
    }

    function selectionCountLabel() {
        if (selectAllPages) {
            return 'all ' + filteredTotal + ' matching';
        }
        return selectedChecks().length + ' selected';
    }

    function clearFilterInputs(form) {
        ['select_all', 'filter_brand', 'filter_category_id', 'filter_source', 'filter_date_from', 'filter_date_to', 'filter_q', 'products[]'].forEach(function (name) {
            form.querySelectorAll('input[name="' + name + '"]').forEach(function (el) { el.remove(); });
        });
    }

    function appendFilterInputs(form) {
        if (!selectAllPages) return;

        var selectInput = document.createElement('input');
        selectInput.type = 'hidden';
        selectInput.name = 'select_all';
        selectInput.value = '1';
        form.appendChild(selectInput);

        function add(name, value) {
            if (!value) return;
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value;
            form.appendChild(input);
        }

        add('filter_brand', filterBrand);
        add('filter_category_id', filterCategoryId);
        add('filter_source', filterSource);
        add('filter_date_from', filterDateFrom);
        add('filter_date_to', filterDateTo);
        add('filter_q', filterQ);
    }

    checks.forEach(function (check) {
        check.addEventListener('change', function () {
            if (selectAllPagesCheckbox && !check.checked) {
                selectAllPages = false;
                selectAllPagesCheckbox.checked = false;
            }
            updateDeleteBtn();
        });
    });

    if (selectPage) {
        selectPage.addEventListener('change', function () {
            selectAllPages = false;
            if (selectAllPagesCheckbox) selectAllPagesCheckbox.checked = false;
            visibleChecks().forEach(function (check) {
                check.checked = selectPage.checked;
            });
            updateDeleteBtn();
        });
    }

    if (selectAllPagesCheckbox) {
        selectAllPagesCheckbox.addEventListener('change', function () {
            selectAllPages = selectAllPagesCheckbox.checked && filteredTotal > 0;
            if (selectAllPages) {
                visibleChecks().forEach(function (check) { check.checked = true; });
                if (selectPage) selectPage.checked = true;
            }
            updateDeleteBtn();
        });
    }

    deleteBtn.addEventListener('click', function () {
        if (selectAllPages) {
            if (!confirm('Remove ' + selectionCountLabel() + ' product(s) from the storefront?')) {
                return;
            }
            clearFilterInputs(deleteForm);
            appendFilterInputs(deleteForm);
            deleteForm.submit();
            return;
        }

        var selected = selectedChecks();
        if (!selected.length) {
            alert('Please select at least one product.');
            return;
        }

        if (!confirm('Remove ' + selected.length + ' selected product(s) from the storefront?')) {
            return;
        }

        clearFilterInputs(deleteForm);

        var seen = {};
        selected.forEach(function (check) {
            if (seen[check.value]) return;
            seen[check.value] = true;
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'products[]';
            input.value = check.value;
            deleteForm.appendChild(input);
        });

        deleteForm.submit();
    });

    updateDeleteBtn();
})();
</script>
@endpush
