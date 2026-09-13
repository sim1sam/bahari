@extends('layouts.admin')

@section('title', $page->exists ? 'Edit Page' : 'Add Page')
@section('page_title', $page->exists ? 'Edit Page' : 'Add Page')

@section('content')
    <form action="{{ $page->exists ? route('admin.pages.update', $page) : route('admin.pages.store') }}" method="POST">
        @csrf
        @if ($page->exists) @method('PUT') @endif

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="title">Title *</label>
                            <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $page->title) }}" required placeholder="Contact Us">
                            @error('title')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="slug">Slug</label>
                            <input type="text" name="slug" id="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $page->slug) }}" placeholder="contact-us">
                            <small class="text-muted">URL: /pages/your-slug — leave blank to auto-generate</small>
                            @error('slug')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="sort_order">Sort</label>
                            <input type="number" name="sort_order" id="sort_order" class="form-control" value="{{ old('sort_order', $page->sort_order ?? 0) }}" min="0">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="meta_description">Meta description</label>
                            <input type="text" name="meta_description" id="meta_description" class="form-control" value="{{ old('meta_description', $page->meta_description) }}" maxlength="300" placeholder="Short SEO description">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Content</label>
                            <x-admin.rich-text-editor
                                name="content"
                                :value="old('content', $page->content)"
                                placeholder="Write page content…"
                                :rows="12"
                            />
                            @error('content')<span class="text-danger small">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>
                <div class="form-check">
                    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" @checked(old('is_active', $page->is_active ?? true))>
                    <label class="form-check-label" for="is_active">Active (visible on storefront)</label>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Save Page</button>
                <a href="{{ route('admin.pages.index') }}" class="btn btn-default">Cancel</a>
                @if ($page->exists && $page->is_active)
                    <a href="{{ $page->url() }}" target="_blank" rel="noopener" class="btn btn-outline-info">View Page</a>
                @endif
            </div>
        </div>
    </form>
@endsection
