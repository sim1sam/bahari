@extends('layouts.admin')

@section('title', 'Shipping Settings')
@section('page_title', 'Shipping Settings')

@section('content')
    <form action="{{ route('admin.shipping.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-lg-8">
                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h3 class="card-title mb-0">Delivery Zones (BDT)</h3>
                    </div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Inside Dhaka Shipping Fee *</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text">৳</span></div>
                                        <input
                                            type="number"
                                            name="shipping_fee_inside_dhaka"
                                            class="form-control"
                                            min="0"
                                            step="0.01"
                                            value="{{ old('shipping_fee_inside_dhaka', $settings->shipping_fee_inside_dhaka ?? 80) }}"
                                            required
                                        >
                                    </div>
                                    <small class="text-muted">Applied when customer selects Inside Dhaka at checkout.</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Outside Dhaka Shipping Fee *</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text">৳</span></div>
                                        <input
                                            type="number"
                                            name="shipping_fee_outside_dhaka"
                                            class="form-control"
                                            min="0"
                                            step="0.01"
                                            value="{{ old('shipping_fee_outside_dhaka', $settings->shipping_fee_outside_dhaka ?? 150) }}"
                                            required
                                        >
                                    </div>
                                    <small class="text-muted">Applied when customer selects Outside Dhaka at checkout.</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label>Free Shipping Threshold *</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text">৳</span></div>
                                        <input
                                            type="number"
                                            name="free_shipping_threshold"
                                            class="form-control"
                                            min="0"
                                            step="0.01"
                                            value="{{ old('free_shipping_threshold', $settings->free_shipping_threshold ?? 2000) }}"
                                            required
                                        >
                                    </div>
                                    <small class="text-muted">Orders at or above this subtotal get free shipping (both zones).</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Save Shipping Settings</button>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card card-outline card-info">
                    <div class="card-header"><h3 class="card-title mb-0">How it works</h3></div>
                    <div class="card-body">
                        <ul class="mb-0 pl-3">
                            <li class="mb-2">Customers choose <strong>Inside Dhaka</strong> or <strong>Outside Dhaka</strong> on cart and checkout.</li>
                            <li class="mb-2">The matching fee is added to the order total.</li>
                            <li>Admin manual orders also use these zone fees when recalculating totals.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
