@extends('layouts.admin')

@section('title', 'Orders')
@section('page_title', 'Orders')

@section('content')
    <div class="card">
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
                            <td>${{ number_format($order->total, 2) }}</td>
                            <td><span class="badge {{ $order->paymentStatusBadgeClass() }}">{{ $order->paymentStatusLabel() }}</span></td>
                            <td><span class="badge badge-info">{{ ucfirst($order->status) }}</span></td>
                            <td>{{ $order->created_at->format('M d, Y H:i') }}</td>
                            <td class="text-nowrap">
                                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-xs btn-primary">View</a>
                                @if ($order->canBeDeleted())
                                    <form action="{{ route('admin.orders.destroy', $order) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this order?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-danger">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted">No orders yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($orders->hasPages())
            <div class="card-footer">{{ $orders->links() }}</div>
        @endif
    </div>
@endsection
