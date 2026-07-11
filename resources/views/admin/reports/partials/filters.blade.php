<div class="reports-filters-card">
    <div class="reports-filters-head">
        <h3><i class="fas fa-filter mr-1 text-info"></i> Report Filters</h3>
    </div>
    <div class="reports-filters-body">
        <form method="GET" action="{{ $action ?? request()->url() }}">
            <div class="reports-filter-grid">
                <div class="reports-filter-field">
                    <label>From</label>
                    <input type="date" name="date_from" class="form-control" value="{{ $filters->dateFrom }}">
                </div>
                <div class="reports-filter-field">
                    <label>To</label>
                    <input type="date" name="date_to" class="form-control" value="{{ $filters->dateTo }}">
                </div>
                <div class="reports-filter-field">
                    <label>Basis</label>
                    <select name="basis" class="form-control">
                        <option value="accrual" @selected($filters->basis === 'accrual')>Accrual (orders)</option>
                        <option value="cash" @selected($filters->basis === 'cash')>Cash (payments)</option>
                    </select>
                </div>
                <div class="reports-filter-field">
                    <label>Order Status</label>
                    <select name="status" class="form-control">
                        <option value="">All (excl. cancelled)</option>
                        @foreach (['pending', 'processing', 'shipped', 'completed', 'cancelled'] as $status)
                            <option value="{{ $status }}" @selected($filters->status === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="reports-filter-field">
                    <label>Payment Status</label>
                    <select name="payment_status" class="form-control">
                        <option value="">All</option>
                        @foreach (['pending', 'paid', 'partial', 'due'] as $paymentStatus)
                            <option value="{{ $paymentStatus }}" @selected($filters->paymentStatus === $paymentStatus)>{{ ucfirst($paymentStatus) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="reports-filter-field">
                    <label>Payment Method</label>
                    <select name="payment_method" class="form-control">
                        <option value="">All</option>
                        @foreach (['cod', 'bank_transfer', 'sslcommerz', 'cash'] as $method)
                            <option value="{{ $method }}" @selected($filters->paymentMethod === $method)>{{ strtoupper(str_replace('_', ' ', $method)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="reports-filter-field">
                    <label>Order Type</label>
                    <select name="order_type" class="form-control">
                        <option value="">All</option>
                        <option value="standard" @selected($filters->orderType === 'standard')>Standard</option>
                        <option value="custom" @selected($filters->orderType === 'custom')>Custom</option>
                    </select>
                </div>
                <div class="reports-filter-check">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="include_cancelled" name="include_cancelled" value="1" @checked(! $filters->excludeCancelled)>
                        <label class="custom-control-label" for="include_cancelled">Include cancelled</label>
                    </div>
                </div>
            </div>
            <div class="reports-filter-actions">
                <button type="submit" class="btn btn-info">
                    <i class="fas fa-filter mr-1"></i> Apply Filters
                </button>
                <a href="{{ $action ?? request()->url() }}" class="btn btn-secondary">Reset</a>
                @if (! empty($exportRoute))
                    <a href="{{ $exportRoute }}" class="btn btn-success">
                        <i class="fas fa-file-csv mr-1"></i> Export CSV
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>
