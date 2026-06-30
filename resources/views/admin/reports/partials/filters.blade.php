<div class="card card-outline card-secondary mb-3">
    <div class="card-header">
        <h3 class="card-title mb-0"><i class="fas fa-filter mr-1"></i> Filters</h3>
    </div>
    <div class="card-body pb-2">
        <form method="GET" action="{{ $action ?? request()->url() }}" class="row">
            <div class="col-md-2 col-sm-6">
                <div class="form-group">
                    <label class="small text-muted mb-1">From</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $filters->dateFrom }}">
                </div>
            </div>
            <div class="col-md-2 col-sm-6">
                <div class="form-group">
                    <label class="small text-muted mb-1">To</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $filters->dateTo }}">
                </div>
            </div>
            <div class="col-md-2 col-sm-6">
                <div class="form-group">
                    <label class="small text-muted mb-1">Basis</label>
                    <select name="basis" class="form-control form-control-sm">
                        <option value="accrual" @selected($filters->basis === 'accrual')>Accrual (orders)</option>
                        <option value="cash" @selected($filters->basis === 'cash')>Cash (payments)</option>
                    </select>
                </div>
            </div>
            <div class="col-md-2 col-sm-6">
                <div class="form-group">
                    <label class="small text-muted mb-1">Order Status</label>
                    <select name="status" class="form-control form-control-sm">
                        <option value="">All (excl. cancelled)</option>
                        @foreach (['pending', 'processing', 'shipped', 'completed', 'cancelled'] as $status)
                            <option value="{{ $status }}" @selected($filters->status === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-2 col-sm-6">
                <div class="form-group">
                    <label class="small text-muted mb-1">Payment Status</label>
                    <select name="payment_status" class="form-control form-control-sm">
                        <option value="">All</option>
                        @foreach (['pending', 'paid', 'partial', 'due'] as $paymentStatus)
                            <option value="{{ $paymentStatus }}" @selected($filters->paymentStatus === $paymentStatus)>{{ ucfirst($paymentStatus) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-2 col-sm-6">
                <div class="form-group">
                    <label class="small text-muted mb-1">Payment Method</label>
                    <select name="payment_method" class="form-control form-control-sm">
                        <option value="">All</option>
                        @foreach (['cod', 'bank_transfer', 'sslcommerz', 'cash'] as $method)
                            <option value="{{ $method }}" @selected($filters->paymentMethod === $method)>{{ strtoupper(str_replace('_', ' ', $method)) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-2 col-sm-6">
                <div class="form-group">
                    <label class="small text-muted mb-1">Order Type</label>
                    <select name="order_type" class="form-control form-control-sm">
                        <option value="">All</option>
                        <option value="standard" @selected($filters->orderType === 'standard')>Standard</option>
                        <option value="custom" @selected($filters->orderType === 'custom')>Custom</option>
                    </select>
                </div>
            </div>
            <div class="col-md-2 col-sm-6 d-flex align-items-end">
                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="include_cancelled" name="include_cancelled" value="1" @checked(! $filters->excludeCancelled)>
                        <label class="custom-control-label" for="include_cancelled">Include cancelled</label>
                    </div>
                </div>
            </div>
            <div class="col-md-12 d-flex flex-wrap align-items-center mb-2" style="gap: 0.5rem;">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search mr-1"></i> Apply</button>
                <a href="{{ $action ?? request()->url() }}" class="btn btn-default btn-sm">Reset</a>
                @if (! empty($exportRoute))
                    <a href="{{ $exportRoute }}" class="btn btn-outline-success btn-sm"><i class="fas fa-file-csv mr-1"></i> Export CSV</a>
                @endif
            </div>
        </form>
    </div>
</div>
