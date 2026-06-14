@extends('layouts.admin')

@section('title', 'Live Products')
@section('page_title', 'Live on Storefront')

@section('content')
    <div class="mb-3">
        <a href="{{ route('admin.processed.index') }}" class="btn btn-default btn-sm">Back to Processed</a>
        <a href="{{ route('admin.api-settings.index') }}" class="btn btn-outline-secondary btn-sm">API Settings</a>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Published Products (from API)</h3></div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Published</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr>
                            <td>
                                @if ($item->processedImageUrl())
                                    <img src="{{ $item->processedImageUrl() }}" alt="" class="rounded border" style="height:48px;width:48px;object-fit:cover">
                                @endif
                            </td>
                            <td>
                                <strong>{{ $item->title }}</strong>
                                @if ($item->sku)<br><code class="small">{{ $item->sku }}</code>@endif
                            </td>
                            <td>{{ money($item->price) }}</td>
                            <td class="text-muted small">{{ $item->reviewed_at?->format('M d, Y H:i') ?: '—' }}</td>
                            <td class="text-nowrap">
                                @if ($item->product)
                                    <a href="{{ route('products.show', $item->product->slug) }}" class="btn btn-xs btn-success" target="_blank">Storefront</a>
                                    <a href="{{ route('admin.products.edit', $item->product_id) }}" class="btn btn-xs btn-info">Edit</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No live products from API yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($items->hasPages())
            <div class="card-footer">{{ $items->links() }}</div>
        @endif
    </div>
@endsection
