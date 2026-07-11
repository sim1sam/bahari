<nav class="settings-nav" aria-label="User management sections">
    <a href="{{ route('admin.users.index') }}" class="settings-nav-link @if (request()->routeIs('admin.users.*')) active @endif">
        <i class="fas fa-users"></i> Users
    </a>
    <a href="{{ route('admin.roles.index') }}" class="settings-nav-link @if (request()->routeIs('admin.roles.*')) active @endif">
        <i class="fas fa-user-shield"></i> Roles
    </a>
</nav>
