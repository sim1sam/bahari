@extends('layouts.admin')

@section('title', 'Wishlist')
@section('page_title', 'Wishlist')

@section('content')
    <div class="wishlists-page">
        <section class="wishlists-hero">
            <div>
                <span class="wishlists-eyebrow">Order management</span>
                <h2>Wishlist</h2>
                <p>Track products customers have saved — useful for follow-ups and demand insight.</p>
                <div class="wishlists-hero-meta">
                    <span class="wishlists-hero-chip"><i class="fas fa-heart"></i> {{ number_format($stats['total']) }} saved</span>
                    <span class="wishlists-hero-chip"><i class="fas fa-users"></i> {{ number_format($stats['customers']) }} customers</span>
                </div>
            </div>
            <div class="wishlists-hero-visual" aria-hidden="true">
                <span class="wishlists-hero-orb"></span>
                <i class="fas fa-heart"></i>
            </div>
        </section>

        <section class="row wishlists-stats">
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="wishlists-stat wishlists-stat--total">
                    <span class="wishlists-stat-icon"><i class="fas fa-heart"></i></span>
                    <div>
                        <div class="wishlists-stat-value">{{ number_format($stats['total']) }}</div>
                        <div class="wishlists-stat-label">Saved Items</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="wishlists-stat wishlists-stat--customers">
                    <span class="wishlists-stat-icon"><i class="fas fa-users"></i></span>
                    <div>
                        <div class="wishlists-stat-value">{{ number_format($stats['customers']) }}</div>
                        <div class="wishlists-stat-label">Customers</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="wishlists-stat wishlists-stat--products">
                    <span class="wishlists-stat-icon"><i class="fas fa-box-open"></i></span>
                    <div>
                        <div class="wishlists-stat-value">{{ number_format($stats['products']) }}</div>
                        <div class="wishlists-stat-label">Products</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="wishlists-stat wishlists-stat--today">
                    <span class="wishlists-stat-icon"><i class="fas fa-calendar-day"></i></span>
                    <div>
                        <div class="wishlists-stat-value">{{ number_format($stats['today']) }}</div>
                        <div class="wishlists-stat-label">Added Today</div>
                    </div>
                </article>
            </div>
        </section>

        <div class="card wishlists-card">
            <div class="wishlists-card-head">
                <div>
                    <h3 class="mb-0">All Wishlist Items</h3>
                    <p class="mb-0 text-muted">
                        @if ($search)
                            Showing {{ $wishlists->count() }} result{{ $wishlists->count() === 1 ? '' : 's' }} for “{{ $search }}”
                        @else
                            {{ $wishlists->total() }} saved {{ Str::plural('item', $wishlists->total()) }} across the store
                        @endif
                    </p>
                </div>
                <form action="{{ route('admin.wishlists.index') }}" method="GET" class="wishlists-search">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Search customer or product..." value="{{ $search }}">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-info" title="Search">
                                <i class="fas fa-search"></i>
                            </button>
                            @if ($search)
                                <a href="{{ route('admin.wishlists.index') }}" class="btn btn-outline-secondary" title="Clear">
                                    <i class="fas fa-times"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table wishlists-table mb-0">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Customer</th>
                            <th>Price</th>
                            <th>Added</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($wishlists as $wishlist)
                            @php
                                $product = $wishlist->product;
                                $customer = $wishlist->user;
                                $image = $product?->imageUrl();
                            @endphp
                            <tr>
                                <td>
                                    <div class="wishlists-product">
                                        <div class="wishlists-thumb">
                                            @if ($image)
                                                <img src="{{ $image }}" alt="{{ $product?->name }}">
                                            @else
                                                <span class="wishlists-thumb-fallback"><i class="fas fa-image"></i></span>
                                            @endif
                                            <span class="wishlists-thumb-heart"><i class="fas fa-heart"></i></span>
                                        </div>
                                        <div class="wishlists-product-meta">
                                            <div class="wishlists-product-name">{{ $product?->name ?? 'Deleted product' }}</div>
                                            @if ($product?->brand || $product?->category)
                                                <div class="wishlists-product-sub">
                                                    {{ collect([$product?->brand, $product?->category?->name])->filter()->implode(' · ') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if ($customer)
                                        <div class="wishlists-customer">
                                            @if ($customer->avatarUrl())
                                                <img src="{{ $customer->avatarUrl() }}" alt="" class="wishlists-avatar wishlists-avatar--image">
                                            @else
                                                <span class="wishlists-avatar">{{ $customer->initials() }}</span>
                                            @endif
                                            <div class="min-w-0">
                                                <div class="wishlists-customer-name">{{ $customer->name }}</div>
                                                <div class="wishlists-customer-email">{{ $customer->email }}</div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted">Unknown customer</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="wishlists-price">{{ $product ? money($product->price) : '—' }}</span>
                                </td>
                                <td>
                                    <div class="wishlists-date">{{ $wishlist->created_at?->format('M j, Y') }}</div>
                                    <div class="wishlists-date-sub" title="{{ $wishlist->created_at?->format('Y-m-d H:i') }}">
                                        {{ $wishlist->created_at?->diffForHumans() }}
                                    </div>
                                </td>
                                <td class="text-right">
                                    <div class="wishlists-actions">
                                        @if ($product)
                                            <a href="{{ route('products.show', $product->slug) }}" target="_blank" class="btn btn-sm btn-outline-info" title="View product">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                            @if (Route::has('admin.products.edit'))
                                                <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-outline-secondary" title="Edit product">
                                                    <i class="fas fa-pen"></i>
                                                </a>
                                            @endif
                                        @endif
                                        <form action="{{ route('admin.wishlists.destroy', $wishlist) }}" method="POST" onsubmit="return confirm('Remove this wishlist item?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="wishlists-empty">
                                    <i class="far fa-heart"></i>
                                    <strong>No wishlist items yet</strong>
                                    <p>When customers save products, they will show up here.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($wishlists->hasPages())
                <div class="wishlists-card-footer">
                    {{ $wishlists->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

@push('styles')
<style>
    .wishlists-page {
        --wl-ink: #0f172a;
        --wl-muted: #64748b;
        --wl-border: #e2e8f0;
    }

    .wishlists-hero {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.25rem;
        padding: 1.4rem 1.5rem;
        border-radius: 1.1rem;
        color: #fff;
        overflow: hidden;
        background:
            radial-gradient(circle at 88% 15%, rgba(251, 113, 133, 0.28), transparent 38%),
            radial-gradient(circle at 12% 85%, rgba(103, 232, 249, 0.18), transparent 40%),
            linear-gradient(135deg, #0f3d47 0%, #0e7490 52%, #be123c 140%);
        box-shadow: 0 14px 32px rgba(8, 145, 178, 0.18);
    }

    .wishlists-eyebrow {
        display: block;
        margin-bottom: 0.2rem;
        color: #fda4af;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.09em;
        text-transform: uppercase;
    }

    .wishlists-hero h2 {
        margin: 0;
        font-size: 1.55rem;
        font-weight: 700;
    }

    .wishlists-hero p {
        margin: 0.4rem 0 0;
        max-width: 34rem;
        color: rgba(255, 255, 255, 0.84);
    }

    .wishlists-hero-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 0.9rem;
    }

    .wishlists-hero-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.28rem 0.7rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.16);
        font-size: 0.78rem;
        font-weight: 600;
    }

    .wishlists-hero-visual {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 4.5rem;
        height: 4.5rem;
        flex-shrink: 0;
        font-size: 1.75rem;
        color: #fecdd3;
    }

    .wishlists-hero-orb {
        position: absolute;
        inset: 0;
        border-radius: 1.25rem;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.14);
        transform: rotate(12deg);
    }

    .wishlists-hero-visual i {
        position: relative;
        z-index: 1;
        filter: drop-shadow(0 8px 16px rgba(190, 18, 60, 0.35));
    }

    .wishlists-stat {
        display: flex;
        align-items: center;
        gap: 0.9rem;
        height: 100%;
        padding: 1rem 1.1rem;
        border: 1px solid var(--wl-border);
        border-radius: 0.95rem;
        background: #fff;
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.04);
    }

    .wishlists-stat-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.6rem;
        height: 2.6rem;
        border-radius: 0.75rem;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .wishlists-stat--total .wishlists-stat-icon { background: #fff1f2; color: #e11d48; }
    .wishlists-stat--customers .wishlists-stat-icon { background: #ecfeff; color: #0891b2; }
    .wishlists-stat--products .wishlists-stat-icon { background: #eff6ff; color: #2563eb; }
    .wishlists-stat--today .wishlists-stat-icon { background: #f5f3ff; color: #7c3aed; }

    .wishlists-stat-value {
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--wl-ink);
        line-height: 1.1;
    }

    .wishlists-stat-label {
        margin-top: 0.15rem;
        color: var(--wl-muted);
        font-size: 0.82rem;
        font-weight: 500;
    }

    .wishlists-card {
        border: 1px solid var(--wl-border);
        border-radius: 1rem;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }

    .wishlists-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.15rem;
        border-bottom: 1px solid #eef2f7;
        background: #fff;
        flex-wrap: wrap;
    }

    .wishlists-card-head h3 {
        font-size: 1rem;
        font-weight: 700;
        color: var(--wl-ink);
    }

    .wishlists-card-head p {
        font-size: 0.8rem;
    }

    .wishlists-search {
        min-width: min(100%, 18rem);
    }

    .wishlists-search .form-control {
        min-height: 2.4rem;
        border-color: #dbe3ed;
        border-radius: 0.55rem 0 0 0.55rem;
        box-shadow: none;
    }

    .wishlists-search .form-control:focus {
        border-color: #22d3ee;
        box-shadow: 0 0 0 3px rgba(34, 211, 238, 0.12);
    }

    .wishlists-search .btn {
        min-height: 2.4rem;
    }

    .wishlists-table thead th {
        border-top: 0;
        border-bottom: 1px solid #e2e8f0;
        background: #f8fafc;
        color: var(--wl-muted);
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        vertical-align: middle;
        white-space: nowrap;
    }

    .wishlists-table tbody td {
        padding-top: 0.95rem;
        padding-bottom: 0.95rem;
        border-top-color: #eef2f7;
        vertical-align: middle;
    }

    .wishlists-table tbody tr:hover {
        background: #f8fafc;
    }

    .wishlists-product {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        min-width: 0;
    }

    .wishlists-thumb {
        position: relative;
        width: 3.25rem;
        height: 3.25rem;
        border-radius: 0.75rem;
        overflow: hidden;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        flex-shrink: 0;
    }

    .wishlists-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: top;
    }

    .wishlists-thumb-fallback {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
        color: #94a3b8;
    }

    .wishlists-thumb-heart {
        position: absolute;
        right: 0.2rem;
        bottom: 0.2rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.05rem;
        height: 1.05rem;
        border-radius: 999px;
        background: #fff;
        color: #e11d48;
        font-size: 0.5rem;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.15);
    }

    .wishlists-product-name {
        font-weight: 700;
        color: #334155;
        line-height: 1.3;
    }

    .wishlists-product-sub {
        margin-top: 0.15rem;
        color: var(--wl-muted);
        font-size: 0.78rem;
    }

    .wishlists-customer {
        display: flex;
        align-items: center;
        gap: 0.7rem;
        min-width: 0;
    }

    .wishlists-avatar {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.35rem;
        height: 2.35rem;
        border-radius: 0.7rem;
        background: linear-gradient(135deg, #0e7490, #0891b2);
        color: #fff;
        font-size: 0.82rem;
        font-weight: 700;
        flex-shrink: 0;
    }

    .wishlists-avatar--image {
        object-fit: cover;
        background: #f1f5f9;
    }

    .wishlists-customer-name {
        font-weight: 700;
        color: #334155;
    }

    .wishlists-customer-email {
        color: var(--wl-muted);
        font-size: 0.78rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 14rem;
    }

    .wishlists-price {
        font-weight: 700;
        color: var(--wl-ink);
        white-space: nowrap;
    }

    .wishlists-date {
        font-weight: 600;
        color: #334155;
        font-size: 0.86rem;
    }

    .wishlists-date-sub {
        margin-top: 0.1rem;
        color: var(--wl-muted);
        font-size: 0.75rem;
    }

    .wishlists-actions {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.3rem;
        flex-wrap: wrap;
    }

    .wishlists-actions .btn {
        width: 1.9rem;
        height: 1.9rem;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.5rem;
    }

    .wishlists-empty {
        padding: 3.25rem 1rem !important;
        text-align: center;
        color: var(--wl-muted);
    }

    .wishlists-empty i {
        display: block;
        margin-bottom: 0.75rem;
        font-size: 2.1rem;
        color: #fb7185;
        opacity: 0.7;
    }

    .wishlists-empty strong {
        display: block;
        color: #334155;
        font-size: 1rem;
    }

    .wishlists-empty p {
        margin: 0.35rem 0 0;
        font-size: 0.86rem;
    }

    .wishlists-card-footer {
        padding: 0.85rem 1rem;
        border-top: 1px solid #eef2f7;
        background: #f8fafc;
    }

    @media (max-width: 767.98px) {
        .wishlists-hero {
            align-items: flex-start;
            flex-direction: column;
            padding: 1.1rem;
        }

        .wishlists-hero h2 {
            font-size: 1.3rem;
        }

        .wishlists-hero-visual {
            display: none;
        }

        .wishlists-search {
            width: 100%;
        }
    }
</style>
@endpush
