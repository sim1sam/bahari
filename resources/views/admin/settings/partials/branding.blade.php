<div class="row">
    <div class="col-lg-6 mb-3">
        <div class="settings-card h-100">
            <div class="settings-card-body">
                <section class="settings-form-panel">
                    <div class="settings-form-panel-head">
                        <span class="settings-form-panel-icon settings-form-panel-icon--brand"><i class="fas fa-paint-brush"></i></span>
                        <div>
                            <h4>Branding</h4>
                            <p>Site identity shown across the storefront and admin panel.</p>
                        </div>
                    </div>
                    <div class="settings-field">
                        <label for="site_name">Site Name *</label>
                        <div class="settings-input-wrap">
                            <span class="settings-input-icon"><i class="fas fa-store"></i></span>
                            <input type="text" name="site_name" id="site_name" class="form-control" value="{{ old('site_name', $settings->site_name) }}" required>
                        </div>
                    </div>
                    <div class="settings-field">
                        <label for="tagline">Tagline</label>
                        <div class="settings-input-wrap">
                            <span class="settings-input-icon"><i class="fas fa-quote-left"></i></span>
                            <input type="text" name="tagline" id="tagline" class="form-control" value="{{ old('tagline', $settings->tagline) }}" placeholder="Short site tagline">
                        </div>
                    </div>
                    <div class="settings-field">
                        <label>Logo</label>
                        @if ($settings->logo)
                            <div class="settings-media-preview">
                                <img src="{{ app(\App\Services\SiteSettingsService::class)->logoUrl() }}" alt="Logo" style="max-height:48px">
                                <label class="mb-0">
                                    <input type="checkbox" name="remove_logo" value="1"> Remove current logo
                                </label>
                            </div>
                        @endif
                        <input type="file" name="logo" class="form-control-file mb-2" accept="image/*">
                        <div class="settings-input-wrap">
                            <span class="settings-input-icon"><i class="fas fa-link"></i></span>
                            <input type="url" name="logo_url" class="form-control" placeholder="Or paste logo URL" value="{{ old('logo_url') }}">
                        </div>
                        <small class="settings-field-hint">Used in admin sidebar, header, footer, and auth pages. Max 2MB.</small>
                    </div>
                    <div class="settings-field mb-0">
                        <label>Favicon</label>
                        @if ($settings->favicon)
                            <div class="settings-media-preview">
                                <img src="{{ app(\App\Services\SiteSettingsService::class)->faviconUrl() }}" alt="Favicon" style="max-height:32px">
                                <label class="mb-0">
                                    <input type="checkbox" name="remove_favicon" value="1"> Remove current favicon
                                </label>
                            </div>
                        @endif
                        <input type="file" name="favicon" class="form-control-file mb-2" accept="image/*,.ico">
                        <div class="settings-input-wrap">
                            <span class="settings-input-icon"><i class="fas fa-link"></i></span>
                            <input type="url" name="favicon_url" class="form-control" placeholder="Or paste favicon URL" value="{{ old('favicon_url') }}">
                        </div>
                        <small class="settings-field-hint">Browser tab icon. Max 1MB.</small>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-3">
        <div class="settings-card h-100">
            <div class="settings-card-body">
                <section class="settings-form-panel">
                    <div class="settings-form-panel-head">
                        <span class="settings-form-panel-icon settings-form-panel-icon--seo"><i class="fas fa-search"></i></span>
                        <div>
                            <h4>SEO &amp; Meta Tags</h4>
                            <p>Default meta tags for search engines and social sharing.</p>
                        </div>
                    </div>
                    <div class="settings-field">
                        <label for="meta_title">Meta Title</label>
                        <input type="text" name="meta_title" id="meta_title" class="form-control settings-textarea" value="{{ old('meta_title', $settings->meta_title) }}" placeholder="Default page title">
                    </div>
                    <div class="settings-field">
                        <label for="meta_description">Meta Description</label>
                        <textarea name="meta_description" id="meta_description" class="form-control settings-textarea" rows="3" placeholder="Default meta description">{{ old('meta_description', $settings->meta_description) }}</textarea>
                    </div>
                    <div class="settings-field">
                        <label for="meta_keywords">Meta Keywords</label>
                        <input type="text" name="meta_keywords" id="meta_keywords" class="form-control settings-textarea" value="{{ old('meta_keywords', $settings->meta_keywords) }}" placeholder="fashion, dresses, shop">
                    </div>
                    <div class="settings-field">
                        <label for="og_title">OG Title</label>
                        <input type="text" name="og_title" id="og_title" class="form-control settings-textarea" value="{{ old('og_title', $settings->og_title) }}" placeholder="Social share title">
                    </div>
                    <div class="settings-field">
                        <label for="og_description">OG Description</label>
                        <textarea name="og_description" id="og_description" class="form-control settings-textarea" rows="2" placeholder="Social share description">{{ old('og_description', $settings->og_description) }}</textarea>
                    </div>
                    <div class="settings-field mb-0">
                        <label for="og_image">OG Image URL</label>
                        <div class="settings-input-wrap">
                            <span class="settings-input-icon"><i class="fas fa-image"></i></span>
                            <input type="url" name="og_image" id="og_image" class="form-control" value="{{ old('og_image', $settings->og_image) }}" placeholder="https://...">
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
