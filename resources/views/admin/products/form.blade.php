@extends('layouts.admin')

@section('title', $product->exists ? 'Edit Product' : 'Add Product')
@section('page_title', $product->exists ? 'Edit Product' : 'Add Product')

@section('content')
    <form action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}" method="POST">
        @csrf
        @if ($product->exists) @method('PUT') @endif

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Name *</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Slug *</label>
                            <input type="text" name="slug" class="form-control" value="{{ old('slug', $product->slug) }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Category</label>
                            <select name="category_id" class="form-control">
                                <option value="">— Select —</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}" @selected(old('category_id', $product->category_id) == $cat->id)>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Price *</label>
                            <input type="number" step="0.01" name="price" class="form-control" value="{{ old('price', $product->price) }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Original Price</label>
                            <input type="number" step="0.01" name="original_price" class="form-control" value="{{ old('original_price', $product->original_price) }}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Image URL</label>
                            <input type="url" name="image" class="form-control" value="{{ old('image', $product->image) }}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description', $product->description) }}</textarea>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Badge</label>
                            <input type="text" name="badge" class="form-control" value="{{ old('badge', $product->badge) }}" placeholder="Sale, New, Hot">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Badge Variant</label>
                            <input type="text" name="badge_variant" class="form-control" value="{{ old('badge_variant', $product->badge_variant) }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Rating</label>
                            <input type="number" step="0.1" name="rating" class="form-control" value="{{ old('rating', $product->rating ?? 4.5) }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Sizes (comma separated)</label>
                            <input type="text" name="sizes" class="form-control" value="{{ old('sizes', implode(',', $product->sizes ?? ['XS','S','M','L','XL'])) }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Colors (comma separated)</label>
                            <input type="text" name="colors" class="form-control" value="{{ old('colors', implode(',', $product->colors ?? ['Black','White','Rose'])) }}">
                        </div>
                    </div>
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
                            <label class="form-check-label" for="active">Active</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Save Product</button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-default">Cancel</a>
            </div>
        </div>
    </form>
@endsection
