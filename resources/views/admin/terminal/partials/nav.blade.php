<nav class="settings-nav" aria-label="Terminal sections">
    <a href="{{ route('admin.terminal.index') }}" class="settings-nav-link @if (request()->routeIs('admin.terminal.index')) active @endif">
        <i class="fas fa-terminal"></i> Overview
    </a>
    <a href="{{ route('admin.migration.index') }}" class="settings-nav-link @if (request()->routeIs('admin.migration.*')) active @endif">
        <i class="fas fa-database"></i> Migration
    </a>
    <a href="{{ route('admin.frontend-build.index') }}" class="settings-nav-link @if (request()->routeIs('admin.frontend-build.*')) active @endif">
        <i class="fas fa-code"></i> NPM Build
    </a>
    <a href="{{ route('admin.storage-link.index') }}" class="settings-nav-link @if (request()->routeIs('admin.storage-link.*')) active @endif">
        <i class="fas fa-link"></i> Storage Link
    </a>
</nav>
