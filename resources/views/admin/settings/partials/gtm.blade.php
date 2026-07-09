<div class="row">
    <div class="col-lg-8">
        <div class="card card-warning card-outline">
            <div class="card-header"><h3 class="card-title">Google Tag Manager</h3></div>
            <div class="card-body">
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="gtm_enabled" name="gtm_enabled" value="1" @checked(old('gtm_enabled', $settings->gtm_enabled ?? false))>
                        <label class="custom-control-label" for="gtm_enabled">Enable Google Tag Manager</label>
                    </div>
                    <small class="text-muted d-block mt-1">Saved in site settings (database). No <code>.env</code> entry needed — works the same after git push/pull.</small>
                </div>
                <div class="form-group mb-0">
                    <label>Container ID</label>
                    <input
                        type="text"
                        name="gtm_container_id"
                        class="form-control @error('gtm_container_id') is-invalid @enderror"
                        value="{{ old('gtm_container_id', $settings->gtm_container_id) }}"
                        placeholder="GTM-XXXXXXX"
                        pattern="^GTM-[A-Z0-9]+$"
                        style="max-width:220px"
                    >
                    @error('gtm_container_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    <small class="text-muted">Find this in your <a href="https://tagmanager.google.com/" target="_blank" rel="noopener noreferrer">Google Tag Manager</a> workspace.</small>
                </div>
            </div>
        </div>
    </div>
</div>
