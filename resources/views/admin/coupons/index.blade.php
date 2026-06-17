@extends('layouts.admin')

@section('title', 'Coupons')
@section('page_title', 'Coupons')

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title mb-0">Coupon Codes</h3>
            <a href="{{ route('admin.coupons.create') }}" class="btn btn-primary btn-sm ml-auto">
                <i class="fas fa-plus mr-1"></i> Add Coupon
            </a>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover mb-0">
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
                                    <span class="badge badge-info">Public</span>
                                @else
                                    <span class="badge badge-warning">Customer Wise</span>
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
                                <span class="badge {{ $coupon->is_active ? 'badge-success' : 'badge-secondary' }}">
                                    {{ $coupon->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.coupons.edit', $coupon) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <button type="submit" form="delete-coupon-{{ $coupon->id }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this coupon?')">Delete</button>
                                <form id="delete-coupon-{{ $coupon->id }}" action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST" class="d-none">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No coupons created yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($coupons->hasPages())
            <div class="card-footer">
                {{ $coupons->links() }}
            </div>
        @endif
    </div>
@endsection
