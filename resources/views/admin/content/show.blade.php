@extends('layouts.admin')

@section('title', 'Content — '.$item->title)
@section('page_title', 'Review Received Content')

@section('content')
    <div class="mb-3">
        <a href="{{ route('admin.content.index') }}" class="btn btn-default btn-sm">Back to Content</a>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Received Image</h3></div>
                <div class="card-body text-center">
                    @if ($item->imageUrl())
                        <img src="{{ $item->imageUrl() }}" alt="" class="img-fluid rounded border" style="max-height:420px">
                    @else
                        <p class="text-muted">No image</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Product Information (from API)</h3></div>
                <form action="{{ route('admin.content.update', $item) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="card-body">
                        <div class="form-group">
                            <label>Title *</label>
                            <input type="text" name="title" class="form-control" value="{{ old('title', $item->title) }}" required>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label>SKU</label>
                                    <input type="text" name="sku" class="form-control" value="{{ old('sku', $item->sku) }}">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Price (BDT) *</label>
                                    <input type="number" name="price" class="form-control" step="0.01" value="{{ old('price', $item->price) }}" required>
                                    @if ((float) $item->price <= 0)
                                        <small class="text-warning d-block mt-1">
                                            Price is 0 — sender may use another field name. Click <strong>Re-download Images & Sync Prices</strong> on Content, or enter price manually.
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Category</label>
                            <input type="text" name="category_name" class="form-control" list="cats" value="{{ old('category_name', $item->category_name) }}">
                            <datalist id="cats">
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->name }}">
                                @endforeach
                            </datalist>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Brand</label>
                                    <input type="text" name="brand" class="form-control" value="{{ old('brand', $item->brand) }}">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Vendor</label>
                                    <input type="text" name="vendor" class="form-control" value="{{ old('vendor', $item->vendor) }}">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description', $item->description) }}</textarea>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group mb-0">
                                    <label>Sizes</label>
                                    <input type="text" name="sizes" class="form-control" value="{{ old('sizes', implode(', ', $item->sizes ?? [])) }}">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group mb-0">
                                    <label>Colors</label>
                                    <input type="text" name="colors" class="form-control" value="{{ old('colors', implode(', ', $item->colors ?? [])) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-secondary">Save Info</button>
                    </div>
                </form>
            </div>

            <div class="card card-primary">
                <div class="card-header"><h3 class="card-title">Process with Logo</h3></div>
                <div class="card-body">
                    @if ($logoUrl)
                        <p class="small text-muted mb-2">Logo:</p>
                        <img src="{{ $logoUrl }}" alt="Logo" class="img-thumbnail mb-3" style="max-height:48px">
                    @else
                        <p class="text-warning">Upload logo on Content page first.</p>
                    @endif
                    <form action="{{ route('admin.content.process', $item) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-block" @disabled(! $logoUrl || ! $item->image)>
                            <i class="fas fa-magic"></i> Apply Logo & Process
                        </button>
                    </form>
                    <p class="text-muted small mt-2 mb-0">Moves to <strong>Processed</strong> menu when done.</p>
                </div>
            </div>

            <form action="{{ route('admin.content.reject', $item) }}" method="POST" onsubmit="return confirm('Reject this item?')">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm">Reject</button>
            </form>
        </div>
    </div>
@endsection
