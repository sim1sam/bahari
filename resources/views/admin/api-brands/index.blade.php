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
                    <span class="ecom-stat-icon"><i class="fas fa-images"></i></span>
                    <div>
                        <div class="ecom-stat-value">{{ number_format($stats['received_items']) }}</div>
                        <div class="ecom-stat-label">Received Items</div>
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
                        @forelse ($brands as $brand)
                            <tr>
                                <td>
                                    <div class="ecom-brand-cell">
                                        <span class="ecom-brand-icon">{{ strtoupper(substr($brand->name, 0, 1)) }}</span>
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
                        @empty
                            <tr>
                                <td colspan="4" class="ecom-empty">
                                    <i class="fas fa-tags"></i>
                                    <strong>No brands saved yet</strong>
                                    <p>Brands are saved automatically when API sends products, or sync/add manually.</p>
                                    <a href="{{ route('admin.api-brands.create') }}" class="btn btn-sm btn-info mt-2 mr-1">
                                        <i class="fas fa-plus mr-1"></i> Add Brand
                                    </a>
                                    <form action="{{ route('admin.api-brands.sync') }}" method="POST" class="d-inline" onsubmit="return confirm('Import brands from all received API items?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-secondary mt-2">
                                            <i class="fas fa-sync mr-1"></i> Sync from Received
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($brands->hasPages())
                <div class="ecom-card-footer">{{ $brands->links() }}</div>
            @endif
        </div>
    </div>
@endsection

@push('styles')
@include('admin.partials.ecom-page-styles')
@endpush
