@extends('layouts.admin')

@section('title', $role->exists ? 'Edit Role' : 'Add Role')
@section('page_title', $role->exists ? 'Edit Role' : 'Add Role')

@section('content')
    <form action="{{ $role->exists ? route('admin.roles.update', $role) : route('admin.roles.store') }}" method="POST">
        @csrf
        @if ($role->exists) @method('PUT') @endif

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Name *</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $role->name) }}" required>
                            @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Slug *</label>
                            <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $role->slug) }}" @disabled($role->isSystem()) required>
                            @if ($role->isSystem())
                                <input type="hidden" name="slug" value="{{ $role->slug }}">
                                <small class="text-muted">System role slugs cannot be changed.</small>
                            @endif
                            @error('slug')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="2">{{ old('description', $role->description) }}</textarea>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-check">
                            <input type="checkbox" name="can_access_admin" value="1" class="form-check-input" id="can_access_admin" @checked(old('can_access_admin', $role->can_access_admin))>
                            <label class="form-check-label" for="can_access_admin">Can access admin panel</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Save Role</button>
                <a href="{{ route('admin.roles.index') }}" class="btn btn-default">Cancel</a>
            </div>
        </div>
    </form>
@endsection
