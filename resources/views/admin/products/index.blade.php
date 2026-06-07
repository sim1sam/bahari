@extends('layouts.admin')

@section('title', 'Products')
@section('page_title', 'Products')

@section('content')
    <div class="card">
        <div class="card-header">
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm float-right"><i class="fas fa-plus"></i> Add Product</a>
            <h3 class="card-title">All Products</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                        <tr>
                            <td>
                                @if ($product->image)
                                    <img src="{{ $product->image }}" alt="" class="img-thumbnail" style="width:50px;height:60px;object-fit:cover">
                                @endif
                            </td>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->category?->name ?? '—' }}</td>
                            <td>${{ number_format($product->price, 2) }}</td>
                            <td>
                                <span class="badge badge-{{ $product->is_active ? 'success' : 'secondary' }}">
                                    {{ $product->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-xs btn-info"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this product?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($products->hasPages())
            <div class="card-footer">{{ $products->links() }}</div>
        @endif
    </div>
@endsection
