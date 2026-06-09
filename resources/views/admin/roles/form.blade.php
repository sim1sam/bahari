@extends('layouts.admin')

@section('title', $role->exists ? 'Edit Role' : 'Add Role')
@section('page_title', $role->exists ? 'Edit Role' : 'Add Role')

@section('content')
    @php
        $selectedPermissions = old('permissions', $role->permissions ?? []);
        $canAccessAdmin = old('can_access_admin', $role->can_access_admin);
    @endphp

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
                        <div class="form-check form-check-inline">
                            <input type="checkbox" name="can_access_admin" value="1" class="form-check-input" id="can_access_admin" @checked($canAccessAdmin)>
                            <label class="form-check-label" for="can_access_admin">Can access admin panel</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" @checked(old('is_active', $role->is_active ?? true))>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card" id="feature-permissions-card" style="{{ $canAccessAdmin ? '' : 'display:none' }}">
            <div class="card-header">
                <h3 class="card-title">Admin Features</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-xs btn-outline-primary" id="select-all-features">Select All</button>
                    <button type="button" class="btn btn-xs btn-outline-secondary" id="deselect-all-features">Deselect All</button>
                </div>
            </div>
            <div class="card-body">
                <p class="text-muted small">Choose which admin sections this role can access. Only checked features will appear in the sidebar.</p>
                <div class="row">
                    @foreach ($features as $key => $feature)
                        <div class="col-md-4 col-sm-6">
                            <div class="form-check mb-2">
                                <input
                                    type="checkbox"
                                    name="permissions[]"
                                    value="{{ $key }}"
                                    class="form-check-input feature-checkbox"
                                    id="feature_{{ $key }}"
                                    @checked(in_array($key, $selectedPermissions, true))
                                >
                                <label class="form-check-label" for="feature_{{ $key }}">
                                    <i class="{{ $feature['icon'] }} mr-1"></i> {{ $feature['label'] }}
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
                @error('permissions')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Save Role</button>
                <a href="{{ route('admin.roles.index') }}" class="btn btn-default">Cancel</a>
            </div>
        </div>

        @unless ($canAccessAdmin)
            <div class="card">
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Save Role</button>
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-default">Cancel</a>
                </div>
            </div>
        @endunless
    </form>
@endsection

@push('scripts')
<script>
    (function () {
        const adminToggle = document.getElementById('can_access_admin');
        const featureCard = document.getElementById('feature-permissions-card');
        const checkboxes = document.querySelectorAll('.feature-checkbox');

        function toggleFeatureCard() {
            if (!adminToggle || !featureCard) return;
            featureCard.style.display = adminToggle.checked ? '' : 'none';
        }

        adminToggle?.addEventListener('change', toggleFeatureCard);

        document.getElementById('select-all-features')?.addEventListener('click', function () {
            checkboxes.forEach(cb => cb.checked = true);
        });

        document.getElementById('deselect-all-features')?.addEventListener('click', function () {
            checkboxes.forEach(cb => cb.checked = false);
        });
    })();
</script>
@endpush
