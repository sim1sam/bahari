@php $gtmEnabled = (bool) old('gtm_enabled', $settings->gtm_enabled ?? false); @endphp

<div class="row">
    <div class="col-lg-8 mb-3">
        <div class="settings-card">
            <div class="settings-card-body">
                <section class="settings-form-panel mb-0">
                    <div class="settings-form-panel-head">
                        <span class="settings-form-panel-icon settings-form-panel-icon--gtm"><i class="fab fa-google"></i></span>
                        <div>
                            <h4>Google Tag Manager</h4>
                            <p>Inject GTM container scripts on the storefront for analytics and marketing tags.</p>
                        </div>
                    </div>
                    <div class="settings-toggle-card {{ $gtmEnabled ? 'settings-toggle-card--on' : '' }}">
                        <div class="settings-toggle-copy">
                            <h5>Enable Google Tag Manager</h5>
                            <p>Load the GTM container on all storefront pages.</p>
                        </div>
                        <label class="settings-toggle" for="gtm_enabled">
                            <input type="checkbox" class="settings-toggle-input" id="gtm_enabled" name="gtm_enabled" value="1" @checked($gtmEnabled)>
                            <span class="settings-toggle-track"><span class="settings-toggle-thumb"></span></span>
                            <span class="settings-toggle-label">{{ $gtmEnabled ? 'Enabled' : 'Disabled' }}</span>
                        </label>
                    </div>
                    <small class="settings-field-hint d-block mb-3">Saved in site settings (database). No <code>.env</code> entry needed — works the same after git push/pull.</small>
                    <div class="settings-field mb-0">
                        <label for="gtm_container_id">Container ID</label>
                        <div class="settings-input-wrap" style="max-width:280px">
                            <span class="settings-input-icon"><i class="fas fa-tag"></i></span>
                            <input
                                type="text"
                                name="gtm_container_id"
                                id="gtm_container_id"
                                class="form-control @error('gtm_container_id') is-invalid @enderror"
                                value="{{ old('gtm_container_id', $settings->gtm_container_id) }}"
                                placeholder="GTM-XXXXXXX"
                                pattern="^GTM-[A-Z0-9]+$"
                            >
                        </div>
                        @error('gtm_container_id')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        <small class="settings-field-hint">
                            Find this in your <a href="https://tagmanager.google.com/" target="_blank" rel="noopener noreferrer">Google Tag Manager</a> workspace.
                        </small>
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
                    <h4>How It Works</h4>
                    <p>What happens when GTM is enabled.</p>
                </div>
            </div>
            <div class="settings-side-body">
                <p class="settings-side-text">The GTM container script is injected in the storefront <code>&lt;head&gt;</code> and <code>&lt;body&gt;</code>. Configure tags, triggers, and variables inside your GTM workspace.</p>
                <p class="settings-side-text mb-0">Container ID format: <code>GTM-XXXXXXX</code></p>
            </div>
        </div>
    </div>
</div>
