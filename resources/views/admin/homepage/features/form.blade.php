@extends('layouts.admin')

@section('title', $feature->exists ? 'Edit Feature' : 'Add Feature')
@section('page_title', $feature->exists ? 'Edit Feature' : 'Add Feature')

@section('content')
    <form action="{{ $feature->exists ? route('admin.homepage.features.update', $feature) : route('admin.homepage.features.store') }}" method="POST">
        @csrf @if ($feature->exists) @method('PUT') @endif
        <div class="card">
            <div class="card-body">
                <div class="form-group"><label>Title *</label><input type="text" name="title" class="form-control" value="{{ old('title', $feature->title) }}" required></div>
                <div class="form-group"><label>Description *</label><textarea name="description" class="form-control" rows="2" required>{{ old('description', $feature->description) }}</textarea></div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Icon *</label>
                            <select name="icon" class="form-control" required>
                                @foreach (App\Models\HomeFeature::ICONS as $key => $path)
                                    <option value="{{ $key }}" @selected(old('icon', $feature->icon ?? 'truck') === $key)>{{ ucfirst($key) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6"><div class="form-group"><label>Sort Order</label><input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $feature->sort_order ?? 0) }}"></div></div>
                </div>
                <div class="form-check"><input type="checkbox" name="is_active" value="1" class="form-check-input" id="active" @checked(old('is_active', $feature->is_active ?? true))><label class="form-check-label" for="active">Active</label></div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('admin.homepage.features.index') }}" class="btn btn-default">Cancel</a>
            </div>
        </div>
    </form>
@endsection
