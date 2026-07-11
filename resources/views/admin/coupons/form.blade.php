@extends('layouts.admin')

@section('title', $coupon->exists ? 'Edit Coupon' : 'Add Coupon')
@section('page_title', $coupon->exists ? 'Edit Coupon' : 'Add Coupon')

@section('content')
    @php
        use App\Models\Coupon;
        $selectedCustomers = old('customer_ids', $selectedCustomers);
        $audience = old('audience', $coupon->audience ?? Coupon::AUDIENCE_PUBLIC);
        $isActive = (bool) old('is_active', $coupon->is_active ?? true);
        $discountType = old('discount_type', $coupon->discount_type ?? Coupon::TYPE_PERCENT);
    @endphp

    <div class="settings-page">
        <a href="{{ route('admin.coupons.index') }}" class="settings-back-link">
            <i class="fas fa-arrow-left"></i> Back to Coupons
        </a>

        <section class="settings-hero">
            <div>
                <span class="settings-eyebrow">Settings</span>
                <h2>{{ $coupon->exists ? 'Edit Coupon' : 'Add Coupon' }}</h2>
                <p>{{ $coupon->exists ? 'Update discount code settings, audience, and usage limits.' : 'Create a new discount code for checkout or specific customers.' }}</p>
                @if ($coupon->exists)
                    <div class="settings-hero-meta">
                        <span class="settings-hero-chip"><i class="fas fa-ticket-alt"></i> {{ $coupon->code }}</span>
                        <span class="settings-hero-chip"><i class="fas fa-chart-bar"></i> {{ $coupon->totalUses() }} used</span>
                    </div>
                @endif
            </div>
            <div class="settings-hero-actions">
                <a href="{{ route('admin.coupons.index') }}" class="btn btn-secondary">
                    <i class="fas fa-list mr-1"></i> All Coupons
                </a>
            </div>
        </section>

        @include('admin.settings.partials.nav')

        <form action="{{ $coupon->exists ? route('admin.coupons.update', $coupon) : route('admin.coupons.store') }}" method="POST">
            @csrf
            @if ($coupon->exists) @method('PUT') @endif

            <div class="row">
                <div class="col-lg-8 mb-3">
                    <div class="settings-card">
                        <div class="settings-card-body">
                            <section class="settings-form-panel">
                                <div class="settings-form-panel-head">
                                    <span class="settings-form-panel-icon settings-form-panel-icon--brand"><i class="fas fa-ticket-alt"></i></span>
                                    <div>
                                        <h4>Coupon Details</h4>
                                        <p>Code customers enter at checkout and an optional display label.</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="settings-field">
                                            <label for="code">Coupon Code *</label>
                                            <div class="settings-input-wrap">
                                                <span class="settings-input-icon"><i class="fas fa-barcode"></i></span>
                                                <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $coupon->code) }}" placeholder="EID20" required>
                                            </div>
                                            @error('code')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="settings-field">
                                            <label for="label">Label</label>
                                            <div class="settings-input-wrap">
                                                <span class="settings-input-icon"><i class="fas fa-tag"></i></span>
                                                <input type="text" name="label" id="label" class="form-control @error('label') is-invalid @enderror" value="{{ old('label', $coupon->label) }}" placeholder="20% off for Eid">
                                            </div>
                                            @error('label')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <section class="settings-form-panel">
                                <div class="settings-form-panel-head">
                                    <span class="settings-form-panel-icon settings-form-panel-icon--newsletter"><i class="fas fa-percent"></i></span>
                                    <div>
                                        <h4>Discount &amp; Audience</h4>
                                        <p>How much to discount and who can use this coupon.</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="settings-field">
                                            <label for="discount_type">Discount Type *</label>
                                            <select name="discount_type" id="discount_type" class="form-control settings-textarea @error('discount_type') is-invalid @enderror" required>
                                                <option value="{{ Coupon::TYPE_PERCENT }}" @selected($discountType === Coupon::TYPE_PERCENT)>Percent</option>
                                                <option value="{{ Coupon::TYPE_FIXED }}" @selected($discountType === Coupon::TYPE_FIXED)>Fixed Amount</option>
                                            </select>
                                            @error('discount_type')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="settings-field">
                                            <label for="discount_value">Discount Value *</label>
                                            <div class="settings-input-wrap">
                                                <span class="settings-input-icon"><i class="fas fa-coins"></i></span>
                                                <input type="number" name="discount_value" id="discount_value" class="form-control @error('discount_value') is-invalid @enderror" value="{{ old('discount_value', $coupon->discount_value) }}" min="0.01" step="0.01" required>
                                            </div>
                                            @error('discount_value')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="settings-field">
                                            <label>Audience *</label>
                                            <div class="coupon-audience-chips">
                                                <label class="coupon-audience-chip coupon-audience-chip--public {{ $audience === Coupon::AUDIENCE_PUBLIC ? 'coupon-audience-chip--active' : '' }}">
                                                    <input type="radio" name="audience" value="{{ Coupon::AUDIENCE_PUBLIC }}" @checked($audience === Coupon::AUDIENCE_PUBLIC)>
                                                    <i class="fas fa-globe"></i> Public
                                                </label>
                                                <label class="coupon-audience-chip coupon-audience-chip--customers {{ $audience === Coupon::AUDIENCE_CUSTOMERS ? 'coupon-audience-chip--active' : '' }}">
                                                    <input type="radio" name="audience" value="{{ Coupon::AUDIENCE_CUSTOMERS }}" @checked($audience === Coupon::AUDIENCE_CUSTOMERS)>
                                                    <i class="fas fa-user-tag"></i> Customer Wise
                                                </label>
                                            </div>
                                            @error('audience')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <section class="settings-form-panel">
                                <div class="settings-form-panel-head">
                                    <span class="settings-form-panel-icon settings-form-panel-icon--footer"><i class="fas fa-calendar-alt"></i></span>
                                    <div>
                                        <h4>Schedule &amp; Limits</h4>
                                        <p>Date range and how many times the coupon can be used.</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="settings-field">
                                            <label for="starts_at">Starts At</label>
                                            <div class="settings-input-wrap">
                                                <span class="settings-input-icon"><i class="fas fa-play"></i></span>
                                                <input type="datetime-local" name="starts_at" id="starts_at" class="form-control @error('starts_at') is-invalid @enderror" value="{{ old('starts_at', $coupon->starts_at?->format('Y-m-d\TH:i')) }}">
                                            </div>
                                            @error('starts_at')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="settings-field">
                                            <label for="ends_at">Ends At</label>
                                            <div class="settings-input-wrap">
                                                <span class="settings-input-icon"><i class="fas fa-stop"></i></span>
                                                <input type="datetime-local" name="ends_at" id="ends_at" class="form-control @error('ends_at') is-invalid @enderror" value="{{ old('ends_at', $coupon->ends_at?->format('Y-m-d\TH:i')) }}">
                                            </div>
                                            @error('ends_at')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="settings-field">
                                            <label for="max_uses">Total Use Limit</label>
                                            <div class="settings-input-wrap">
                                                <span class="settings-input-icon"><i class="fas fa-hashtag"></i></span>
                                                <input type="number" name="max_uses" id="max_uses" class="form-control @error('max_uses') is-invalid @enderror" value="{{ old('max_uses', $coupon->max_uses) }}" min="1" placeholder="Unlimited">
                                            </div>
                                            @error('max_uses')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="settings-field">
                                            <label for="per_customer_limit">Per Customer Use Limit</label>
                                            <div class="settings-input-wrap">
                                                <span class="settings-input-icon"><i class="fas fa-user"></i></span>
                                                <input type="number" name="per_customer_limit" id="per_customer_limit" class="form-control @error('per_customer_limit') is-invalid @enderror" value="{{ old('per_customer_limit', $coupon->per_customer_limit) }}" min="1" placeholder="Unlimited">
                                            </div>
                                            @error('per_customer_limit')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <section class="settings-form-panel mb-0" id="coupon-customers-field">
                                <div class="settings-form-panel-head">
                                    <span class="settings-form-panel-icon settings-form-panel-icon--social"><i class="fas fa-users"></i></span>
                                    <div>
                                        <h4>Allowed Customers</h4>
                                        <p>Select customers who can use this coupon when audience is Customer Wise.</p>
                                    </div>
                                </div>
                                <div class="settings-field mb-2">
                                    <select id="coupon-customers-select" name="customer_ids[]" class="form-control settings-textarea @error('customer_ids') is-invalid @enderror" multiple size="8">
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}" @selected(in_array($customer->id, array_map('intval', $selectedCustomers), true))>
                                                {{ $customer->name }} ({{ $customer->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('customer_ids')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                    @error('customer_ids.*')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                </div>
                                <div id="selected-customers-list" class="coupon-customer-chips"></div>
                                <small class="settings-field-hint">Hold Ctrl/Cmd to select multiple customers. Only applies when audience is Customer Wise.</small>
                            </section>
                        </div>

                        <div class="settings-card-footer d-flex align-items-center flex-wrap gap-2">
                            <button type="submit" class="btn btn-info btn-lg">
                                <i class="fas fa-save mr-1"></i> Save Coupon
                            </button>
                            <a href="{{ route('admin.coupons.index') }}" class="btn btn-secondary">Cancel</a>
                            @if ($coupon->exists)
                                <button type="submit" form="delete-coupon" class="btn btn-danger ml-auto" onclick="return confirm('Delete this coupon?')">
                                    <i class="fas fa-trash mr-1"></i> Delete
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 mb-3">
                    <div class="settings-side-card">
                        <div class="settings-side-head">
                            <span class="settings-side-icon settings-side-icon--check"><i class="fas fa-power-off"></i></span>
                            <div>
                                <h4>Status</h4>
                                <p>Control whether this coupon is available at checkout.</p>
                            </div>
                        </div>
                        <div class="settings-side-body">
                            <div class="settings-toggle-card {{ $isActive ? 'settings-toggle-card--on' : '' }}" data-toggle-card="active">
                                <div class="settings-toggle-copy">
                                    <h5>Active Coupon</h5>
                                    <p>Inactive coupons cannot be applied at checkout.</p>
                                </div>
                                <label class="settings-toggle" for="is_active">
                                    <input type="checkbox" class="settings-toggle-input" id="is_active" name="is_active" value="1" @checked($isActive)>
                                    <span class="settings-toggle-track"><span class="settings-toggle-thumb"></span></span>
                                    <span class="settings-toggle-label" data-label-for="active">{{ $isActive ? 'Active' : 'Inactive' }}</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="settings-side-card">
                        <div class="settings-side-head">
                            <span class="settings-side-icon settings-side-icon--info"><i class="fas fa-info-circle"></i></span>
                            <div>
                                <h4>Tips</h4>
                                <p>Best practices for coupon codes.</p>
                            </div>
                        </div>
                        <div class="settings-side-body">
                            <ul class="settings-side-list">
                                <li>Use short, memorable codes like <code>EID20</code>.</li>
                                <li>Percent discounts work well for promotions; fixed amounts for flat-off deals.</li>
                                <li>Set an end date to automatically expire seasonal offers.</li>
                                <li>Customer Wise coupons are hidden from public checkout unless the customer is selected.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        @if ($coupon->exists)
            <form id="delete-coupon" action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST" class="d-none">
                @csrf
                @method('DELETE')
            </form>
        @endif
    </div>
@endsection

@push('styles')
    @include('admin.settings.partials.page-styles')
    <style>
        .coupon-audience-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .coupon-audience-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            margin: 0;
            padding: 0.5rem 0.9rem;
            border: 2px solid #e2e8f0;
            border-radius: 999px;
            background: #fff;
            color: #475569;
            font-size: 0.82rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .coupon-audience-chip input { position: absolute; opacity: 0; pointer-events: none; }

        .coupon-audience-chip--public.coupon-audience-chip--active {
            border-color: #3b82f6;
            background: linear-gradient(135deg, #2563eb, #3b82f6);
            color: #fff;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.25);
        }

        .coupon-audience-chip--customers.coupon-audience-chip--active {
            border-color: #8b5cf6;
            background: linear-gradient(135deg, #7c3aed, #8b5cf6);
            color: #fff;
            box-shadow: 0 4px 14px rgba(124, 58, 237, 0.25);
        }

        .coupon-customer-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
            min-height: 1.5rem;
        }

        .coupon-customer-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.3rem 0.65rem;
            border-radius: 999px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 0.78rem;
            font-weight: 600;
        }

        .coupon-customer-chip button {
            border: 0;
            background: transparent;
            color: #64748b;
            font-size: 1rem;
            line-height: 1;
            padding: 0;
            cursor: pointer;
        }

        .coupon-customer-chip button:hover { color: #dc2626; }

        #coupon-customers-field.coupon-customers-field--hidden { display: none; }
    </style>
@endpush

@push('scripts')
<script>
    (function () {
        const audienceInputs = document.querySelectorAll('input[name="audience"]');
        const customersField = document.getElementById('coupon-customers-field');
        const customerSelect = document.getElementById('coupon-customers-select');
        const selectedCustomersList = document.getElementById('selected-customers-list');
        const audienceChips = document.querySelectorAll('.coupon-audience-chip');
        const activeToggle = document.getElementById('is_active');

        function toggleCustomers() {
            if (!customersField) return;
            const selected = document.querySelector('input[name="audience"]:checked');
            const isCustomerWise = selected && selected.value === '{{ Coupon::AUDIENCE_CUSTOMERS }}';
            customersField.classList.toggle('coupon-customers-field--hidden', !isCustomerWise);
        }

        function syncAudienceChips() {
            audienceChips.forEach(function (chip) {
                const input = chip.querySelector('input');
                chip.classList.toggle('coupon-audience-chip--active', input && input.checked);
            });
        }

        audienceInputs.forEach(function (input) {
            input.addEventListener('change', function () {
                syncAudienceChips();
                toggleCustomers();
            });
        });

        syncAudienceChips();
        toggleCustomers();

        function renderSelectedCustomers() {
            if (!customerSelect || !selectedCustomersList) return;

            const selectedOptions = Array.from(customerSelect.selectedOptions);
            selectedCustomersList.innerHTML = '';

            if (!selectedOptions.length) {
                selectedCustomersList.innerHTML = '<span class="settings-field-hint mb-0">No customers selected.</span>';
                return;
            }

            selectedOptions.forEach(function (option) {
                const chip = document.createElement('span');
                chip.className = 'coupon-customer-chip';

                const text = document.createElement('span');
                text.textContent = option.textContent.trim();

                const remove = document.createElement('button');
                remove.type = 'button';
                remove.setAttribute('aria-label', 'Remove customer');
                remove.innerHTML = '&times;';
                remove.addEventListener('click', function () {
                    option.selected = false;
                    renderSelectedCustomers();
                });

                chip.appendChild(text);
                chip.appendChild(remove);
                selectedCustomersList.appendChild(chip);
            });
        }

        customerSelect?.addEventListener('change', renderSelectedCustomers);
        renderSelectedCustomers();

        if (activeToggle) {
            activeToggle.addEventListener('change', function () {
                const card = activeToggle.closest('.settings-toggle-card');
                const label = card?.querySelector('.settings-toggle-label');
                if (card) card.classList.toggle('settings-toggle-card--on', activeToggle.checked);
                if (label) label.textContent = activeToggle.checked ? 'Active' : 'Inactive';
            });
        }
    })();
</script>
@endpush
