@extends('layouts.admin')

@section('title', $banner->exists ? 'Edit Banner' : 'Add Banner')
@section('page_title', $banner->exists ? 'Edit Banner' : 'Add Banner')

@section('content')
    <form action="{{ $banner->exists ? route('admin.homepage.banners.update', $banner) : route('admin.homepage.banners.store') }}" method="POST" enctype="multipart/form-data">
        @csrf @if ($banner->exists) @method('PUT') @endif
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group"><label>Title *</label><input type="text" name="title" class="form-control" value="{{ old('title', $banner->title) }}" required></div>
                        <div class="form-group"><label>Subtitle</label><textarea name="subtitle" class="form-control" rows="2">{{ old('subtitle', $banner->subtitle) }}</textarea></div>
                        <div class="row">
                            <div class="col-md-4"><div class="form-group"><label>Badge</label><input type="text" name="badge" class="form-control" value="{{ old('badge', $banner->badge) }}" placeholder="Limited Time"></div></div>
                            <div class="col-md-4"><div class="form-group"><label>Button Text</label><input type="text" name="button_text" class="form-control" value="{{ old('button_text', $banner->button_text) }}"></div></div>
                            <div class="col-md-4"><div class="form-group"><label>Button Link</label><input type="text" name="button_href" class="form-control" value="{{ old('button_href', $banner->button_href) }}"></div></div>
                        </div>
                        <div class="form-group"><label>Sort Order</label><input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $banner->sort_order ?? 0) }}" style="max-width:120px"></div>
                        <div class="form-check"><input type="checkbox" name="is_active" value="1" class="form-check-input" id="active" @checked(old('is_active', $banner->is_active ?? true))><label class="form-check-label" for="active">Active</label></div>
                    </div>
                    <div class="col-md-4">@include('admin.homepage.partials.image-field', ['model' => $banner])</div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('admin.homepage.banners.index') }}" class="btn btn-default">Cancel</a>
            </div>
        </div>
    </form>
@endsection
