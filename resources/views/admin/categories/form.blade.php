@extends('layouts.admin')

@section('title', $category->exists ? 'Edit Category' : 'Add Category')
@section('page_title', $category->exists ? 'Edit Category' : 'Add Category')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if ($category->exists) @method('PUT') @endif

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Name *</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $category->name) }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Slug *</label>
                            <input type="text" name="slug" class="form-control" value="{{ old('slug', $category->slug) }}" required>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="2">{{ old('description', $category->description) }}</textarea>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Color</label>
                            <select name="color" class="form-control">
                                @foreach (['brand','rose','purple','amber','blue','cyan'] as $color)
                                    <option value="{{ $color }}" @selected(old('color', $category->color) === $color)>{{ ucfirst($color) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $category->sort_order ?? 0) }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group pt-4">
                            <div class="form-check">
                                <input type="checkbox" name="is_sale" value="1" class="form-check-input" id="sale" @checked(old('is_sale', $category->is_sale))>
                                <label class="form-check-label" for="sale">Sale Category</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" name="is_active" value="1" class="form-check-input" id="active" @checked(old('is_active', $category->is_active ?? true))>
                                <label class="form-check-label" for="active">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card card-outline card-primary">
                    <div class="card-header"><h3 class="card-title">Hero Image</h3></div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">Used on the category page header banner.</p>
                        @if ($category->isExternalImage($category->image))
                            <div class="alert alert-warning py-2 small">Current image is an old external URL. Remove it and upload a file, or paste a <strong>direct image URL</strong> to save pathwise.</div>
                        @endif
                        @if ($category->imagePath())
                            <div class="mb-3">
                                <img src="{{ $category->imageUrl() }}" alt="Hero preview" class="img-thumbnail d-block" style="max-height:140px;width:100%;object-fit:cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                <div class="alert alert-warning py-2 small" style="display:none">Preview could not load. Re-upload or use a direct image URL.</div>
                                <p class="text-muted small mt-2 mb-0"><strong>Saved path:</strong> <code>{{ $category->imagePath() }}</code></p>
                            </div>
                            <div class="custom-control custom-checkbox mb-3">
                                <input type="checkbox" class="custom-control-input" id="remove_image" name="remove_image" value="1" @checked(old('remove_image'))>
                                <label class="custom-control-label" for="remove_image">Remove current hero image</label>
                            </div>
                        @endif
                        <div class="form-group">
                            <label>Upload image</label>
                            <input type="file" name="image" class="form-control-file @error('image') is-invalid @enderror" accept="image/png,image/jpeg,image/jpg,image/webp">
                            <small class="text-muted">Saved to <code>storage/app/public/categories/</code> — PNG, JPG, WEBP, max 3MB</small>
                            @error('image')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group mb-0">
                            <label>Or direct image URL</label>
                            <input type="url" name="image_url" class="form-control @error('image_url') is-invalid @enderror" placeholder="https://example.com/image.jpg" value="{{ old('image_url') }}">
                            <small class="text-muted">Must be a direct image link (ends in .jpg, .png, etc). Website URLs will not work.</small>
                            @error('image_url')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card card-outline card-info">
                    <div class="card-header"><h3 class="card-title">Card Image</h3></div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">Used on homepage and categories listing cards.</p>
                        @if ($category->isExternalImage($category->card_image))
                            <div class="alert alert-warning py-2 small">Current image is an old external URL. Remove it and upload a file, or paste a <strong>direct image URL</strong> to save pathwise.</div>
                        @endif
                        @if ($category->cardImagePath())
                            <div class="mb-3">
                                <img src="{{ $category->cardImageUrl() }}" alt="Card preview" class="img-thumbnail d-block" style="max-height:140px;width:100%;object-fit:cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                <div class="alert alert-warning py-2 small" style="display:none">Preview could not load. Re-upload or use a direct image URL.</div>
                                <p class="text-muted small mt-2 mb-0"><strong>Saved path:</strong> <code>{{ $category->cardImagePath() }}</code></p>
                            </div>
                            <div class="custom-control custom-checkbox mb-3">
                                <input type="checkbox" class="custom-control-input" id="remove_card_image" name="remove_card_image" value="1" @checked(old('remove_card_image'))>
                                <label class="custom-control-label" for="remove_card_image">Remove current card image</label>
                            </div>
                        @endif
                        <div class="form-group">
                            <label>Upload image</label>
                            <input type="file" name="card_image" class="form-control-file @error('card_image') is-invalid @enderror" accept="image/png,image/jpeg,image/jpg,image/webp">
                            <small class="text-muted">Saved to <code>storage/app/public/categories/</code> — PNG, JPG, WEBP, max 3MB</small>
                            @error('card_image')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group mb-0">
                            <label>Or direct image URL</label>
                            <input type="url" name="card_image_url" class="form-control @error('card_image_url') is-invalid @enderror" placeholder="https://example.com/image.jpg" value="{{ old('card_image_url') }}">
                            <small class="text-muted">Must be a direct image link (ends in .jpg, .png, etc). Website URLs will not work.</small>
                            @error('card_image_url')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Category</button>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-default">Cancel</a>
            </div>
        </div>
    </form>
@endsection
