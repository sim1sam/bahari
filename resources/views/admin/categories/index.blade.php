@extends('layouts.admin')

@section('title', 'Categories')
@section('page_title', 'Categories')

@section('content')
    <div class="ecom-page categories-page">
        <section class="ecom-hero">
            <div>
                <span class="ecom-eyebrow">Ecommerce</span>
                <h2>Categories</h2>
                <p>Organize your catalog and sync categories from received API content.</p>
            </div>
            <div class="ecom-hero-actions">
                <a href="{{ route('admin.categories.create') }}" class="btn btn-info">
                    <i class="fas fa-plus mr-1"></i> Add Category
                </a>
                <a href="{{ route('admin.content.index') }}" class="btn btn-light">
                    <i class="fas fa-cloud-download-alt mr-1"></i> Import Product
                </a>
                @if ($canSyncReceived ?? false)
                    <form action="{{ route('admin.categories.sync-received') }}" method="POST" class="d-inline" onsubmit="return confirm('Create categories from all received API items?')">
                        @csrf
                        <button type="submit" class="btn btn-light">
                            <i class="fas fa-sync mr-1"></i> Sync from API
                        </button>
                    </form>
                @endif
            </div>
        </section>

        <section class="row ecom-stats">
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="ecom-stat ecom-stat--total">
                    <span class="ecom-stat-icon"><i class="fas fa-folder"></i></span>
                    <div>
                        <div class="ecom-stat-value">{{ number_format($stats['total']) }}</div>
                        <div class="ecom-stat-label">Total Categories</div>
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
                    <span class="ecom-stat-icon"><i class="fas fa-box"></i></span>
                    <div>
                        <div class="ecom-stat-value">{{ number_format($stats['with_products']) }}</div>
                        <div class="ecom-stat-label">With Products</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="ecom-stat ecom-stat--api">
                    <span class="ecom-stat-icon"><i class="fas fa-shopping-bag"></i></span>
                    <div>
                        <div class="ecom-stat-value">{{ number_format($stats['products']) }}</div>
                        <div class="ecom-stat-label">Total Products</div>
                    </div>
                </article>
            </div>
        </section>

        <div class="card ecom-card">
            <div class="ecom-card-head">
                <div>
                    <h3 class="mb-0">All Categories</h3>
                    <p class="mb-0 text-muted">{{ $categories->total() }} {{ Str::plural('category', $categories->total()) }} in catalog</p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table ecom-table mb-0">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Products</th>
                            @if ($canSyncReceived ?? false)
                                <th>API Received</th>
                            @endif
                            <th>Featured</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categories as $category)
                            <tr>
                                <td>
                                    <div class="ecom-category-cell">
                                        <span class="ecom-category-icon"><i class="fas fa-folder"></i></span>
                                        <div>
                                            <div class="ecom-category-name">{{ $category->name }}</div>
                                            <code class="ecom-category-slug">{{ $category->slug }}</code>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="ecom-count-badge">
                                        <i class="fas fa-box"></i> {{ number_format($category->products_count) }}
                                    </span>
                                </td>
                                @if ($canSyncReceived ?? false)
                                    <td>
                                        <span class="ecom-count-badge ecom-count-badge--api">
                                            <i class="fas fa-cloud"></i> {{ number_format($category->received_items_count ?? 0) }}
                                        </span>
                                    </td>
                                @endif
                                <td>
                                    @if ($category->is_featured)
                                        <span class="badge badge-info">Homepage</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="ecom-status {{ $category->is_active ? 'ecom-status--live' : 'ecom-status--hidden' }}">
                                        {{ $category->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <div class="ecom-actions">
                                        <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-xs btn-outline-info" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this category?')">
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
                                <td colspan="{{ ($canSyncReceived ?? false) ? 6 : 5 }}" class="ecom-empty">
                                    <i class="fas fa-folder-open"></i>
                                    <strong>No categories yet</strong>
                                    <p>Add categories manually or sync from received API content.</p>
                                    <a href="{{ route('admin.categories.create') }}" class="btn btn-sm btn-info mt-2">
                                        <i class="fas fa-plus mr-1"></i> Add Category
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($categories->hasPages())
                <div class="ecom-card-footer">{{ $categories->links() }}</div>
            @endif
        </div>
    </div>
@endsection

@push('styles')
@include('admin.partials.ecom-page-styles')
@endpush
