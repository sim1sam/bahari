<div class="row">
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

    <div class="col-lg-12">
        <div class="card card-success card-outline">
            <div class="card-header"><h3 class="card-title">Social Links</h3></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="fab fa-facebook text-primary"></i> Facebook URL</label>
                            <input type="url" name="facebook_url" class="form-control" value="{{ old('facebook_url', $settings->facebook_url) }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="fab fa-instagram text-danger"></i> Instagram URL</label>
                            <input type="url" name="instagram_url" class="form-control" value="{{ old('instagram_url', $settings->instagram_url) }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-md-0">
                            <label><i class="fab fa-tiktok"></i> TikTok URL</label>
                            <input type="url" name="tiktok_url" class="form-control" value="{{ old('tiktok_url', $settings->tiktok_url) }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-0">
                            <label><i class="fab fa-youtube text-danger"></i> YouTube URL</label>
                            <input type="url" name="youtube_url" class="form-control" value="{{ old('youtube_url', $settings->youtube_url) }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
