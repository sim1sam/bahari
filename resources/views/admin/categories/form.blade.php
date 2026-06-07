@extends('layouts.admin')

@section('title', $category->exists ? 'Edit Category' : 'Add Category')
@section('page_title', $category->exists ? 'Edit Category' : 'Add Category')

@section('content')
    <form action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" method="POST">
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
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Image URL</label>
                            <input type="url" name="image" class="form-control" value="{{ old('image', $category->image) }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Card Image URL</label>
                            <input type="url" name="card_image" class="form-control" value="{{ old('card_image', $category->card_image) }}">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Save Category</button>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-default">Cancel</a>
            </div>
        </div>
    </form>
@endsection
