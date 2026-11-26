@extends('layouts.admin')

@section('title', 'Shipping Categories')
@section('page_title', 'Shipping Categories (Cost Calc)')

@section('content')
    <div class="ecom-page">
        <section class="ecom-hero mb-3">
            <div>
                <span class="ecom-eyebrow">Pricing</span>
                <h2>Shipping Categories</h2>
                <p>Same as CRM cost calculation — retail shipping rate per item type + INR→BDT conversion rate for bulk product pricing.</p>
            </div>
            <div class="ecom-hero-actions">
                <a href="{{ route('admin.products.bulk.create') }}" class="btn btn-info">
                    <i class="fas fa-layer-group mr-1"></i> Bulk Add Products
                </a>
            </div>
        </section>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-4 mb-3">
                <div class="card card-outline card-info">
                    <div class="card-header"><h3 class="card-title mb-0">Conversion Rate</h3></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.shipping-charges.conversion-rate') }}">
                            @csrf
                            @method('PUT')
                            <div class="form-group">
                                <label>INR → BDT rate</label>
                                <input type="number" name="conversion_rate" class="form-control" step="0.01" min="0.01"
                                       value="{{ old('conversion_rate', $setting->conversion_rate) }}" required>
                                <small class="text-muted">Used in retail / wholesale / reselling formula (CRM same).</small>
                            </div>
                            <button type="submit" class="btn btn-info btn-sm">Save Rate</button>
                        </form>
                    </div>
                </div>

                <div class="card card-outline card-primary mt-3">
                    <div class="card-header"><h3 class="card-title mb-0">Add Category</h3></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.shipping-charges.store') }}">
                            @csrf
                            <div class="form-group">
                                <label>Item name *</label>
                                <input type="text" name="item_name" class="form-control" required maxlength="255" value="{{ old('item_name') }}">
                            </div>
                            <div class="form-group">
                                <label>Retail shipping rate (BDT) *</label>
                                <input type="number" name="retail_rate" class="form-control" step="0.01" min="0" required value="{{ old('retail_rate', 0) }}">
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Unit</label>
                                        <select name="unit" class="form-control">
                                            <option value="PC">PC</option>
                                            <option value="KG">KG</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Sort</label>
                                        <input type="number" name="sort_order" class="form-control" min="0" value="{{ old('sort_order', 0) }}">
                                    </div>
                                </div>
                            </div>
                            <div class="custom-control custom-checkbox mb-3">
                                <input type="checkbox" class="custom-control-input" name="show_in_cost_calculation" id="show_calc_new" value="1" checked>
                                <label class="custom-control-label" for="show_calc_new">Show in cost calculation</label>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">Add Category</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card card-outline card-secondary">
                    <div class="card-header">
                        <h3 class="card-title mb-0">All Shipping Categories</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Retail rate</th>
                                    <th>Unit</th>
                                    <th>Sort</th>
                                    <th>In calc</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($shippingCharges as $charge)
                                    <tr>
                                        <td colspan="6" class="p-2 border-bottom">
                                            <form method="POST" action="{{ route('admin.shipping-charges.update', $charge) }}" class="form-row align-items-end">
                                                @csrf
                                                @method('PUT')
                                                <div class="col-md-4 mb-1">
                                                    <input type="text" name="item_name" class="form-control form-control-sm" value="{{ $charge->item_name }}" required>
                                                </div>
                                                <div class="col-md-2 mb-1">
                                                    <input type="number" name="retail_rate" class="form-control form-control-sm" step="0.01" min="0" value="{{ $charge->retail_rate }}" required>
                                                </div>
                                                <div class="col-md-1 mb-1">
                                                    <select name="unit" class="form-control form-control-sm">
                                                        <option value="PC" @selected($charge->unit === 'PC')>PC</option>
                                                        <option value="KG" @selected($charge->unit === 'KG')>KG</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-1 mb-1">
                                                    <input type="number" name="sort_order" class="form-control form-control-sm" min="0" value="{{ $charge->sort_order }}">
                                                </div>
                                                <div class="col-md-2 mb-1">
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" class="custom-control-input" name="show_in_cost_calculation" id="show_{{ $charge->id }}" value="1" @checked($charge->show_in_cost_calculation)>
                                                        <label class="custom-control-label" for="show_{{ $charge->id }}">Yes</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-2 mb-1 text-right">
                                                    <button type="submit" class="btn btn-xs btn-primary">Save</button>
                                                    <button type="submit" form="delete-charge-{{ $charge->id }}" class="btn btn-xs btn-outline-danger" onclick="return confirm('Delete this category?')">Del</button>
                                                </div>
                                            </form>
                                            <form id="delete-charge-{{ $charge->id }}" method="POST" action="{{ route('admin.shipping-charges.destroy', $charge) }}" class="d-none">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">No shipping categories yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($shippingCharges->hasPages())
                        <div class="card-footer">{{ $shippingCharges->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
