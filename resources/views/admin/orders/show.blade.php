@extends('layouts.admin')

@section('title', 'Order '.$order->number)
@section('page_title', 'Order '.$order->number)

@section('content')
    <div class="mb-3">
        <a href="{{ route('admin.orders.edit', $order) }}" class="btn btn-info btn-sm">Edit Order</a>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-default btn-sm">Back to Orders</a>
        @php $pendingTxn = $order->paymentTransactions->first(fn ($t) => $t->isPending()); @endphp
        @if ($pendingTxn)
            <a href="{{ route('admin.transactions.show', $pendingTxn) }}" class="btn btn-warning btn-sm">Review Payment Screenshot</a>
        @endif
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Order Items</h3></div>
                <div class="card-body table-responsive p-0">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Image</th>
                                <th>Link</th>
                                <th>Qty</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->items as $item)
                                <tr>
                                    <td>{{ $item->product_name }}</td>
                                    <td>
                                        @if ($item->imageUrl())
                                            <a href="{{ $item->imageUrl() }}" target="_blank" rel="noopener">
                                                <img src="{{ $item->imageUrl() }}" alt="" class="rounded" style="max-height:48px">
                                            </a>
                                        @else — @endif
                                    </td>
                                    <td>
                                        @if ($item->product_link)
                                            <a href="{{ $item->product_link }}" target="_blank" rel="noopener">Open</a>
                                        @else — @endif
                                    </td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ money($item->price * $item->quantity) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Payment history --}}
            <div class="card">
                <div class="card-header"><h3 class="card-title">Payment History</h3></div>
                <div class="card-body p-0">
                    @if ($order->payments->isEmpty())
                        <p class="text-muted p-3 mb-0">No payments recorded yet.</p>
                    @else
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Bank</th>
                                    <th>Notes</th>
                                    <th>Screenshot</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($order->payments as $payment)
                                    <tr>
                                        <td>{{ $payment->created_at->format('M d, Y H:i') }}</td>
                                        <td><strong>{{ money($payment->amount) }}</strong></td>
                                        <td>{{ $payment->methodLabel() }}</td>
                                        <td>{{ $payment->bank_name ?: '—' }}</td>
                                        <td>{{ $payment->notes ?: '—' }}</td>
                                        <td>
                                            @if ($payment->screenshotUrl())
                                                <a href="{{ $payment->screenshotUrl() }}" target="_blank" rel="noopener">View</a>
                                            @else — @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Details</h3></div>
                <div class="card-body">
                    <p><strong>Customer:</strong> {{ $order->customer_name }}</p>
                    <p><strong>Email:</strong> {{ $order->customer_email }}</p>
                    <p><strong>Phone:</strong> {{ $order->customer_phone ?: '—' }}</p>
                    @if ($order->address)
                        <p><strong>Address:</strong><br>{{ $order->address }}<br>{{ $order->city }}, {{ $order->zip }}</p>
                    @endif
                    <hr>
                    <p>Subtotal: {{ money($order->subtotal) }}</p>
                    @if ($order->discount > 0)
                        <p>Discount: -{{ money($order->discount) }}</p>
                    @endif
                    <p>Shipping: {{ money_or_free($order->shipping) }}</p>
                    <p><strong>Total: {{ money($order->total) }}</strong></p>
                    <p>
                        <strong>Paid:</strong> {{ money($order->amount_paid) }} ·
                        <strong>Due:</strong> {{ money($order->amountDue()) }}
                    </p>
                    <p>
                        Payment: {{ $order->paymentMethodLabel() }}
                        <span class="badge {{ $order->paymentStatusBadgeClass() }} ml-1">{{ $order->paymentStatusLabel() }}</span>
                    </p>
                    @if ($order->isCustom())
                        <p><span class="badge badge-info">Custom Order</span></p>
                        @if ($order->bank_name)
                            <p><strong>Customer Bank:</strong> {{ $order->bank_name }}</p>
                        @endif
                        @if ($order->paymentScreenshotUrl())
                            <p><strong>Customer Screenshot:</strong></p>
                            <a href="{{ $order->paymentScreenshotUrl() }}" target="_blank" rel="noopener">
                                <img src="{{ $order->paymentScreenshotUrl() }}" alt="Payment" class="img-fluid rounded border mt-1" style="max-height:120px">
                            </a>
                        @endif
                        @if ($order->notes)
                            <p><strong>Customer Notes:</strong> {{ $order->notes }}</p>
                        @endif
                    @endif
                </div>
            </div>

            {{-- Approve order --}}
            @if ($order->status === 'pending')
                <div class="card card-success">
                    <div class="card-header"><h3 class="card-title">Approve Order</h3></div>
                    <form action="{{ route('admin.orders.approve', $order) }}" method="POST">
                        @csrf
                        <div class="card-body">
                            <p class="text-muted small">Approve and set payment status.</p>
                            <div class="form-group">
                                <label>Payment Status</label>
                                <select name="payment_status" id="payment_status" class="form-control" required onchange="togglePartialAmount()">
                                    <option value="paid">Paid — full amount received</option>
                                    <option value="partial">Partial — some amount received</option>
                                    <option value="due">Due — nothing received yet</option>
                                </select>
                            </div>
                            <div class="form-group" id="partial_amount_group" style="display:none">
                                <label>Amount Received (BDT)</label>
                                <input type="number" name="amount_paid" class="form-control" min="0" max="{{ $order->total }}" step="0.01" placeholder="0.00">
                                <small class="text-muted">Max: {{ money($order->total) }}</small>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-success btn-block">Approve Order</button>
                        </div>
                    </form>
                </div>
            @endif

            {{-- Add payment (COD & partial) --}}
            @if ($order->amountDue() > 0)
                <div class="card card-primary">
                    <div class="card-header"><h3 class="card-title">Add Payment</h3></div>
                    <form action="{{ route('admin.orders.payments.store', $order) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">
                            <p class="text-muted small">Balance due: <strong>{{ money($order->amountDue()) }}</strong></p>
                            <div class="form-group">
                                <label>Amount (BDT)</label>
                                <input type="number" name="amount" class="form-control" min="0.01" max="{{ $order->amountDue() }}" step="0.01" required value="{{ $order->amountDue() }}">
                            </div>
                            <div class="form-group">
                                <label>Payment Method</label>
                                <select name="payment_method" id="pay_method" class="form-control" required onchange="toggleBankField()">
                                    <option value="cod">COD</option>
                                    <option value="cash">Cash</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                </select>
                            </div>
                            <div class="form-group" id="bank_field" style="display:none">
                                <label>Bank</label>
                                <select name="bank_name" class="form-control">
                                    <option value="">Select bank...</option>
                                    @foreach ($banks as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Screenshot (optional)</label>
                                <input type="file" name="screenshot" class="form-control-file" accept="image/*">
                            </div>
                            <div class="form-group">
                                <label>Notes</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Payment reference..."></textarea>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary btn-block">Record Payment</button>
                        </div>
                    </form>
                </div>
            @endif

            <div class="card">
                <div class="card-footer">
                    @if ($order->canBeDeleted())
                        <form action="{{ route('admin.orders.destroy', $order) }}" method="POST" class="mb-3" onsubmit="return confirm('Delete this order?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-block">Delete Order</button>
                        </form>
                    @endif
                    <form action="{{ route('admin.orders.status', $order) }}" method="POST">
                        @csrf @method('PATCH')
                        <div class="form-group">
                            <label>Order Status</label>
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

@push('scripts')
<script>
function togglePartialAmount() {
    var sel = document.getElementById('payment_status');
    var grp = document.getElementById('partial_amount_group');
    grp.style.display = sel && sel.value === 'partial' ? 'block' : 'none';
}
function toggleBankField() {
    var sel = document.getElementById('pay_method');
    var grp = document.getElementById('bank_field');
    grp.style.display = sel && sel.value === 'bank_transfer' ? 'block' : 'none';
}
</script>
@endpush
