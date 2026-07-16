@extends('layouts.admin')

@section('title', 'Shop Page')
@section('page_title', 'Shop Page')

@section('content')
    <div class="settings-page">
        <section class="settings-hero">
            <div>
                <span class="settings-eyebrow">Marketing</span>
                <h2>Shop Page</h2>
                <p>Page settings for /shop. Manage products and brand schedules from the submenus. Brand images are uploaded in API Brands.</p>
                <div class="settings-hero-meta">
                    <span class="settings-hero-chip"><i class="fas fa-toggle-{{ $settings->is_enabled ? 'on' : 'off' }}"></i> {{ $settings->is_enabled ? 'Enabled' : 'Disabled' }}</span>
                    <span class="settings-hero-chip"><i class="fas fa-tshirt"></i> {{ $selectedProductCount ?? 0 }} products</span>
                    <span class="settings-hero-chip"><i class="fas fa-copyright"></i> {{ $activeBrandCount }} brands</span>
                    <a href="{{ route('shop.index') }}" target="_blank" class="settings-hero-chip"><i class="fas fa-external-link-alt"></i> View shop</a>
                </div>
            </div>
        </section>

        <div class="row mb-3">
            <div class="col-md-4 mb-3">
                <div class="settings-card h-100">
                    <div class="settings-card-body">
                        <h4><i class="fas fa-tshirt text-info mr-1"></i> Shop Products</h4>
                        <p class="text-muted small">List-wise product select — pin products to show on /shop.</p>
                        <a href="{{ route('admin.shop-page.products.edit') }}" class="btn btn-info btn-block">
                            <i class="fas fa-list mr-1"></i> Select products
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="settings-card h-100">
                    <div class="settings-card-body">
                        <h4><i class="fas fa-copyright text-info mr-1"></i> Shop Brands</h4>
                        <p class="text-muted small">Brand-wise schedules with date ranges for /shop.</p>
                        <a href="{{ route('admin.shop-page.brands.edit') }}" class="btn btn-info btn-block">
                            <i class="fas fa-calendar-alt mr-1"></i> Manage brands
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="settings-card h-100">
                    <div class="settings-card-body">
                        <h4><i class="fas fa-image text-info mr-1"></i> Brand Images</h4>
                        <p class="text-muted small">Upload brand images used as left cards on /shop.</p>
                        <a href="{{ route('admin.api-brands.index') }}" class="btn btn-info btn-block">
                            <i class="fas fa-tags mr-1"></i> API Brands
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.shop-page.update') }}" method="POST" id="shop-page-form">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-lg-8 mb-3">
                    <div class="settings-card">
                        <div class="settings-card-body">
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <section class="settings-form-panel mb-0">
                                <div class="settings-form-panel-head">
                                    <span class="settings-form-panel-icon"><i class="fas fa-store"></i></span>
                                    <div>
                                        <h4>Page visibility &amp; copy</h4>
                                        <p>Control /shop title text and collection section labels.</p>
                                    </div>
                                </div>

                                <div class="custom-control custom-switch mb-3">
                                    <input type="hidden" name="is_enabled" value="0">
                                    <input type="checkbox" class="custom-control-input" id="is_enabled" name="is_enabled" value="1" @checked(old('is_enabled', $settings->is_enabled))>
                                    <label class="custom-control-label" for="is_enabled">Show Shop page &amp; navbar link</label>
                                </div>

                                <div class="settings-field">
                                    <label for="hero_title">Page title *</label>
                                    <input type="text" name="hero_title" id="hero_title" class="form-control" value="{{ old('hero_title', $settings->hero_title) }}" required>
                                </div>
                                <div class="settings-field">
                                    <label for="hero_subtitle">Page subtitle</label>
                                    <textarea name="hero_subtitle" id="hero_subtitle" class="form-control" rows="2">{{ old('hero_subtitle', $settings->hero_subtitle) }}</textarea>
                                </div>
                                <div class="settings-field">
                                    <label for="hero_cta_label">Button label</label>
                                    <input type="text" name="hero_cta_label" id="hero_cta_label" class="form-control" value="{{ old('hero_cta_label', $settings->hero_cta_label) }}" placeholder="Browse products">
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="settings-field mb-0">
                                            <label for="section_title">Products section title</label>
                                            <input type="text" name="section_title" id="section_title" class="form-control" value="{{ old('section_title', $settings->section_title) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="settings-field mb-0">
                                            <label for="section_subtitle">Products section subtitle</label>
                                            <input type="text" name="section_subtitle" id="section_subtitle" class="form-control" value="{{ old('section_subtitle', $settings->section_subtitle) }}">
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                        <div class="settings-card-footer">
                            <button type="submit" class="btn btn-info btn-lg">
                                <i class="fas fa-save mr-1"></i> Save Shop Page
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 mb-3">
                    <div class="settings-side-card">
                        <div class="settings-side-head">
                            <span class="settings-side-icon settings-side-icon--info"><i class="fas fa-info-circle"></i></span>
                            <div>
                                <h4>How images work</h4>
                                <p>Brand cards on /shop.</p>
                            </div>
                        </div>
                        <div class="settings-side-body">
                            <ul class="settings-side-list">
                                <li><strong>Shop Products</strong> — pin products</li>
                                <li><strong>Shop Brands</strong> — date schedules</li>
                                <li><strong>API Brands</strong> — upload brand images</li>
                                <li>Left card on /shop uses the brand image</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('styles')
    @include('admin.settings.partials.page-styles')
@endpush
