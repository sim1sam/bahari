@extends('layouts.admin')

@section('title', 'Bulk Add Products')
@section('page_title', 'Bulk Add Products')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css" rel="stylesheet" />
<style>
    .select2-container--bootstrap4 .select2-selection--single {
        height: calc(2.25rem + 2px) !important;
        padding: 0.375rem 0.75rem;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
    }
    .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
        line-height: 1.5;
        padding-left: 0;
        color: #495057;
    }
    .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
        height: calc(2.25rem + 2px);
    }
    .select2-container--bootstrap4 .select2-dropdown {
        border-color: #ced4da;
    }
    .select2-container--bootstrap4 .select2-results__option--highlighted[aria-selected] {
        background-color: #007bff;
    }
    .select2-container { width: 100% !important; }
    .image-preview-container {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
        margin-top: 20px;
    }
    @media (min-width: 576px) {
        .image-preview-container { grid-template-columns: repeat(3, 1fr); }
    }
    @media (min-width: 768px) {
        .image-preview-container { grid-template-columns: repeat(6, 1fr); }
    }
    .image-preview-wrapper {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 15px;
        background: #fff;
        display: flex;
        flex-direction: column;
    }
    .image-preview-item {
        position: relative;
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 10px;
        background: #f8f9fa;
        width: 100%;
        margin: 0 auto;
    }
    .image-preview-item img {
        width: 100%;
        height: auto;
        border-radius: 4px;
        display: block;
    }
    .image-preview-item .remove-image {
        position: absolute;
        top: 5px;
        right: 5px;
        background: #dc3545;
        color: white;
        border: none;
        border-radius: 50%;
        width: 25px;
        height: 25px;
        cursor: pointer;
        line-height: 1;
    }
    .item-config-per-image {
        background: #f8f9fa;
        padding: 12px;
        border-radius: 8px;
        margin-top: 12px;
    }
    .item-config-per-image .form-label {
        font-size: 0.875rem;
        margin-bottom: 0.25rem;
        font-weight: 600;
    }
    input[type="number"]::-webkit-inner-spin-button,
    input[type="number"]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    input[type="number"] {
        -moz-appearance: textfield;
    }
    .processed-item-card {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        overflow: hidden;
        background: #fff;
    }
    .processed-image-wrapper {
        position: relative;
    }
    .processed-image-wrapper img {
        width: 100%;
        display: block;
    }
    .processed-price-badge {
        position: absolute;
        top: 8px;
        right: 8px;
        background: rgba(220, 53, 69, 0.95);
        color: #fff;
        padding: 4px 10px;
        border-radius: 4px;
        font-weight: 700;
        font-size: 0.85rem;
    }
</style>
@endpush

@section('content')
    <div class="ecom-page">
        <section class="ecom-hero mb-3">
            <div>
                <span class="ecom-eyebrow">Ecommerce</span>
                <h2>Bulk Add Products</h2>
                <p>Upload multiple images — shipping category + INR price auto-calculates retail / wholesale / reselling (BDT). Logo + price card applied like CRM content processing.</p>
            </div>
            <div class="ecom-hero-actions">
                <a href="{{ route('admin.products.index') }}" class="btn btn-light">
                    <i class="fas fa-list mr-1"></i> All Products
                </a>
                <a href="{{ route('admin.shipping-charges.index') }}" class="btn btn-light">
                    <i class="fas fa-shipping-fast mr-1"></i> Shipping Categories
                </a>
            </div>
        </section>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card card-outline card-primary">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.products.bulk.store') }}" enctype="multipart/form-data" id="bulk-product-form">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="form-group">
                                @php
                                    $currentBrand = old('brand', old('brand_custom'));
                                    $selectedBrand = old('brand_select', $currentBrand ?: '');
                                    if ($selectedBrand && ! $brands->contains($selectedBrand) && $selectedBrand !== '__custom__') {
                                        $selectedBrand = '__custom__';
                                    }
                                @endphp
                                <label>Brand</label>
                                <select name="brand_select" id="brand-select" class="form-control select2-brand">
                                    <option value="">— Select brand —</option>
                                    @foreach ($brands as $brandName)
                                        <option value="{{ $brandName }}" @selected($selectedBrand === $brandName)>{{ $brandName }}</option>
                                    @endforeach
                                    <option value="__custom__" @selected($selectedBrand === '__custom__')>— Add new brand —</option>
                                </select>
                                <input
                                    type="text"
                                    name="brand_custom"
                                    id="brand-custom"
                                    class="form-control mt-2"
                                    maxlength="120"
                                    value="{{ old('brand_custom', $selectedBrand === '__custom__' ? $currentBrand : '') }}"
                                    placeholder="Type new brand name"
                                    style="{{ $selectedBrand === '__custom__' ? '' : 'display:none' }}"
                                >
                                <small class="text-muted">All API + product brands. Searchable list.</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Store Category <span class="text-danger">*</span></label>
                                <select name="ecommerce_category_id" id="ecommerce_category_id" class="form-control select2-category" required>
                                    <option value="">Select Category</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}" @selected((string) old('ecommerce_category_id') === (string) $cat->id)>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">From <a href="{{ route('admin.categories.index') }}" target="_blank">Categories</a></small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Stock (each product)</label>
                                <input type="number" name="stock" class="form-control" min="0" value="{{ old('stock', 100) }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label>Size Details</label>
                        <textarea name="sizes" id="sizes-field" class="form-control" rows="2" placeholder="XS, S, M, L, XL — or one per line">{{ old('sizes') }}</textarea>
                        <small class="text-muted">Comma or line separated. Applied to every product in this batch.</small>
                    </div>

                    <div class="form-group mb-3">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" name="same_price" id="same-price" value="1" {{ old('same_price') ? 'checked' : '' }}>
                            <label class="custom-control-label" for="same-price">
                                <strong>Same Price</strong> — one shipping category + INR for all images
                            </label>
                        </div>
                    </div>

                    <div id="single-price-config" class="mb-4" style="display: none;">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">Common configuration for all images</h6>
                                <p class="text-muted small mb-3">Conversion rate: <strong>{{ number_format($conversionRate, 2) }}</strong>
                                    · <a href="{{ route('admin.shipping-charges.index') }}">Edit rate / categories</a>
                                </p>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Title</label>
                                            <input type="text" name="common_title" id="common-title" class="form-control" maxlength="255" placeholder="Optional" value="{{ old('common_title') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Shipping Category <span class="text-danger">*</span></label>
                                            <select name="common_category_id" id="common-category" class="form-control">
                                                <option value="">Select Category</option>
                                                @foreach ($shippingCharges as $charge)
                                                    <option value="{{ $charge->id }}" @selected((string) old('common_category_id') === (string) $charge->id)>{{ $charge->item_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>INR Price <span class="text-danger">*</span></label>
                                            <input type="number" name="common_inr_price" id="common-inr-price" class="form-control" step="1" min="1" placeholder="0" value="{{ old('common_inr_price') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label>Calculated prices (BDT)</label>
                                        <div class="d-flex flex-wrap" style="gap: 6px;">
                                            <span class="badge badge-danger" id="common-retail-price">Retail ৳0</span>
                                            <span class="badge badge-primary" id="common-wholesale-price">Wholesale ৳0</span>
                                            <span class="badge badge-info" id="common-reselling-price">Reselling ৳0</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Upload Images <span class="text-danger">*</span></label>
                        <input type="file" name="images[]" class="form-control-file" id="image-upload" multiple accept="image/*" required>
                        <small class="text-muted d-block">Select multiple images at once. Each image becomes one product with logo + price card.</small>
                    </div>

                    <div id="image-previews" class="image-preview-container"></div>

                    <div class="mt-4">
                        <div class="custom-control custom-checkbox mb-3">
                            <input type="checkbox" class="custom-control-input" name="go_live" id="go-live" value="1" {{ old('go_live', '1') ? 'checked' : '' }}>
                            <label class="custom-control-label" for="go-live">
                                <strong>Go Live</strong> — publish products to storefront immediately
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check-circle mr-1"></i> Process &amp; Add Products
                        </button>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>

                @if (session('bulk_products'))
                    <div class="mt-5">
                        <h5 class="mb-3">Added Products</h5>
                        <div class="row">
                            @foreach (session('bulk_products') as $item)
                                <div class="col-md-3 col-sm-6 mb-3">
                                    <div class="processed-item-card">
                                        <div class="processed-image-wrapper">
                                            @if (!empty($item['image']))
                                                <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}">
                                            @endif
                                            @if (!empty($item['price']))
                                                <div class="processed-price-badge">৳{{ number_format($item['price'], 0) }}</div>
                                            @endif
                                        </div>
                                        <div class="p-2 small">
                                            <a href="{{ route('admin.products.edit', $item['id']) }}">{{ $item['name'] }}</a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.jQuery) {
        const select2Opts = {
            theme: 'bootstrap4',
            width: '100%',
            allowClear: true,
            placeholder: 'Select…',
        };

        $('#brand-select').select2($.extend({}, select2Opts, { placeholder: 'Select brand' }));
        $('#ecommerce_category_id').select2($.extend({}, select2Opts, { placeholder: 'Select category' }));
        $('#common-category').select2($.extend({}, select2Opts, { placeholder: 'Select shipping category' }));

        $('#brand-select, #ecommerce_category_id, #common-category').on('select2:open', function () {
            setTimeout(function () {
                const $search = $('.select2-container--open .select2-search__field');
                if ($search.length) $search[0].focus();
            }, 40);
        });

        function toggleBrandCustom() {
            const isCustom = $('#brand-select').val() === '__custom__';
            $('#brand-custom').toggle(isCustom);
            if (isCustom) $('#brand-custom').focus();
        }

        $('#brand-select').on('change', toggleBrandCustom);
        toggleBrandCustom();
    }

    const imageUpload = document.getElementById('image-upload');
    const imagePreviews = document.getElementById('image-previews');
    const samePriceCheckbox = document.getElementById('same-price');
    const singlePriceConfig = document.getElementById('single-price-config');
    const commonCategory = document.getElementById('common-category');
    const commonInrPrice = document.getElementById('common-inr-price');
    const bulkForm = document.getElementById('bulk-product-form');

    const shippingOptionsHtml = `@foreach ($shippingCharges as $charge)<option value="{{ $charge->id }}">{{ $charge->item_name }}</option>@endforeach`;

    function toggleSamePriceMode() {
        const samePrice = samePriceCheckbox.checked;
        singlePriceConfig.style.display = samePrice ? 'block' : 'none';

        if (commonCategory) {
            samePrice ? commonCategory.setAttribute('required', 'required') : commonCategory.removeAttribute('required');
        }
        if (commonInrPrice) {
            samePrice ? commonInrPrice.setAttribute('required', 'required') : commonInrPrice.removeAttribute('required');
        }

        imagePreviews.querySelectorAll('.item-config-per-image').forEach(function (block) {
            block.style.display = samePrice ? 'none' : 'block';
            block.querySelectorAll('input, select').forEach(function (el) {
                if (samePrice) {
                    el.disabled = true;
                    el.removeAttribute('required');
                } else {
                    el.disabled = false;
                    if (el.classList.contains('item-category') || el.classList.contains('item-inr-price')) {
                        el.setAttribute('required', 'required');
                    }
                }
            });
        });

        if (!samePrice) {
            imagePreviews.querySelectorAll('.image-preview-wrapper').forEach(function (wrapper) {
                if (wrapper.querySelector('.item-config-per-image')) return;
                const index = parseInt(wrapper.dataset.index, 10);
                const itemConfig = createItemConfigForImage(index);
                wrapper.appendChild(itemConfig);
                setupItemPriceCalculation(itemConfig);
            });
        }
    }

    samePriceCheckbox.addEventListener('change', toggleSamePriceMode);
    toggleSamePriceMode();

    if (commonCategory && commonInrPrice) {
        commonCategory.addEventListener('change', calculateCommonRetailPrice);
        commonInrPrice.addEventListener('input', calculateCommonRetailPrice);
    }

    bulkForm.addEventListener('submit', function (e) {
        toggleSamePriceMode();

        const wrappers = imagePreviews.querySelectorAll('.image-preview-wrapper');
        if (wrappers.length === 0) {
            e.preventDefault();
            alert('Please upload at least one image.');
            return;
        }

        if (samePriceCheckbox.checked) {
            if (!commonCategory.value || !commonInrPrice.value || parseInt(commonInrPrice.value, 10) <= 0) {
                e.preventDefault();
                alert('Please select a shipping category and enter an INR price for all images.');
                singlePriceConfig.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }
        }

        document.querySelectorAll('input[name="process_indices[]"]').forEach(el => el.remove());
        wrappers.forEach(function (wrapper) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'process_indices[]';
            input.value = parseInt(wrapper.dataset.index, 10);
            bulkForm.appendChild(input);
        });
    });

    imageUpload.addEventListener('change', function (e) {
        const files = Array.from(e.target.files);
        imagePreviews.innerHTML = '';

        files.forEach(function (file, index) {
            if (!file.type.startsWith('image/')) return;

            const reader = new FileReader();
            reader.onload = function (ev) {
                const previewContainer = document.createElement('div');
                previewContainer.className = 'image-preview-wrapper';
                previewContainer.dataset.index = index;

                const previewItem = document.createElement('div');
                previewItem.className = 'image-preview-item';
                previewItem.innerHTML =
                    '<button type="button" class="remove-image" onclick="removeBulkImage(' + index + ')">×</button>' +
                    '<img src="' + ev.target.result + '" alt="Preview">' +
                    '<small class="d-block mt-2 text-center">Image ' + (index + 1) + '</small>';
                previewContainer.appendChild(previewItem);

                if (!samePriceCheckbox.checked) {
                    const itemConfig = createItemConfigForImage(index);
                    previewContainer.appendChild(itemConfig);
                    setupItemPriceCalculation(itemConfig);
                }

                imagePreviews.appendChild(previewContainer);
            };
            reader.readAsDataURL(file);
        });
    });

    window.removeBulkImage = function (index) {
        const previewWrapper = document.querySelector('.image-preview-wrapper[data-index="' + index + '"]');
        if (previewWrapper) previewWrapper.remove();
    };

    function createItemConfigForImage(index) {
        const itemConfig = document.createElement('div');
        itemConfig.className = 'item-config-per-image';
        itemConfig.dataset.index = index;
        itemConfig.innerHTML =
            '<label class="form-label">Title</label>' +
            '<input type="text" name="items[' + index + '][title]" class="form-control form-control-sm mb-2" maxlength="255" placeholder="Optional">' +
            '<label class="form-label">Shipping Category <span class="text-danger">*</span></label>' +
            '<select name="items[' + index + '][category_id]" class="form-control form-control-sm item-category mb-2" required>' +
            '<option value="">Select Category</option>' + shippingOptionsHtml +
            '</select>' +
            '<label class="form-label">INR Price <span class="text-danger">*</span></label>' +
            '<input type="number" name="items[' + index + '][inr_price]" class="form-control form-control-sm item-inr-price mb-2" step="1" min="1" required placeholder="0">' +
            '<label class="form-label mb-1">Calculated prices (BDT)</label>' +
            '<div class="d-flex flex-wrap" style="gap:4px;">' +
            '<span class="badge badge-danger item-retail-price">Retail ৳0</span>' +
            '<span class="badge badge-primary item-wholesale-price">Wholesale ৳0</span>' +
            '<span class="badge badge-info item-reselling-price">Reselling ৳0</span>' +
            '</div>';
        return itemConfig;
    }

    async function calculateContentPrices(categoryId, priceInr) {
        if (!categoryId || !priceInr || priceInr <= 0) {
            return { retail: 0, wholesale: 0, reselling: 0 };
        }
        try {
            const params = new URLSearchParams({ category_id: categoryId, inr_price: priceInr });
            const response = await fetch('{{ route('admin.products.bulk.calculate-prices') }}?' + params.toString(), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!response.ok) return { retail: 0, wholesale: 0, reselling: 0 };
            return await response.json();
        } catch (error) {
            console.error('Error calculating prices:', error);
            return { retail: 0, wholesale: 0, reselling: 0 };
        }
    }

    function formatBdtBadge(amount, prefix) {
        return prefix + ' ৳' + Number(amount || 0).toLocaleString('en-US', { maximumFractionDigits: 0 });
    }

    function updatePriceBadges(retailEl, wholesaleEl, resellingEl, prices) {
        if (retailEl) retailEl.textContent = formatBdtBadge(prices.retail, 'Retail');
        if (wholesaleEl) wholesaleEl.textContent = formatBdtBadge(prices.wholesale, 'Wholesale');
        if (resellingEl) resellingEl.textContent = formatBdtBadge(prices.reselling, 'Reselling');
    }

    async function calculateCommonRetailPrice() {
        const prices = await calculateContentPrices(commonCategory.value, commonInrPrice.value);
        updatePriceBadges(
            document.getElementById('common-retail-price'),
            document.getElementById('common-wholesale-price'),
            document.getElementById('common-reselling-price'),
            prices
        );
    }

    function setupItemPriceCalculation(itemConfig) {
        const categorySelect = itemConfig.querySelector('.item-category');
        const priceInput = itemConfig.querySelector('.item-inr-price');
        const updatePrice = async function () {
            const prices = await calculateContentPrices(categorySelect.value, priceInput.value);
            updatePriceBadges(
                itemConfig.querySelector('.item-retail-price'),
                itemConfig.querySelector('.item-wholesale-price'),
                itemConfig.querySelector('.item-reselling-price'),
                prices
            );
        };
        categorySelect.addEventListener('change', updatePrice);
        priceInput.addEventListener('input', updatePrice);
    }
});
</script>
@endpush
