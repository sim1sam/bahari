@extends('layouts.admin')

@section('title', 'Site Settings')
@section('page_title', 'Site Settings')

@section('content')
    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-lg-6">
                <div class="card card-primary card-outline">
                    <div class="card-header"><h3 class="card-title">Branding</h3></div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Site Name *</label>
                            <input type="text" name="site_name" class="form-control" value="{{ old('site_name', $settings->site_name) }}" required>
                        </div>
                        <div class="form-group">
                            <label>Tagline</label>
                            <input type="text" name="tagline" class="form-control" value="{{ old('tagline', $settings->tagline) }}" placeholder="Short site tagline">
                        </div>
                        <div class="form-group">
                            <label>Logo</label>
                            @if ($settings->logo)
                                <div class="mb-2">
                                    <img src="{{ app(\App\Services\SiteSettingsService::class)->logoUrl() }}" alt="Logo" class="img-thumbnail" style="max-height:60px">
                                </div>
                                <div class="custom-control custom-checkbox mb-2">
                                    <input type="checkbox" class="custom-control-input" id="remove_logo" name="remove_logo" value="1">
                                    <label class="custom-control-label" for="remove_logo">Remove current logo</label>
                                </div>
                            @endif
                            <input type="file" name="logo" class="form-control-file mb-2" accept="image/*">
                            <input type="url" name="logo_url" class="form-control" placeholder="Or paste logo URL" value="{{ old('logo_url') }}">
                            <small class="text-muted">Used in admin sidebar, header, footer, and auth pages. Max 2MB.</small>
                        </div>
                        <div class="form-group">
                            <label>Favicon</label>
                            @if ($settings->favicon)
                                <div class="mb-2">
                                    <img src="{{ app(\App\Services\SiteSettingsService::class)->faviconUrl() }}" alt="Favicon" class="img-thumbnail" style="max-height:32px">
                                </div>
                                <div class="custom-control custom-checkbox mb-2">
                                    <input type="checkbox" class="custom-control-input" id="remove_favicon" name="remove_favicon" value="1">
                                    <label class="custom-control-label" for="remove_favicon">Remove current favicon</label>
                                </div>
                            @endif
                            <input type="file" name="favicon" class="form-control-file mb-2" accept="image/*,.ico">
                            <input type="url" name="favicon_url" class="form-control" placeholder="Or paste favicon URL" value="{{ old('favicon_url') }}">
                            <small class="text-muted">Browser tab icon. Max 1MB.</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card card-info card-outline">
                    <div class="card-header"><h3 class="card-title">SEO & Meta Tags</h3></div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Meta Title</label>
                            <input type="text" name="meta_title" class="form-control" value="{{ old('meta_title', $settings->meta_title) }}" placeholder="Default page title">
                        </div>
                        <div class="form-group">
                            <label>Meta Description</label>
                            <textarea name="meta_description" class="form-control" rows="3" placeholder="Default meta description">{{ old('meta_description', $settings->meta_description) }}</textarea>
                        </div>
                        <div class="form-group">
                            <label>Meta Keywords</label>
                            <input type="text" name="meta_keywords" class="form-control" value="{{ old('meta_keywords', $settings->meta_keywords) }}" placeholder="fashion, dresses, shop">
                        </div>
                        <div class="form-group">
                            <label>OG Title</label>
                            <input type="text" name="og_title" class="form-control" value="{{ old('og_title', $settings->og_title) }}" placeholder="Social share title">
                        </div>
                        <div class="form-group">
                            <label>OG Description</label>
                            <textarea name="og_description" class="form-control" rows="2" placeholder="Social share description">{{ old('og_description', $settings->og_description) }}</textarea>
                        </div>
                        <div class="form-group">
                            <label>OG Image URL</label>
                            <input type="url" name="og_image" class="form-control" value="{{ old('og_image', $settings->og_image) }}" placeholder="https://...">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-12">
                <div class="card card-secondary card-outline">
                    <div class="card-header"><h3 class="card-title">Footer</h3></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Brand Description</label>
                                    <textarea name="footer_description" class="form-control" rows="3" placeholder="Short about text under the logo">{{ old('footer_description', $settings->footer_description) }}</textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Contact Email</label>
                                            <input type="email" name="contact_email" class="form-control" value="{{ old('contact_email', $settings->contact_email) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Contact Phone</label>
                                            <input type="text" name="contact_phone" class="form-control" value="{{ old('contact_phone', $settings->contact_phone) }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Copyright Text</label>
                                    <input type="text" name="footer_copyright" class="form-control" value="{{ old('footer_copyright', $settings->footer_copyright) }}" placeholder="© {year} {site}. All rights reserved.">
                                    <small class="text-muted">Use <code>{year}</code> and <code>{site}</code> as placeholders.</small>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Shop Column Title</label>
                                            <input type="text" name="footer_shop_title" class="form-control" value="{{ old('footer_shop_title', $settings->footer_shop_title) }}" placeholder="Shop">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Support Column Title</label>
                                            <input type="text" name="footer_support_title" class="form-control" value="{{ old('footer_support_title', $settings->footer_support_title) }}" placeholder="Support">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="newsletter_enabled" name="newsletter_enabled" value="1" @checked(old('newsletter_enabled', $settings->newsletter_enabled ?? true))>
                                        <label class="custom-control-label" for="newsletter_enabled">Show newsletter in footer</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Newsletter Title</label>
                                    <input type="text" name="newsletter_title" class="form-control" value="{{ old('newsletter_title', $settings->newsletter_title) }}" placeholder="Stay Updated">
                                </div>
                                <div class="form-group">
                                    <label>Newsletter Text</label>
                                    <textarea name="newsletter_text" class="form-control" rows="2" placeholder="Get exclusive deals...">{{ old('newsletter_text', $settings->newsletter_text) }}</textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Newsletter Email Placeholder</label>
                                            <input type="text" name="newsletter_placeholder" class="form-control" value="{{ old('newsletter_placeholder', $settings->newsletter_placeholder) }}" placeholder="Your email">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Newsletter Button Text</label>
                                            <input type="text" name="newsletter_button_text" class="form-control" value="{{ old('newsletter_button_text', $settings->newsletter_button_text) }}" placeholder="Join">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Success Message</label>
                                    <input type="text" name="newsletter_success_message" class="form-control" value="{{ old('newsletter_success_message', $settings->newsletter_success_message) }}" placeholder="Thanks for subscribing! Check your inbox for updates.">
                                </div>
                                <p class="text-muted small mb-0">
                                    Footer menu links: <a href="{{ route('admin.homepage.footer-links.index') }}">Homepage → Footer Links</a>.
                                    Subscribers: <a href="{{ route('admin.newsletter.index') }}">Newsletter Subscribers</a>.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card card-warning card-outline">
                    <div class="card-header"><h3 class="card-title">Top Bar</h3></div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Top Bar Text (Desktop)</label>
                            <input type="text" name="top_bar_text" class="form-control" value="{{ old('top_bar_text', $settings->top_bar_text) }}" placeholder="Free shipping on orders over $50">
                        </div>
                        <div class="form-group">
                            <label>Top Bar Text (Mobile)</label>
                            <input type="text" name="top_bar_text_mobile" class="form-control" value="{{ old('top_bar_text_mobile', $settings->top_bar_text_mobile) }}" placeholder="Free shipping $50+">
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Background Color</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="color" class="form-control form-control-color" value="{{ old('top_bar_bg_color', $settings->top_bar_bg_color ?? '#164e63') }}" oninput="this.nextElementSibling.value=this.value">
                                        <input type="text" name="top_bar_bg_color" class="form-control" value="{{ old('top_bar_bg_color', $settings->top_bar_bg_color ?? '#164e63') }}" pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$" oninput="this.previousElementSibling.value=this.value">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Text Color</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="color" class="form-control form-control-color" value="{{ old('top_bar_text_color', $settings->top_bar_text_color ?? '#ffffff') }}" oninput="this.nextElementSibling.value=this.value">
                                        <input type="text" name="top_bar_text_color" class="form-control" value="{{ old('top_bar_text_color', $settings->top_bar_text_color ?? '#ffffff') }}" pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$" oninput="this.previousElementSibling.value=this.value">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Link Color</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="color" class="form-control form-control-color" value="{{ old('top_bar_link_color', $settings->top_bar_link_color ?? '#cffafe') }}" oninput="this.nextElementSibling.value=this.value">
                                        <input type="text" name="top_bar_link_color" class="form-control" value="{{ old('top_bar_link_color', $settings->top_bar_link_color ?? '#cffafe') }}" pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$" oninput="this.previousElementSibling.value=this.value">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="top-bar-preview" class="rounded px-3 py-2 text-sm" style="background:{{ old('top_bar_bg_color', $settings->top_bar_bg_color ?? '#164e63') }};color:{{ old('top_bar_text_color', $settings->top_bar_text_color ?? '#ffffff') }}">
                            <span>{{ old('top_bar_text', $settings->top_bar_text) ?: 'Top bar preview text' }}</span>
                            <span class="ml-3" style="color:{{ old('top_bar_link_color', $settings->top_bar_link_color ?? '#cffafe') }}">Sample link</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-12">
                <div class="card card-primary card-outline">
                    <div class="card-header"><h3 class="card-title">Website Colors</h3></div>
                    <div class="card-body">
                        <p class="text-muted small">These colors apply across the storefront — buttons, links, hero slider, and footer.</p>
                        <div class="row">
                            <div class="col-md-4">
                                @include('admin.settings.partials.color-field', [
                                    'name' => 'theme_primary',
                                    'label' => 'Primary Color',
                                    'value' => $settings->theme_primary,
                                    'default' => '#0891b2',
                                ])
                                <small class="text-muted">Buttons, links, accents</small>
                            </div>
                            <div class="col-md-4">
                                @include('admin.settings.partials.color-field', [
                                    'name' => 'theme_primary_dark',
                                    'label' => 'Primary Dark',
                                    'value' => $settings->theme_primary_dark,
                                    'default' => '#164e63',
                                ])
                                <small class="text-muted">Hero slider, dark sections</small>
                            </div>
                            <div class="col-md-4">
                                @include('admin.settings.partials.color-field', [
                                    'name' => 'theme_footer_bg',
                                    'label' => 'Footer Background',
                                    'value' => $settings->theme_footer_bg,
                                    'default' => '#1c1917',
                                ])
                            </div>
                            <div class="col-md-4">
                                @include('admin.settings.partials.color-field', [
                                    'name' => 'theme_text',
                                    'label' => 'Text Color',
                                    'value' => $settings->theme_text,
                                    'default' => '#1c1917',
                                ])
                            </div>
                            <div class="col-md-4">
                                @include('admin.settings.partials.color-field', [
                                    'name' => 'theme_background',
                                    'label' => 'Page Background',
                                    'value' => $settings->theme_background,
                                    'default' => '#f8fafc',
                                ])
                            </div>
                        </div>
                        <div class="mt-3 p-3 rounded d-flex flex-wrap align-items-center gap-3" id="theme-preview" style="background:{{ old('theme_background', $settings->theme_background ?? '#f8fafc') }}">
                            <span class="px-4 py-2 rounded-lg text-white text-sm font-medium" style="background:{{ old('theme_primary', $settings->theme_primary ?? '#0891b2') }}">Primary Button</span>
                            <span class="text-sm font-medium" style="color:{{ old('theme_primary', $settings->theme_primary ?? '#0891b2') }}">Primary Link</span>
                            <span class="px-3 py-1 rounded text-sm" style="background:color-mix(in srgb, {{ old('theme_primary', $settings->theme_primary ?? '#0891b2') }} 10%, white); color:{{ old('theme_primary', $settings->theme_primary ?? '#0891b2') }}">Light Badge</span>
                            <span class="px-4 py-2 rounded-lg text-white text-sm" style="background:{{ old('theme_footer_bg', $settings->theme_footer_bg ?? '#1c1917') }}">Footer</span>
                            <span class="text-sm" style="color:{{ old('theme_text', $settings->theme_text ?? '#1c1917') }}">Body text sample</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card card-warning card-outline">
                    <div class="card-header"><h3 class="card-title">Google Tag Manager</h3></div>
                    <div class="card-body">
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="gtm_enabled" name="gtm_enabled" value="1" @checked(old('gtm_enabled', $settings->gtm_enabled ?? false))>
                                <label class="custom-control-label" for="gtm_enabled">Enable Google Tag Manager</label>
                            </div>
                            <small class="text-muted d-block mt-1">Saved in site settings (database). No <code>.env</code> entry needed — works the same after git push/pull.</small>
                        </div>
                        <div class="form-group mb-0">
                            <label>Container ID</label>
                            <input
                                type="text"
                                name="gtm_container_id"
                                class="form-control @error('gtm_container_id') is-invalid @enderror"
                                value="{{ old('gtm_container_id', $settings->gtm_container_id) }}"
                                placeholder="GTM-XXXXXXX"
                                pattern="^GTM-[A-Z0-9]+$"
                                style="max-width:220px"
                            >
                            @error('gtm_container_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            <small class="text-muted">Find this in your <a href="https://tagmanager.google.com/" target="_blank" rel="noopener noreferrer">Google Tag Manager</a> workspace.</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card card-danger card-outline">
                    <div class="card-header"><h3 class="card-title">SSLCommerz Payment API</h3></div>
                    <div class="card-body">
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="sslcommerz_enabled" name="sslcommerz_enabled" value="1" @checked(old('sslcommerz_enabled', $settings->sslcommerz_enabled ?? false))>
                                <label class="custom-control-label" for="sslcommerz_enabled">Enable SSLCommerz online payment</label>
                            </div>
                            <small class="text-muted d-block mt-1">Saved in site settings (database). No <code>.env</code> needed — syncs after git push/pull.</small>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="sslcommerz_sandbox" name="sslcommerz_sandbox" value="1" @checked(old('sslcommerz_sandbox', $settings->sslcommerz_sandbox ?? true))>
                                <label class="custom-control-label" for="sslcommerz_sandbox">Sandbox mode (testing)</label>
                            </div>
                            <small class="text-muted">Turn off for live payments on your production store.</small>
                        </div>
                        <div class="form-group">
                            <label>Store ID</label>
                            <input
                                type="text"
                                name="sslcommerz_store_id"
                                class="form-control @error('sslcommerz_store_id') is-invalid @enderror"
                                value="{{ old('sslcommerz_store_id', $settings->sslcommerz_store_id) }}"
                                placeholder="your_store_id"
                            >
                            @error('sslcommerz_store_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group mb-0">
                            <label>Store Password</label>
                            <input
                                type="password"
                                name="sslcommerz_store_password"
                                class="form-control @error('sslcommerz_store_password') is-invalid @enderror"
                                placeholder="{{ $settings->sslcommerz_store_password ? 'Leave blank to keep current password' : 'Store password from SSLCommerz' }}"
                                autocomplete="new-password"
                            >
                            @error('sslcommerz_store_password')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            <small class="text-muted">Get credentials from your <a href="https://developer.sslcommerz.com/" target="_blank" rel="noopener noreferrer">SSLCommerz merchant panel</a>.</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card card-success card-outline">
                    <div class="card-header"><h3 class="card-title">Social Links</h3></div>
                    <div class="card-body">
                        <div class="form-group">
                            <label><i class="fab fa-facebook text-primary"></i> Facebook URL</label>
                            <input type="url" name="facebook_url" class="form-control" value="{{ old('facebook_url', $settings->facebook_url) }}">
                        </div>
                        <div class="form-group">
                            <label><i class="fab fa-instagram text-danger"></i> Instagram URL</label>
                            <input type="url" name="instagram_url" class="form-control" value="{{ old('instagram_url', $settings->instagram_url) }}">
                        </div>
                        <div class="form-group">
                            <label><i class="fab fa-tiktok"></i> TikTok URL</label>
                            <input type="url" name="tiktok_url" class="form-control" value="{{ old('tiktok_url', $settings->tiktok_url) }}">
                        </div>
                        <div class="form-group">
                            <label><i class="fab fa-youtube text-danger"></i> YouTube URL</label>
                            <input type="url" name="youtube_url" class="form-control" value="{{ old('youtube_url', $settings->youtube_url) }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Settings</button>
            </div>
        </div>
    </form>
@endsection
