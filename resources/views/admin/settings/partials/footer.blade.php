@php $newsletterOn = (bool) old('newsletter_enabled', $settings->newsletter_enabled ?? true); @endphp

<div class="row">
    <div class="col-lg-8 mb-3">
        <div class="settings-card">
            <div class="settings-card-body">
                <div class="row">
                    <div class="col-lg-6">
                        <section class="settings-form-panel">
                            <div class="settings-form-panel-head">
                                <span class="settings-form-panel-icon settings-form-panel-icon--footer"><i class="fas fa-shoe-prints"></i></span>
                                <div>
                                    <h4>Footer Content</h4>
                                    <p>Brand description, contact info, and copyright text.</p>
                                </div>
                            </div>
                            <div class="settings-field">
                                <label for="footer_description">Brand Description</label>
                                <textarea name="footer_description" id="footer_description" class="form-control settings-textarea" rows="3" placeholder="Short about text under the logo">{{ old('footer_description', $settings->footer_description) }}</textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="settings-field">
                                        <label for="contact_email">Contact Email</label>
                                        <div class="settings-input-wrap">
                                            <span class="settings-input-icon"><i class="fas fa-envelope"></i></span>
                                            <input type="email" name="contact_email" id="contact_email" class="form-control" value="{{ old('contact_email', $settings->contact_email) }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="settings-field">
                                        <label for="contact_phone">Contact Phone</label>
                                        <div class="settings-input-wrap">
                                            <span class="settings-input-icon"><i class="fas fa-phone"></i></span>
                                            <input type="text" name="contact_phone" id="contact_phone" class="form-control" value="{{ old('contact_phone', $settings->contact_phone) }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="settings-field mb-0">
                                <label for="footer_copyright">Copyright Text</label>
                                <input type="text" name="footer_copyright" id="footer_copyright" class="form-control settings-textarea" value="{{ old('footer_copyright', $settings->footer_copyright) }}" placeholder="© {year} {site}. All rights reserved.">
                                <small class="settings-field-hint">Use <code>{year}</code> and <code>{site}</code> as placeholders.</small>
                            </div>
                        </section>
                    </div>
                    <div class="col-lg-6">
                        <section class="settings-form-panel">
                            <div class="settings-form-panel-head">
                                <span class="settings-form-panel-icon settings-form-panel-icon--newsletter"><i class="fas fa-envelope-open-text"></i></span>
                                <div>
                                    <h4>Newsletter Block</h4>
                                    <p>Footer signup form text and visibility.</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="settings-field">
                                        <label for="footer_shop_title">Shop Column Title</label>
                                        <input type="text" name="footer_shop_title" id="footer_shop_title" class="form-control settings-textarea" value="{{ old('footer_shop_title', $settings->footer_shop_title) }}" placeholder="Shop">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="settings-field">
                                        <label for="footer_support_title">Support Column Title</label>
                                        <input type="text" name="footer_support_title" id="footer_support_title" class="form-control settings-textarea" value="{{ old('footer_support_title', $settings->footer_support_title) }}" placeholder="Support">
                                    </div>
                                </div>
                            </div>
                            <div class="settings-toggle-card {{ $newsletterOn ? 'settings-toggle-card--on' : '' }}">
                                <div class="settings-toggle-copy">
                                    <h5>Show Newsletter in Footer</h5>
                                    <p>Display the email signup form in the storefront footer.</p>
                                </div>
                                <label class="settings-toggle" for="newsletter_enabled">
                                    <input type="checkbox" class="settings-toggle-input" id="newsletter_enabled" name="newsletter_enabled" value="1" @checked($newsletterOn)>
                                    <span class="settings-toggle-track"><span class="settings-toggle-thumb"></span></span>
                                    <span class="settings-toggle-label">{{ $newsletterOn ? 'Visible' : 'Hidden' }}</span>
                                </label>
                            </div>
                            <div class="settings-field">
                                <label for="newsletter_title">Newsletter Title</label>
                                <input type="text" name="newsletter_title" id="newsletter_title" class="form-control settings-textarea" value="{{ old('newsletter_title', $settings->newsletter_title) }}" placeholder="Stay Updated">
                            </div>
                            <div class="settings-field">
                                <label for="newsletter_text">Newsletter Text</label>
                                <textarea name="newsletter_text" id="newsletter_text" class="form-control settings-textarea" rows="2" placeholder="Get exclusive deals...">{{ old('newsletter_text', $settings->newsletter_text) }}</textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="settings-field">
                                        <label for="newsletter_placeholder">Email Placeholder</label>
                                        <input type="text" name="newsletter_placeholder" id="newsletter_placeholder" class="form-control settings-textarea" value="{{ old('newsletter_placeholder', $settings->newsletter_placeholder) }}" placeholder="Your email">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="settings-field">
                                        <label for="newsletter_button_text">Button Text</label>
                                        <input type="text" name="newsletter_button_text" id="newsletter_button_text" class="form-control settings-textarea" value="{{ old('newsletter_button_text', $settings->newsletter_button_text) }}" placeholder="Join">
                                    </div>
                                </div>
                            </div>
                            <div class="settings-field mb-0">
                                <label for="newsletter_success_message">Success Message</label>
                                <input type="text" name="newsletter_success_message" id="newsletter_success_message" class="form-control settings-textarea" value="{{ old('newsletter_success_message', $settings->newsletter_success_message) }}" placeholder="Thanks for subscribing!">
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>

        <div class="settings-card mt-3">
            <div class="settings-card-body">
                <section class="settings-form-panel mb-0">
                    <div class="settings-form-panel-head">
                        <span class="settings-form-panel-icon settings-form-panel-icon--social"><i class="fas fa-share-alt"></i></span>
                        <div>
                            <h4>Social Links</h4>
                            <p>Social media profile URLs shown in the footer.</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="settings-field">
                                <label for="facebook_url"><i class="fab fa-facebook text-primary"></i> Facebook URL</label>
                                <input type="url" name="facebook_url" id="facebook_url" class="form-control settings-textarea" value="{{ old('facebook_url', $settings->facebook_url) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="settings-field">
                                <label for="instagram_url"><i class="fab fa-instagram text-danger"></i> Instagram URL</label>
                                <input type="url" name="instagram_url" id="instagram_url" class="form-control settings-textarea" value="{{ old('instagram_url', $settings->instagram_url) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="settings-field">
                                <label for="tiktok_url"><i class="fab fa-tiktok"></i> TikTok URL</label>
                                <input type="url" name="tiktok_url" id="tiktok_url" class="form-control settings-textarea" value="{{ old('tiktok_url', $settings->tiktok_url) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="settings-field mb-0">
                                <label for="youtube_url"><i class="fab fa-youtube text-danger"></i> YouTube URL</label>
                                <input type="url" name="youtube_url" id="youtube_url" class="form-control settings-textarea" value="{{ old('youtube_url', $settings->youtube_url) }}">
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-3">
        <div class="settings-side-card">
            <div class="settings-side-head">
                <span class="settings-side-icon settings-side-icon--info"><i class="fas fa-link"></i></span>
                <div>
                    <h4>Related Pages</h4>
                    <p>Manage footer links and subscribers separately.</p>
                </div>
            </div>
            <div class="settings-side-body">
                <p class="settings-side-text mb-2">Footer menu links are managed under Homepage → Footer Links.</p>
                <a href="{{ route('admin.homepage.footer-links.index') }}" class="btn btn-primary btn-sm btn-block mb-2">
                    <i class="fas fa-link mr-1"></i> Footer Links
                </a>
                <a href="{{ route('admin.newsletter.index') }}" class="btn btn-info btn-sm btn-block">
                    <i class="fas fa-envelope mr-1"></i> Newsletter Subscribers
                </a>
            </div>
        </div>
    </div>
</div>
