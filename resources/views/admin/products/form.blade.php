@extends('layouts.admin')

@section('title', $product->exists ? 'Edit Product' : 'Add Product')
@section('page_title', $product->exists ? 'Edit Product' : 'Add Product')

@section('content')
    @php
        $isApiProduct = $isApiProduct ?? ($product->exists && $product->isLiveFromApi() && ! $product->isManualProduct());
        $galleryImages = collect($product->images ?? [])
            ->map(fn ($path) => app(\App\Services\MediaStorageService::class)->storedPath($path))
            ->filter()
            ->unique()
            ->values();
    @endphp

    <form
        action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}"
        method="POST"
        enctype="multipart/form-data"
    >
        @csrf
        @if ($product->exists) @method('PUT') @endif

        @if ($isApiProduct)
            <div class="alert alert-info">
                This product was published from <strong>API Processed</strong>. Name, slug, images, and descriptions are managed by the API workflow. You can adjust pricing, category, stock, and visibility here.
            </div>
        @endif

        <div class="card card-outline card-primary">
            <div class="card-header"><h3 class="card-title mb-0">Basic Information</h3></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Name *</label>
                            <input type="text" name="name" id="product-name" class="form-control" value="{{ old('name', $product->name) }}" {{ $isApiProduct ? 'readonly' : 'required' }}>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Slug</label>
                            <input
                                type="text"
                                name="slug"
                                id="product-slug"
                                class="form-control"
                                value="{{ old('slug', $product->slug) }}"
                                {{ $isApiProduct ? 'readonly' : 'readonly' }}
                                placeholder="Auto-generated from name"
                            >
                            @unless ($isApiProduct)
                                <small class="form-text text-muted">Generated from the product name. Click the field to edit manually.</small>
                            @endunless
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Brand</label>
                            <input type="text" name="brand" class="form-control" value="{{ old('brand', $product->brand) }}" {{ $isApiProduct ? 'readonly' : '' }} placeholder="e.g. Bahari">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Category *</label>
                            <select name="category_id" class="form-control" required>
                                <option value="">— Select category —</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}" @selected(old('category_id', $product->category_id) == $cat->id)>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-outline card-primary">
            <div class="card-header"><h3 class="card-title mb-0">Pricing & Stock</h3></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Purchase Price</label>
                            <input type="number" step="0.01" name="purchase_price" class="form-control" value="{{ old('purchase_price', $product->purchase_price) }}" {{ $isApiProduct ? 'readonly' : '' }}>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Sale Price *</label>
                            <input type="number" step="0.01" name="price" class="form-control" value="{{ old('price', $product->price) }}" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Original / Discount Price</label>
                            <input type="number" step="0.01" name="original_price" class="form-control" value="{{ old('original_price', $product->original_price) }}" placeholder="Shown crossed-out">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Stock Qty *</label>
                            <input type="number" min="0" name="stock" class="form-control" value="{{ old('stock', $product->stock ?? 0) }}" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @unless ($isApiProduct)
            <div class="card card-outline card-primary">
                <div class="card-header"><h3 class="card-title mb-0">Descriptions</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Short Description</label>
                        <textarea name="short_description" class="form-control" rows="2" maxlength="500" placeholder="Brief summary for product cards">{{ old('short_description', $product->short_description) }}</textarea>
                    </div>
                    <div class="form-group mb-0">
                        <label>Full Description</label>
                        <x-admin.rich-text-editor
                            name="description"
                            :value="old('description', $product->description)"
                        />
                    </div>
                </div>
            </div>

            <div class="card card-outline card-primary">
                <div class="card-header"><h3 class="card-title mb-0">Images</h3></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Thumbnail</label>
                                @if ($product->imageUrl())
                                    <div class="mb-2">
                                        <img src="{{ $product->imageUrl() }}" alt="" class="img-thumbnail" style="max-height:120px">
                                    </div>
                                    <div class="custom-control custom-checkbox mb-2">
                                        <input type="checkbox" class="custom-control-input" id="remove_thumbnail" name="remove_thumbnail" value="1">
                                        <label class="custom-control-label" for="remove_thumbnail">Remove current thumbnail</label>
                                    </div>
                                @endif
                                <input type="file" name="thumbnail" class="form-control-file mb-2" accept="image/*">
                                <input type="url" name="thumbnail_url" class="form-control" placeholder="Or paste thumbnail URL" value="{{ old('thumbnail_url') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Gallery Images</label>
                                @if ($galleryImages->isNotEmpty())
                                    <div class="d-flex flex-wrap mb-2">
                                        @foreach ($galleryImages as $path)
                                            @php $url = app(\App\Services\MediaStorageService::class)->url($path); @endphp
                                            @if ($url)
                                                <label class="mr-2 mb-2 text-center">
                                                    <img src="{{ $url }}" alt="" class="img-thumbnail d-block mb-1" style="width:72px;height:88px;object-fit:cover">
                                                    <input type="checkbox" name="remove_gallery[]" value="{{ $path }}"> Remove
                                                </label>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                                <input type="file" name="gallery[]" class="form-control-file mb-2" accept="image/*" multiple>
                                <input type="url" name="gallery_urls[]" class="form-control mb-2" placeholder="Gallery image URL">
                                <input type="url" name="gallery_urls[]" class="form-control" placeholder="Another gallery image URL">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-outline card-primary">
                <div class="card-header"><h3 class="card-title mb-0">Variants</h3></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Sizes (comma separated)</label>
                                <input type="text" name="sizes" class="form-control" value="{{ old('sizes', implode(', ', $product->sizes ?? [])) }}" placeholder="S, M, L, XL">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Colors (comma separated)</label>
                                <input type="text" name="colors" class="form-control" value="{{ old('colors', implode(', ', $product->colors ?? [])) }}" placeholder="Black, White, Rose">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endunless

        <div class="card card-outline card-primary">
            <div class="card-header"><h3 class="card-title mb-0">Visibility</h3></div>
            <div class="card-body">
                <div class="row">
                    @unless ($isApiProduct)
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Badge</label>
                                <input type="text" name="badge" class="form-control" value="{{ old('badge', $product->badge) }}" placeholder="Sale, New, Hot">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Badge Style</label>
                                <select name="badge_variant" class="form-control">
                                    @foreach (['default', 'sale', 'new', 'hot'] as $variant)
                                        <option value="{{ $variant }}" @selected(old('badge_variant', $product->badge_variant) === $variant)>{{ ucfirst($variant) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Rating</label>
                                <input type="number" step="0.1" min="0" max="5" name="rating" class="form-control" value="{{ old('rating', $product->rating ?? 4.5) }}">
                            </div>
                        </div>
                    @endunless
                    <div class="col-md-12">
                        <div class="form-check form-check-inline">
                            <input type="checkbox" name="is_featured" value="1" class="form-check-input" id="featured" @checked(old('is_featured', $product->is_featured))>
                            <label class="form-check-label" for="featured">Featured</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" name="is_new_arrival" value="1" class="form-check-input" id="new" @checked(old('is_new_arrival', $product->is_new_arrival))>
                            <label class="form-check-label" for="new">New Arrival</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="active" @checked(old('is_active', $product->is_active ?? true))>
                            <label class="form-check-label" for="active">Live on Storefront</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    {{ $product->exists ? 'Update Product' : 'Create Product' }}
                </button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-default">Cancel</a>
            </div>
        </div>
    </form>
@endsection

@unless ($isApiProduct ?? false)
    @push('scripts')
        <script>
            (function () {
                var nameInput = document.getElementById('product-name');
                var slugInput = document.getElementById('product-slug');
                if (!nameInput || !slugInput) return;

                var isNew = @json(! $product->exists);

                function slugify(text) {
                    return text.toString().toLowerCase().trim()
                        .replace(/[^\w\s-]/g, '')
                        .replace(/[\s_-]+/g, '-')
                        .replace(/^-+|-+$/g, '');
                }

                function syncSlugFromName() {
                    if (!isNew || slugInput.dataset.manual === '1') {
                        return;
                    }

                    slugInput.value = slugify(nameInput.value);
                }

                nameInput.addEventListener('input', syncSlugFromName);

                slugInput.addEventListener('focus', function () {
                    slugInput.readOnly = false;
                });

                slugInput.addEventListener('input', function () {
                    slugInput.dataset.manual = '1';
                    slugInput.readOnly = false;
                });

                if (isNew) {
                    syncSlugFromName();
                }
            })();
        </script>
    @endpush
@endunless
