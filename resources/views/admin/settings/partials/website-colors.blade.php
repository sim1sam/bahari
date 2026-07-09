<div class="row">
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
</div>
