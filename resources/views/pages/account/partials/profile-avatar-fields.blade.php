<div class="rounded-2xl lg:rounded-xl bg-surface-elevated border border-border p-5" x-data="{ preview: @json($user->avatarUrl()) }">
    <h2 class="text-sm font-semibold mb-4">Profile Photo</h2>
    <div class="flex items-center gap-4">
        <template x-if="preview">
            <img :src="preview" alt="Profile preview" class="w-20 h-20 rounded-2xl object-cover border border-border shadow-sm shrink-0">
        </template>
        <template x-if="!preview">
            <x-account.avatar :user="$user" size="lg" rounded="2xl" />
        </template>
        <div class="flex-1 min-w-0">
            <input
                type="file"
                name="avatar"
                id="avatar_input"
                accept="image/jpeg,image/png,image/webp,image/gif"
                class="account-input file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-brand-50 file:text-brand-700 w-full"
                @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : @json($user->avatarUrl())"
            >
            <p class="text-xs text-ink-muted mt-1.5">JPG, PNG or WebP. Max 2MB.</p>
            @error('avatar')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            @if ($user->avatar)
                <label class="inline-flex items-center gap-2 mt-2 text-xs text-red-600 cursor-pointer">
                    <input type="checkbox" name="remove_avatar" value="1" class="rounded border-border text-red-600" @change="if ($event.target.checked) preview = null">
                    Remove photo
                </label>
            @endif
        </div>
    </div>
</div>
