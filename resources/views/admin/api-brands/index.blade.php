@extends('layouts.admin')

@section('title', 'API Brands')
@section('page_title', 'API Received Brands')

@section('content')
    <div class="ecom-page api-brands-page">
        <section class="ecom-hero">
            <div>
                <span class="ecom-eyebrow">Ecommerce</span>
                <h2>API Brands</h2>
                <p>Manage brands from received API content and link them to import workflows.</p>
            </div>
            <div class="ecom-hero-actions">
                <a href="{{ route('admin.api-brands.create') }}" class="btn btn-info">
                    <i class="fas fa-plus mr-1"></i> Add Brand
                </a>
                <form action="{{ route('admin.api-brands.sync') }}" method="POST" class="d-inline" onsubmit="return confirm('Import brands from all received API items?')">
                    @csrf
                    <button type="submit" class="btn btn-light">
                        <i class="fas fa-sync mr-1"></i> Sync from Received
                    </button>
                </form>
                <a href="{{ route('admin.content.index') }}" class="btn btn-light">
                    <i class="fas fa-cloud-download-alt mr-1"></i> Import Product
                </a>
            </div>
        </section>

        <section class="row ecom-stats">
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="ecom-stat ecom-stat--total">
                    <span class="ecom-stat-icon"><i class="fas fa-tags"></i></span>
                    <div>
                        <div class="ecom-stat-value">{{ number_format($stats['total']) }}</div>
                        <div class="ecom-stat-label">Total Brands</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="ecom-stat ecom-stat--live">
                    <span class="ecom-stat-icon"><i class="fas fa-check-circle"></i></span>
                    <div>
                        <div class="ecom-stat-value">{{ number_format($stats['active']) }}</div>
                        <div class="ecom-stat-label">Active</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="ecom-stat ecom-stat--manual">
                    <span class="ecom-stat-icon"><i class="fas fa-link"></i></span>
                    <div>
                        <div class="ecom-stat-value">{{ number_format($stats['with_items']) }}</div>
                        <div class="ecom-stat-label">With Items</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="ecom-stat ecom-stat--api">
                    <span class="ecom-stat-icon"><i class="fas fa-image"></i></span>
                    <div>
                        <div class="ecom-stat-value">{{ number_format($stats['with_images'] ?? 0) }}</div>
                        <div class="ecom-stat-label">With Images</div>
                    </div>
                </article>
            </div>
        </section>

        <div class="card ecom-card">
            <div class="ecom-card-head">
                <div>
                    <h3 class="mb-0">All Brands</h3>
                    <p class="mb-0 text-muted">Showing {{ $brands->count() }} of {{ $brands->total() }} brands</p>
                </div>
                <a href="{{ route('admin.processed.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-check-circle mr-1"></i> Processed Product
                </a>
            </div>

            @if ($brands->isEmpty())
                <div class="api-brands-empty">
                    <div class="api-brands-empty-visual" aria-hidden="true">
                        <span class="api-brands-empty-orb"></span>
                        <span class="api-brands-empty-icon"><i class="fas fa-tags"></i></span>
                    </div>
                    <h4 class="api-brands-empty-title">No brands yet</h4>
                    <p class="api-brands-empty-copy">
                        Brands appear here when the API sends products, or when you add or sync them manually.
                    </p>
                    <div class="api-brands-empty-actions">
                        <a href="{{ route('admin.api-brands.create') }}" class="btn btn-info">
                            <i class="fas fa-plus mr-1"></i> Add Brand
                        </a>
                        <form action="{{ route('admin.api-brands.sync') }}" method="POST" class="d-inline" onsubmit="return confirm('Import brands from all received API items?')">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary">
                                <i class="fas fa-sync mr-1"></i> Sync from Received
                            </button>
                        </form>
                    </div>
                    <p class="api-brands-empty-hint">
                        Tip: use <strong>Import Product</strong> in the header to pull received API content.
                    </p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table ecom-table mb-0">
                        <thead>
                            <tr>
                                <th>Brand</th>
                                <th>Received Items</th>
                                <th>Status</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($brands as $brand)
                                <tr>
                                    <td>
                                        <div class="ecom-brand-cell">
                                            @if ($brand->imageUrl())
                                                <img src="{{ $brand->imageUrl() }}" alt="{{ $brand->name }}" class="ecom-brand-thumb rounded" style="width:40px;height:52px;object-fit:cover">
                                            @else
                                                <span class="ecom-brand-icon">{{ strtoupper(substr($brand->name, 0, 1)) }}</span>
                                            @endif
                                            <div>
                                                <div class="ecom-brand-name">{{ $brand->name }}</div>
                                                <code class="ecom-brand-slug">{{ $brand->slug }}</code>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="ecom-count-badge ecom-count-badge--api">
                                            <i class="fas fa-images"></i> {{ number_format($brand->received_items_count) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="ecom-status {{ $brand->is_active ? 'ecom-status--live' : 'ecom-status--hidden' }}">
                                            {{ $brand->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <div class="ecom-actions">
                                            <a href="{{ route('admin.content.index', ['brand' => $brand->name]) }}" class="btn btn-xs btn-outline-warning" title="View received">
                                                <i class="fas fa-images"></i>
                                            </a>
                                            <a href="{{ route('admin.processed.index', ['brand' => $brand->name]) }}" class="btn btn-xs btn-outline-info" title="View processed">
                                                <i class="fas fa-check-circle"></i>
                                            </a>
                                            <a href="{{ route('admin.api-brands.edit', $brand) }}" class="btn btn-xs btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.api-brands.destroy', $brand) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this brand? Received items will keep their brand name.')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-xs btn-outline-danger" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($brands->hasPages())
                    <div class="ecom-card-footer">{{ $brands->links() }}</div>
                @endif
            @endif
        </div>
    </div>
@endsection

@push('styles')
@include('admin.partials.ecom-page-styles')
<style>
    .api-brands-empty {
        position: relative;
        margin: 0.35rem 0.85rem 1rem;
        padding: 2.4rem 1.5rem 1.85rem;
        text-align: center;
        border: 1px dashed #cbd5e1;
        border-radius: 1rem;
        background:
            radial-gradient(circle at 50% 0%, rgba(8, 145, 178, 0.10), transparent 55%),
            linear-gradient(180deg, #f8fafc 0%, #ffffff 70%);
        overflow: hidden;
    }

    .api-brands-empty-visual {
        position: relative;
        width: 4.25rem;
        height: 4.25rem;
        margin: 0 auto 1rem;
    }

    .api-brands-empty-orb {
        position: absolute;
        inset: 0;
        border-radius: 1.15rem;
        background: linear-gradient(145deg, #ecfeff 0%, #cffafe 55%, #a5f3fc 100%);
        box-shadow: 0 10px 24px rgba(8, 145, 178, 0.14);
        transform: rotate(8deg);
    }

    .api-brands-empty-icon {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #0e7490;
        font-size: 1.35rem;
        z-index: 1;
    }

    .api-brands-empty-title {
        margin: 0;
        font-size: 1.15rem;
        font-weight: 700;
        color: #0f172a;
    }

    .api-brands-empty-copy {
        max-width: 26rem;
        margin: 0.45rem auto 0;
        color: #64748b;
        font-size: 0.9rem;
        line-height: 1.5;
    }

    .api-brands-empty-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: center;
        gap: 0.55rem;
        margin-top: 1.15rem;
    }

    .api-brands-empty-actions .btn {
        min-width: 9.5rem;
        font-weight: 600;
    }

    .api-brands-empty-hint {
        margin: 1rem 0 0;
        color: #94a3b8;
        font-size: 0.78rem;
    }

    .api-brands-empty-hint strong {
        color: #64748b;
        font-weight: 600;
    }

    @media (max-width: 575.98px) {
        .api-brands-empty {
            margin: 0.25rem 0.5rem 0.75rem;
            padding: 1.75rem 1rem 1.35rem;
        }

        .api-brands-empty-actions {
            flex-direction: column;
            width: 100%;
        }

        .api-brands-empty-actions .btn,
        .api-brands-empty-actions form {
            width: 100%;
        }

        .api-brands-empty-actions .btn {
            display: block;
        }
    }
</style>
@endpush
