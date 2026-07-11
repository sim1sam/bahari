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
                    <div class="ecom-bulk-actions">
                        <label class="ecom-select-all mb-0">
                            <input type="checkbox" id="select-all"> Select all
                        </label>
                        <button type="button" class="btn btn-sm btn-outline-danger" id="btn-delete-selected" disabled>
                            <i class="fas fa-trash mr-1"></i> Delete Selected
                        </button>
                    </div>
                @endif
            </div>

            <div class="table-responsive">
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
@endpush

@push('scripts')
<script>
(function () {
    var checks = document.querySelectorAll('.product-check');
    var selectAll = document.getElementById('select-all');
    var deleteBtn = document.getElementById('btn-delete-selected');
    var deleteForm = document.getElementById('delete-batch-form');

    if (!checks.length || !deleteBtn || !deleteForm) {
        return;
    }

    function selectedChecks() {
        return Array.from(checks).filter(function (check) {
            return check.checked;
        });
    }

    function updateDeleteBtn() {
        deleteBtn.disabled = selectedChecks().length === 0;
    }

    checks.forEach(function (check) {
        check.addEventListener('change', updateDeleteBtn);
    });

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            checks.forEach(function (check) {
                check.checked = selectAll.checked;
            });
            updateDeleteBtn();
        });
    }

    deleteBtn.addEventListener('click', function () {
        var selected = selectedChecks();

        if (!selected.length) {
            alert('Please select at least one product.');
            return;
        }

        if (!confirm('Remove ' + selected.length + ' selected product(s) from the storefront?')) {
            return;
        }

        deleteForm.querySelectorAll('input[name="products[]"]').forEach(function (input) {
            input.remove();
        });

        selected.forEach(function (check) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'products[]';
            input.value = check.value;
            deleteForm.appendChild(input);
        });

        deleteForm.submit();
    });
})();
</script>
@endpush
