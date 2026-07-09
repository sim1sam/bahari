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
                <div class="form-group mb-0">
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
                <div class="form-group mb-0">
                    <label>OG Image URL</label>
                    <input type="url" name="og_image" class="form-control" value="{{ old('og_image', $settings->og_image) }}" placeholder="https://...">
                </div>
            </div>
        </div>
    </div>
</div>
