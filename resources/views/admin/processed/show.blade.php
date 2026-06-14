@extends('layouts.admin')

@section('title', $isLive ? 'Live — '.$item->title : 'Processed — '.$item->title)
@section('page_title', $isLive ? 'Live Product' : 'Review Processed Product')

@section('content')
    <div class="mb-3">
        <a href="{{ route('admin.processed.index') }}" class="btn btn-default btn-sm">Back to Processed</a>
        @if ($isLive && $item->product)
            <a href="{{ route('products.show', $item->product->slug) }}" class="btn btn-success btn-sm" target="_blank">View on Storefront</a>
            <a href="{{ route('admin.products.edit', $item->product_id) }}" class="btn btn-info btn-sm">Edit Product</a>
        @endif
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Processed Image (with logo)</h3></div>
                <div class="card-body text-center">
                    @if ($item->processedImageUrl())
                        <img src="{{ $item->processedImageUrl() }}" alt="" class="img-fluid rounded border" style="max-height:440px">
                    @else
                        <p class="text-muted">No processed image</p>
                    @endif
                </div>
            </div>
            @if ($item->imageUrl())
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Original (before logo)</h3></div>
                    <div class="card-body text-center">
                        <img src="{{ $item->imageUrl() }}" alt="" class="img-fluid rounded border opacity-75" style="max-height:200px">
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-6">
            @if ($isLive)
                <div class="alert alert-success">
                    <strong>Live on storefront</strong> — customers can see and buy this product.
                </div>
            @endif

            <div class="card">
                <div class="card-header"><h3 class="card-title">Product Information</h3></div>
                @if (! $isLive)
                    <form action="{{ route('admin.processed.update', $item) }}" method="POST">
                        @csrf @method('PUT')
                        <div class="card-body">
                            @include('admin.processed.partials.fields', ['item' => $item, 'categories' => $categories])
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-secondary">Save Changes</button>
                        </div>
                    </form>
                @else
                    <div class="card-body">
                        <p><strong>Title:</strong> {{ $item->title }}</p>
                        <p><strong>SKU:</strong> {{ $item->sku ?: '—' }}</p>
                        <p><strong>Price:</strong> {{ money($item->price) }}</p>
                        <p><strong>Category:</strong> {{ $item->category_name ?: '—' }}</p>
                        <p><strong>Sizes:</strong> {{ implode(', ', $item->sizes ?? []) ?: '—' }}</p>
                        <p><strong>Colors:</strong> {{ implode(', ', $item->colors ?? []) ?: '—' }}</p>
                        @if ($item->description)
                            <p><strong>Description:</strong><br>{{ $item->description }}</p>
                        @endif
                    </div>
                @endif
            </div>

            @if (! $isLive)
                <div class="card card-success">
                    <div class="card-header"><h3 class="card-title">Go Live on Storefront</h3></div>
                    <form action="{{ route('admin.processed.live-item', $item) }}" method="POST" onsubmit="return confirm('Publish this product on the storefront?')">
                        @csrf
                        <div class="card-body">
                            <p class="text-muted small mb-0">This will create the product on your shop — homepage, categories, and product page.</p>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-success btn-block btn-lg">
                                <i class="fas fa-globe"></i> Go Live
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>
@endsection
