@extends('layouts.admin')

@section('title', 'Orders')
@section('page_title', 'Orders')

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title mb-0">Order List</h3>
            <a href="{{ route('admin.orders.transfer-settings.edit') }}" class="btn btn-sm btn-outline-primary ml-auto">
                <i class="fas fa-plug mr-1"></i> API Order Transfer Setting
            </a>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Email</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>API Transfer</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td>
                                {{ $order->number }}
                                @if ($order->isCustom())
                                    <span class="badge badge-secondary ml-1">Custom</span>
                                @endif
                            </td>
                            <td>{{ $order->customer_name }}</td>
                            <td>{{ $order->customer_email }}</td>
                            <td>{{ money($order->total) }}</td>
                            <td><span class="badge {{ $order->paymentStatusBadgeClass() }}">{{ $order->paymentStatusLabel() }}</span></td>
                            <td>
                                <form action="{{ route('admin.orders.status', $order) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="form-control form-control-sm" onchange="this.form.submit()">
                                        @foreach (['pending','processing','shipped','completed','cancelled'] as $status)
                                            <option value="{{ $status }}" @selected($order->status === $status)>{{ ucfirst($status) }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                            <td>
                                <span class="badge badge-{{ match ($order->external_transfer_status) {
                                    'sent' => 'success',
                                    'failed' => 'danger',
                                    'skipped' => 'secondary',
                                    default => 'light',
                                } }}">{{ ucfirst($order->external_transfer_status ?? 'pending') }}</span>
                                @if ($order->external_transfer_message)
                                    <div class="text-muted small" title="{{ $order->external_transfer_message }}">{{ Str::limit($order->external_transfer_message, 32) }}</div>
                                @endif
                            </td>
                            <td>{{ $order->created_at->format('M d, Y H:i') }}</td>
                            <td class="text-nowrap">
                                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-xs btn-primary">View</a>
                                <a href="{{ route('admin.orders.edit', $order) }}" class="btn btn-xs btn-info">Edit</a>
                                @if ($order->canBeDeleted())
                                    <form action="{{ route('admin.orders.destroy', $order) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this order?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-danger">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted">No orders yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($orders->hasPages())
            <div class="card-footer">{{ $orders->links() }}</div>
        @endif
    </div>
@endsection
