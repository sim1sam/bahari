@extends('layouts.admin')

@section('title', $accountHead->exists ? 'Edit Account Head' : 'Create Account Head')
@section('page_title', $accountHead->exists ? 'Edit Account Head' : 'Create Account Head')

@section('content')
    @php
        $isActive = (bool) old('is_active', $accountHead->is_active ?? true);
    @endphp

    <div class="settings-page">
        <a href="{{ route('admin.account-heads.index') }}" class="settings-back-link">
            <i class="fas fa-arrow-left"></i> Back to Account Heads
        </a>

        <section class="settings-hero">
            <div>
                <span class="settings-eyebrow">Account</span>
                <h2>{{ $accountHead->exists ? 'Edit Account Head' : 'Create Account Head' }}</h2>
                <p>{{ $accountHead->exists ? 'Update ledger account details and type.' : 'Add a new head to your chart of accounts.' }}</p>
                @if ($accountHead->exists)
                    <div class="settings-hero-meta">
                        @if ($accountHead->code)
                            <span class="settings-hero-chip"><i class="fas fa-barcode"></i> {{ $accountHead->code }}</span>
                        @endif
                        <span class="settings-hero-chip"><i class="fas fa-tag"></i> {{ $accountHead->typeLabel() }}</span>
                    </div>
                @endif
            </div>
        </section>

        @include('admin.account.partials.nav')

        <form action="{{ $accountHead->exists ? route('admin.account-heads.update', $accountHead) : route('admin.account-heads.store') }}" method="POST">
            @csrf
            @if ($accountHead->exists) @method('PUT') @endif

            <div class="row">
                <div class="col-lg-8 mb-3">
                    <div class="settings-card">
                        <div class="settings-card-body">
                            <section class="settings-form-panel mb-0">
                                <div class="settings-form-panel-head">
                                    <span class="settings-form-panel-icon settings-form-panel-icon--brand"><i class="fas fa-calculator"></i></span>
                                    <div>
                                        <h4>Account Head Details</h4>
                                        <p>Name, code, type, and description for this ledger account.</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="settings-field">
                                            <label for="code">Code</label>
                                            <div class="settings-input-wrap">
                                                <span class="settings-input-icon"><i class="fas fa-barcode"></i></span>
                                                <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $accountHead->code) }}" placeholder="SALES">
                                            </div>
                                            @error('code')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="settings-field">
                                            <label for="name">Name *</label>
                                            <div class="settings-input-wrap">
                                                <span class="settings-input-icon"><i class="fas fa-tag"></i></span>
                                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $accountHead->name) }}" required>
                                            </div>
                                            @error('name')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="settings-field">
                                            <label for="account_head_type_id">Type *</label>
                                            <select name="account_head_type_id" id="account_head_type_id" class="form-control settings-textarea @error('account_head_type_id') is-invalid @enderror" required>
                                                <option value="" disabled @selected(! old('account_head_type_id', $accountHead->account_head_type_id))>Select type</option>
                                                @foreach ($accountTypes as $type)
                                                    <option value="{{ $type->id }}" @selected((string) old('account_head_type_id', $accountHead->account_head_type_id) === (string) $type->id)>
                                                        {{ $type->name }}@unless ($type->is_active) (inactive)@endunless
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('account_head_type_id')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                            @if ($accountTypes->isEmpty())
                                                <p class="text-muted small mt-2 mb-0">
                                                    No account types yet.
                                                    <a href="{{ route('admin.account-types.create') }}">Create one first</a>.
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="settings-field">
                                            <label for="sort_order">Sort Order</label>
                                            <div class="settings-input-wrap">
                                                <span class="settings-input-icon"><i class="fas fa-sort-numeric-down"></i></span>
                                                <input type="number" name="sort_order" id="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', $accountHead->sort_order ?? 0) }}" min="0">
                                            </div>
                                            @error('sort_order')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="settings-field mb-0">
                                            <label for="description">Description</label>
                                            <textarea name="description" id="description" class="form-control settings-textarea" rows="3" placeholder="Optional notes about this account head">{{ old('description', $accountHead->description) }}</textarea>
                                            @error('description')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                        <div class="settings-card-footer d-flex align-items-center flex-wrap gap-2">
                            <button type="submit" class="btn btn-info btn-lg">
                                <i class="fas fa-save mr-1"></i> {{ $accountHead->exists ? 'Update Account Head' : 'Create Account Head' }}
                            </button>
                            <a href="{{ route('admin.account-heads.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 mb-3">
                    <div class="settings-side-card">
                        <div class="settings-side-head">
                            <span class="settings-side-icon settings-side-icon--check"><i class="fas fa-power-off"></i></span>
                            <div>
                                <h4>Status</h4>
                                <p>Inactive heads are hidden from selection.</p>
                            </div>
                        </div>
                        <div class="settings-side-body">
                            <div class="settings-toggle-card {{ $isActive ? 'settings-toggle-card--on' : '' }}">
                                <div class="settings-toggle-copy">
                                    <h5>Active</h5>
                                    <p>Only active account heads are available for transactions.</p>
                                </div>
                                <label class="settings-toggle" for="is_active">
                                    <input type="checkbox" class="settings-toggle-input" id="is_active" name="is_active" value="1" @checked($isActive)>
                                    <span class="settings-toggle-track"><span class="settings-toggle-thumb"></span></span>
                                    <span class="settings-toggle-label">{{ $isActive ? 'Active' : 'Inactive' }}</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="settings-side-card">
                        <div class="settings-side-head">
                            <span class="settings-side-icon settings-side-icon--info"><i class="fas fa-info-circle"></i></span>
                            <div>
                                <h4>Account Types</h4>
                                <p>Types are managed separately and loaded dynamically.</p>
                            </div>
                        </div>
                        <div class="settings-side-body">
                            @if ($accountTypes->isNotEmpty())
                                <ul class="settings-side-list">
                                    @foreach ($accountTypes as $type)
                                        <li>
                                            <strong>{{ $type->name }}</strong>
                                            @if ($type->description)
                                                — {{ $type->description }}
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted small mb-2">Create account types before adding heads.</p>
                            @endif
                            <a href="{{ route('admin.account-types.index') }}" class="btn btn-sm btn-outline-info">
                                <i class="fas fa-tags mr-1"></i> Manage Types
                            </a>
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
