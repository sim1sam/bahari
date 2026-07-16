@extends('layouts.admin')

@section('title', 'Shop Brands')
@section('page_title', 'Shop Brands')

@section('content')
    @php
        $formRows = old('brands');
        if (! is_array($formRows)) {
            $formRows = $rows->map(fn ($row) => [
                'id' => $row['id'],
                'brand' => $row['brand'],
                'product_count' => $row['product_count'],
                'selected' => $row['selected'],
                'starts_at' => $row['starts_at'],
                'ends_at' => $row['ends_at'],
                'sort_order' => $row['sort_order'],
                'is_active' => $row['is_active'],
                'is_live_now' => $row['is_live_now'],
            ])->all();
        } else {
            // Merge product counts / live flags from server rows.
            $lookup = $rows->keyBy('brand');
            $formRows = collect($formRows)->map(function ($row) use ($lookup) {
                $brand = $row['brand'] ?? '';
                $meta = $lookup->get($brand, []);

                return array_merge([
                    'product_count' => $meta['product_count'] ?? 0,
                    'is_live_now' => $meta['is_live_now'] ?? false,
                ], $row, [
                    'selected' => filter_var($row['selected'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'is_active' => filter_var($row['is_active'] ?? false, FILTER_VALIDATE_BOOLEAN),
                ]);
            })->all();
        }
    @endphp

    <div class="settings-page">
        <a href="{{ route('admin.shop-page.edit') }}" class="settings-back-link">
            <i class="fas fa-arrow-left"></i> Back to Shop Page
        </a>

        <section class="settings-hero">
            <div>
                <span class="settings-eyebrow">Marketing</span>
                <h2>Shop brands</h2>
                <p>Select which live brands show on /shop. Only brands with storefront products are listed.</p>
                <div class="settings-hero-meta">
                    <span class="settings-hero-chip"><i class="fas fa-copyright"></i> {{ $liveBrandCount }} live brands</span>
                    <span class="settings-hero-chip"><i class="fas fa-eye"></i> {{ $activeBrandCount }} active on shop</span>
                    <a href="{{ route('shop.index') }}" target="_blank" class="settings-hero-chip"><i class="fas fa-external-link-alt"></i> View shop</a>
                </div>
            </div>
            <div class="settings-hero-actions">
                <a href="{{ route('admin.shop-page.products.edit') }}" class="btn btn-outline-light">
                    <i class="fas fa-tshirt mr-1"></i> Product list
                </a>
            </div>
        </section>

        <form action="{{ route('admin.shop-page.brands.update') }}" method="POST" id="shop-brands-form">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-lg-9 mb-3">
                    <div class="settings-card">
                        <div class="settings-card-head">
                            <div>
                                <h3>Select live brands</h3>
                                <p>Tick a brand to show its products on the shop page. Optional date range limits when it is live.</p>
                            </div>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-secondary mr-1" id="select-all-brands">Select all</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="clear-all-brands">Clear</button>
                            </div>
                        </div>

                        <div class="settings-card-body border-bottom">
                            <div class="custom-control custom-switch mb-0">
                                <input type="hidden" name="show_all_when_empty" value="0">
                                <input type="checkbox" class="custom-control-input" id="show_all_when_empty" name="show_all_when_empty" value="1" @checked(old('show_all_when_empty', $settings->show_all_when_empty))>
                                <label class="custom-control-label" for="show_all_when_empty">If no brand is selected/active, show all storefront products</label>
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
                                        <th>Brand</th>
                                        <th>Live products</th>
                                        <th>Starts</th>
                                        <th>Ends</th>
                                        <th>Active</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($formRows as $index => $row)
                                        @php
                                            $selected = ! empty($row['selected']);
                                            $brand = $row['brand'] ?? '';
                                        @endphp
                                        <tr class="brand-row {{ $selected ? 'table-info' : '' }}">
                                            <td>
                                                <input type="hidden" name="brands[{{ $index }}][id]" value="{{ $row['id'] ?? '' }}">
                                                <input type="hidden" name="brands[{{ $index }}][brand]" value="{{ $brand }}">
                                                <input type="hidden" name="brands[{{ $index }}][sort_order]" value="{{ $row['sort_order'] ?? $index }}">
                                                <input type="hidden" name="brands[{{ $index }}][selected]" value="0">
                                                <div class="custom-control custom-checkbox">
                                                    <input
                                                        type="checkbox"
                                                        class="custom-control-input brand-select"
                                                        id="brand_select_{{ $index }}"
                                                        name="brands[{{ $index }}][selected]"
                                                        value="1"
                                                        @checked($selected)
                                                    >
                                                    <label class="custom-control-label" for="brand_select_{{ $index }}"></label>
                                                </div>
                                            </td>
                                            <td>
                                                <strong>{{ $brand }}</strong>
                                                @if (($row['product_count'] ?? 0) < 1)
                                                    <div class="small text-warning">No live products right now</div>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-light border">{{ $row['product_count'] ?? 0 }} products</span>
                                            </td>
                                            <td>
                                                <input type="date" name="brands[{{ $index }}][starts_at]" class="form-control form-control-sm" value="{{ $row['starts_at'] ?? '' }}" {{ $selected ? '' : 'disabled' }}>
                                            </td>
                                            <td>
                                                <input type="date" name="brands[{{ $index }}][ends_at]" class="form-control form-control-sm" value="{{ $row['ends_at'] ?? '' }}" {{ $selected ? '' : 'disabled' }}>
                                            </td>
                                            <td>
                                                <input type="hidden" name="brands[{{ $index }}][is_active]" value="0">
                                                <div class="custom-control custom-switch">
                                                    <input
                                                        type="checkbox"
                                                        class="custom-control-input brand-active"
                                                        id="brand_active_{{ $index }}"
                                                        name="brands[{{ $index }}][is_active]"
                                                        value="1"
                                                        @checked(!empty($row['is_active']))
                                                        {{ $selected ? '' : 'disabled' }}
                                                    >
                                                    <label class="custom-control-label" for="brand_active_{{ $index }}"></label>
                                                </div>
                                            </td>
                                            <td>
                                                @if ($selected && ! empty($row['is_live_now']))
                                                    <span class="settings-status settings-status--live">Live now</span>
                                                @elseif ($selected)
                                                    <span class="settings-status settings-status--hidden">Scheduled</span>
                                                @else
                                                    <span class="text-muted small">Off</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="settings-empty">
                                                <i class="fas fa-copyright"></i>
                                                <strong>No live brands found</strong>
                                                <p>Add products with a brand name on the storefront first.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="settings-card-footer d-flex justify-content-between align-items-center">
                            <span class="text-muted small"><span id="selected-brand-count">0</span> brand(s) selected</span>
                            <button type="submit" class="btn btn-info btn-lg">
                                <i class="fas fa-save mr-1"></i> Save selected brands
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 mb-3">
                    <div class="settings-side-card">
                        <div class="settings-side-head">
                            <span class="settings-side-icon settings-side-icon--info"><i class="fas fa-info-circle"></i></span>
                            <div>
                                <h4>How it works</h4>
                                <p>Select brands — no typing.</p>
                            </div>
                        </div>
                        <div class="settings-side-body">
                            <ul class="settings-side-list">
                                <li>List shows only brands from <strong>live storefront products</strong>.</li>
                                <li>Selected brands control which brand products appear on /shop.</li>
                                <li>Optional start/end dates limit when a brand is live.</li>
                                <li>Leave dates empty for always-on.</li>
                                <li>Pin specific items in <strong>Shop Products</strong>.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('styles')
    @include('admin.settings.partials.page-styles')
    <style>
        .brand-row.table-info { background: rgba(8, 145, 178, 0.08) !important; }
        .brand-row input[type="date"]:disabled { opacity: 0.5; }
    </style>
@endpush

@push('scripts')
<script>
(function () {
    function refreshRow(row) {
        var selected = row.querySelector('.brand-select');
        var checked = !!(selected && selected.checked);
        row.classList.toggle('table-info', checked);
        row.querySelectorAll('input[type="date"], .brand-active').forEach(function (el) {
            el.disabled = !checked;
        });
    }

    function refreshCount() {
        var countEl = document.getElementById('selected-brand-count');
        if (!countEl) return;
        countEl.textContent = document.querySelectorAll('.brand-select:checked').length;
    }

    document.querySelectorAll('.brand-row').forEach(function (row) {
        var selected = row.querySelector('.brand-select');
        if (!selected) return;
        selected.addEventListener('change', function () {
            refreshRow(row);
            refreshCount();
        });
        refreshRow(row);
    });

    document.getElementById('select-all-brands')?.addEventListener('click', function () {
        document.querySelectorAll('.brand-row').forEach(function (row) {
            var selected = row.querySelector('.brand-select');
            if (selected) selected.checked = true;
            refreshRow(row);
        });
        refreshCount();
    });

    document.getElementById('clear-all-brands')?.addEventListener('click', function () {
        document.querySelectorAll('.brand-row').forEach(function (row) {
            var selected = row.querySelector('.brand-select');
            if (selected) selected.checked = false;
            refreshRow(row);
        });
        refreshCount();
    });

    refreshCount();
})();
</script>
@endpush
