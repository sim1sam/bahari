@extends('layouts.admin')

@section('title', 'Homepage')
@section('page_title', 'Homepage Management')

@section('content')
    <div class="row">
        <div class="col-md-6 col-lg-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-images fa-2x text-primary mb-3"></i>
                    <h5>Hero Sliders</h5>
                    <p class="text-muted">{{ $sliderCount }} slide(s)</p>
                    <a href="{{ route('admin.homepage.sliders.index') }}" class="btn btn-primary btn-sm">Manage</a>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-percent fa-2x text-warning mb-3"></i>
                    <h5>Discount Banners</h5>
                    <p class="text-muted">{{ $bannerCount }} banner(s)</p>
                    <a href="{{ route('admin.homepage.banners.index') }}" class="btn btn-warning btn-sm">Manage</a>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-star fa-2x text-info mb-3"></i>
                    <h5>Trust Features</h5>
                    <p class="text-muted">{{ $featureCount }} feature(s)</p>
                    <a href="{{ route('admin.homepage.features.index') }}" class="btn btn-info btn-sm">Manage</a>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-link fa-2x text-secondary mb-3"></i>
                    <h5>Footer Links</h5>
                    <p class="text-muted">{{ $footerLinkCount }} link(s)</p>
                    <a href="{{ route('admin.homepage.footer-links.index') }}" class="btn btn-secondary btn-sm">Manage</a>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-envelope fa-2x text-success mb-3"></i>
                    <h5>Newsletter</h5>
                    <p class="text-muted">{{ $subscriberCount }} subscriber(s)</p>
                    <a href="{{ route('admin.newsletter.index') }}" class="btn btn-success btn-sm">View Subscribers</a>
                </div>
            </div>
        </div>
    </div>
    <div class="card mt-3">
        <div class="card-body">
            <p class="mb-0 text-muted">Newsletter text and footer description are managed in <a href="{{ route('admin.settings.edit') }}">Site Settings</a>.</p>
        </div>
    </div>
@endsection
