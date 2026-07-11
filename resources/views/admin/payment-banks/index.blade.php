@extends('layouts.admin')

@section('title', 'Payment Banks')
@section('page_title', 'Payment Banks')

@section('content')
    <div class="payment-banks-page">
        <section class="payment-banks-hero">
            <div>
                <span class="payment-banks-eyebrow">Payment settings</span>
                <h2>Payment Banks</h2>
                <p>Manage bank accounts, mobile wallets, and QR codes shown at checkout.</p>
            </div>
            <div class="payment-banks-hero-actions">
                <a href="{{ route('admin.bank-payments.create') }}" class="btn btn-light">
                    <i class="fas fa-money-check-alt mr-1"></i> Make Payment
                </a>
            </div>
        </section>

        <section class="row payment-banks-stats">
            <div class="col-md-4 mb-3">
                <article class="payment-banks-stat payment-banks-stat--total">
                    <span class="payment-banks-stat-icon"><i class="fas fa-university"></i></span>
                    <div>
                        <div class="payment-banks-stat-value">{{ number_format($stats['total']) }}</div>
                        <div class="payment-banks-stat-label">Total Banks</div>
                    </div>
                </article>
            </div>
            <div class="col-md-4 mb-3">
                <article class="payment-banks-stat payment-banks-stat--active">
                    <span class="payment-banks-stat-icon"><i class="fas fa-check-circle"></i></span>
                    <div>
                        <div class="payment-banks-stat-value">{{ number_format($stats['active']) }}</div>
                        <div class="payment-banks-stat-label">Active on Checkout</div>
                    </div>
                </article>
            </div>
            <div class="col-md-4 mb-3">
                <article class="payment-banks-stat payment-banks-stat--inactive">
                    <span class="payment-banks-stat-icon"><i class="fas fa-pause-circle"></i></span>
                    <div>
                        <div class="payment-banks-stat-value">{{ number_format($stats['inactive']) }}</div>
                        <div class="payment-banks-stat-label">Inactive</div>
                    </div>
                </article>
            </div>
        </section>

        <div class="row">
            <div class="col-xl-4">
                <div class="card payment-banks-card payment-banks-card--sticky">
                    <div class="payment-banks-card-head">
                        <span class="payment-banks-section-icon payment-banks-section-icon--cyan">
                            <i class="fas fa-plus"></i>
                        </span>
                        <div>
                            <h3 class="mb-0">Add Bank / Wallet</h3>
                            <p class="mb-0 text-muted">Create a new payment option for customers.</p>
                        </div>
                    </div>

                    <form action="{{ route('admin.payment-banks.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body payment-banks-form-body">
                            <div class="form-group">
                                <label>Name *</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="bKash, Nagad, Bank name" required>
                                @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                            <div class="form-group">
                                <label>Account Name</label>
                                <input type="text" name="account_name" class="form-control" value="{{ old('account_name') }}" placeholder="Account holder name">
                            </div>
                            <div class="form-group">
                                <label>Account Number</label>
                                <input type="text" name="account_number" class="form-control" value="{{ old('account_number') }}" placeholder="01XXXXXXXXX">
                            </div>
                            <div class="form-group">
                                <label>Branch / Type</label>
                                <input type="text" name="branch" class="form-control" value="{{ old('branch') }}" placeholder="Personal, Merchant, Branch name">
                            </div>
                            <div class="form-group">
                                <label>Instructions</label>
                                <input type="text" name="instructions" class="form-control" value="{{ old('instructions') }}" placeholder="Send money then upload screenshot">
                            </div>
                            <div class="form-group">
                                <label>Expense Charge (%)</label>
                                <input type="number" name="charge_percent" class="form-control @error('charge_percent') is-invalid @enderror" value="{{ old('charge_percent', 0) }}" min="0" max="100" step="0.01">
                                @error('charge_percent')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                <small class="form-text text-muted">Automatically added when an expense is paid from this bank.</small>
                            </div>
                            <div class="form-group">
                                <label>Opening Balance (BDT)</label>
                                <input type="number" name="opening_balance" class="form-control @error('opening_balance') is-invalid @enderror" value="{{ old('opening_balance', 0) }}" min="0" step="0.01">
                                @error('opening_balance')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                            <div class="form-group">
                                <label>QR / Bank Image</label>
                                <div class="payment-banks-file">
                                    <input type="file" name="image" id="add-bank-image" class="payment-banks-file-input" accept="image/*">
                                    <label for="add-bank-image" class="payment-banks-file-label">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <span>Choose image</span>
                                    </label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Sort Order</label>
                                <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}" min="0">
                            </div>
                            <div class="custom-control custom-switch payment-banks-switch">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" checked>
                                <label class="custom-control-label" for="is_active">Active on checkout</label>
                            </div>
                        </div>
                        <div class="payment-banks-card-footer">
                            <button type="submit" class="btn btn-info btn-block">
                                <i class="fas fa-plus mr-1"></i> Add Bank
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-xl-8">
                <div class="payment-banks-list-head">
                    <div>
                        <h3 class="mb-0">Configured Banks</h3>
                        <p class="mb-0 text-muted">{{ $banks->count() }} {{ Str::plural('bank', $banks->count()) }} in your payment list</p>
                    </div>
                </div>

                @forelse ($banks as $bank)
                    <article class="card payment-banks-card payment-banks-item {{ $bank->is_active ? 'payment-banks-item--active' : 'payment-banks-item--inactive' }}">
                        <div class="payment-banks-item-head">
                            <div class="payment-banks-item-title">
                                @if ($bank->imageUrl())
                                    <img src="{{ $bank->imageUrl() }}" alt="{{ $bank->name }}" class="payment-banks-item-thumb">
                                @else
                                    <span class="payment-banks-item-thumb payment-banks-item-thumb--placeholder">
                                        <i class="fas fa-university"></i>
                                    </span>
                                @endif
                                <div>
                                    <h4 class="mb-0">{{ $bank->name }}</h4>
                                    <small class="text-muted">
                                        @if ($bank->account_number)
                                            {{ $bank->account_number }}
                                        @else
                                            No account number
                                        @endif
                                    </small>
                                    <div class="small mt-1">
                                        <strong>Opening:</strong> {{ money($bank->opening_balance ?? 0) }}
                                        <span class="mx-1">•</span>
                                        <strong>Balance:</strong> {{ money($bankBalances[$bank->id] ?? 0) }}
                                        <span class="mx-1">•</span>
                                        <strong>Charge:</strong> {{ number_format((float) $bank->charge_percent, 2) }}%
                                    </div>
                                </div>
                            </div>
                            <span class="payment-banks-status {{ $bank->is_active ? 'payment-banks-status--active' : 'payment-banks-status--inactive' }}">
                                {{ $bank->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>

                        <form action="{{ route('admin.payment-banks.update', $bank) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="card-body payment-banks-form-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Name *</label>
                                            <input type="text" name="name" class="form-control" value="{{ old('name', $bank->name) }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Account Name</label>
                                            <input type="text" name="account_name" class="form-control" value="{{ old('account_name', $bank->account_name) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Account Number</label>
                                            <input type="text" name="account_number" class="form-control" value="{{ old('account_number', $bank->account_number) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Branch / Type</label>
                                            <input type="text" name="branch" class="form-control" value="{{ old('branch', $bank->branch) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="form-group mb-md-0">
                                            <label>Instructions</label>
                                            <input type="text" name="instructions" class="form-control" value="{{ old('instructions', $bank->instructions) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group mb-md-0">
                                            <label>Charge (%)</label>
                                            <input type="number" name="charge_percent" class="form-control" value="{{ old('charge_percent', $bank->charge_percent) }}" min="0" max="100" step="0.01">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group mb-md-0">
                                            <label>Opening Bal.</label>
                                            <input type="number" name="opening_balance" class="form-control" value="{{ old('opening_balance', $bank->opening_balance ?? 0) }}" min="0" step="0.01">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group mb-md-0">
                                            <label>Sort Order</label>
                                            <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $bank->sort_order) }}" min="0">
                                        </div>
                                    </div>
                                </div>

                                <div class="payment-banks-item-media">
                                    <div class="payment-banks-item-media-preview">
                                        @if ($bank->imageUrl())
                                            <img src="{{ $bank->imageUrl() }}" alt="{{ $bank->name }}" class="payment-banks-preview-img">
                                            <div class="custom-control custom-checkbox mt-2">
                                                <input type="checkbox" class="custom-control-input" id="remove-image-{{ $bank->id }}" name="remove_image" value="1">
                                                <label class="custom-control-label" for="remove-image-{{ $bank->id }}">Remove image</label>
                                            </div>
                                        @else
                                            <div class="payment-banks-preview-empty">
                                                <i class="fas fa-image"></i>
                                                <span>No image uploaded</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="payment-banks-item-media-upload">
                                        <label class="d-block">Replace QR / Bank Image</label>
                                        <div class="payment-banks-file">
                                            <input type="file" name="image" id="bank-image-{{ $bank->id }}" class="payment-banks-file-input" accept="image/*">
                                            <label for="bank-image-{{ $bank->id }}" class="payment-banks-file-label">
                                                <i class="fas fa-cloud-upload-alt"></i>
                                                <span>Upload new image</span>
                                            </label>
                                        </div>
                                        <div class="custom-control custom-switch payment-banks-switch mt-3">
                                            <input type="checkbox" class="custom-control-input" id="active-{{ $bank->id }}" name="is_active" value="1" @checked($bank->is_active)>
                                            <label class="custom-control-label" for="active-{{ $bank->id }}">Active on checkout</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="payment-banks-card-footer payment-banks-card-footer--split">
                                <button type="submit" class="btn btn-info">
                                    <i class="fas fa-save mr-1"></i> Save Changes
                                </button>
                                <button type="submit" form="delete-bank-{{ $bank->id }}" class="btn btn-outline-danger" onclick="return confirm('Delete this bank?')">
                                    <i class="fas fa-trash mr-1"></i> Delete
                                </button>
                            </div>
                        </form>
                        <form id="delete-bank-{{ $bank->id }}" action="{{ route('admin.payment-banks.destroy', $bank) }}" method="POST" class="d-none">
                            @csrf
                            @method('DELETE')
                        </form>
                    </article>
                @empty
                    <div class="card payment-banks-card payment-banks-empty">
                        <i class="fas fa-university"></i>
                        <strong>No payment banks yet</strong>
                        <p>Add your first bank account or mobile wallet using the form on the left.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .payment-banks-page {
        --pb-ink: #0f172a;
        --pb-muted: #64748b;
        --pb-border: #e2e8f0;
    }

    .payment-banks-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.25rem;
        padding: 1.35rem 1.5rem;
        border-radius: 1.1rem;
        color: #fff;
        background:
            radial-gradient(circle at 88% 15%, rgba(103, 232, 249, 0.25), transparent 35%),
            linear-gradient(135deg, #0f3d47 0%, #0e7490 55%, #0891b2 100%);
        box-shadow: 0 14px 32px rgba(8, 145, 178, 0.18);
    }

    .payment-banks-eyebrow {
        display: block;
        margin-bottom: 0.2rem;
        color: #a5f3fc;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.09em;
        text-transform: uppercase;
    }

    .payment-banks-hero h2 {
        margin: 0;
        font-size: 1.55rem;
        font-weight: 700;
    }

    .payment-banks-hero p {
        margin: 0.4rem 0 0;
        color: rgba(255, 255, 255, 0.82);
    }

    .payment-banks-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.55rem;
        flex-shrink: 0;
    }

    .payment-banks-stat {
        display: flex;
        align-items: center;
        gap: 0.9rem;
        height: 100%;
        padding: 1rem 1.1rem;
        border: 1px solid var(--pb-border);
        border-radius: 0.95rem;
        background: #fff;
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.04);
    }

    .payment-banks-stat-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.6rem;
        height: 2.6rem;
        border-radius: 0.75rem;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .payment-banks-stat--total .payment-banks-stat-icon { background: #ecfeff; color: #0891b2; }
    .payment-banks-stat--active .payment-banks-stat-icon { background: #ecfdf5; color: #059669; }
    .payment-banks-stat--inactive .payment-banks-stat-icon { background: #f8fafc; color: #64748b; }

    .payment-banks-stat-value {
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--pb-ink);
        line-height: 1.1;
    }

    .payment-banks-stat-label {
        margin-top: 0.15rem;
        color: var(--pb-muted);
        font-size: 0.82rem;
        font-weight: 500;
    }

    .payment-banks-card {
        border: 1px solid var(--pb-border);
        border-radius: 1rem;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
        overflow: hidden;
        margin-bottom: 1rem;
    }

    .payment-banks-card--sticky {
        position: sticky;
        top: 1rem;
    }

    .payment-banks-card-head,
    .payment-banks-list-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 1rem 1.15rem;
        border-bottom: 1px solid #eef2f7;
        background: #fff;
    }

    .payment-banks-list-head {
        margin-bottom: 0.85rem;
        border: 1px solid var(--pb-border);
        border-radius: 0.95rem;
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.04);
    }

    .payment-banks-list-head h3,
    .payment-banks-card-head h3 {
        font-size: 1rem;
        font-weight: 700;
        color: var(--pb-ink);
    }

    .payment-banks-list-head p,
    .payment-banks-card-head p {
        font-size: 0.8rem;
    }

    .payment-banks-section-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.4rem;
        height: 2.4rem;
        border-radius: 0.75rem;
        font-size: 0.95rem;
        flex-shrink: 0;
    }

    .payment-banks-section-icon--cyan {
        background: #ecfeff;
        color: #0891b2;
    }

    .payment-banks-card-head {
        gap: 0.85rem;
    }

    .payment-banks-form-body {
        padding: 1.1rem 1.15rem;
    }

    .payment-banks-form-body label {
        font-size: 0.78rem;
        font-weight: 600;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .payment-banks-card-footer {
        padding: 0.9rem 1.15rem;
        border-top: 1px solid #eef2f7;
        background: #f8fafc;
    }

    .payment-banks-card-footer--split {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
    }

    .payment-banks-switch .custom-control-label {
        font-size: 0.86rem;
        font-weight: 600;
        color: #334155;
        text-transform: none;
        letter-spacing: 0;
    }

    .payment-banks-file {
        position: relative;
    }

    .payment-banks-file-input {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
        z-index: 2;
    }

    .payment-banks-file-label {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
        width: 100%;
        padding: 0.7rem 0.85rem;
        border: 1px dashed #cbd5e1;
        border-radius: 0.75rem;
        background: #f8fafc;
        color: #475569;
        font-size: 0.84rem;
        font-weight: 600;
        cursor: pointer;
        transition: border-color 0.15s ease, background 0.15s ease;
    }

    .payment-banks-file:hover .payment-banks-file-label,
    .payment-banks-file-input:focus + .payment-banks-file-label {
        border-color: #22d3ee;
        background: #ecfeff;
    }

    .payment-banks-item-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.95rem 1.15rem;
        border-bottom: 1px solid #eef2f7;
        background: #fff;
    }

    .payment-banks-item--active .payment-banks-item-head {
        background: linear-gradient(180deg, #f0fdfa 0%, #fff 100%);
    }

    .payment-banks-item-title {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        min-width: 0;
    }

    .payment-banks-item-title h4 {
        font-size: 1rem;
        font-weight: 700;
        color: var(--pb-ink);
    }

    .payment-banks-item-thumb {
        width: 2.8rem;
        height: 2.8rem;
        border-radius: 0.75rem;
        object-fit: cover;
        border: 1px solid var(--pb-border);
        flex-shrink: 0;
    }

    .payment-banks-item-thumb--placeholder {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #ecfeff;
        color: #0891b2;
        font-size: 1rem;
    }

    .payment-banks-status {
        display: inline-block;
        padding: 0.25rem 0.65rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        flex-shrink: 0;
    }

    .payment-banks-status--active {
        color: #047857;
        background: #d1fae5;
    }

    .payment-banks-status--inactive {
        color: #475569;
        background: #f1f5f9;
    }

    .payment-banks-item-media {
        display: grid;
        grid-template-columns: 140px 1fr;
        gap: 1rem;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px dashed #e2e8f0;
    }

    .payment-banks-preview-img {
        width: 100%;
        max-height: 120px;
        object-fit: contain;
        border: 1px solid var(--pb-border);
        border-radius: 0.75rem;
        background: #fff;
        padding: 0.35rem;
    }

    .payment-banks-preview-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        min-height: 100px;
        border: 1px dashed #cbd5e1;
        border-radius: 0.75rem;
        background: #f8fafc;
        color: var(--pb-muted);
        font-size: 0.78rem;
        text-align: center;
        padding: 0.5rem;
    }

    .payment-banks-preview-empty i {
        font-size: 1.2rem;
        opacity: 0.55;
    }

    .payment-banks-empty {
        padding: 3rem 1.5rem;
        text-align: center;
        color: var(--pb-muted);
    }

    .payment-banks-empty i {
        display: block;
        margin-bottom: 0.75rem;
        font-size: 2rem;
        opacity: 0.45;
    }

    .payment-banks-empty strong {
        display: block;
        color: #334155;
        font-size: 1rem;
    }

    .payment-banks-empty p {
        margin: 0.35rem 0 0;
        font-size: 0.86rem;
    }

    @media (max-width: 1199.98px) {
        .payment-banks-card--sticky {
            position: static;
        }
    }

    @media (max-width: 767.98px) {
        .payment-banks-hero {
            align-items: flex-start;
            flex-direction: column;
            padding: 1.1rem;
        }

        .payment-banks-hero h2 {
            font-size: 1.3rem;
        }

        .payment-banks-hero-actions {
            width: 100%;
        }

        .payment-banks-hero-actions .btn {
            flex: 1;
        }

        .payment-banks-item-media {
            grid-template-columns: 1fr;
        }

        .payment-banks-card-footer--split {
            flex-direction: column;
            align-items: stretch;
        }

        .payment-banks-card-footer--split .btn {
            width: 100%;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.querySelectorAll('.payment-banks-file-input').forEach(function (input) {
        input.addEventListener('change', function () {
            var label = this.nextElementSibling;
            if (!label) return;
            var span = label.querySelector('span');
            if (!span) return;
            span.textContent = this.files && this.files[0] ? this.files[0].name : 'Choose image';
        });
    });
</script>
@endpush
