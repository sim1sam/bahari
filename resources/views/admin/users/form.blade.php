@extends('layouts.admin')

@section('title', $user->exists ? 'Edit User' : 'Add User')
@section('page_title', $user->exists ? 'Edit User' : 'Add User')

@section('content')
    <div class="settings-page">
        <a href="{{ route('admin.users.index') }}" class="settings-back-link">
            <i class="fas fa-arrow-left"></i> Back to Users
        </a>

        <section class="settings-hero">
            <div>
                <span class="settings-eyebrow">Users</span>
                <h2>{{ $user->exists ? 'Edit User' : 'Add User' }}</h2>
                <p>{{ $user->exists ? 'Update account details, role assignment, and password.' : 'Create a new staff account with admin panel access.' }}</p>
                @if ($user->exists)
                    <div class="settings-hero-meta">
                        <span class="settings-hero-chip"><i class="fas fa-envelope"></i> {{ $user->email }}</span>
                        @if ($user->role)
                            <span class="settings-hero-chip"><i class="fas fa-user-shield"></i> {{ $user->role->name }}</span>
                        @endif
                    </div>
                @endif
            </div>
            <div class="settings-hero-actions">
                <a href="{{ route('admin.roles.index') }}" class="btn btn-info">
                    <i class="fas fa-user-shield mr-1"></i> Manage Roles
                </a>
            </div>
        </section>

        @include('admin.users.partials.nav')

        <form action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}" method="POST">
            @csrf
            @if ($user->exists) @method('PUT') @endif

            <div class="row">
                <div class="col-lg-8 mb-3">
                    <div class="settings-card">
                        <div class="settings-card-body">
                            <section class="settings-form-panel mb-0">
                                <div class="settings-form-panel-head">
                                    <span class="settings-form-panel-icon settings-form-panel-icon--brand"><i class="fas fa-user"></i></span>
                                    <div>
                                        <h4>Account Details</h4>
                                        <p>Basic profile information and login credentials.</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="settings-field">
                                            <label for="name">Name *</label>
                                            <div class="settings-input-wrap">
                                                <span class="settings-input-icon"><i class="fas fa-user"></i></span>
                                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                                            </div>
                                            @error('name')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="settings-field">
                                            <label for="email">Email *</label>
                                            <div class="settings-input-wrap">
                                                <span class="settings-input-icon"><i class="fas fa-envelope"></i></span>
                                                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                                            </div>
                                            @error('email')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="settings-field">
                                            <label for="role_id">Role *</label>
                                            <div class="settings-input-wrap">
                                                <span class="settings-input-icon"><i class="fas fa-user-shield"></i></span>
                                                <select name="role_id" id="role_id" class="form-control @error('role_id') is-invalid @enderror" required>
                                                    <option value="">— Select role —</option>
                                                    @foreach ($roles as $role)
                                                        <option value="{{ $role->id }}" @selected(old('role_id', $user->role_id) == $role->id)>
                                                            {{ $role->name }}@unless($role->is_active) (Inactive)@endunless
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @error('role_id')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="settings-field">
                                            <label for="password">Password {{ $user->exists ? '' : '*' }}</label>
                                            <div class="settings-input-wrap">
                                                <span class="settings-input-icon"><i class="fas fa-lock"></i></span>
                                                <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" {{ $user->exists ? '' : 'required' }}>
                                            </div>
                                            @if ($user->exists)
                                                <small class="settings-field-hint">Leave blank to keep current password.</small>
                                            @endif
                                            @error('password')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="settings-field mb-0">
                                            <label for="password_confirmation">Confirm Password {{ $user->exists ? '' : '*' }}</label>
                                            <div class="settings-input-wrap">
                                                <span class="settings-input-icon"><i class="fas fa-lock"></i></span>
                                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" {{ $user->exists ? '' : 'required' }}>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                        <div class="settings-card-footer d-flex align-items-center flex-wrap gap-2">
                            <button type="submit" class="btn btn-info btn-lg">
                                <i class="fas fa-save mr-1"></i> {{ $user->exists ? 'Update User' : 'Create User' }}
                            </button>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 mb-3">
                    <div class="settings-side-card">
                        <div class="settings-side-head">
                            <span class="settings-side-icon settings-side-icon--info"><i class="fas fa-info-circle"></i></span>
                            <div>
                                <h4>Access Notes</h4>
                                <p>How roles control admin access.</p>
                            </div>
                        </div>
                        <div class="settings-side-body">
                            <ul class="settings-side-list">
                                <li>Only roles with <strong>admin access</strong> can log into this panel.</li>
                                <li>Feature permissions are configured per role under <a href="{{ route('admin.roles.index') }}">Roles</a>.</li>
                                <li>Inactive roles prevent users from logging in even with valid credentials.</li>
                                <li>You cannot delete your own account from this panel.</li>
                            </ul>
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
