<nav class="settings-nav" aria-label="Settings sections">
    <a href="{{ route('admin.homepage.index') }}" class="settings-nav-link @if (request()->routeIs('admin.homepage.*') || request()->routeIs('admin.newsletter.*')) active @endif">
        <i class="fas fa-home"></i> Homepage
    </a>
    <a href="{{ route('admin.settings.branding.edit') }}" class="settings-nav-link @if (request()->routeIs('admin.settings.branding.*')) active @endif">
        <i class="fas fa-paint-brush"></i> Branding
    </a>
    <a href="{{ route('admin.settings.footer.edit') }}" class="settings-nav-link @if (request()->routeIs('admin.settings.footer.*')) active @endif">
        <i class="fas fa-shoe-prints"></i> Footer
    </a>
    <a href="{{ route('admin.settings.top_bar.edit') }}" class="settings-nav-link @if (request()->routeIs('admin.settings.top_bar.*')) active @endif">
        <i class="fas fa-bars"></i> Top Bar
    </a>
    <a href="{{ route('admin.settings.website_colors.edit') }}" class="settings-nav-link @if (request()->routeIs('admin.settings.website_colors.*')) active @endif">
        <i class="fas fa-palette"></i> Website Colors
    </a>
    <a href="{{ route('admin.settings.gtm.edit') }}" class="settings-nav-link @if (request()->routeIs('admin.settings.gtm.*')) active @endif">
        <i class="fab fa-google"></i> Google Tag Manager
    </a>
    <a href="{{ route('admin.coupons.index') }}" class="settings-nav-link @if (request()->routeIs('admin.coupons.*')) active @endif">
        <i class="fas fa-ticket-alt"></i> Coupons
    </a>
    <a href="{{ route('admin.shipping.edit') }}" class="settings-nav-link @if (request()->routeIs('admin.shipping.*')) active @endif">
        <i class="fas fa-truck"></i> Shipping Fee
    </a>
</nav>
