<div class="form-group">
    <label>Title *</label>
    <input type="text" name="title" class="form-control" value="{{ old('title', $item->title) }}" required>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>SKU</label>
            <input type="text" name="sku" class="form-control" value="{{ old('sku', $item->sku) }}">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Slug</label>
            <input type="text" name="slug" class="form-control" value="{{ old('slug', $item->slug) }}">
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Price (BDT) *</label>
            <input type="number" name="price" class="form-control" step="0.01" value="{{ old('price', $item->price) }}" required>
            @if ((float) $item->price <= 0)
                <small class="text-warning d-block mt-1">
                    Price is 0 in database. The price on the image comes from the transfer site.
                    @php($payloadPrice = app(\App\Services\ApiReceivedPriceService::class)->extract($item->payloadData())['price'] ?? 0)
                    @if ($payloadPrice > 0)
                        API payload has <strong>{{ money($payloadPrice) }}</strong> — save or click Go Live to apply.
                    @else
                        Enter price manually or re-send from transfer site with <code>price_bdt</code>.
                    @endif
                </small>
            @endif
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Original Price</label>
            <input type="number" name="original_price" class="form-control" step="0.01" value="{{ old('original_price', $item->original_price) }}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Purchase Price (BDT)</label>
            <input type="number" name="purchase_price" class="form-control" step="0.01" value="{{ old('purchase_price', $item->purchase_price) }}">
            <small class="text-muted">From API field <code>purchase_price_bdt</code></small>
        </div>
    </div>
</div>
<div class="form-group">
    <label>Category</label>
    <input type="text" name="category_name" class="form-control" list="category-list" value="{{ old('category_name', $item->category_name) }}">
    <datalist id="category-list">
        @foreach ($categories as $cat)
            <option value="{{ $cat->name }}">
        @endforeach
    </datalist>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Brand</label>
            <input type="text" name="brand" class="form-control" value="{{ old('brand', $item->brand) }}">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Vendor</label>
            <input type="text" name="vendor" class="form-control" value="{{ old('vendor', $item->vendor) }}">
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Sizes (comma separated)</label>
            <input type="text" name="sizes" class="form-control" value="{{ old('sizes', implode(', ', $item->sizes ?? [])) }}">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Colors (comma separated)</label>
            <input type="text" name="colors" class="form-control" value="{{ old('colors', implode(', ', $item->colors ?? [])) }}">
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Badge</label>
            <input type="text" name="badge" class="form-control" value="{{ old('badge', $item->badge) }}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Rating</label>
            <input type="number" name="rating" class="form-control" step="0.1" min="0" max="5" value="{{ old('rating', $item->rating) }}">
        </div>
    </div>
</div>
<div class="form-group mb-0">
    <label>Description</label>
    <textarea name="description" class="form-control" rows="4">{{ old('description', $item->description) }}</textarea>
</div>
