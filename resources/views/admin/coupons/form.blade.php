@extends('layouts.admin')

@section('title', $coupon->exists ? 'Edit Coupon' : 'Add Coupon')
@section('page_title', $coupon->exists ? 'Edit Coupon' : 'Add Coupon')

@section('content')
    @php
        $selectedCustomers = old('customer_ids', $selectedCustomers);
        $audience = old('audience', $coupon->audience ?? \App\Models\Coupon::AUDIENCE_PUBLIC);
    @endphp

    <form action="{{ $coupon->exists ? route('admin.coupons.update', $coupon) : route('admin.coupons.store') }}" method="POST">
        @csrf
        @if ($coupon->exists) @method('PUT') @endif

        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Coupon Details</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Coupon Code *</label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $coupon->code) }}" placeholder="EID20" required>
                            @error('code')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label>Label</label>
                            <input type="text" name="label" class="form-control @error('label') is-invalid @enderror" value="{{ old('label', $coupon->label) }}" placeholder="20% off for Eid">
                            @error('label')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Discount Type *</label>
                            <select name="discount_type" class="form-control @error('discount_type') is-invalid @enderror" required>
                                <option value="{{ \App\Models\Coupon::TYPE_PERCENT }}" @selected(old('discount_type', $coupon->discount_type) === \App\Models\Coupon::TYPE_PERCENT)>Percent</option>
                                <option value="{{ \App\Models\Coupon::TYPE_FIXED }}" @selected(old('discount_type', $coupon->discount_type) === \App\Models\Coupon::TYPE_FIXED)>Fixed Amount</option>
                            </select>
                            @error('discount_type')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Discount Value *</label>
                            <input type="number" name="discount_value" class="form-control @error('discount_value') is-invalid @enderror" value="{{ old('discount_value', $coupon->discount_value) }}" min="0.01" step="0.01" required>
                            @error('discount_value')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Audience *</label>
                            <select name="audience" id="coupon-audience" class="form-control @error('audience') is-invalid @enderror" required>
                                <option value="{{ \App\Models\Coupon::AUDIENCE_PUBLIC }}" @selected($audience === \App\Models\Coupon::AUDIENCE_PUBLIC)>Public</option>
                                <option value="{{ \App\Models\Coupon::AUDIENCE_CUSTOMERS }}" @selected($audience === \App\Models\Coupon::AUDIENCE_CUSTOMERS)>Customer Wise</option>
                            </select>
                            @error('audience')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Starts At</label>
                            <input type="datetime-local" name="starts_at" class="form-control @error('starts_at') is-invalid @enderror" value="{{ old('starts_at', $coupon->starts_at?->format('Y-m-d\TH:i')) }}">
                            @error('starts_at')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Ends At</label>
                            <input type="datetime-local" name="ends_at" class="form-control @error('ends_at') is-invalid @enderror" value="{{ old('ends_at', $coupon->ends_at?->format('Y-m-d\TH:i')) }}">
                            @error('ends_at')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Total Use Limit</label>
                            <input type="number" name="max_uses" class="form-control @error('max_uses') is-invalid @enderror" value="{{ old('max_uses', $coupon->max_uses) }}" min="1" placeholder="Unlimited">
                            @error('max_uses')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Per Customer Use Limit</label>
                            <input type="number" name="per_customer_limit" class="form-control @error('per_customer_limit') is-invalid @enderror" value="{{ old('per_customer_limit', $coupon->per_customer_limit) }}" min="1" placeholder="Unlimited">
                            @error('per_customer_limit')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-md-12" id="coupon-customers-field">
                        <div class="form-group">
                            <label>Allowed Customers</label>
                            <select name="customer_ids[]" class="form-control @error('customer_ids') is-invalid @enderror" multiple size="8">
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}" @selected(in_array($customer->id, array_map('intval', $selectedCustomers), true))>
                                        {{ $customer->name }} ({{ $customer->email }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple customers. Used only when audience is Customer Wise.</small>
                            @error('customer_ids')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                            @error('customer_ids.*')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" @checked(old('is_active', $coupon->is_active ?? true))>
                            <label class="custom-control-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex">
                <button type="submit" class="btn btn-primary">Save Coupon</button>
                <a href="{{ route('admin.coupons.index') }}" class="btn btn-default ml-2">Cancel</a>
                @if ($coupon->exists)
                    <button type="submit" form="delete-coupon" class="btn btn-danger ml-auto" onclick="return confirm('Delete this coupon?')">Delete</button>
                @endif
            </div>
        </div>
    </form>

    @if ($coupon->exists)
        <form id="delete-coupon" action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST" class="d-none">
            @csrf
            @method('DELETE')
        </form>
    @endif
@endsection

@push('scripts')
<script>
    (function () {
        const audience = document.getElementById('coupon-audience');
        const customers = document.getElementById('coupon-customers-field');

        function toggleCustomers() {
            if (!audience || !customers) return;
            customers.style.display = audience.value === '{{ \App\Models\Coupon::AUDIENCE_CUSTOMERS }}' ? '' : 'none';
        }

        audience?.addEventListener('change', toggleCustomers);
        toggleCustomers();
    })();
</script>
@endpush
