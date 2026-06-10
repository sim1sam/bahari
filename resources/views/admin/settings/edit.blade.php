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
                            <small class="text-muted">Used in header, footer, and auth pages. Max 2MB.</small>
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

            <div class="col-lg-6">
                <div class="card card-secondary card-outline">
                    <div class="card-header"><h3 class="card-title">Footer & Contact</h3></div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Footer Description</label>
                            <textarea name="footer_description" class="form-control" rows="3">{{ old('footer_description', $settings->footer_description) }}</textarea>
                        </div>
                        <div class="form-group">
                            <label>Contact Email</label>
                            <input type="email" name="contact_email" class="form-control" value="{{ old('contact_email', $settings->contact_email) }}">
                        </div>
                        <div class="form-group">
                            <label>Contact Phone</label>
                            <input type="text" name="contact_phone" class="form-control" value="{{ old('contact_phone', $settings->contact_phone) }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card card-warning card-outline">
                    <div class="card-header"><h3 class="card-title">Homepage & Top Bar</h3></div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Top Bar Text (Desktop)</label>
                            <input type="text" name="top_bar_text" class="form-control" value="{{ old('top_bar_text', $settings->top_bar_text) }}">
                        </div>
                        <div class="form-group">
                            <label>Top Bar Text (Mobile)</label>
                            <input type="text" name="top_bar_text_mobile" class="form-control" value="{{ old('top_bar_text_mobile', $settings->top_bar_text_mobile) }}">
                        </div>
                        <div class="form-group">
                            <label>Newsletter Title</label>
                            <input type="text" name="newsletter_title" class="form-control" value="{{ old('newsletter_title', $settings->newsletter_title) }}">
                        </div>
                        <div class="form-group">
                            <label>Newsletter Text</label>
                            <textarea name="newsletter_text" class="form-control" rows="2">{{ old('newsletter_text', $settings->newsletter_text) }}</textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Footer Shop Column Title</label>
                                    <input type="text" name="footer_shop_title" class="form-control" value="{{ old('footer_shop_title', $settings->footer_shop_title) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Footer Support Column Title</label>
                                    <input type="text" name="footer_support_title" class="form-control" value="{{ old('footer_support_title', $settings->footer_support_title) }}">
                                </div>
                            </div>
                        </div>
                        <p class="text-muted small mb-0">Sliders, banners, and footer links are managed in <a href="{{ route('admin.homepage.index') }}">Homepage</a>.</p>
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
