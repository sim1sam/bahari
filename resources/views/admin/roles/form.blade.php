@extends('layouts.admin')

@section('title', $role->exists ? 'Edit Role' : 'Add Role')
@section('page_title', $role->exists ? 'Edit Role' : 'Add Role')

@section('content')
    @php
        $selectedPermissions = old('permissions', $role->permissions ?? []);
        $canAccessAdmin = (bool) old('can_access_admin', $role->can_access_admin);
        $isActive = (bool) old('is_active', $role->is_active ?? true);
    @endphp

    <div class="settings-page">
        <a href="{{ route('admin.roles.index') }}" class="settings-back-link">
            <i class="fas fa-arrow-left"></i> Back to Roles
        </a>

        <section class="settings-hero">
            <div>
                <span class="settings-eyebrow">Users</span>
                <h2>{{ $role->exists ? 'Edit Role' : 'Add Role' }}</h2>
                <p>{{ $role->exists ? 'Update role settings and admin feature permissions.' : 'Create a new role with admin access and sidebar permissions.' }}</p>
                @if ($role->exists)
                    <div class="settings-hero-meta">
                        <span class="settings-hero-chip"><i class="fas fa-tag"></i> {{ $role->slug }}</span>
                        @if ($role->isSystem())
                            <span class="settings-hero-chip"><i class="fas fa-lock"></i> System role</span>
                        @endif
                    </div>
                @endif
            </div>
            <div class="settings-hero-actions">
                <a href="{{ route('admin.users.index') }}" class="btn btn-info">
                    <i class="fas fa-users mr-1"></i> Users
                </a>
            </div>
        </section>

        @include('admin.users.partials.nav')

        <form action="{{ $role->exists ? route('admin.roles.update', $role) : route('admin.roles.store') }}" method="POST">
            @csrf
            @if ($role->exists) @method('PUT') @endif

            <div class="row">
                <div class="col-lg-8 mb-3">
                    <div class="settings-card mb-3">
                        <div class="settings-card-body">
                            <section class="settings-form-panel mb-0">
                                <div class="settings-form-panel-head">
                                    <span class="settings-form-panel-icon settings-form-panel-icon--brand"><i class="fas fa-user-shield"></i></span>
                                    <div>
                                        <h4>Role Details</h4>
                                        <p>Name, slug, and description for this access level.</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="settings-field">
                                            <label for="name">Name *</label>
                                            <div class="settings-input-wrap">
                                                <span class="settings-input-icon"><i class="fas fa-tag"></i></span>
                                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $role->name) }}" required>
                                            </div>
                                            @error('name')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="settings-field">
                                            <label for="slug">Slug *</label>
                                            <div class="settings-input-wrap">
                                                <span class="settings-input-icon"><i class="fas fa-code"></i></span>
                                                <input type="text" name="slug" id="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $role->slug) }}" @disabled($role->isSystem()) required>
                                            </div>
                                            @if ($role->isSystem())
                                                <input type="hidden" name="slug" value="{{ $role->slug }}">
                                                <small class="settings-field-hint">System role slugs cannot be changed.</small>
                                            @endif
                                            @error('slug')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="settings-field mb-0">
                                            <label for="description">Description</label>
                                            <textarea name="description" id="description" class="form-control settings-textarea" rows="2">{{ old('description', $role->description) }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>

                    <div class="settings-card" id="feature-permissions-card" @unless($canAccessAdmin) style="display:none" @endunless>
                        <div class="settings-card-head">
                            <div>
                                <h3>Admin Features</h3>
                                <p>Choose which admin sections appear in the sidebar for this role.</p>
                            </div>
                            <div class="role-bulk-actions">
                                <button type="button" class="btn btn-primary btn-sm" id="select-all-features">
                                    <i class="fas fa-check-double mr-1"></i> Select All
                                </button>
                                <button type="button" class="btn btn-secondary btn-sm" id="deselect-all-features">
                                    <i class="fas fa-times mr-1"></i> Deselect All
                                </button>
                            </div>
                        </div>
                        <div class="settings-card-body">
                            <div class="role-feature-grid">
                                @foreach ($features as $key => $feature)
                                    <label class="role-feature-chip feature-checkbox-label {{ in_array($key, $selectedPermissions, true) ? 'role-feature-chip--checked' : '' }}" for="feature_{{ $key }}">
                                        <input
                                            type="checkbox"
                                            name="permissions[]"
                                            value="{{ $key }}"
                                            class="feature-checkbox"
                                            id="feature_{{ $key }}"
                                            @checked(in_array($key, $selectedPermissions, true))
                                        >
                                        <i class="{{ $feature['icon'] }}"></i>
                                        <span>{{ $feature['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('permissions')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="settings-card mt-3">
                        <div class="settings-card-footer d-flex align-items-center flex-wrap gap-2">
                            <button type="submit" class="btn btn-info btn-lg">
                                <i class="fas fa-save mr-1"></i> Save Role
                            </button>
                            <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 mb-3">
                    <div class="settings-side-card">
                        <div class="settings-side-head">
                            <span class="settings-side-icon settings-side-icon--check"><i class="fas fa-door-open"></i></span>
                            <div>
                                <h4>Access Options</h4>
                                <p>Control admin panel login and role status.</p>
                            </div>
                        </div>
                        <div class="settings-side-body">
                            <div class="settings-toggle-card {{ $canAccessAdmin ? 'settings-toggle-card--on' : '' }}" data-toggle-card="admin">
                                <div class="settings-toggle-copy">
                                    <h5>Admin Panel Access</h5>
                                    <p>Allow users with this role to log into the admin panel.</p>
                                </div>
                                <label class="settings-toggle" for="can_access_admin">
                                    <input type="checkbox" class="settings-toggle-input" id="can_access_admin" name="can_access_admin" value="1" @checked($canAccessAdmin)>
                                    <span class="settings-toggle-track"><span class="settings-toggle-thumb"></span></span>
                                    <span class="settings-toggle-label" data-label-for="admin">{{ $canAccessAdmin ? 'Enabled' : 'Disabled' }}</span>
                                </label>
                            </div>
                            <div class="settings-toggle-card {{ $isActive ? 'settings-toggle-card--on' : '' }}" data-toggle-card="active">
                                <div class="settings-toggle-copy">
                                    <h5>Active Role</h5>
                                    <p>Inactive roles block login for assigned users.</p>
                                </div>
                                <label class="settings-toggle" for="is_active">
                                    <input type="checkbox" class="settings-toggle-input" id="is_active" name="is_active" value="1" @checked($isActive)>
                                    <span class="settings-toggle-track"><span class="settings-toggle-thumb"></span></span>
                                    <span class="settings-toggle-label" data-label-for="active">{{ $isActive ? 'Active' : 'Inactive' }}</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="settings-side-card">
                        <div class="settings-side-head">
                            <span class="settings-side-icon settings-side-icon--info"><i class="fas fa-info-circle"></i></span>
                            <div>
                                <h4>Permissions</h4>
                                <p>How feature access works.</p>
                            </div>
                        </div>
                        <div class="settings-side-body">
                            <p class="settings-side-text">Only checked features appear in the sidebar for users assigned to this role. Unchecked sections are hidden and blocked at the route level.</p>
                            <p class="settings-side-text mb-0">System roles (e.g. Super Admin) have protected slugs that cannot be changed.</p>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('styles')
    @include('admin.users.partials.page-styles')
@endpush

@push('scripts')
<script>
    (function () {
        const adminToggle = document.getElementById('can_access_admin');
        const activeToggle = document.getElementById('is_active');
        const featureCard = document.getElementById('feature-permissions-card');
        const checkboxes = document.querySelectorAll('.feature-checkbox');

        function syncToggle(input) {
            const card = input.closest('.settings-toggle-card');
            const label = card?.querySelector('.settings-toggle-label');
            if (card) card.classList.toggle('settings-toggle-card--on', input.checked);
            if (label) {
                if (input.id === 'can_access_admin') {
                    label.textContent = input.checked ? 'Enabled' : 'Disabled';
                } else {
                    label.textContent = input.checked ? 'Active' : 'Inactive';
                }
            }
        }

        function toggleFeatureCard() {
            if (!adminToggle || !featureCard) return;
            featureCard.style.display = adminToggle.checked ? '' : 'none';
        }

        function syncFeatureChips() {
            document.querySelectorAll('.feature-checkbox-label').forEach(function (label) {
                const input = label.querySelector('.feature-checkbox');
                label.classList.toggle('role-feature-chip--checked', input && input.checked);
            });
        }

        adminToggle?.addEventListener('change', function () {
            syncToggle(adminToggle);
            toggleFeatureCard();
        });

        activeToggle?.addEventListener('change', function () {
            syncToggle(activeToggle);
        });

        checkboxes.forEach(function (cb) {
            cb.addEventListener('change', syncFeatureChips);
        });

        document.getElementById('select-all-features')?.addEventListener('click', function () {
            checkboxes.forEach(function (cb) { cb.checked = true; });
            syncFeatureChips();
        });

        document.getElementById('deselect-all-features')?.addEventListener('click', function () {
            checkboxes.forEach(function (cb) { cb.checked = false; });
            syncFeatureChips();
        });
    })();
</script>
@endpush
