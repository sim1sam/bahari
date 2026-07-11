@extends('layouts.admin')

@section('title', $sectionLabel)
@section('page_title', $sectionLabel)

@section('content')
    @php
        $sectionRoute = str_replace('-', '_', $section);
        $siteSettings = app(\App\Services\SiteSettingsService::class);
        $hasLogo = filled($settings->logo);
        $hasFavicon = filled($settings->favicon);
        $seoFields = collect(['meta_title', 'meta_description', 'meta_keywords', 'og_title', 'og_description', 'og_image'])
            ->filter(fn ($f) => filled($settings->{$f}))
            ->count();
        $socialCount = collect(['facebook_url', 'instagram_url', 'tiktok_url', 'youtube_url'])
            ->filter(fn ($f) => filled($settings->{$f}))
            ->count();
        $newsletterOn = (bool) old('newsletter_enabled', $settings->newsletter_enabled ?? true);
        $gtmEnabled = (bool) old('gtm_enabled', $settings->gtm_enabled ?? false);
        $hasGtmId = filled(old('gtm_container_id', $settings->gtm_container_id));
    @endphp

    <div class="settings-page">
        <section class="settings-hero">
            <div>
                <span class="settings-eyebrow">Settings</span>
                <h2>{{ $sectionLabel }}</h2>
                @switch($section)
                    @case('branding')
                        <p>Site name, logo, favicon, and SEO meta tags for your storefront and admin panel.</p>
                        <div class="settings-hero-meta">
                            <span class="settings-hero-chip"><i class="fas fa-store"></i> {{ $settings->site_name }}</span>
                            <span class="settings-hero-chip"><i class="fas fa-image"></i> {{ $hasLogo ? 'Logo set' : 'No logo' }}</span>
                        </div>
                        @break
                    @case('footer')
                        <p>Footer content, contact details, newsletter block, and social media links.</p>
                        <div class="settings-hero-meta">
                            <span class="settings-hero-chip"><i class="fas fa-envelope"></i> Newsletter {{ $newsletterOn ? 'on' : 'off' }}</span>
                            <span class="settings-hero-chip"><i class="fas fa-share-alt"></i> {{ $socialCount }} social link{{ $socialCount === 1 ? '' : 's' }}</span>
                        </div>
                        @break
                    @case('top-bar')
                        <p>Announcement bar text and colors shown above the storefront header.</p>
                        <div class="settings-hero-meta">
                            <span class="settings-hero-chip"><i class="fas fa-desktop"></i> Desktop text</span>
                            <span class="settings-hero-chip"><i class="fas fa-mobile-alt"></i> Mobile text</span>
                        </div>
                        @break
                    @case('website-colors')
                        <p>Storefront theme colors — buttons, links, hero slider, and footer.</p>
                        <div class="settings-hero-meta">
                            <span class="settings-hero-chip"><i class="fas fa-palette"></i> {{ $settings->theme_primary ?? '#0891b2' }}</span>
                            <span class="settings-hero-chip"><i class="fas fa-fill-drip"></i> 5 color tokens</span>
                        </div>
                        @break
                    @case('gtm')
                        <p>Google Tag Manager container for analytics and marketing tags.</p>
                        <div class="settings-hero-meta">
                            <span class="settings-hero-chip"><i class="fas fa-chart-line"></i> {{ $gtmEnabled ? 'Enabled' : 'Disabled' }}</span>
                            @if ($hasGtmId)
                                <span class="settings-hero-chip"><i class="fas fa-tag"></i> {{ $settings->gtm_container_id }}</span>
                            @endif
                        </div>
                        @break
                @endswitch
            </div>
            <div class="settings-hero-actions">
                @if ($section === 'footer')
                    <a href="{{ route('admin.homepage.footer-links.index') }}" class="btn btn-primary">
                        <i class="fas fa-link mr-1"></i> Footer Links
                    </a>
                    <a href="{{ route('admin.newsletter.index') }}" class="btn btn-info">
                        <i class="fas fa-envelope mr-1"></i> Subscribers
                    </a>
                @elseif ($section === 'branding')
                    <a href="{{ url('/') }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary">
                        <i class="fas fa-external-link-alt mr-1"></i> View Storefront
                    </a>
                @endif
            </div>
        </section>

        @if ($section === 'branding')
            <section class="row mb-3">
                <div class="col-xl-3 col-sm-6 mb-3">
                    <article class="settings-stat settings-stat--teal">
                        <span class="settings-stat-icon"><i class="fas fa-font"></i></span>
                        <div>
                            <div class="settings-stat-value">{{ Str::limit($settings->site_name, 12) }}</div>
                            <div class="settings-stat-label">Site Name</div>
                        </div>
                    </article>
                </div>
                <div class="col-xl-3 col-sm-6 mb-3">
                    <article class="settings-stat settings-stat--blue">
                        <span class="settings-stat-icon"><i class="fas fa-image"></i></span>
                        <div>
                            <div class="settings-stat-value">{{ $hasLogo ? 'Set' : 'Missing' }}</div>
                            <div class="settings-stat-label">Logo</div>
                        </div>
                    </article>
                </div>
                <div class="col-xl-3 col-sm-6 mb-3">
                    <article class="settings-stat settings-stat--purple">
                        <span class="settings-stat-icon"><i class="fas fa-star"></i></span>
                        <div>
                            <div class="settings-stat-value">{{ $hasFavicon ? 'Set' : 'Missing' }}</div>
                            <div class="settings-stat-label">Favicon</div>
                        </div>
                    </article>
                </div>
                <div class="col-xl-3 col-sm-6 mb-3">
                    <article class="settings-stat settings-stat--green">
                        <span class="settings-stat-icon"><i class="fas fa-search"></i></span>
                        <div>
                            <div class="settings-stat-value">{{ $seoFields }}/6</div>
                            <div class="settings-stat-label">SEO Fields</div>
                        </div>
                    </article>
                </div>
            </section>
        @elseif ($section === 'footer')
            <section class="row mb-3">
                <div class="col-xl-3 col-sm-6 mb-3">
                    <article class="settings-stat settings-stat--amber">
                        <span class="settings-stat-icon"><i class="fas fa-envelope-open-text"></i></span>
                        <div>
                            <div class="settings-stat-value">{{ $newsletterOn ? 'On' : 'Off' }}</div>
                            <div class="settings-stat-label">Newsletter</div>
                        </div>
                    </article>
                </div>
                <div class="col-xl-3 col-sm-6 mb-3">
                    <article class="settings-stat settings-stat--green">
                        <span class="settings-stat-icon"><i class="fas fa-share-alt"></i></span>
                        <div>
                            <div class="settings-stat-value">{{ $socialCount }}</div>
                            <div class="settings-stat-label">Social Links</div>
                        </div>
                    </article>
                </div>
                <div class="col-xl-3 col-sm-6 mb-3">
                    <article class="settings-stat settings-stat--blue">
                        <span class="settings-stat-icon"><i class="fas fa-phone"></i></span>
                        <div>
                            <div class="settings-stat-value">{{ filled($settings->contact_email) || filled($settings->contact_phone) ? 'Set' : 'Missing' }}</div>
                            <div class="settings-stat-label">Contact Info</div>
                        </div>
                    </article>
                </div>
                <div class="col-xl-3 col-sm-6 mb-3">
                    <article class="settings-stat settings-stat--teal">
                        <span class="settings-stat-icon"><i class="fas fa-copyright"></i></span>
                        <div>
                            <div class="settings-stat-value">{{ filled($settings->footer_copyright) ? 'Set' : 'Default' }}</div>
                            <div class="settings-stat-label">Copyright</div>
                        </div>
                    </article>
                </div>
            </section>
        @elseif ($section === 'gtm')
            <section class="row mb-3">
                <div class="col-xl-3 col-sm-6 mb-3">
                    <article class="settings-stat settings-stat--{{ $gtmEnabled ? 'green' : 'rose' }}">
                        <span class="settings-stat-icon"><i class="fas fa-power-off"></i></span>
                        <div>
                            <div class="settings-stat-value">{{ $gtmEnabled ? 'Enabled' : 'Disabled' }}</div>
                            <div class="settings-stat-label">GTM Status</div>
                        </div>
                    </article>
                </div>
                <div class="col-xl-3 col-sm-6 mb-3">
                    <article class="settings-stat settings-stat--amber">
                        <span class="settings-stat-icon"><i class="fas fa-tag"></i></span>
                        <div>
                            <div class="settings-stat-value">{{ $hasGtmId ? 'Set' : 'Missing' }}</div>
                            <div class="settings-stat-label">Container ID</div>
                        </div>
                    </article>
                </div>
            </section>
        @endif

        @include('admin.settings.partials.nav')

        <form action="{{ route('admin.settings.'.$sectionRoute.'.update') }}" method="POST" @if ($section === 'branding') enctype="multipart/form-data" @endif>
            @csrf
            @method('PUT')

            @include('admin.settings.partials.'.$section)

            <div class="settings-card mt-3">
                <div class="settings-card-footer">
                    <button type="submit" class="btn btn-info btn-lg">
                        <i class="fas fa-save mr-1"></i> Save {{ $sectionLabel }}
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('styles')
    @include('admin.settings.partials.page-styles')
@endpush

@push('scripts')
    @if ($section === 'top-bar')
        <script>
            document.querySelectorAll('[name="top_bar_bg_color"], [name="top_bar_text_color"], [name="top_bar_link_color"], [name="top_bar_text"]').forEach(function (el) {
                el.addEventListener('input', function () {
                    var preview = document.getElementById('top-bar-preview');
                    if (!preview) return;
                    var bg = document.querySelector('[name="top_bar_bg_color"]').value;
                    var text = document.querySelector('[name="top_bar_text_color"]').value;
                    var link = document.querySelector('[name="top_bar_link_color"]').value;
                    var barText = document.querySelector('[name="top_bar_text"]').value || 'Top bar preview text';
                    preview.style.background = bg;
                    preview.style.color = text;
                    preview.querySelector('span:first-child').textContent = barText;
                    preview.querySelector('span:last-child').style.color = link;
                });
            });
        </script>
    @elseif ($section === 'website-colors')
        <script>
            document.querySelectorAll('[name="theme_primary"], [name="theme_primary_dark"], [name="theme_footer_bg"], [name="theme_text"], [name="theme_background"]').forEach(function (el) {
                el.addEventListener('input', function () {
                    var preview = document.getElementById('theme-preview');
                    if (!preview) return;
                    var primary = document.querySelector('[name="theme_primary"]').value;
                    var footer = document.querySelector('[name="theme_footer_bg"]').value;
                    var text = document.querySelector('[name="theme_text"]').value;
                    var bg = document.querySelector('[name="theme_background"]').value;
                    preview.style.background = bg;
                    preview.querySelector('[data-preview="button"]').style.background = primary;
                    preview.querySelector('[data-preview="link"]').style.color = primary;
                    preview.querySelector('[data-preview="badge"]').style.background = 'color-mix(in srgb, ' + primary + ' 10%, white)';
                    preview.querySelector('[data-preview="badge"]').style.color = primary;
                    preview.querySelector('[data-preview="footer"]').style.background = footer;
                    preview.querySelector('[data-preview="text"]').style.color = text;
                });
            });
        </script>
    @elseif ($section === 'gtm')
        <script>
            document.querySelectorAll('.settings-toggle-input').forEach(function (input) {
                input.addEventListener('change', function () {
                    var card = input.closest('.settings-toggle-card');
                    var label = card.querySelector('.settings-toggle-label');
                    if (card) card.classList.toggle('settings-toggle-card--on', input.checked);
                    if (label) label.textContent = input.checked ? 'Enabled' : 'Disabled';
                });
            });
        </script>
    @elseif ($section === 'footer')
        <script>
            var newsletterToggle = document.getElementById('newsletter_enabled');
            if (newsletterToggle) {
                newsletterToggle.addEventListener('change', function () {
                    var card = newsletterToggle.closest('.settings-toggle-card');
                    var label = card.querySelector('.settings-toggle-label');
                    if (card) card.classList.toggle('settings-toggle-card--on', newsletterToggle.checked);
                    if (label) label.textContent = newsletterToggle.checked ? 'Visible' : 'Hidden';
                });
            }
        </script>
    @endif
@endpush
