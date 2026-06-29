@extends('layouts.admin')

@section('title', 'Live Products')
@section('page_title', 'Live Products — Storefront')

@section('content')
    <div class="mb-3">
        <a href="{{ route('admin.processed.index') }}" class="btn btn-default btn-sm">Processed</a>
        <a href="{{ route('admin.processed.live') }}" class="btn btn-success btn-sm">Live on Site</a>
        <a href="{{ route('admin.content.index') }}" class="btn btn-outline-secondary btn-sm">Content</a>
    </div>

    <form id="delete-batch-form" action="{{ route('admin.products.destroy-batch') }}" method="POST" class="d-none">
        @csrf
        @method('DELETE')
    </form>

    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
            <h3 class="card-title mb-0">Live Products (from API Go Live)</h3>
            @if ($products->count() > 0)
                <div class="mt-2 mt-md-0 d-flex flex-wrap align-items-center">
                    <label class="mb-0 mr-3">
                        <input type="checkbox" id="select-all"> Select all
                    </label>
                    <button type="button" class="btn btn-danger btn-sm" id="btn-delete-selected" disabled>
                        <i class="fas fa-trash"></i> Delete Selected
                    </button>
                </div>
            @endif
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th width="40"></th>
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
                                <input type="checkbox" class="product-check" name="products[]" value="{{ $product->id }}">
                            </td>
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
                            <td colspan="8" class="text-center text-muted py-5">
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

@push('scripts')
<script>
(function () {
    var checks = document.querySelectorAll('.product-check');
    var selectAll = document.getElementById('select-all');
    var deleteBtn = document.getElementById('btn-delete-selected');
    var deleteForm = document.getElementById('delete-batch-form');

    if (!checks.length || !deleteBtn || !deleteForm) {
        return;
    }

    function selectedChecks() {
        return Array.from(checks).filter(function (check) {
            return check.checked;
        });
    }

    function updateDeleteBtn() {
        deleteBtn.disabled = selectedChecks().length === 0;
    }

    checks.forEach(function (check) {
        check.addEventListener('change', updateDeleteBtn);
    });

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            checks.forEach(function (check) {
                check.checked = selectAll.checked;
            });
            updateDeleteBtn();
        });
    }

    deleteBtn.addEventListener('click', function () {
        var selected = selectedChecks();

        if (!selected.length) {
            alert('Please select at least one product.');
            return;
        }

        if (!confirm('Remove ' + selected.length + ' selected product(s) from the storefront?')) {
            return;
        }

        deleteForm.querySelectorAll('input[name="products[]"]').forEach(function (input) {
            input.remove();
        });

        selected.forEach(function (check) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'products[]';
            input.value = check.value;
            deleteForm.appendChild(input);
        });

        deleteForm.submit();
    });
})();
</script>
@endpush
