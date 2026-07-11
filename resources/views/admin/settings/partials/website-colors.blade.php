<div class="row">
    <div class="col-lg-8 mb-3">
        <div class="settings-card">
            <div class="settings-card-body">
                <section class="settings-form-panel mb-0">
                    <div class="settings-form-panel-head">
                        <span class="settings-form-panel-icon settings-form-panel-icon--colors"><i class="fas fa-palette"></i></span>
                        <div>
                            <h4>Website Colors</h4>
                            <p>These colors apply across the storefront — buttons, links, hero slider, and footer.</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            @include('admin.settings.partials.color-field', [
                                'name' => 'theme_primary',
                                'label' => 'Primary Color',
                                'value' => $settings->theme_primary,
                                'default' => '#0891b2',
                                'hint' => 'Buttons, links, accents',
                            ])
                        </div>
                        <div class="col-md-4">
                            @include('admin.settings.partials.color-field', [
                                'name' => 'theme_primary_dark',
                                'label' => 'Primary Dark',
                                'value' => $settings->theme_primary_dark,
                                'default' => '#164e63',
                                'hint' => 'Hero slider, dark sections',
                            ])
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
                    <div class="settings-field mb-0">
                        <label>Theme Preview</label>
                        <div class="settings-theme-preview" id="theme-preview" style="background:{{ old('theme_background', $settings->theme_background ?? '#f8fafc') }}">
                            <span class="px-4 py-2 rounded-lg text-white text-sm font-weight-bold" data-preview="button" style="background:{{ old('theme_primary', $settings->theme_primary ?? '#0891b2') }}">Primary Button</span>
                            <span class="text-sm font-weight-bold" data-preview="link" style="color:{{ old('theme_primary', $settings->theme_primary ?? '#0891b2') }}">Primary Link</span>
                            <span class="px-3 py-1 rounded text-sm" data-preview="badge" style="background:color-mix(in srgb, {{ old('theme_primary', $settings->theme_primary ?? '#0891b2') }} 10%, white); color:{{ old('theme_primary', $settings->theme_primary ?? '#0891b2') }}">Light Badge</span>
                            <span class="px-4 py-2 rounded-lg text-white text-sm" data-preview="footer" style="background:{{ old('theme_footer_bg', $settings->theme_footer_bg ?? '#1c1917') }}">Footer</span>
                            <span class="text-sm" data-preview="text" style="color:{{ old('theme_text', $settings->theme_text ?? '#1c1917') }}">Body text sample</span>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-3">
        <div class="settings-side-card">
            <div class="settings-side-head">
                <span class="settings-side-icon settings-side-icon--info"><i class="fas fa-fill-drip"></i></span>
                <div>
                    <h4>Color Tokens</h4>
                    <p>How each color is used on the storefront.</p>
                </div>
            </div>
            <div class="settings-side-body">
                <ul class="settings-side-list">
                    <li><strong>Primary</strong> — CTA buttons, links, and accent highlights.</li>
                    <li><strong>Primary Dark</strong> — Hero slider overlays and dark sections.</li>
                    <li><strong>Footer BG</strong> — Storefront footer background.</li>
                    <li><strong>Text / Background</strong> — Body copy and page canvas.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
