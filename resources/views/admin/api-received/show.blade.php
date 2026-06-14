@extends('layouts.admin')

@section('title', 'Process API Item')
@section('page_title', 'Process Received Product')

@section('content')
    <div class="mb-3">
        <a href="{{ route('admin.api-received.index') }}" class="btn btn-default btn-sm">Back to API Received</a>
        @if ($item->isImported() && $item->product_id)
            <a href="{{ route('admin.products.edit', $item->product_id) }}" class="btn btn-info btn-sm">Edit Product</a>
            <a href="{{ route('products.show', $item->product->slug) }}" class="btn btn-outline-primary btn-sm" target="_blank">View on Site</a>
        @endif
    </div>

    <div class="row">
        <div class="col-md-7">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Images</h3></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 text-center mb-3">
                            <p class="text-muted small mb-2">Original (from API)</p>
                            @if ($item->imageUrl())
                                <img src="{{ $item->imageUrl() }}" alt="Original" class="img-fluid rounded border" style="max-height:280px">
                            @else
                                <p class="text-muted">No image</p>
                            @endif
                        </div>
                        <div class="col-md-6 text-center mb-3">
                            <p class="text-muted small mb-2">Processed (with logo)</p>
                            @if ($item->processedImageUrl())
                                <img src="{{ $item->processedImageUrl() }}" alt="Processed" class="img-fluid rounded border" style="max-height:280px">
                            @else
                                <div class="border rounded p-5 text-muted">Not processed yet</div>
                            @endif
                        </div>
                    </div>

                    @if ($item->canProcess())
                        <form action="{{ route('admin.api-received.process', $item) }}" method="POST" class="text-center">
                            @csrf
                            <button type="submit" class="btn btn-primary" @disabled(! $logoUrl || ! $item->image)>
                                <i class="fas fa-magic"></i> Apply Logo & Process
                            </button>
                            @unless ($logoUrl)
                                <p class="text-danger small mt-2 mb-0">Upload a site logo below first.</p>
                            @endunless
                        </form>
                    @endif
                </div>
            </div>

            @if (! $item->isImported())
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Product Information</h3></div>
                    <form action="{{ route('admin.api-received.update', $item) }}" method="POST">
                        @csrf @method('PUT')
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label>Title *</label>
                                        <input type="text" name="title" class="form-control" value="{{ old('title', $item->title) }}" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>SKU</label>
                                        <input type="text" name="sku" class="form-control" value="{{ old('sku', $item->sku) }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Slug</label>
                                        <input type="text" name="slug" class="form-control" value="{{ old('slug', $item->slug) }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Price (BDT) *</label>
                                        <input type="number" name="price" class="form-control" step="0.01" min="0" value="{{ old('price', $item->price) }}" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Original Price</label>
                                        <input type="number" name="original_price" class="form-control" step="0.01" min="0" value="{{ old('original_price', $item->original_price) }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Category</label>
                                        <input type="text" name="category_name" class="form-control" list="category-list" value="{{ old('category_name', $item->category_name) }}">
                                        <datalist id="category-list">
                                            @foreach ($categories as $cat)
                                                <option value="{{ $cat->name }}">
                                            @endforeach
                                        </datalist>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Badge</label>
                                        <input type="text" name="badge" class="form-control" value="{{ old('badge', $item->badge) }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Rating</label>
                                        <input type="number" name="rating" class="form-control" step="0.1" min="0" max="5" value="{{ old('rating', $item->rating) }}">
                                    </div>
                                </div>
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
                                <div class="col-md-12">
                                    <div class="form-group mb-0">
                                        <label>Description</label>
                                        <textarea name="description" class="form-control" rows="4">{{ old('description', $item->description) }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-secondary">Save Changes</button>
                        </div>
                    </form>
                </div>
            @endif
        </div>

        <div class="col-md-5">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Status</h3></div>
                <div class="card-body">
                    <p><strong>Status:</strong> <span class="badge {{ $item->statusBadgeClass() }}">{{ $item->statusLabel() }}</span></p>
                    <p><strong>Source:</strong> {{ $item->source?->name ?: '—' }}</p>
                    <p><strong>Source ID:</strong> {{ $item->source_id ?: '—' }}</p>
                    <p><strong>Received:</strong> {{ $item->created_at->format('M d, Y H:i') }}</p>
                    @if ($item->reviewed_at)
                        <p><strong>Reviewed:</strong> {{ $item->reviewed_at->format('M d, Y H:i') }}</p>
                    @endif
                </div>
            </div>

            <div class="card card-outline card-secondary">
                <div class="card-header"><h3 class="card-title">Site Logo (watermark)</h3></div>
                <form action="{{ route('admin.api-received.logo') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        @if ($logoUrl)
                            <div class="text-center mb-3">
                                <img src="{{ $logoUrl }}" alt="Logo" class="img-thumbnail" style="max-height:80px">
                            </div>
                        @endif
                        <div class="form-group mb-0">
                            <label>Upload logo (PNG with transparency works best)</label>
                            <input type="file" name="logo" class="form-control-file" accept="image/*" required>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-secondary btn-block">Upload Logo</button>
                    </div>
                </form>
            </div>

            @if ($item->canPublish())
                <div class="card card-success">
                    <div class="card-header"><h3 class="card-title">Publish to Site</h3></div>
                    <form action="{{ route('admin.api-received.publish', $item) }}" method="POST" onsubmit="return confirm('Publish this product on the storefront?')">
                        @csrf
                        <div class="card-body">
                            <p class="text-muted small mb-0">Logo has been applied. Publishing will create the live product with all information.</p>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-success btn-block">Publish Product</button>
                        </div>
                    </form>
                </div>
            @elseif ($item->isImported())
                <div class="alert alert-success">This product is live on the site.</div>
            @else
                <div class="alert alert-info">Workflow: upload logo → <strong>Apply Logo & Process</strong> → <strong>Publish Product</strong></div>
            @endif

            @if (! $item->isImported())
                <div class="card card-danger">
                    <div class="card-header"><h3 class="card-title">Reject</h3></div>
                    <form action="{{ route('admin.api-received.reject', $item) }}" method="POST">
                        @csrf
                        <div class="card-body">
                            <textarea name="admin_notes" class="form-control" rows="2" placeholder="Reason..."></textarea>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-danger btn-block">Reject Item</button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>
@endsection
