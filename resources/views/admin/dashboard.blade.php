@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['products'] }}</h3>
                    <p>Products</p>
                </div>
                <div class="icon"><i class="fas fa-tshirt"></i></div>
                <a href="{{ route('admin.products.index') }}" class="small-box-footer">Manage <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['categories'] }}</h3>
                    <p>Categories</p>
                </div>
                <div class="icon"><i class="fas fa-tags"></i></div>
                <a href="{{ route('admin.categories.index') }}" class="small-box-footer">Manage <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['orders'] }}</h3>
                    <p>Orders</p>
                </div>
                <div class="icon"><i class="fas fa-shopping-cart"></i></div>
                <a href="{{ route('admin.orders.index') }}" class="small-box-footer">View <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ money($stats['revenue'], 0) }}</h3>
                    <p>Revenue</p>
                </div>
                <div class="icon"><i class="fas fa-dollar-sign"></i></div>
                <a href="{{ route('admin.orders.index') }}" class="small-box-footer">Details <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Recent Orders</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentOrders as $order)
                        <tr>
                            <td><a href="{{ route('admin.orders.show', $order) }}">{{ $order->number }}</a></td>
                            <td>{{ $order->customer_name }}</td>
                            <td>{{ money($order->total) }}</td>
                            <td><span class="badge badge-info">{{ ucfirst($order->status) }}</span></td>
                            <td>{{ $order->created_at->format('M d, Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">No orders yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
