@extends('layouts.admin')

@section('title', 'Homepage')
@section('page_title', 'Homepage Management')

@section('content')
    <div class="settings-page">
        <section class="settings-hero">
            <div>
                <span class="settings-eyebrow">Settings</span>
                <h2>Homepage</h2>
                <p>Manage hero sliders, banners, trust features, footer links, and newsletter subscribers.</p>
                <div class="settings-hero-meta">
                    <span class="settings-hero-chip"><i class="fas fa-images"></i> {{ $sliderCount }} slider{{ $sliderCount === 1 ? '' : 's' }}</span>
                    <span class="settings-hero-chip"><i class="fas fa-envelope"></i> {{ $subscriberCount }} subscriber{{ $subscriberCount === 1 ? '' : 's' }}</span>
                </div>
            </div>
            <div class="settings-hero-actions">
                <a href="{{ route('admin.settings.footer.edit') }}" class="btn btn-primary">
                    <i class="fas fa-shoe-prints mr-1"></i> Footer Settings
                </a>
                <a href="{{ url('/') }}" target="_blank" rel="noopener noreferrer" class="btn btn-info">
                    <i class="fas fa-external-link-alt mr-1"></i> View Storefront
                </a>
            </div>
        </section>

        <section class="row mb-3">
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="settings-stat settings-stat--teal">
                    <span class="settings-stat-icon"><i class="fas fa-images"></i></span>
                    <div>
                        <div class="settings-stat-value">{{ $sliderCount }}</div>
                        <div class="settings-stat-label">Hero Sliders</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="settings-stat settings-stat--amber">
                    <span class="settings-stat-icon"><i class="fas fa-percent"></i></span>
                    <div>
                        <div class="settings-stat-value">{{ $bannerCount }}</div>
                        <div class="settings-stat-label">Discount Banners</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="settings-stat settings-stat--blue">
                    <span class="settings-stat-icon"><i class="fas fa-star"></i></span>
                    <div>
                        <div class="settings-stat-value">{{ $featureCount }}</div>
                        <div class="settings-stat-label">Trust Features</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="settings-stat settings-stat--green">
                    <span class="settings-stat-icon"><i class="fas fa-envelope"></i></span>
                    <div>
                        <div class="settings-stat-value">{{ $subscriberCount }}</div>
                        <div class="settings-stat-label">Subscribers</div>
                    </div>
                </article>
            </div>
        </section>

        @include('admin.settings.partials.nav')

        <div class="settings-module-grid">
            <article class="settings-module-card">
                <span class="settings-module-icon settings-module-icon--sliders"><i class="fas fa-images"></i></span>
                <h5>Hero Sliders</h5>
                <p class="settings-module-count">{{ $sliderCount }} slide{{ $sliderCount === 1 ? '' : 's' }}</p>
                <a href="{{ route('admin.homepage.sliders.index') }}" class="btn btn-info btn-sm mt-auto">Manage</a>
            </article>
            <article class="settings-module-card">
                <span class="settings-module-icon settings-module-icon--banners"><i class="fas fa-percent"></i></span>
                <h5>Discount Banners</h5>
                <p class="settings-module-count">{{ $bannerCount }} banner{{ $bannerCount === 1 ? '' : 's' }}</p>
                <a href="{{ route('admin.homepage.banners.index') }}" class="btn btn-warning btn-sm mt-auto">Manage</a>
            </article>
            <article class="settings-module-card">
                <span class="settings-module-icon settings-module-icon--features"><i class="fas fa-star"></i></span>
                <h5>Trust Features</h5>
                <p class="settings-module-count">{{ $featureCount }} feature{{ $featureCount === 1 ? '' : 's' }}</p>
                <a href="{{ route('admin.homepage.features.index') }}" class="btn btn-primary btn-sm mt-auto">Manage</a>
            </article>
            <article class="settings-module-card">
                <span class="settings-module-icon settings-module-icon--links"><i class="fas fa-link"></i></span>
                <h5>Footer Links</h5>
                <p class="settings-module-count">{{ $footerLinkCount }} link{{ $footerLinkCount === 1 ? '' : 's' }}</p>
                <a href="{{ route('admin.homepage.footer-links.index') }}" class="btn btn-secondary btn-sm mt-auto">Manage</a>
            </article>
            <article class="settings-module-card">
                <span class="settings-module-icon settings-module-icon--newsletter"><i class="fas fa-envelope"></i></span>
                <h5>Newsletter</h5>
                <p class="settings-module-count">{{ $subscriberCount }} subscriber{{ $subscriberCount === 1 ? '' : 's' }}</p>
                <a href="{{ route('admin.newsletter.index') }}" class="btn btn-success btn-sm mt-auto">View Subscribers</a>
            </article>
        </div>

        <div class="settings-note mt-3">
            Newsletter text and footer description are managed in <a href="{{ route('admin.settings.footer.edit') }}">Settings → Footer</a>.
        </div>
    </div>
@endsection

@push('styles')
    @include('admin.settings.partials.page-styles')
@endpush
