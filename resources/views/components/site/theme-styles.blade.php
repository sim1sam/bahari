@php
    $primary = $siteSettings->theme_primary ?? '#0891b2';
    $primaryDark = $siteSettings->theme_primary_dark ?? '#164e63';
    $footerBg = $siteSettings->theme_footer_bg ?? '#1c1917';
    $text = $siteSettings->theme_text ?? '#1c1917';
    $background = $siteSettings->theme_background ?? '#f8fafc';
@endphp
<style>
    :root {
        --theme-primary: {{ $primary }};
        --theme-primary-dark: {{ $primaryDark }};
        --theme-footer-bg: {{ $footerBg }};
        --theme-text: {{ $text }};
        --theme-background: {{ $background }};

        --color-brand-50: color-mix(in srgb, var(--theme-primary) 8%, white);
        --color-brand-100: color-mix(in srgb, var(--theme-primary) 15%, white);
        --color-brand-200: color-mix(in srgb, var(--theme-primary) 30%, white);
        --color-brand-300: color-mix(in srgb, var(--theme-primary) 50%, white);
        --color-brand-400: color-mix(in srgb, var(--theme-primary) 75%, white);
        --color-brand-500: color-mix(in srgb, var(--theme-primary) 90%, white);
        --color-brand-600: var(--theme-primary);
        --color-brand-700: color-mix(in srgb, var(--theme-primary) 85%, black);
        --color-brand-800: color-mix(in srgb, var(--theme-primary-dark) 70%, black);
        --color-brand-900: var(--theme-primary-dark);
        --color-brand-950: color-mix(in srgb, var(--theme-primary-dark) 80%, black);

        --color-surface: var(--theme-background);
        --color-ink: var(--theme-text);
    }

    .bg-ink {
        background-color: var(--theme-footer-bg) !important;
    }
</style>
