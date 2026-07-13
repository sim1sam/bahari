@extends('layouts.admin')

@section('title', 'Watermark')
@section('page_title', 'Watermark')

@section('content')
    <div class="settings-page">
        <section class="settings-hero">
            <div>
                <span class="settings-eyebrow">Settings</span>
                <h2>Watermark</h2>
                <p>Upload your brand logo and set how large it appears when processing imported product images.</p>
                <div class="settings-hero-meta">
                    <span class="settings-hero-chip"><i class="fas fa-image"></i> {{ $logoUrl ? 'Logo set' : 'No logo' }}</span>
                    <span class="settings-hero-chip"><i class="fas fa-expand-arrows-alt"></i> {{ $logoScale }}% size</span>
                </div>
            </div>
            <div class="settings-hero-actions">
                <a href="{{ route('admin.content.index') }}" class="btn btn-primary">
                    <i class="fas fa-images mr-1"></i> Import Product
                </a>
            </div>
        </section>

        @include('admin.settings.partials.nav')

        <div class="row">
            <div class="col-lg-6 mb-3">
                <div class="settings-card h-100">
                    <div class="settings-card-body">
                        <section class="settings-form-panel mb-0">
                            <div class="settings-form-panel-head">
                                <span class="settings-form-panel-icon settings-form-panel-icon--teal"><i class="fas fa-stamp"></i></span>
                                <div>
                                    <h4>Watermark Logo</h4>
                                    <p>PNG, JPG, or WEBP up to 2MB. Applied centered on each processed image.</p>
                                </div>
                            </div>

                            <div class="watermark-logo-frame {{ $logoUrl ? 'watermark-logo-frame--has-logo' : '' }}">
                                @if ($logoUrl)
                                    <img src="{{ $logoUrl }}" alt="Watermark logo" id="watermark-logo-preview-img">
                                @else
                                    <div class="watermark-logo-placeholder" id="watermark-logo-placeholder">
                                        <i class="fas fa-image"></i>
                                        <span>No watermark uploaded</span>
                                    </div>
                                @endif
                            </div>

                            <form action="{{ route('admin.settings.watermark.logo') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <label class="watermark-dropzone" for="watermark-logo-file">
                                    <input type="file" name="logo" id="watermark-logo-file" class="watermark-dropzone-input" accept="image/*" required>
                                    <span class="watermark-dropzone-icon"><i class="fas fa-file-image"></i></span>
                                    <span class="watermark-dropzone-title">Drop logo here or browse</span>
                                    <span class="watermark-dropzone-hint" id="watermark-logo-filename">PNG, JPG, WEBP up to 2MB</span>
                                </label>
                                <button type="submit" class="btn btn-info btn-block">
                                    <i class="fas fa-upload mr-1"></i> Upload Watermark
                                </button>
                            </form>
                        </section>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-3">
                <div class="settings-card h-100">
                    <div class="settings-card-body">
                        <section class="settings-form-panel mb-0">
                            <div class="settings-form-panel-head">
                                <span class="settings-form-panel-icon settings-form-panel-icon--blue"><i class="fas fa-expand-arrows-alt"></i></span>
                                <div>
                                    <h4>Watermark Size</h4>
                                    <p>How large the watermark appears on each processed image (10–50% of image width).</p>
                                </div>
                            </div>

                            <form action="{{ route('admin.settings.watermark.logo-scale') }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="watermark-scale-control">
                                    <input type="range" name="api_logo_scale" class="watermark-scale-range" min="10" max="50" step="1" value="{{ old('api_logo_scale', $logoScale) }}" id="watermark-logo-scale-range">
                                    <div class="watermark-scale-value">
                                        <input type="number" class="form-control form-control-sm watermark-scale-number" min="10" max="50" step="1" value="{{ old('api_logo_scale', $logoScale) }}" id="watermark-logo-scale-number" aria-label="Watermark scale percent">
                                        <span>%</span>
                                    </div>
                                </div>
                                <div class="watermark-scale-track">
                                    <span style="width: {{ $logoScale }}%" id="watermark-scale-fill"></span>
                                </div>
                                <button type="submit" class="btn btn-info">
                                    <i class="fas fa-save mr-1"></i> Save Size
                                </button>
                            </form>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    @include('admin.settings.partials.page-styles')
    <style>
        .watermark-logo-frame {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 8rem;
            margin-bottom: 1rem;
            border: 2px dashed #cbd5e1;
            border-radius: 0.85rem;
            background: linear-gradient(135deg, rgba(236, 254, 255, 0.65) 0%, rgba(248, 250, 252, 1) 100%);
            padding: 0.75rem;
        }

        .watermark-logo-frame--has-logo {
            border-style: solid;
            border-color: #a5f3fc;
            background: #fff;
        }

        .watermark-logo-frame img {
            max-height: 5rem;
            max-width: 100%;
            object-fit: contain;
        }

        .watermark-logo-placeholder {
            text-align: center;
            color: #94a3b8;
            font-size: 0.82rem;
        }

        .watermark-logo-placeholder i {
            display: block;
            margin-bottom: 0.35rem;
            font-size: 1.35rem;
            opacity: 0.55;
        }

        .watermark-dropzone {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.25rem;
            margin-bottom: 0.85rem;
            padding: 1rem 0.75rem;
            border: 1.5px dashed #94a3b8;
            border-radius: 0.85rem;
            background: #fff;
            cursor: pointer;
            transition: border-color 0.15s ease, background 0.15s ease;
        }

        .watermark-dropzone:hover,
        .watermark-dropzone.watermark-dropzone--active {
            border-color: #22d3ee;
            background: #ecfeff;
        }

        .watermark-dropzone-input {
            position: absolute;
            width: 1px;
            height: 1px;
            opacity: 0;
            pointer-events: none;
        }

        .watermark-dropzone-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.4rem;
            height: 2.4rem;
            border-radius: 999px;
            background: #ecfeff;
            color: #0891b2;
            font-size: 1rem;
        }

        .watermark-dropzone-title {
            color: #334155;
            font-size: 0.82rem;
            font-weight: 700;
        }

        .watermark-dropzone-hint {
            color: #94a3b8;
            font-size: 0.72rem;
        }

        .watermark-scale-control {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.5rem;
        }

        .watermark-scale-range {
            flex: 1;
            accent-color: #0891b2;
            cursor: pointer;
        }

        .watermark-scale-value {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            flex-shrink: 0;
        }

        .watermark-scale-number {
            width: 3.5rem;
            text-align: center;
            font-weight: 700;
            border-radius: 0.5rem;
        }

        .watermark-scale-value > span {
            color: #64748b;
            font-size: 0.85rem;
            font-weight: 700;
        }

        .watermark-scale-track {
            height: 0.35rem;
            margin-bottom: 1rem;
            border-radius: 999px;
            background: #e2e8f0;
            overflow: hidden;
        }

        .watermark-scale-track > span {
            display: block;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #0891b2, #22d3ee);
            transition: width 0.15s ease;
        }
    </style>
@endpush

@push('scripts')
<script>
(function () {
    var input = document.getElementById('watermark-logo-file');
    if (input) {
        var dropzone = input.closest('.watermark-dropzone');
        var filenameEl = document.getElementById('watermark-logo-filename');
        var previewImg = document.getElementById('watermark-logo-preview-img');
        var placeholder = document.getElementById('watermark-logo-placeholder');
        var logoFrame = document.querySelector('.watermark-logo-frame');

        input.addEventListener('change', function () {
            var file = this.files && this.files[0];
            if (filenameEl) {
                filenameEl.textContent = file ? file.name : 'PNG, JPG, WEBP up to 2MB';
            }
            if (!file || !file.type.startsWith('image/')) return;

            var reader = new FileReader();
            reader.onload = function (event) {
                if (previewImg) {
                    previewImg.src = event.target.result;
                } else if (logoFrame) {
                    if (placeholder) placeholder.remove();
                    var img = document.createElement('img');
                    img.id = 'watermark-logo-preview-img';
                    img.alt = 'Watermark preview';
                    img.src = event.target.result;
                    logoFrame.appendChild(img);
                    logoFrame.classList.add('watermark-logo-frame--has-logo');
                }
            };
            reader.readAsDataURL(file);
        });

        if (dropzone) {
            dropzone.addEventListener('dragover', function (e) {
                e.preventDefault();
                dropzone.classList.add('watermark-dropzone--active');
            });
            dropzone.addEventListener('dragleave', function () {
                dropzone.classList.remove('watermark-dropzone--active');
            });
            dropzone.addEventListener('drop', function (e) {
                e.preventDefault();
                dropzone.classList.remove('watermark-dropzone--active');
            });
        }
    }

    var scaleRange = document.getElementById('watermark-logo-scale-range');
    var scaleNumber = document.getElementById('watermark-logo-scale-number');
    var scaleFill = document.getElementById('watermark-scale-fill');

    function syncScale(value) {
        var num = Math.min(50, Math.max(10, parseInt(value, 10) || 28));
        if (scaleRange) scaleRange.value = num;
        if (scaleNumber) scaleNumber.value = num;
        if (scaleFill) scaleFill.style.width = num + '%';
    }

    if (scaleRange) {
        scaleRange.addEventListener('input', function () { syncScale(this.value); });
    }
    if (scaleNumber) {
        scaleNumber.addEventListener('input', function () { syncScale(this.value); });
    }
})();
</script>
@endpush
