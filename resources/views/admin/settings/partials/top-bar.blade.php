<div class="row">
    <div class="col-lg-8 mb-3">
        <div class="settings-card">
            <div class="settings-card-body">
                <section class="settings-form-panel mb-0">
                    <div class="settings-form-panel-head">
                        <span class="settings-form-panel-icon settings-form-panel-icon--topbar"><i class="fas fa-bars"></i></span>
                        <div>
                            <h4>Top Bar</h4>
                            <p>Announcement bar shown above the storefront header on desktop and mobile.</p>
                        </div>
                    </div>
                    <div class="settings-field">
                        <label for="top_bar_text">Top Bar Text (Desktop)</label>
                        <div class="settings-input-wrap">
                            <span class="settings-input-icon"><i class="fas fa-desktop"></i></span>
                            <input type="text" name="top_bar_text" id="top_bar_text" class="form-control" value="{{ old('top_bar_text', $settings->top_bar_text) }}" placeholder="Free shipping on orders over $50">
                        </div>
                    </div>
                    <div class="settings-field">
                        <label for="top_bar_text_mobile">Top Bar Text (Mobile)</label>
                        <div class="settings-input-wrap">
                            <span class="settings-input-icon"><i class="fas fa-mobile-alt"></i></span>
                            <input type="text" name="top_bar_text_mobile" id="top_bar_text_mobile" class="form-control" value="{{ old('top_bar_text_mobile', $settings->top_bar_text_mobile) }}" placeholder="Free shipping $50+">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            @include('admin.settings.partials.color-field', [
                                'name' => 'top_bar_bg_color',
                                'label' => 'Background Color',
                                'value' => $settings->top_bar_bg_color,
                                'default' => '#164e63',
                            ])
                        </div>
                        <div class="col-md-4">
                            @include('admin.settings.partials.color-field', [
                                'name' => 'top_bar_text_color',
                                'label' => 'Text Color',
                                'value' => $settings->top_bar_text_color,
                                'default' => '#ffffff',
                            ])
                        </div>
                        <div class="col-md-4">
                            @include('admin.settings.partials.color-field', [
                                'name' => 'top_bar_link_color',
                                'label' => 'Link Color',
                                'value' => $settings->top_bar_link_color,
                                'default' => '#cffafe',
                            ])
                        </div>
                    </div>
                    <div class="settings-field mb-0">
                        <label>Live Preview</label>
                        <div id="top-bar-preview" class="settings-preview-bar" style="background:{{ old('top_bar_bg_color', $settings->top_bar_bg_color ?? '#164e63') }};color:{{ old('top_bar_text_color', $settings->top_bar_text_color ?? '#ffffff') }}">
                            <span>{{ old('top_bar_text', $settings->top_bar_text) ?: 'Top bar preview text' }}</span>
                            <span class="ml-3" style="color:{{ old('top_bar_link_color', $settings->top_bar_link_color ?? '#cffafe') }}">Sample link</span>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-3">
        <div class="settings-side-card">
            <div class="settings-side-head">
                <span class="settings-side-icon settings-side-icon--info"><i class="fas fa-info-circle"></i></span>
                <div>
                    <h4>Tips</h4>
                    <p>Keep messages short and actionable.</p>
                </div>
            </div>
            <div class="settings-side-body">
                <ul class="settings-side-list">
                    <li>Use mobile text for shorter announcements on small screens.</li>
                    <li>Link color should contrast well against the background.</li>
                    <li>Leave text empty to hide the top bar entirely.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
