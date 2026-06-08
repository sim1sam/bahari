<div class="rounded-2xl bg-surface-elevated border border-border p-5 space-y-4">
    <h2 class="text-sm font-semibold">Personal Info</h2>
    <div>
        <label for="name_m" class="block text-sm font-medium mb-1.5">Full name</label>
        <input type="text" name="name" id="name_m" value="{{ old('name', $user->name) }}" required class="auth-input !pl-4 @error('name') border-red-400 @enderror">
        @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="email_m" class="block text-sm font-medium mb-1.5">Email</label>
        <input type="email" name="email" id="email_m" value="{{ old('email', $user->email) }}" required class="auth-input !pl-4 @error('email') border-red-400 @enderror">
        @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
</div>

<div class="rounded-2xl bg-surface-elevated border border-border p-5 space-y-4">
    <h2 class="text-sm font-semibold">Change Password</h2>
    <div>
        <label for="current_password_m" class="block text-sm font-medium mb-1.5">Current password</label>
        <input type="password" name="current_password" id="current_password_m" class="auth-input !pl-4 @error('current_password') border-red-400 @enderror">
        @error('current_password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="password_m" class="block text-sm font-medium mb-1.5">New password</label>
        <input type="password" name="password" id="password_m" class="auth-input !pl-4 @error('password') border-red-400 @enderror">
        @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="password_confirmation_m" class="block text-sm font-medium mb-1.5">Confirm password</label>
        <input type="password" name="password_confirmation" id="password_confirmation_m" class="auth-input !pl-4">
    </div>
</div>
