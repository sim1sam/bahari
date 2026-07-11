<nav class="settings-nav" aria-label="Account sections">
    <a href="{{ route('admin.account-heads.index') }}" class="settings-nav-link @if (request()->routeIs('admin.account-heads.*')) active @endif">
        <i class="fas fa-list"></i> Account Heads
    </a>
    <a href="{{ route('admin.account-types.index') }}" class="settings-nav-link @if (request()->routeIs('admin.account-types.*')) active @endif">
        <i class="fas fa-tags"></i> Account Types
    </a>
</nav>
