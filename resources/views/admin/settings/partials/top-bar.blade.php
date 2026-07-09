<div class="row">
    <div class="col-lg-8">
        <div class="card card-warning card-outline">
            <div class="card-header"><h3 class="card-title">Top Bar</h3></div>
            <div class="card-body">
                <div class="form-group">
                    <label>Top Bar Text (Desktop)</label>
                    <input type="text" name="top_bar_text" class="form-control" value="{{ old('top_bar_text', $settings->top_bar_text) }}" placeholder="Free shipping on orders over $50">
                </div>
                <div class="form-group">
                    <label>Top Bar Text (Mobile)</label>
                    <input type="text" name="top_bar_text_mobile" class="form-control" value="{{ old('top_bar_text_mobile', $settings->top_bar_text_mobile) }}" placeholder="Free shipping $50+">
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Background Color</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="color" class="form-control form-control-color" value="{{ old('top_bar_bg_color', $settings->top_bar_bg_color ?? '#164e63') }}" oninput="this.nextElementSibling.value=this.value">
                                <input type="text" name="top_bar_bg_color" class="form-control" value="{{ old('top_bar_bg_color', $settings->top_bar_bg_color ?? '#164e63') }}" pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$" oninput="this.previousElementSibling.value=this.value">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Text Color</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="color" class="form-control form-control-color" value="{{ old('top_bar_text_color', $settings->top_bar_text_color ?? '#ffffff') }}" oninput="this.nextElementSibling.value=this.value">
                                <input type="text" name="top_bar_text_color" class="form-control" value="{{ old('top_bar_text_color', $settings->top_bar_text_color ?? '#ffffff') }}" pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$" oninput="this.previousElementSibling.value=this.value">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Link Color</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="color" class="form-control form-control-color" value="{{ old('top_bar_link_color', $settings->top_bar_link_color ?? '#cffafe') }}" oninput="this.nextElementSibling.value=this.value">
                                <input type="text" name="top_bar_link_color" class="form-control" value="{{ old('top_bar_link_color', $settings->top_bar_link_color ?? '#cffafe') }}" pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$" oninput="this.previousElementSibling.value=this.value">
                            </div>
                        </div>
                    </div>
                </div>
                <div id="top-bar-preview" class="rounded px-3 py-2 text-sm" style="background:{{ old('top_bar_bg_color', $settings->top_bar_bg_color ?? '#164e63') }};color:{{ old('top_bar_text_color', $settings->top_bar_text_color ?? '#ffffff') }}">
                    <span>{{ old('top_bar_text', $settings->top_bar_text) ?: 'Top bar preview text' }}</span>
                    <span class="ml-3" style="color:{{ old('top_bar_link_color', $settings->top_bar_link_color ?? '#cffafe') }}">Sample link</span>
                </div>
            </div>
        </div>
    </div>
</div>
