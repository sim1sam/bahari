@extends('layouts.admin')

@section('title', 'Shipping Settings')
@section('page_title', 'Shipping Settings')

@section('content')
    @php
        $insideFee = old('shipping_fee_inside_dhaka', $settings->shipping_fee_inside_dhaka ?? 80);
        $outsideFee = old('shipping_fee_outside_dhaka', $settings->shipping_fee_outside_dhaka ?? 150);
        $freeThreshold = old('free_shipping_threshold', $settings->free_shipping_threshold ?? 2000);
    @endphp

    <div class="settings-page">
        <section class="settings-hero">
            <div>
                <span class="settings-eyebrow">Settings</span>
                <h2>Shipping Fee (BDT)</h2>
                <p>Configure delivery zone fees and free shipping threshold for Bangladesh checkout.</p>
                <div class="settings-hero-meta">
                    <span class="settings-hero-chip"><i class="fas fa-map-marker-alt"></i> Inside Dhaka ৳{{ $insideFee }}</span>
                    <span class="settings-hero-chip"><i class="fas fa-truck"></i> Outside Dhaka ৳{{ $outsideFee }}</span>
                </div>
            </div>
            <div class="settings-hero-actions">
                <a href="{{ route('admin.orders.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus mr-1"></i> Create Order
                </a>
            </div>
        </section>

        <section class="row mb-3">
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="settings-stat settings-stat--teal">
                    <span class="settings-stat-icon"><i class="fas fa-map-marker-alt"></i></span>
                    <div>
                        <div class="settings-stat-value">৳{{ $insideFee }}</div>
                        <div class="settings-stat-label">Inside Dhaka</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="settings-stat settings-stat--blue">
                    <span class="settings-stat-icon"><i class="fas fa-truck"></i></span>
                    <div>
                        <div class="settings-stat-value">৳{{ $outsideFee }}</div>
                        <div class="settings-stat-label">Outside Dhaka</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="settings-stat settings-stat--green">
                    <span class="settings-stat-icon"><i class="fas fa-gift"></i></span>
                    <div>
                        <div class="settings-stat-value">৳{{ $freeThreshold }}</div>
                        <div class="settings-stat-label">Free Shipping At</div>
                    </div>
                </article>
            </div>
        </section>

        @include('admin.settings.partials.nav')

        <form action="{{ route('admin.shipping.update') }}" method="POST">
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
                                    <span class="settings-form-panel-icon settings-form-panel-icon--shipping"><i class="fas fa-truck"></i></span>
                                    <div>
                                        <h4>Delivery Zones (BDT)</h4>
                                        <p>Shipping fees applied when customers select a delivery zone at checkout.</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="settings-field">
                                            <label for="shipping_fee_inside_dhaka">Inside Dhaka Shipping Fee *</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend"><span class="input-group-text">৳</span></div>
                                                <input
                                                    type="number"
                                                    name="shipping_fee_inside_dhaka"
                                                    id="shipping_fee_inside_dhaka"
                                                    class="form-control"
                                                    min="0"
                                                    step="0.01"
                                                    value="{{ $insideFee }}"
                                                    required
                                                >
                                            </div>
                                            <small class="settings-field-hint">Applied when customer selects Inside Dhaka at checkout.</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="settings-field">
                                            <label for="shipping_fee_outside_dhaka">Outside Dhaka Shipping Fee *</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend"><span class="input-group-text">৳</span></div>
                                                <input
                                                    type="number"
                                                    name="shipping_fee_outside_dhaka"
                                                    id="shipping_fee_outside_dhaka"
                                                    class="form-control"
                                                    min="0"
                                                    step="0.01"
                                                    value="{{ $outsideFee }}"
                                                    required
                                                >
                                            </div>
                                            <small class="settings-field-hint">Applied when customer selects Outside Dhaka at checkout.</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="settings-field mb-0">
                                            <label for="free_shipping_threshold">Free Shipping Threshold *</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend"><span class="input-group-text">৳</span></div>
                                                <input
                                                    type="number"
                                                    name="free_shipping_threshold"
                                                    id="free_shipping_threshold"
                                                    class="form-control"
                                                    min="0"
                                                    step="0.01"
                                                    value="{{ $freeThreshold }}"
                                                    required
                                                >
                                            </div>
                                            <small class="settings-field-hint">Orders at or above this subtotal get free shipping (both zones).</small>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                        <div class="settings-card-footer">
                            <button type="submit" class="btn btn-info btn-lg">
                                <i class="fas fa-save mr-1"></i> Save Shipping Settings
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 mb-3">
                    <div class="settings-side-card">
                        <div class="settings-side-head">
                            <span class="settings-side-icon settings-side-icon--info"><i class="fas fa-info-circle"></i></span>
                            <div>
                                <h4>How It Works</h4>
                                <p>Delivery zone logic at checkout.</p>
                            </div>
                        </div>
                        <div class="settings-side-body">
                            <ul class="settings-side-list">
                                <li>Customers choose <strong>Inside Dhaka</strong> or <strong>Outside Dhaka</strong> on cart and checkout.</li>
                                <li>The matching fee is added to the order total.</li>
                                <li>Orders above the free shipping threshold pay ৳0 delivery.</li>
                                <li>Admin manual orders also use these zone fees when recalculating totals.</li>
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
