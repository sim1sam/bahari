@extends('layouts.admin')

@section('title', 'Live Products')
@section('page_title', 'Live Products — Storefront')

@section('content')
    <div class="mb-3">
        <a href="{{ route('admin.processed.index') }}" class="btn btn-default btn-sm">Processed</a>
        <a href="{{ route('admin.processed.live') }}" class="btn btn-success btn-sm">Live on Site</a>
        <a href="{{ route('admin.content.index') }}" class="btn btn-outline-secondary btn-sm">Content</a>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title mb-0">Live Products (from API Go Live)</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>SKU</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td>
                                @if ($url = $product->imageUrl())
                                    <img src="{{ $url }}" alt="" class="img-thumbnail" style="width:50px;height:60px;object-fit:cover">
                                @endif
                            </td>
                            <td>{{ $product->name }}</td>
                            <td><code class="small">{{ $product->apiReceivedItem?->sku ?: '—' }}</code></td>
                            <td>{{ $product->category?->name ?? '—' }}</td>
                            <td>{{ money($product->price) }}</td>
                            <td>
                                <span class="badge badge-{{ $product->is_active ? 'success' : 'secondary' }}">
                                    {{ $product->is_active ? 'Live' : 'Hidden' }}
                                </span>
                            </td>
                            <td class="text-nowrap">
                                <a href="{{ route('products.show', $product->slug) }}" class="btn btn-xs btn-success" target="_blank" title="View on storefront">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                                <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-xs btn-info" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this product from the storefront?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                No live products yet. Process items in <a href="{{ route('admin.content.index') }}">Content</a>, then <strong>Go Live</strong> from <a href="{{ route('admin.processed.index') }}">Processed</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($products->hasPages())
            <div class="card-footer">{{ $products->links() }}</div>
        @endif
    </div>
@endsection
