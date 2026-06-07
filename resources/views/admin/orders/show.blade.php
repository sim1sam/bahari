@extends('layouts.admin')

@section('title', 'Order '.$order->number)
@section('page_title', 'Order '.$order->number)

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Order Items</h3></div>
                <div class="card-body table-responsive p-0">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Size</th>
                                <th>Color</th>
                                <th>Qty</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->items as $item)
                                <tr>
                                    <td>{{ $item->product_name }}</td>
                                    <td>{{ $item->size }}</td>
                                    <td>{{ $item->color }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>${{ number_format($item->price * $item->quantity, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Details</h3></div>
                <div class="card-body">
                    <p><strong>Customer:</strong> {{ $order->customer_name }}</p>
                    <p><strong>Email:</strong> {{ $order->customer_email }}</p>
                    <p><strong>Phone:</strong> {{ $order->customer_phone }}</p>
                    <p><strong>Address:</strong><br>{{ $order->address }}<br>{{ $order->city }}, {{ $order->zip }}</p>
                    <hr>
                    <p>Subtotal: ${{ number_format($order->subtotal, 2) }}</p>
                    @if ($order->discount > 0)
                        <p>Discount: -${{ number_format($order->discount, 2) }}</p>
                    @endif
                    <p>Shipping: {{ $order->shipping == 0 ? 'Free' : '$'.number_format($order->shipping, 2) }}</p>
                    <p><strong>Total: ${{ number_format($order->total, 2) }}</strong></p>
                    <p>Payment: {{ ucfirst($order->payment_method) }}</p>
                </div>
                <div class="card-footer">
                    <form action="{{ route('admin.orders.status', $order) }}" method="POST">
                        @csrf @method('PATCH')
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control" onchange="this.form.submit()">
                                @foreach (['pending','processing','shipped','completed','cancelled'] as $status)
                                    <option value="{{ $status }}" @selected($order->status === $status)>{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
