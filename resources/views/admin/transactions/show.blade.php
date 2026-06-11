@extends('layouts.admin')

@section('title', 'Transaction #'.$transaction->id)
@section('page_title', 'Review Payment Transaction')

@section('content')
    <div class="mb-3">
        <a href="{{ route('admin.transactions.index') }}" class="btn btn-default btn-sm">Back to Transactions</a>
        <a href="{{ route('admin.orders.show', $transaction->order) }}" class="btn btn-outline-primary btn-sm">View Order</a>
    </div>

    <div class="row">
        <div class="col-md-7">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Payment Screenshot</h3></div>
                <div class="card-body text-center">
                    @if ($transaction->screenshotUrl())
                        <a href="{{ $transaction->screenshotUrl() }}" target="_blank" rel="noopener">
                            <img src="{{ $transaction->screenshotUrl() }}" alt="Payment screenshot" class="img-fluid rounded border" style="max-height:480px">
                        </a>
                        <p class="text-muted small mt-2">Click image to open full size</p>
                    @else
                        <p class="text-muted mb-0">No screenshot uploaded.</p>
                    @endif
                </div>
            </div>

            @if ($transaction->order->items->isNotEmpty())
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Order Items</h3></div>
                    <div class="card-body table-responsive p-0">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Qty</th>
                                    <th>Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($transaction->order->items as $item)
                                    <tr>
                                        <td>{{ $item->product_name }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>{{ money($item->price * $item->quantity) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-5">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Transaction Details</h3></div>
                <div class="card-body">
                    <p><strong>Transaction #:</strong> {{ $transaction->id }}</p>
                    <p><strong>Status:</strong> <span class="badge {{ $transaction->statusBadgeClass() }}">{{ $transaction->statusLabel() }}</span></p>
                    <p><strong>Order:</strong> <a href="{{ route('admin.orders.show', $transaction->order) }}">{{ $transaction->order->number }}</a></p>
                    <p><strong>Customer:</strong> {{ $transaction->order->customer_name }}</p>
                    <p><strong>Email:</strong> {{ $transaction->order->customer_email }}</p>
                    <p><strong>Phone:</strong> {{ $transaction->order->customer_phone ?: '—' }}</p>
                    <hr>
                    <p><strong>Submitted Amount:</strong> {{ money($transaction->amount) }}</p>
                    <p><strong>Order Total:</strong> {{ money($transaction->order->total) }}</p>
                    <p><strong>Bank:</strong> {{ $transaction->bank_name ?: '—' }}</p>
                    <p><strong>Submitted:</strong> {{ $transaction->created_at->format('M d, Y H:i') }}</p>
                    @if ($transaction->reviewed_at)
                        <p><strong>Reviewed:</strong> {{ $transaction->reviewed_at->format('M d, Y H:i') }}</p>
                        <p><strong>Reviewed By:</strong> {{ $transaction->reviewer?->name ?: '—' }}</p>
                    @endif
                    @if ($transaction->admin_notes)
                        <p><strong>Admin Notes:</strong><br>{{ $transaction->admin_notes }}</p>
                    @endif
                </div>
            </div>

            @if ($transaction->isPending())
                <div class="card card-success">
                    <div class="card-header"><h3 class="card-title">Approve Payment</h3></div>
                    <form action="{{ route('admin.transactions.approve', $transaction) }}" method="POST" onsubmit="return confirm('Approve this payment and mark order as paid?')">
                        @csrf
                        <div class="card-body">
                            <p class="text-muted small">Approving will record the payment, update order payment status, and move the order to processing.</p>
                            <div class="form-group mb-0">
                                <label>Notes (optional)</label>
                                <textarea name="admin_notes" class="form-control" rows="2" placeholder="Internal note..."></textarea>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-success btn-block">Approve & Mark Paid</button>
                        </div>
                    </form>
                </div>

                <div class="card card-danger">
                    <div class="card-header"><h3 class="card-title">Reject Payment</h3></div>
                    <form action="{{ route('admin.transactions.reject', $transaction) }}" method="POST" onsubmit="return confirm('Reject this payment screenshot?')">
                        @csrf
                        <div class="card-body">
                            <div class="form-group mb-0">
                                <label>Reason *</label>
                                <textarea name="admin_notes" class="form-control @error('admin_notes') is-invalid @enderror" rows="3" placeholder="Explain why this payment was rejected..." required>{{ old('admin_notes') }}</textarea>
                                @error('admin_notes')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-danger btn-block">Reject Payment</button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>
@endsection
