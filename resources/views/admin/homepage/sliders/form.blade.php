@extends('layouts.admin')

@section('title', $slider->exists ? 'Edit Slide' : 'Add Slide')
@section('page_title', $slider->exists ? 'Edit Slide' : 'Add Slide')

@section('content')
    <form action="{{ $slider->exists ? route('admin.homepage.sliders.update', $slider) : route('admin.homepage.sliders.store') }}" method="POST" enctype="multipart/form-data">
        @csrf @if ($slider->exists) @method('PUT') @endif
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group"><label>Title *</label><input type="text" name="title" class="form-control" value="{{ old('title', $slider->title) }}" required></div>
                        <div class="form-group"><label>Subtitle</label><textarea name="subtitle" class="form-control" rows="2">{{ old('subtitle', $slider->subtitle) }}</textarea></div>
                        <div class="row">
                            <div class="col-md-6"><div class="form-group"><label>Badge</label><input type="text" name="badge" class="form-control" value="{{ old('badge', $slider->badge) }}"></div></div>
                            <div class="col-md-6"><div class="form-group"><label>Sort Order</label><input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $slider->sort_order ?? 0) }}"></div></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6"><div class="form-group"><label>Primary Button</label><input type="text" name="primary_btn" class="form-control" value="{{ old('primary_btn', $slider->primary_btn) }}"></div></div>
                            <div class="col-md-6"><div class="form-group"><label>Primary Link</label><input type="text" name="primary_href" class="form-control" value="{{ old('primary_href', $slider->primary_href) }}" placeholder="/shop or https://..."></div></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6"><div class="form-group"><label>Secondary Button</label><input type="text" name="secondary_btn" class="form-control" value="{{ old('secondary_btn', $slider->secondary_btn) }}"></div></div>
                            <div class="col-md-6"><div class="form-group"><label>Secondary Link</label><input type="text" name="secondary_href" class="form-control" value="{{ old('secondary_href', $slider->secondary_href) }}"></div></div>
                        </div>
                        <div class="form-group"><label>Features (comma separated)</label><input type="text" name="features" class="form-control" value="{{ old('features', implode(', ', $slider->features ?? [])) }}" placeholder="Free Shipping, Easy Returns"></div>
                        <div class="form-check"><input type="checkbox" name="is_active" value="1" class="form-check-input" id="active" @checked(old('is_active', $slider->is_active ?? true))><label class="form-check-label" for="active">Active</label></div>
                    </div>
                    <div class="col-md-4">@include('admin.homepage.partials.image-field', ['model' => $slider])</div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('admin.homepage.sliders.index') }}" class="btn btn-default">Cancel</a>
            </div>
        </div>
    </form>
@endsection
