@php
    $siteName = $site->siteName();
    $tagline = $site->tagline();
    $themeColor = $siteSettings->theme_primary ?? '#0891b2';
    $logoUrl = $site->logoUrl();
    $iconUrl = route('pwa.icon', ['size' => 192]);
    $tabBarOffset = ($tabBarOffset ?? true) ? 'max-lg:bottom-17' : 'bottom-0';
@endphp

<div
    id="pwa-install-banner"
    class="pwa-install-banner fixed inset-x-0 z-[60] {{ $tabBarOffset }} safe-bottom px-3 pb-3 lg:bottom-4 lg:pb-4 lg:px-4"
    role="dialog"
    aria-labelledby="pwa-install-title"
    aria-hidden="true"
    data-site-name="{{ $siteName }}"
>
    <div
        class="mx-auto flex max-w-3xl items-center gap-3 rounded-2xl border border-white/20 bg-gradient-to-r from-brand-700 to-brand-600 px-3 py-3 text-white shadow-2xl shadow-brand-900/25 sm:gap-4 sm:px-4 sm:py-3.5"
        style="--tw-gradient-from: {{ $themeColor }}; --tw-gradient-to: color-mix(in srgb, {{ $themeColor }} 85%, black);"
    >
        <div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-white/15 ring-1 ring-white/20 sm:h-14 sm:w-14">
            @if ($logoUrl)
                <img src="{{ $logoUrl }}" alt="" class="h-full w-full object-contain p-1.5">
            @else
                <img src="{{ $iconUrl }}" alt="" class="h-full w-full object-cover">
            @endif
        </div>

        <div class="min-w-0 flex-1">
            <p id="pwa-install-title" class="truncate text-sm font-bold leading-tight sm:text-base">
                Download {{ $siteName }} App
            </p>
            <p class="mt-0.5 line-clamp-2 text-xs leading-snug text-white/85 sm:text-sm" data-pwa-ios-hint>
                Tap <span class="font-semibold">Download</span> for quick install on this device.
            </p>
            <p class="mt-0.5 line-clamp-2 text-xs leading-snug text-white/85 sm:text-sm" data-pwa-desktop-hint>
                {{ Str::limit($tagline, 72) ?: 'Get the app on your home screen for faster shopping.' }}
            </p>
        </div>

        <div class="flex shrink-0 items-center gap-2">
            <button
                type="button"
                data-pwa-download
                class="inline-flex items-center gap-1.5 rounded-xl bg-white px-3.5 py-2 text-xs font-bold text-brand-700 shadow-sm transition hover:bg-white/90 disabled:cursor-wait disabled:opacity-80 sm:px-4 sm:text-sm"
            >
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4"/>
                </svg>
                Download
            </button>

            <button
                type="button"
                data-pwa-dismiss
                class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/10 text-white transition hover:bg-white/20"
                aria-label="Dismiss install prompt"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>
</div>

<style>
    .pwa-install-banner {
        opacity: 0;
        visibility: hidden;
        transform: translateY(110%);
        transition: opacity 0.3s ease, transform 0.3s ease, visibility 0.3s ease;
        pointer-events: none;
    }

    .pwa-install-banner.pwa-install-banner--visible {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
        pointer-events: auto;
    }

    body.pwa-banner-visible .storefront-tab-bar,
    body.pwa-banner-visible .account-tab-bar {
        bottom: 5.75rem;
    }

    @media (min-width: 1024px) {
        body.pwa-banner-visible .storefront-tab-bar,
        body.pwa-banner-visible .account-tab-bar {
            bottom: 0;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var banner = document.getElementById('pwa-install-banner');
        if (!banner) return;

        var iosHint = banner.querySelector('[data-pwa-ios-hint]');
        var desktopHint = banner.querySelector('[data-pwa-desktop-hint]');
        var isIos = /iphone|ipad|ipod/i.test(navigator.userAgent) && !window.MSStream;

        if (iosHint && desktopHint) {
            iosHint.classList.toggle('hidden', !isIos);
            desktopHint.classList.toggle('hidden', isIos);
        }
    });
</script>
