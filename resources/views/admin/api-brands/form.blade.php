@extends('layouts.admin')

@section('title', $brand->exists ? 'Edit Brand' : 'Add Brand')
@section('page_title', $brand->exists ? 'Edit API Brand' : 'Add API Brand')

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

    <form action="{{ $brand->exists ? route('admin.api-brands.update', $brand) : route('admin.api-brands.store') }}" method="POST">
        @csrf
        @if ($brand->exists) @method('PUT') @endif

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Brand name *</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $brand->name) }}" required maxlength="100">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Slug</label>
                            <input type="text" name="slug" class="form-control" value="{{ old('slug', $brand->slug) }}" maxlength="100" placeholder="Auto from name if empty">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Notes</label>
                            <textarea name="notes" class="form-control" rows="3" maxlength="1000">{{ old('notes', $brand->notes) }}</textarea>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" @checked(old('is_active', $brand->exists ? $brand->is_active : true))>
                            <label class="custom-control-label" for="is_active">Active (show in brand filters)</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">{{ $brand->exists ? 'Update' : 'Create' }} Brand</button>
                <a href="{{ route('admin.api-brands.index') }}" class="btn btn-default">Cancel</a>
            </div>
        </div>
    </form>
@endsection
