@extends('layouts.admin')

@section('title', 'Coupons')
@section('page_title', 'Coupons')

@section('content')
    @php
        use App\Models\Coupon;
        $totalCoupons = $coupons->total();
        $activeCoupons = Coupon::query()->where('is_active', true)->count();
        $publicCoupons = Coupon::query()->where('audience', Coupon::AUDIENCE_PUBLIC)->count();
    @endphp

    <div class="settings-page">
        <section class="settings-hero">
            <div>
                <span class="settings-eyebrow">Settings</span>
                <h2>Coupons</h2>
                <p>Create and manage discount codes for checkout and customer-specific promotions.</p>
                <div class="settings-hero-meta">
                    <span class="settings-hero-chip"><i class="fas fa-ticket-alt"></i> {{ $totalCoupons }} coupon{{ $totalCoupons === 1 ? '' : 's' }}</span>
                    <span class="settings-hero-chip"><i class="fas fa-check-circle"></i> {{ $activeCoupons }} active</span>
                </div>
            </div>
            <div class="settings-hero-actions">
                <a href="{{ route('admin.coupons.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus mr-1"></i> Add Coupon
                </a>
            </div>
        </section>

        <section class="row mb-3">
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="settings-stat settings-stat--teal">
                    <span class="settings-stat-icon"><i class="fas fa-ticket-alt"></i></span>
                    <div>
                        <div class="settings-stat-value">{{ $totalCoupons }}</div>
                        <div class="settings-stat-label">Total Coupons</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="settings-stat settings-stat--green">
                    <span class="settings-stat-icon"><i class="fas fa-check-circle"></i></span>
                    <div>
                        <div class="settings-stat-value">{{ $activeCoupons }}</div>
                        <div class="settings-stat-label">Active</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="settings-stat settings-stat--blue">
                    <span class="settings-stat-icon"><i class="fas fa-globe"></i></span>
                    <div>
                        <div class="settings-stat-value">{{ $publicCoupons }}</div>
                        <div class="settings-stat-label">Public</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="settings-stat settings-stat--purple">
                    <span class="settings-stat-icon"><i class="fas fa-user-tag"></i></span>
                    <div>
                        <div class="settings-stat-value">{{ $totalCoupons - $publicCoupons }}</div>
                        <div class="settings-stat-label">Customer Wise</div>
                    </div>
                </article>
            </div>
        </section>

        @include('admin.settings.partials.nav')

        <div class="settings-card">
            <div class="settings-card-head">
                <div>
                    <h3>Coupon Codes</h3>
                    <p>Discount codes applied at checkout with usage limits and date ranges.</p>
                </div>
                <a href="{{ route('admin.coupons.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus mr-1"></i> Add Coupon
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 settings-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Discount</th>
                            <th>Audience</th>
                            <th>Date Range</th>
                            <th>Used</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($coupons as $coupon)
                            <tr>
                                <td>
                                    <strong>{{ $coupon->code }}</strong>
                                    @if ($coupon->label)
                                        <div class="text-muted small">{{ $coupon->label }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if ($coupon->discount_type === \App\Models\Coupon::TYPE_PERCENT)
                                        {{ rtrim(rtrim($coupon->discount_value, '0'), '.') }}%
                                    @else
                                        {{ money($coupon->discount_value) }}
                                    @endif
                                </td>
                                <td>
                                    @if ($coupon->isPublic())
                                        <span class="settings-status settings-status--public">Public</span>
                                    @else
                                        <span class="settings-status settings-status--private">Customer Wise</span>
                                        <div class="text-muted small">{{ $coupon->customers->count() }} selected</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="small">
                                        <div>From: {{ $coupon->starts_at?->format('d M Y, h:i A') ?? 'Any time' }}</div>
                                        <div>To: {{ $coupon->ends_at?->format('d M Y, h:i A') ?? 'No end' }}</div>
                                    </div>
                                </td>
                                <td>
                                    <strong>{{ $coupon->totalUses() }}</strong>
                                    @if ($coupon->max_uses)
                                        / {{ $coupon->max_uses }}
                                    @endif
                                    <div class="text-muted small">
                                        Per customer: {{ $coupon->per_customer_limit ?: 'Unlimited' }}
                                    </div>
                                </td>
                                <td>
                                    <span class="settings-status {{ $coupon->is_active ? 'settings-status--live' : 'settings-status--hidden' }}">
                                        {{ $coupon->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <div class="settings-actions">
                                        <a href="{{ route('admin.coupons.edit', $coupon) }}" class="btn btn-sm btn-primary">Edit</a>
                                        <button type="submit" form="delete-coupon-{{ $coupon->id }}" class="btn btn-sm btn-danger" onclick="return confirm('Delete this coupon?')">Delete</button>
                                        <form id="delete-coupon-{{ $coupon->id }}" action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST" class="d-none">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="settings-empty">
                                    <i class="fas fa-ticket-alt"></i>
                                    <strong>No coupons yet</strong>
                                    <p>Create your first discount code to offer promotions at checkout.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($coupons->hasPages())
                <div class="settings-card-footer">{{ $coupons->links() }}</div>
            @endif
        </div>
    </div>
@endsection

@push('styles')
    @include('admin.settings.partials.page-styles')
@endpush
