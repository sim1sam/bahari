@extends('layouts.admin')

@section('title', $accountType->exists ? 'Edit Account Type' : 'Create Account Type')
@section('page_title', $accountType->exists ? 'Edit Account Type' : 'Create Account Type')

@section('content')
    @php
        $isActive = (bool) old('is_active', $accountType->is_active ?? true);
    @endphp

    <div class="settings-page">
        <a href="{{ route('admin.account-types.index') }}" class="settings-back-link">
            <i class="fas fa-arrow-left"></i> Back to Account Types
        </a>

        <section class="settings-hero">
            <div>
                <span class="settings-eyebrow">Account</span>
                <h2>{{ $accountType->exists ? 'Edit Account Type' : 'Create Account Type' }}</h2>
                <p>{{ $accountType->exists ? 'Update type details used by account heads.' : 'Add a new category for your chart of accounts.' }}</p>
                @if ($accountType->exists)
                    <div class="settings-hero-meta">
                        <span class="settings-hero-chip"><i class="fas fa-code"></i> {{ $accountType->slug }}</span>
                    </div>
                @endif
            </div>
        </section>

        @include('admin.account.partials.nav')

        <form action="{{ $accountType->exists ? route('admin.account-types.update', $accountType) : route('admin.account-types.store') }}" method="POST">
            @csrf
            @if ($accountType->exists) @method('PUT') @endif

            <div class="row">
                <div class="col-lg-8 mb-3">
                    <div class="settings-card">
                        <div class="settings-card-body">
                            <section class="settings-form-panel mb-0">
                                <div class="settings-form-panel-head">
                                    <span class="settings-form-panel-icon settings-form-panel-icon--brand"><i class="fas fa-tags"></i></span>
                                    <div>
                                        <h4>Account Type Details</h4>
                                        <p>Name, slug, and description for this category.</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="settings-field">
                                            <label for="name">Name *</label>
                                            <div class="settings-input-wrap">
                                                <span class="settings-input-icon"><i class="fas fa-tag"></i></span>
                                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $accountType->name) }}" required>
                                            </div>
                                            @error('name')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="settings-field">
                                            <label for="slug">Slug</label>
                                            <div class="settings-input-wrap">
                                                <span class="settings-input-icon"><i class="fas fa-code"></i></span>
                                                <input type="text" name="slug" id="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $accountType->slug) }}" placeholder="Auto from name">
                                            </div>
                                            @error('slug')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="settings-field">
                                            <label for="sort_order">Sort Order</label>
                                            <div class="settings-input-wrap">
                                                <span class="settings-input-icon"><i class="fas fa-sort-numeric-down"></i></span>
                                                <input type="number" name="sort_order" id="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', $accountType->sort_order ?? 0) }}" min="0">
                                            </div>
                                            @error('sort_order')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="settings-field mb-0">
                                            <label for="description">Description</label>
                                            <textarea name="description" id="description" class="form-control settings-textarea" rows="3" placeholder="Optional notes about this type">{{ old('description', $accountType->description) }}</textarea>
                                            @error('description')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                        <div class="settings-card-footer d-flex align-items-center flex-wrap gap-2">
                            <button type="submit" class="btn btn-info btn-lg">
                                <i class="fas fa-save mr-1"></i> {{ $accountType->exists ? 'Update Account Type' : 'Create Account Type' }}
                            </button>
                            <a href="{{ route('admin.account-types.index') }}" class="btn btn-secondary btn-lg">Cancel</a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 mb-3">
                    <div class="settings-side-card">
                        <div class="settings-side-head">
                            <span class="settings-side-icon settings-side-icon--check"><i class="fas fa-power-off"></i></span>
                            <div>
                                <h4>Status</h4>
                                <p>Inactive types are hidden from new account heads.</p>
                            </div>
                        </div>
                        <div class="settings-side-body">
                            <div class="settings-toggle-card {{ $isActive ? 'settings-toggle-card--on' : '' }}">
                                <div class="settings-toggle-copy">
                                    <h5>Active</h5>
                                    <p>Only active types appear in the account head dropdown.</p>
                                </div>
                                <label class="settings-toggle" for="is_active">
                                    <input type="checkbox" class="settings-toggle-input" id="is_active" name="is_active" value="1" @checked($isActive)>
                                    <span class="settings-toggle-track"><span class="settings-toggle-thumb"></span></span>
                                    <span class="settings-toggle-label">{{ $isActive ? 'Active' : 'Inactive' }}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('styles')
    @include('admin.settings.partials.page-styles')
@endpush

@push('scripts')
<script>
    (function () {
        var toggle = document.getElementById('is_active');
        if (!toggle) return;

        toggle.addEventListener('change', function () {
            var card = toggle.closest('.settings-toggle-card');
            var label = card?.querySelector('.settings-toggle-label');
            if (card) card.classList.toggle('settings-toggle-card--on', toggle.checked);
            if (label) label.textContent = toggle.checked ? 'Active' : 'Inactive';
        });
    })();
</script>
@endpush
