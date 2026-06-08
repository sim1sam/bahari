@extends('layouts.account')

@section('title', 'Profile')
@section('page_title', 'Profile')
@section('mobile_title', 'Profile')
@section('page_subtitle', 'Manage your account details and password')

@section('breadcrumb')
    <a href="{{ route('account.dashboard') }}" class="hover:text-brand-600">Dashboard</a>
    <span>/</span>
    <span class="text-ink">Profile</span>
@endsection

@section('content')
    {{-- Mobile --}}
    <div class="lg:hidden px-4 pt-4 space-y-5 max-w-xl mx-auto">
        <form action="{{ route('account.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')
            @include('pages.account.partials.profile-avatar-fields')
            @include('pages.account.partials.profile-form-fields')
            <button type="submit" class="auth-btn-primary w-full">Save Changes</button>
        </form>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="w-full py-3 rounded-xl border border-red-200 text-red-600 text-sm font-semibold bg-red-50">Logout</button>
        </form>
    </div>

    {{-- Desktop --}}
    <form action="{{ route('account.profile.update') }}" method="POST" enctype="multipart/form-data" class="hidden lg:block px-8 pt-8 w-full">
        @csrf
        @method('PUT')
        <div class="mb-6">
            @include('pages.account.partials.profile-avatar-fields')
        </div>
        <div class="grid grid-cols-2 gap-6">
            <div class="account-panel">
                <div class="account-panel-header"><h2 class="font-semibold">Personal Information</h2></div>
                <div class="account-panel-body space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium mb-1.5">Full name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required class="account-input @error('name') border-red-400 @enderror">
                        @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium mb-1.5">Email address</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required class="account-input @error('email') border-red-400 @enderror">
                        @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
            <div class="account-panel">
                <div class="account-panel-header"><h2 class="font-semibold">Change Password</h2></div>
                <div class="account-panel-body space-y-4">
                    <p class="text-xs text-ink-muted">Leave blank to keep current password</p>
                    <div>
                        <label for="current_password" class="block text-sm font-medium mb-1.5">Current password</label>
                        <input type="password" name="current_password" id="current_password" class="account-input @error('current_password') border-red-400 @enderror">
                        @error('current_password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium mb-1.5">New password</label>
                        <input type="password" name="password" id="password" class="account-input @error('password') border-red-400 @enderror">
                        @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium mb-1.5">Confirm password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="account-input">
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-6 flex items-center gap-3">
            <button type="submit" class="px-6 py-2.5 rounded-lg bg-brand-600 text-white text-sm font-semibold hover:bg-brand-700 transition-colors">Save Changes</button>
            <a href="{{ route('account.dashboard') }}" class="px-6 py-2.5 rounded-lg border border-border text-sm font-medium text-ink-muted hover:text-ink">Cancel</a>
        </div>
    </form>
@endsection
