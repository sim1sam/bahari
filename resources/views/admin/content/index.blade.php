@extends('layouts.admin')

@section('title', 'Import Product')
@section('page_title', 'Content — Received Images')

@section('content')
    <div class="ecom-page content-page">
        <form id="batch-form" action="{{ route('admin.content.process-batch') }}" method="POST" class="d-none">
            @csrf
        </form>
        <form id="delete-batch-form" action="{{ route('admin.content.destroy-batch') }}" method="POST" class="d-none">
            @csrf
        </form>

        @if (session('generated_credentials'))
            <div class="content-alert content-alert--success">
                <i class="fas fa-key"></i>
                <div>
                    <strong>API credentials generated</strong>
                    <span>
                        Key: <code>{{ session('generated_credentials')['api_key'] }}</code> ·
                        Token: <code>{{ session('generated_credentials')['api_token'] }}</code>
                    </span>
                </div>
            </div>
        @endif

        <section class="ecom-hero">
            <div>
                <span class="ecom-eyebrow">Ecommerce</span>
                <h2>Import Product</h2>
                <p>Review API-received images and process them into your catalog pipeline.</p>
            </div>
            <div class="ecom-hero-actions">
                <a href="{{ route('admin.processed.index') }}" class="btn btn-info">
                    <i class="fas fa-check-circle mr-1"></i> Processed Product
                </a>
                <form action="{{ route('admin.content.repair-images') }}" method="POST" class="d-inline" onsubmit="return confirm('Re-download images and sync prices from API payload for pending items?')">
                    @csrf
                    <button type="submit" class="btn btn-light">
                        <i class="fas fa-sync mr-1"></i> Repair Images
                    </button>
                </form>
            </div>
        </section>

        <section class="row ecom-stats">
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="ecom-stat ecom-stat--total">
                    <span class="ecom-stat-icon"><i class="fas fa-inbox"></i></span>
                    <div>
                        <div class="ecom-stat-value">{{ number_format($stats['pending']) }}</div>
                        <div class="ecom-stat-label">Pending Received</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="ecom-stat ecom-stat--manual">
                    <span class="ecom-stat-icon"><i class="fas fa-magic"></i></span>
                    <div>
                        <div class="ecom-stat-value">{{ number_format($stats['processed']) }}</div>
                        <div class="ecom-stat-label">Processed</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="ecom-stat ecom-stat--live">
                    <span class="ecom-stat-icon"><i class="fas fa-store"></i></span>
                    <div>
                        <div class="ecom-stat-value">{{ number_format($stats['imported']) }}</div>
                        <div class="ecom-stat-label">Published</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="ecom-stat ecom-stat--api">
                    <span class="ecom-stat-icon"><i class="fas fa-tags"></i></span>
                    <div>
                        <div class="ecom-stat-value">{{ number_format($stats['brands']) }}</div>
                        <div class="ecom-stat-label">Active Brands</div>
                    </div>
                </article>
            </div>
        </section>

        <div class="card ecom-card content-gallery-wrap">
            <div class="content-batch-bar" id="content-batch-bar">
                <div class="content-batch-bar__top">
                    <div class="content-batch-bar__lead">
                        <span class="content-batch-bar__badge" id="batch-count-badge">0</span>
                        <div>
                            <h3 class="content-batch-bar__title">Batch Process</h3>
                            <p class="content-batch-bar__summary" id="batch-selection-summary">Select images below to apply watermark</p>
                        </div>
                    </div>

                    @if ($logoUrl)
                        <a href="{{ route('admin.settings.watermark.edit') }}" class="content-batch-bar__watermark" title="Edit watermark">
                            <span class="content-batch-bar__watermark-thumb">
                                <img src="{{ $logoUrl }}" alt="Watermark">
                            </span>
                            <span class="content-batch-bar__watermark-meta">
                                <strong>Watermark ready</strong>
                                <span>{{ $logoScale }}% of image width</span>
                            </span>
                            <i class="fas fa-pen content-batch-bar__watermark-edit"></i>
                        </a>
                    @else
                        <a href="{{ route('admin.settings.watermark.edit') }}" class="content-batch-bar__watermark content-batch-bar__watermark--missing">
                            <span class="content-batch-bar__watermark-thumb content-batch-bar__watermark-thumb--empty">
                                <i class="fas fa-stamp"></i>
                            </span>
                            <span class="content-batch-bar__watermark-meta">
                                <strong>Watermark required</strong>
                                <span>Upload before processing</span>
                            </span>
                            <i class="fas fa-arrow-right content-batch-bar__watermark-edit"></i>
                        </a>
                    @endif

                    <div class="content-batch-bar__select">
                        <label class="content-select-chip content-select-chip--page">
                            <input type="checkbox" id="select-page">
                            <span><i class="fas fa-check"></i> This page</span>
                        </label>
                        <label class="content-select-chip content-select-chip--all">
                            <input type="checkbox" id="select-all-pages">
                            <span><i class="fas fa-layer-group"></i> All pages</span>
                        </label>
                    </div>

                    <div class="content-batch-bar__actions">
                        <button type="button" class="btn btn-info content-batch-bar__process" id="btn-process-selected" disabled>
                            <i class="fas fa-magic"></i>
                            <span>Process Selected</span>
                        </button>
                        <button type="button" class="btn btn-outline-danger content-batch-bar__delete" id="btn-delete-selected" disabled>
                            <i class="fas fa-trash"></i>
                            <span>Delete</span>
                        </button>
                    </div>
                </div>

                <div class="content-batch-bar__steps" aria-hidden="true">
                    <span class="content-batch-step content-batch-step--select"><i class="fas fa-check-square"></i> Select images</span>
                    <span class="content-batch-step-line"></span>
                    <span class="content-batch-step content-batch-step--process"><i class="fas fa-stamp"></i> Apply watermark</span>
                    <span class="content-batch-step-line"></span>
                    <span class="content-batch-step content-batch-step--done"><i class="fas fa-check-circle"></i> Move to processed</span>
                </div>
            </div>

            <div class="content-toolbar">
                <div class="content-toolbar-top">
                    <div>
                        <h3 class="mb-0">Received Images</h3>
                        <p class="mb-0 text-muted">
                            {{ number_format($pendingCount) }} pending ·
                            Showing {{ $items->firstItem() ?? 0 }}–{{ $items->lastItem() ?? 0 }} of {{ $items->total() }}
                        </p>
                    </div>
                </div>

                <form action="{{ route('admin.content.index') }}" method="GET" class="content-filter-bar">
                    <div class="content-filter-field">
                        <label>Brand</label>
                        <select name="brand" class="form-control" aria-label="Brand">
                            <option value="">All brands</option>
                            @foreach ($brands as $brandOption)
                                <option value="{{ $brandOption }}" @selected($brand === $brandOption)>{{ $brandOption }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="content-filter-field">
                        <label>From</label>
                        <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}" aria-label="From date">
                    </div>
                    <div class="content-filter-field">
                        <label>To</label>
                        <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}" aria-label="To date">
                    </div>
                    <div class="content-filter-actions">
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-filter mr-1"></i> Apply
                        </button>
                        @if ($brand || $dateFrom || $dateTo)
                            <a href="{{ route('admin.content.index') }}" class="btn btn-outline-secondary">Clear</a>
                        @endif
                    </div>
                </form>

                @if ($brand || $dateFrom || $dateTo)
                    <div class="content-active-filters">
                        <span class="content-active-filters-label">Active filters:</span>
                        @if ($brand)
                            <span class="content-filter-chip"><i class="fas fa-tag"></i> {{ $brand }}</span>
                        @endif
                        @if ($dateFrom)
                            <span class="content-filter-chip"><i class="fas fa-calendar"></i> From {{ $dateFrom }}</span>
                        @endif
                        @if ($dateTo)
                            <span class="content-filter-chip"><i class="fas fa-calendar"></i> To {{ $dateTo }}</span>
                        @endif
                    </div>
                @endif
            </div>

            <div class="content-grid-body">
                @if ($items->isEmpty())
                    <div class="content-empty">
                        <i class="fas fa-cloud-download-alt"></i>
                        <strong>No received images</strong>
                        <p>Items sent from the Content API will appear here for review and processing.</p>
                    </div>
                @else
                    <div class="content-gallery-grid">
                        @foreach ($items as $item)
                            <article class="content-gallery-card {{ $item->imageUrl() ? '' : 'content-gallery-card--missing' }}" data-item-id="{{ $item->id }}">
                                <label class="content-gallery-select">
                                    <input type="checkbox" class="item-check" name="items[]" value="{{ $item->id }}" form="batch-form">
                                    <span class="content-gallery-select-mark"><i class="fas fa-check"></i></span>
                                </label>
                                <div class="content-gallery-corner-actions">
                                    <form action="{{ route('admin.content.reimport', $item) }}" method="POST" class="content-gallery-reimport" onsubmit="return confirm('Re-import this item from the API payload?')">
                                        @csrf
                                        <button type="submit" class="content-gallery-reimport-btn" title="Re-import">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.content.destroy', $item) }}" method="POST" class="content-gallery-delete" onsubmit="return confirm('Delete this received item permanently?')">
                                        @csrf
                                        <button type="submit" class="content-gallery-delete-btn" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                                    <a href="{{ route('admin.content.show', $item) }}" class="content-gallery-media">
                                        @if ($item->imageUrl())
                                            <img src="{{ $item->imageUrl() }}" alt="{{ $item->title }}">
                                            <span class="content-gallery-overlay">
                                                <i class="fas fa-search-plus"></i> Preview
                                            </span>
                                        @else
                                            <span class="content-gallery-no-image">
                                                <i class="fas fa-image"></i>
                                                <span>Missing image</span>
                                            </span>
                                        @endif
                                    </a>
                                    <div class="content-gallery-body">
                                        <h4 class="content-gallery-title" title="{{ $item->title }}">{{ $item->title }}</h4>
                                        <div class="content-gallery-tags">
                                            @if ($item->brand)
                                                <span class="content-gallery-tag content-gallery-tag--brand">{{ $item->brand }}</span>
                                            @endif
                                            @if ($item->sku)
                                                <span class="content-gallery-tag">{{ $item->sku }}</span>
                                            @endif
                                        </div>
                                        @if ($item->vendor)
                                            <p class="content-gallery-vendor">{{ $item->vendor }}</p>
                                        @endif
                                        <div class="content-gallery-footer">
                                            <span class="content-gallery-price">{{ money($item->price) }}</span>
                                            <a href="{{ route('admin.content.show', $item) }}" class="content-gallery-open">
                                                Open <i class="fas fa-arrow-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if ($items->hasPages())
                    <div class="ecom-card-footer">{{ $items->links() }}</div>
                @endif
        </div>
    </div>
@endsection

@push('styles')
@include('admin.partials.ecom-page-styles')
<style>
    .content-alert {
        display: flex;
        align-items: flex-start;
        gap: 0.85rem;
        margin-bottom: 1rem;
        padding: 0.85rem 1rem;
        border-radius: 0.85rem;
    }

    .content-alert > i { font-size: 1.1rem; margin-top: 0.1rem; }
    .content-alert strong { display: block; font-size: 0.9rem; }
    .content-alert span { display: block; margin-top: 0.15rem; font-size: 0.82rem; }
    .content-alert code { font-size: 0.78rem; }

    .content-alert--success {
        border: 1px solid #a7f3d0;
        background: #ecfdf5;
        color: #047857;
    }

    .content-alert--warning {
        border: 1px solid #fde68a;
        background: #fffbeb;
        color: #b45309;
    }


    .content-batch-bar {
        padding: 1rem 1.15rem 0.9rem;
        border-bottom: 1px solid #dbeafe;
        background:
            radial-gradient(circle at 100% 0%, rgba(34, 211, 238, 0.12), transparent 42%),
            linear-gradient(135deg, #f0f9ff 0%, #ecfeff 55%, #f8fafc 100%);
    }

    .content-batch-bar--active {
        background:
            radial-gradient(circle at 100% 0%, rgba(34, 211, 238, 0.18), transparent 42%),
            linear-gradient(135deg, #e0f2fe 0%, #cffafe 55%, #f0fdfa 100%);
        box-shadow: inset 0 -1px 0 rgba(8, 145, 178, 0.08);
    }

    .content-batch-bar__top {
        display: grid;
        grid-template-columns: minmax(200px, 1.2fr) minmax(180px, 1fr) auto auto;
        gap: 0.85rem;
        align-items: center;
    }

    .content-batch-bar__lead {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        min-width: 0;
    }

    .content-batch-bar__badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.75rem;
        height: 2.75rem;
        border-radius: 0.85rem;
        background: #fff;
        border: 2px solid #bae6fd;
        color: #0891b2;
        font-size: 1rem;
        font-weight: 800;
        flex-shrink: 0;
        transition: all 0.2s ease;
    }

    .content-batch-bar--active .content-batch-bar__badge {
        background: linear-gradient(135deg, #0891b2, #06b6d4);
        border-color: #0891b2;
        color: #fff;
        box-shadow: 0 8px 18px rgba(8, 145, 178, 0.28);
    }

    .content-batch-bar__title {
        margin: 0;
        color: #0f172a;
        font-size: 0.95rem;
        font-weight: 800;
    }

    .content-batch-bar__summary {
        margin: 0.15rem 0 0;
        color: #64748b;
        font-size: 0.78rem;
        line-height: 1.35;
    }

    .content-batch-bar--active .content-batch-bar__summary {
        color: #0e7490;
        font-weight: 600;
    }

    .content-batch-bar__watermark {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0.55rem 0.7rem;
        border: 1px solid #a5f3fc;
        border-radius: 0.85rem;
        background: rgba(255, 255, 255, 0.88);
        color: inherit;
        text-decoration: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease, transform 0.15s ease;
        min-width: 0;
    }

    .content-batch-bar__watermark:hover {
        border-color: #22d3ee;
        box-shadow: 0 8px 20px rgba(8, 145, 178, 0.12);
        transform: translateY(-1px);
        text-decoration: none;
        color: inherit;
    }

    .content-batch-bar__watermark--missing {
        border-color: #fde68a;
        background: #fffbeb;
    }

    .content-batch-bar__watermark--missing:hover {
        border-color: #fbbf24;
        box-shadow: 0 8px 20px rgba(245, 158, 11, 0.12);
    }

    .content-batch-bar__watermark-thumb {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 0.65rem;
        background: #ecfeff;
        overflow: hidden;
        flex-shrink: 0;
    }

    .content-batch-bar__watermark-thumb img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .content-batch-bar__watermark-thumb--empty {
        color: #d97706;
        background: #fef3c7;
        font-size: 1rem;
    }

    .content-batch-bar__watermark-meta {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .content-batch-bar__watermark-meta strong {
        color: #0f172a;
        font-size: 0.78rem;
        font-weight: 800;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .content-batch-bar__watermark-meta span {
        color: #64748b;
        font-size: 0.7rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .content-batch-bar__watermark--missing .content-batch-bar__watermark-meta strong {
        color: #b45309;
    }

    .content-batch-bar__watermark-edit {
        margin-left: auto;
        color: #94a3b8;
        font-size: 0.72rem;
        flex-shrink: 0;
    }

    .content-batch-bar__select {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.45rem;
    }

    .content-batch-bar__actions {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        justify-content: flex-end;
    }

    .content-batch-bar__process,
    .content-batch-bar__delete {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-weight: 700;
        white-space: nowrap;
        padding: 0.55rem 1rem;
    }

    .content-batch-bar__process {
        box-shadow: 0 8px 20px rgba(8, 145, 178, 0.2);
    }

    .content-batch-bar__steps {
        display: flex;
        align-items: center;
        gap: 0.45rem;
        margin-top: 0.85rem;
        padding-top: 0.8rem;
        border-top: 1px dashed rgba(8, 145, 178, 0.18);
        flex-wrap: wrap;
    }

    .content-batch-step {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.28rem 0.6rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.72);
        color: #64748b;
        font-size: 0.7rem;
        font-weight: 700;
    }

    .content-batch-step--select { color: #2563eb; }
    .content-batch-step--process { color: #0891b2; }
    .content-batch-step--done { color: #059669; }

    .content-batch-bar--active .content-batch-step--select {
        background: #eff6ff;
        color: #1d4ed8;
    }

    .content-batch-step-line {
        flex: 1;
        min-width: 1.5rem;
        height: 2px;
        border-radius: 999px;
        background: linear-gradient(90deg, #bae6fd, #ddd6fe);
        opacity: 0.8;
    }

    .content-panel-label {
        display: flex;
        align-items: center;
        gap: 0.45rem;
        margin-bottom: 0.75rem;
        color: #0891b2;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .content-toolbar {
        padding: 1rem 1.15rem 0.85rem;
        border-bottom: 1px solid #eef2f7;
        background: #fff;
    }

    .content-toolbar-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 0.9rem;
        flex-wrap: wrap;
    }

    .content-toolbar-top h3 {
        font-size: 1rem;
        font-weight: 700;
        color: #0f172a;
    }

    .content-toolbar-top p {
        font-size: 0.8rem;
    }

    .content-select-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.45rem;
    }

    .content-select-chip {
        margin: 0;
        cursor: pointer;
    }

    .content-select-chip input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .content-select-chip span {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.45rem 0.85rem;
        border: 2px solid transparent;
        border-radius: 999px;
        font-size: 0.76rem;
        font-weight: 700;
        transition: all 0.15s ease;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
    }

    .content-select-chip--page span {
        background: #eff6ff;
        border-color: #93c5fd;
        color: #1d4ed8;
    }

    .content-select-chip--page:hover span {
        background: #dbeafe;
        border-color: #60a5fa;
    }

    .content-select-chip--page input:checked + span {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        border-color: #1d4ed8;
        color: #fff;
        box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);
    }

    .content-select-chip--all span {
        background: #f5f3ff;
        border-color: #c4b5fd;
        color: #6d28d9;
    }

    .content-select-chip--all:hover span {
        background: #ede9fe;
        border-color: #a78bfa;
    }

    .content-select-chip--all input:checked + span {
        background: linear-gradient(135deg, #7c3aed, #6d28d9);
        border-color: #6d28d9;
        color: #fff;
        box-shadow: 0 4px 14px rgba(124, 58, 237, 0.35);
    }

    .content-select-chip input:checked + span i {
        color: inherit;
    }

    .content-select-status {
        color: #0891b2;
        font-size: 0.76rem;
        font-weight: 700;
    }

    .content-filter-bar {
        display: grid;
        grid-template-columns: minmax(160px, 1.4fr) repeat(2, minmax(130px, 1fr)) auto;
        gap: 0.65rem;
        align-items: end;
        padding: 0.85rem;
        border: 1px solid #e2e8f0;
        border-radius: 0.85rem;
        background: #f8fafc;
    }

    .content-filter-field label {
        display: block;
        margin-bottom: 0.3rem;
        color: #64748b;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .content-filter-field .form-control {
        min-height: 2.45rem;
        border-color: #dbe3ed;
        border-radius: 0.6rem;
        background: #fff;
        box-shadow: none;
    }

    .content-filter-field .form-control:focus {
        border-color: #22d3ee;
        box-shadow: 0 0 0 3px rgba(34, 211, 238, 0.12);
    }

    .content-filter-actions {
        display: flex;
        gap: 0.4rem;
        align-items: center;
        padding-bottom: 0.05rem;
    }

    .content-active-filters {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.45rem;
        margin-top: 0.75rem;
    }

    .content-active-filters-label {
        color: #64748b;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .content-filter-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.25rem 0.6rem;
        border-radius: 999px;
        background: #ecfeff;
        color: #0e7490;
        font-size: 0.74rem;
        font-weight: 600;
    }

    .content-grid-body {
        padding: 1rem 1.15rem 0.75rem;
        background: #f8fafc;
    }

    .content-gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(11.5rem, 1fr));
        gap: 1rem;
    }

    .content-gallery-card {
        position: relative;
        display: flex;
        flex-direction: column;
        border: 2px solid transparent;
        border-radius: 1rem;
        background: #fff;
        overflow: hidden;
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06);
        transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
    }

    .content-gallery-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(8, 145, 178, 0.12);
    }

    .content-gallery-card--selected {
        border-color: #0891b2;
        box-shadow: 0 0 0 3px rgba(8, 145, 178, 0.15), 0 12px 28px rgba(8, 145, 178, 0.12);
    }

    .content-gallery-card--missing .content-gallery-media {
        background: #fef2f2;
    }

    .content-gallery-select {
        position: absolute;
        top: 0.55rem;
        left: 0.55rem;
        z-index: 3;
        margin: 0;
        cursor: pointer;
    }

    .content-gallery-select input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .content-gallery-select-mark {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.65rem;
        height: 1.65rem;
        border: 2px solid rgba(255, 255, 255, 0.95);
        border-radius: 0.45rem;
        background: rgba(15, 23, 42, 0.45);
        color: transparent;
        font-size: 0.7rem;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.2);
        transition: all 0.15s ease;
    }

    .content-gallery-card:hover .content-gallery-select-mark,
    .content-gallery-card--selected .content-gallery-select-mark {
        background: #fff;
        border-color: #0891b2;
        color: transparent;
    }

    .content-gallery-select input:checked + .content-gallery-select-mark {
        background: #0891b2;
        border-color: #0891b2;
        color: #fff;
    }

    .content-gallery-corner-actions {
        position: absolute;
        top: 0.55rem;
        right: 0.55rem;
        z-index: 3;
        display: flex;
        gap: 0.35rem;
    }

    .content-gallery-delete,
    .content-gallery-reimport {
        margin: 0;
    }

    .content-gallery-delete-btn,
    .content-gallery-reimport-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.65rem;
        height: 1.65rem;
        border: 2px solid rgba(255, 255, 255, 0.95);
        border-radius: 0.45rem;
        color: #fff;
        font-size: 0.68rem;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.2);
        cursor: pointer;
        transition: background 0.15s ease, transform 0.15s ease;
    }

    .content-gallery-delete-btn {
        background: rgba(220, 38, 38, 0.88);
    }

    .content-gallery-reimport-btn {
        background: rgba(8, 145, 178, 0.92);
    }

    .content-gallery-delete-btn:hover {
        background: #b91c1c;
        transform: scale(1.05);
    }

    .content-gallery-reimport-btn:hover {
        background: #0e7490;
        transform: scale(1.05);
    }

    .content-gallery-media {
        position: relative;
        display: block;
        aspect-ratio: 3 / 4;
        overflow: hidden;
        background: #e2e8f0;
    }

    .content-gallery-media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.2s ease;
    }

    .content-gallery-card:hover .content-gallery-media img {
        transform: scale(1.04);
    }

    .content-gallery-overlay {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        background: rgba(15, 23, 42, 0.45);
        color: #fff;
        font-size: 0.78rem;
        font-weight: 700;
        opacity: 0;
        transition: opacity 0.15s ease;
    }

    .content-gallery-card:hover .content-gallery-overlay {
        opacity: 1;
    }

    .content-gallery-no-image {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        height: 100%;
        color: #b91c1c;
        font-size: 0.76rem;
        font-weight: 600;
    }

    .content-gallery-no-image i {
        font-size: 1.4rem;
        opacity: 0.55;
    }

    .content-gallery-body {
        display: flex;
        flex-direction: column;
        flex: 1;
        padding: 0.75rem 0.8rem 0.8rem;
    }

    .content-gallery-title {
        margin: 0 0 0.45rem;
        color: #1e293b;
        font-size: 0.8rem;
        font-weight: 700;
        line-height: 1.35;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .content-gallery-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.3rem;
        margin-bottom: 0.35rem;
    }

    .content-gallery-tag {
        display: inline-block;
        padding: 0.15rem 0.45rem;
        border-radius: 999px;
        background: #f1f5f9;
        color: #64748b;
        font-size: 0.65rem;
        font-weight: 700;
        max-width: 100%;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .content-gallery-tag--brand {
        background: #f5f3ff;
        color: #6d28d9;
    }

    .content-gallery-vendor {
        margin: 0 0 0.5rem;
        color: #94a3b8;
        font-size: 0.68rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .content-gallery-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        margin-top: auto;
        padding-top: 0.55rem;
        border-top: 1px solid #f1f5f9;
    }

    .content-gallery-price {
        color: #0891b2;
        font-size: 0.88rem;
        font-weight: 800;
    }

    .content-gallery-open {
        color: #64748b;
        font-size: 0.72rem;
        font-weight: 700;
        text-decoration: none;
        white-space: nowrap;
    }

    .content-gallery-open:hover {
        color: #0891b2;
        text-decoration: none;
    }

    .content-empty {
        padding: 3rem 1rem;
        text-align: center;
        color: #64748b;
    }

    .content-empty i {
        display: block;
        margin-bottom: 0.75rem;
        font-size: 2rem;
        opacity: 0.45;
    }

    .content-empty strong {
        display: block;
        color: #334155;
        font-size: 1rem;
    }

    .content-empty p {
        margin: 0.35rem 0 0;
        font-size: 0.86rem;
    }

    @media (max-width: 1199.98px) {
        .content-batch-bar__top {
            grid-template-columns: 1fr 1fr;
        }

        .content-batch-bar__actions {
            grid-column: 1 / -1;
            justify-content: stretch;
        }

        .content-batch-bar__process,
        .content-batch-bar__delete {
            flex: 1;
            justify-content: center;
        }
    }

    @media (max-width: 991.98px) {
        .content-batch-bar__top {
            grid-template-columns: 1fr;
        }

        .content-filter-bar {
            grid-template-columns: 1fr 1fr;
        }

        .content-filter-actions {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 767.98px) {
        .content-batch-bar__select {
            width: 100%;
        }

        .content-batch-bar__actions {
            flex-direction: column;
        }

        .content-batch-bar__process,
        .content-batch-bar__delete {
            width: 100%;
        }

        .content-batch-bar__steps {
            display: none;
        }

        .content-toolbar-top {
            flex-direction: column;
        }

        .content-select-toolbar {
            width: 100%;
        }

        .content-filter-bar {
            grid-template-columns: 1fr;
        }

        .content-gallery-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
(function () {
    var checks = document.querySelectorAll('.item-check');
    var selectPage = document.getElementById('select-page');
    var selectAllPagesCheckbox = document.getElementById('select-all-pages');
    var selectAllStatus = document.getElementById('select-all-status');
    var batchBar = document.getElementById('content-batch-bar');
    var batchCountBadge = document.getElementById('batch-count-badge');
    var batchSummary = document.getElementById('batch-selection-summary');
    var btn = document.getElementById('btn-process-selected');
    var deleteBtn = document.getElementById('btn-delete-selected');
    var form = document.getElementById('batch-form');
    var deleteForm = document.getElementById('delete-batch-form');
    var filteredTotal = {{ (int) $items->total() }};
    var pageTotal = checks.length;
    var filterBrand = @json($brand);
    var filterDateFrom = @json($dateFrom);
    var filterDateTo = @json($dateTo);
    var selectAllPages = false;

    function selectedChecks() {
        return Array.from(checks).filter(function (c) { return c.checked; });
    }

    function hasSelection() {
        return selectAllPages || selectedChecks().length > 0;
    }

    function selectionCount() {
        if (selectAllPages) {
            return filteredTotal;
        }

        return selectedChecks().length;
    }

    function updateSelectAllStatus() {
        var count = selectionCount();

        if (batchCountBadge) {
            batchCountBadge.textContent = String(count);
        }

        if (batchSummary) {
            if (selectAllPages && filteredTotal > 0) {
                batchSummary.textContent = 'All ' + filteredTotal + ' matching images selected across all pages';
            } else if (count > 0) {
                batchSummary.textContent = count + ' image' + (count === 1 ? '' : 's') + ' ready to process';
            } else {
                batchSummary.textContent = 'Select images below to apply watermark';
            }
        }

        if (batchBar) {
            batchBar.classList.toggle('content-batch-bar--active', count > 0);
        }

        if (!selectAllStatus) return;
        if (selectAllPages && filteredTotal > 0) {
            selectAllStatus.textContent = 'All ' + filteredTotal + ' matching items selected (all pages)';
            selectAllStatus.classList.remove('d-none');
        } else if (selectedChecks().length > 0) {
            selectAllStatus.textContent = selectedChecks().length + ' on this page selected';
            selectAllStatus.classList.remove('d-none');
        } else {
            selectAllStatus.textContent = '';
            selectAllStatus.classList.add('d-none');
        }
    }

    function syncPageCheckbox() {
        if (!selectPage || selectAllPages) return;
        selectPage.checked = pageTotal > 0 && selectedChecks().length === pageTotal;
    }

    function clearMasterSelection() {
        selectAllPages = false;
        if (selectAllPagesCheckbox) selectAllPagesCheckbox.checked = false;
        if (selectPage) selectPage.checked = false;
    }

    function selectionCountLabel() {
        if (selectAllPages) {
            return 'all ' + filteredTotal + ' matching';
        }
        if (selectPage && selectPage.checked) {
            return selectedChecks().length + ' on this page';
        }
        return selectedChecks().length + ' selected';
    }

    function updateCardStates() {
        document.querySelectorAll('.content-gallery-card').forEach(function (card) {
            var check = card.querySelector('.item-check');
            if (!check) return;
            card.classList.toggle('content-gallery-card--selected', check.checked);
        });
    }

    function updateBtn() {
        if (btn) btn.disabled = !hasSelection();
        if (deleteBtn) deleteBtn.disabled = !hasSelection();
        updateSelectAllStatus();
        updateCardStates();
    }

    function prepareDeleteBatchSubmit() {
        if (!deleteForm) return;

        deleteForm.querySelectorAll('input[name="select_all"]').forEach(function (el) { el.remove(); });
        deleteForm.querySelectorAll('input[name="filter_brand"]').forEach(function (el) { el.remove(); });
        deleteForm.querySelectorAll('input[name="filter_date_from"]').forEach(function (el) { el.remove(); });
        deleteForm.querySelectorAll('input[name="filter_date_to"]').forEach(function (el) { el.remove(); });
        deleteForm.querySelectorAll('input[name="items[]"]').forEach(function (el) { el.remove(); });

        if (selectAllPages) {
            var selectInput = document.createElement('input');
            selectInput.type = 'hidden';
            selectInput.name = 'select_all';
            selectInput.value = '1';
            deleteForm.appendChild(selectInput);

            if (filterBrand) {
                var brandInput = document.createElement('input');
                brandInput.type = 'hidden';
                brandInput.name = 'filter_brand';
                brandInput.value = filterBrand;
                deleteForm.appendChild(brandInput);
            }
            if (filterDateFrom) {
                var fromInput = document.createElement('input');
                fromInput.type = 'hidden';
                fromInput.name = 'filter_date_from';
                fromInput.value = filterDateFrom;
                deleteForm.appendChild(fromInput);
            }
            if (filterDateTo) {
                var toInput = document.createElement('input');
                toInput.type = 'hidden';
                toInput.name = 'filter_date_to';
                toInput.value = filterDateTo;
                deleteForm.appendChild(toInput);
            }
        } else {
            selectedChecks().forEach(function (check) {
                var itemInput = document.createElement('input');
                itemInput.type = 'hidden';
                itemInput.name = 'items[]';
                itemInput.value = check.value;
                deleteForm.appendChild(itemInput);
            });
        }
    }

    function prepareBatchSubmit() {
        form.querySelectorAll('input[name="select_all"]').forEach(function (el) { el.remove(); });
        form.querySelectorAll('input[name="filter_brand"]').forEach(function (el) { el.remove(); });
        form.querySelectorAll('input[name="filter_date_from"]').forEach(function (el) { el.remove(); });
        form.querySelectorAll('input[name="filter_date_to"]').forEach(function (el) { el.remove(); });

        if (selectAllPages) {
            checks.forEach(function (c) { c.disabled = true; });

            var selectInput = document.createElement('input');
            selectInput.type = 'hidden';
            selectInput.name = 'select_all';
            selectInput.value = '1';
            form.appendChild(selectInput);

            if (filterBrand) {
                var brandInput = document.createElement('input');
                brandInput.type = 'hidden';
                brandInput.name = 'filter_brand';
                brandInput.value = filterBrand;
                form.appendChild(brandInput);
            }
            if (filterDateFrom) {
                var fromInput = document.createElement('input');
                fromInput.type = 'hidden';
                fromInput.name = 'filter_date_from';
                fromInput.value = filterDateFrom;
                form.appendChild(fromInput);
            }
            if (filterDateTo) {
                var toInput = document.createElement('input');
                toInput.type = 'hidden';
                toInput.name = 'filter_date_to';
                toInput.value = filterDateTo;
                form.appendChild(toInput);
            }
        } else {
            checks.forEach(function (c) { c.disabled = false; });
        }
    }

    checks.forEach(function (c) {
        c.addEventListener('change', function () {
            if (!c.checked) clearMasterSelection();
            syncPageCheckbox();
            updateBtn();
        });
    });

    if (selectPage) {
        selectPage.addEventListener('change', function () {
            selectAllPages = false;
            if (selectAllPagesCheckbox) selectAllPagesCheckbox.checked = false;
            checks.forEach(function (check) { check.checked = selectPage.checked; });
            updateBtn();
        });
    }

    if (selectAllPagesCheckbox) {
        selectAllPagesCheckbox.addEventListener('change', function () {
            selectAllPages = selectAllPagesCheckbox.checked && filteredTotal > 0;
            checks.forEach(function (check) { check.checked = selectAllPagesCheckbox.checked; });
            if (selectPage) selectPage.checked = selectAllPagesCheckbox.checked;
            updateBtn();
        });
    }

    if (btn && form) {
        btn.addEventListener('click', function () {
            if (!hasSelection()) {
                alert('Please select at least one received item.');
                return;
            }
            if (!confirm('Apply watermark and process ' + selectionCountLabel() + ' images?')) {
                return;
            }
            prepareBatchSubmit();
            form.submit();
        });
    }

    if (deleteBtn && deleteForm) {
        deleteBtn.addEventListener('click', function () {
            if (!hasSelection()) {
                alert('Please select at least one received item.');
                return;
            }
            if (!confirm('Delete ' + selectionCountLabel() + ' received item(s) permanently?')) {
                return;
            }
            prepareDeleteBatchSubmit();
            deleteForm.submit();
        });
    }

    updateBtn();
})();
</script>
@endpush
