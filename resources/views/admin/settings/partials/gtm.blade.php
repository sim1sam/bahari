@php
    $gtmEnabled = (bool) old('gtm_enabled', $settings->gtm_enabled ?? false);
    $metaCapiEnabled = (bool) old('meta_capi_enabled', $settings->meta_capi_enabled ?? false);
    try {
        $hasMetaToken = filled($settings->meta_capi_access_token) || filled(config('services.meta.access_token'));
    } catch (Throwable) {
        $hasMetaToken = filled(config('services.meta.access_token'));
    }
    $metaPixelConfigured = filled(config('services.meta.pixel_id'));
@endphp

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
                    <small class="settings-field-hint d-block mb-3">Saved in site settings (database). FB Pixel &amp; GA4 IDs stay in your GTM workspace.</small>
                    <div class="settings-field mb-4">
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

                    <div class="settings-form-panel-head mt-2">
                        <span class="settings-form-panel-icon settings-form-panel-icon--gtm"><i class="fab fa-facebook"></i></span>
                        <div>
                            <h4>Meta Conversions API (server)</h4>
                            <p>Server-side events with the same <code>event_id</code> as web GTM for deduplication. Browser Pixel stays in GTM.</p>
                        </div>
                    </div>
                    <div class="settings-toggle-card {{ $metaCapiEnabled ? 'settings-toggle-card--on' : '' }}">
                        <div class="settings-toggle-copy">
                            <h5>Enable Meta CAPI</h5>
                            <p>Send Purchase and funnel events from Laravel.</p>
                        </div>
                        <label class="settings-toggle" for="meta_capi_enabled">
                            <input type="checkbox" class="settings-toggle-input" id="meta_capi_enabled" name="meta_capi_enabled" value="1" @checked($metaCapiEnabled)>
                            <span class="settings-toggle-track"><span class="settings-toggle-thumb"></span></span>
                            <span class="settings-toggle-label">{{ $metaCapiEnabled ? 'Enabled' : 'Disabled' }}</span>
                        </label>
                    </div>
                    <div class="settings-field mb-3">
                        <label for="meta_capi_access_token">Access token</label>
                        <input
                            type="password"
                            name="meta_capi_access_token"
                            id="meta_capi_access_token"
                            class="form-control @error('meta_capi_access_token') is-invalid @enderror"
                            value=""
                            autocomplete="new-password"
                            placeholder="{{ $hasMetaToken ? '•••••••• (leave blank to keep current)' : 'Paste Meta CAPI access token' }}"
                        >
                        @error('meta_capi_access_token')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        <small class="settings-field-hint">Or set <code>META_CAPI_ACCESS_TOKEN</code> in <code>.env</code>. Leave blank to keep the saved / env token.</small>
                    </div>
                    <div class="settings-field mb-0">
                        <label for="meta_capi_test_event_code">Test event code (optional)</label>
                        <div class="settings-input-wrap" style="max-width:280px">
                            <span class="settings-input-icon"><i class="fas fa-flask"></i></span>
                            <input
                                type="text"
                                name="meta_capi_test_event_code"
                                id="meta_capi_test_event_code"
                                class="form-control @error('meta_capi_test_event_code') is-invalid @enderror"
                                value="{{ old('meta_capi_test_event_code', $settings->meta_capi_test_event_code) }}"
                                placeholder="TEST12345"
                            >
                        </div>
                        @error('meta_capi_test_event_code')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        <small class="settings-field-hint">
                            Leave empty for <strong>live</strong> CAPI (Overview). Set only while testing in Meta → Test events.
                            Pixel ID: <code>META_PIXEL_ID</code> in <code>.env</code>
                            @if ($metaPixelConfigured)
                                (set)
                            @else
                                (missing)
                            @endif
                            — browser Pixel stays in GTM.
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
                    <p>Web GTM + server CAPI together.</p>
                </div>
            </div>
            <div class="settings-side-body">
                <p class="settings-side-text">Existing GTM loads your container. The store pushes ecommerce events into <code>dataLayer</code> so GA4/FB tags fire.</p>
                <p class="settings-side-text">CAPI sends the same events server-side with matching <code>event_id</code> so Meta dedupes browser + server.</p>
                <p class="settings-side-text mb-0"><strong>Avoid duplicates in GTM:</strong> On every FB Pixel tag, set Event ID to <code>@{{dlv - event_id}}</code> (not the Unique Event ID template). Pause the Custom HTML “Meta Pixel ID …” tag if “FB Pixel - All Pages” already fires PageView.</p>
            </div>
        </div>
    </div>
</div>
