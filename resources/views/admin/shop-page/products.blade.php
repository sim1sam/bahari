@extends('layouts.admin')

@section('title', 'Shop Products')
@section('page_title', 'Shop Products')

@section('content')
    <div class="settings-page">
        <a href="{{ route('admin.shop-page.edit') }}" class="settings-back-link">
            <i class="fas fa-arrow-left"></i> Back to Shop Page
        </a>

        <section class="settings-hero">
            <div>
                <span class="settings-eyebrow">Marketing</span>
                <h2>Shop products</h2>
                <p>Filter by date or search, then select products to pin on /shop.</p>
                <div class="settings-hero-meta">
                    <span class="settings-hero-chip"><i class="fas fa-check"></i> {{ $selectedCount }} selected</span>
                    <span class="settings-hero-chip"><i class="fas fa-box"></i> {{ count($products) }} listed</span>
                    <a href="{{ route('shop.index') }}" target="_blank" class="settings-hero-chip"><i class="fas fa-external-link-alt"></i> View shop</a>
                </div>
            </div>
            <div class="settings-hero-actions">
                <a href="{{ route('admin.shop-page.brands.edit') }}" class="btn btn-outline-light">
                    <i class="fas fa-copyright mr-1"></i> Brand wise
                </a>
            </div>
        </section>

        <form method="GET" action="{{ route('admin.shop-page.products.edit') }}" class="mb-3">
            <div class="settings-card">
                <div class="settings-card-body py-3">
                    <div class="form-row align-items-end">
                        <div class="col-md-3 mb-2 mb-md-0">
                            <label for="date_from" class="small text-muted mb-1 d-block">From date</label>
                            <input type="date" name="date_from" id="date_from" class="form-control" value="{{ $dateFrom }}">
                        </div>
                        <div class="col-md-3 mb-2 mb-md-0">
                            <label for="date_to" class="small text-muted mb-1 d-block">To date</label>
                            <input type="date" name="date_to" id="date_to" class="form-control" value="{{ $dateTo }}">
                        </div>
                        <div class="col-md-4 mb-2 mb-md-0">
                            <label for="q" class="small text-muted mb-1 d-block">Search</label>
                            <input type="text" name="q" id="q" class="form-control" value="{{ $search }}" placeholder="Product or brand...">
                        </div>
                        <div class="col-md-2 mb-2 mb-md-0">
                            <button class="btn btn-info btn-block" type="submit"><i class="fas fa-filter mr-1"></i> Filter</button>
                        </div>
                    </div>
                    @if ($hasFilters)
                        <div class="mt-2">
                            <a href="{{ route('admin.shop-page.products.edit') }}" class="small">Clear filters</a>
                        </div>
                    @endif
                </div>
            </div>
        </form>

        <form action="{{ route('admin.shop-page.products.update') }}" method="POST" id="shop-products-form">
            @csrf
            @method('PUT')
            @if ($search !== '')
                <input type="hidden" name="q" value="{{ $search }}">
            @endif
            @if ($dateFrom !== '')
                <input type="hidden" name="date_from" value="{{ $dateFrom }}">
            @endif
            @if ($dateTo !== '')
                <input type="hidden" name="date_to" value="{{ $dateTo }}">
            @endif

            <div class="settings-card">
                <div class="settings-card-head">
                    <div>
                        <h3>Product list</h3>
                        <p>Check products to pin on the shop page. Date uses upload / publish date.</p>
                    </div>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-secondary mr-1" id="select-all-products">Select all</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="clear-all-products">Clear</button>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="px-3 pt-3">
                        <div class="alert alert-danger mb-0">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-hover mb-0 settings-table">
                        <thead>
                            <tr>
                                <th style="width:48px"></th>
                                <th>Product</th>
                                <th>Brand</th>
                                <th>Date</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($products as $product)
                                <tr>
                                    <td>
                                        <div class="custom-control custom-checkbox">
                                            <input
                                                type="checkbox"
                                                class="custom-control-input product-select"
                                                id="product_{{ $product['id'] }}"
                                                name="featured_product_ids[]"
                                                value="{{ $product['id'] }}"
                                                @checked(in_array($product['id'], $selected, true))
                                            >
                                            <label class="custom-control-label" for="product_{{ $product['id'] }}"></label>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if ($product['image'])
                                                <img src="{{ $product['image'] }}" alt="" class="rounded mr-2" style="height:44px;width:36px;object-fit:cover">
                                            @endif
                                            <strong>{{ $product['name'] }}</strong>
                                        </div>
                                    </td>
                                    <td>{{ $product['brand'] ?: '—' }}</td>
                                    <td>{{ $product['published_at'] ?: '—' }}</td>
                                    <td>{{ $product['price_formatted'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="settings-empty">
                                        <i class="fas fa-box-open"></i>
                                        <strong>No products found</strong>
                                        <p>Try another date range or search, or add storefront products first.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="settings-card-footer d-flex justify-content-between align-items-center">
                    <span class="text-muted small"><span id="selected-count">0</span> visible selected</span>
                    <button type="submit" class="btn btn-info btn-lg">
                        <i class="fas fa-save mr-1"></i> Save selected products
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('styles')
    @include('admin.settings.partials.page-styles')
@endpush

@push('scripts')
<script>
(function () {
    var boxes = function () { return document.querySelectorAll('.product-select'); };
    var countEl = document.getElementById('selected-count');

    function refreshCount() {
        if (!countEl) return;
        countEl.textContent = document.querySelectorAll('.product-select:checked').length;
    }

    document.getElementById('select-all-products')?.addEventListener('click', function () {
        boxes().forEach(function (el) { el.checked = true; });
        refreshCount();
    });

    document.getElementById('clear-all-products')?.addEventListener('click', function () {
        boxes().forEach(function (el) { el.checked = false; });
        refreshCount();
    });

    boxes().forEach(function (el) {
        el.addEventListener('change', refreshCount);
    });

    refreshCount();
})();
</script>
@endpush
