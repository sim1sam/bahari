<nav class="settings-nav" aria-label="User management sections">
    @if (auth()->user()->canAccessAdminFeature('users'))
        <a href="{{ route('admin.users.index') }}" class="settings-nav-link @if (request()->routeIs('admin.users.*')) active @endif">
            <i class="fas fa-users"></i> Users
        </a>
    @endif
    @if (auth()->user()->canAccessAdminFeature('roles'))
        <a href="{{ route('admin.roles.index') }}" class="settings-nav-link @if (request()->routeIs('admin.roles.*')) active @endif">
            <i class="fas fa-user-shield"></i> Roles
        </a>
    @endif
    @if (auth()->user()->canAccessAdminFeature('activity_logs'))
        <a href="{{ route('admin.activity-logs.index') }}" class="settings-nav-link @if (request()->routeIs('admin.activity-logs.*')) active @endif">
            <i class="fas fa-history"></i> Logs
        </a>
    @endif
</nav>
