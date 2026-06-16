@extends('layouts.admin')

@section('title', 'Payment Banks')
@section('page_title', 'Payment Banks')

@section('content')
    <div class="row">
        <div class="col-lg-4">
            <div class="card card-primary card-outline">
                <div class="card-header"><h3 class="card-title">Add Bank / Wallet</h3></div>
                <form action="{{ route('admin.payment-banks.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label>Name *</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="bKash, Nagad, Bank name" required>
                            @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label>Account Name</label>
                            <input type="text" name="account_name" class="form-control" value="{{ old('account_name') }}">
                        </div>
                        <div class="form-group">
                            <label>Account Number</label>
                            <input type="text" name="account_number" class="form-control" value="{{ old('account_number') }}">
                        </div>
                        <div class="form-group">
                            <label>Branch / Type</label>
                            <input type="text" name="branch" class="form-control" value="{{ old('branch') }}" placeholder="Personal, Merchant, Branch name">
                        </div>
                        <div class="form-group">
                            <label>Instructions</label>
                            <input type="text" name="instructions" class="form-control" value="{{ old('instructions') }}" placeholder="Send money then upload screenshot">
                        </div>
                        <div class="form-group">
                            <label>QR / Bank Image</label>
                            <input type="file" name="image" class="form-control-file" accept="image/*">
                        </div>
                        <div class="form-group">
                            <label>Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}" min="0">
                        </div>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" checked>
                            <label class="custom-control-label" for="is_active">Active on checkout</label>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Add Bank</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-8">
            @forelse ($banks as $bank)
                <div class="card card-outline {{ $bank->is_active ? 'card-success' : 'card-secondary' }}">
                    <div class="card-header d-flex align-items-center">
                        <h3 class="card-title mb-0">{{ $bank->name }}</h3>
                        <span class="badge {{ $bank->is_active ? 'badge-success' : 'badge-secondary' }} ml-2">{{ $bank->is_active ? 'Active' : 'Inactive' }}</span>
                    </div>
                    <form action="{{ route('admin.payment-banks.update', $bank) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Name *</label>
                                                <input type="text" name="name" class="form-control" value="{{ old('name', $bank->name) }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Account Name</label>
                                                <input type="text" name="account_name" class="form-control" value="{{ old('account_name', $bank->account_name) }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Account Number</label>
                                                <input type="text" name="account_number" class="form-control" value="{{ old('account_number', $bank->account_number) }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Branch / Type</label>
                                                <input type="text" name="branch" class="form-control" value="{{ old('branch', $bank->branch) }}">
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="form-group mb-md-0">
                                                <label>Instructions</label>
                                                <input type="text" name="instructions" class="form-control" value="{{ old('instructions', $bank->instructions) }}">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group mb-md-0">
                                                <label>Sort Order</label>
                                                <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $bank->sort_order) }}" min="0">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    @if ($bank->imageUrl())
                                        <img src="{{ $bank->imageUrl() }}" alt="{{ $bank->name }}" class="img-thumbnail mb-2" style="max-height:120px">
                                        <div class="custom-control custom-checkbox mb-2">
                                            <input type="checkbox" class="custom-control-input" id="remove-image-{{ $bank->id }}" name="remove_image" value="1">
                                            <label class="custom-control-label" for="remove-image-{{ $bank->id }}">Remove image</label>
                                        </div>
                                    @endif
                                    <input type="file" name="image" class="form-control-file" accept="image/*">
                                    <div class="custom-control custom-switch mt-3">
                                        <input type="checkbox" class="custom-control-input" id="active-{{ $bank->id }}" name="is_active" value="1" @checked($bank->is_active)>
                                        <label class="custom-control-label" for="active-{{ $bank->id }}">Active</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <button type="submit" form="delete-bank-{{ $bank->id }}" class="btn btn-danger ml-auto" onclick="return confirm('Delete this bank?')">Delete</button>
                        </div>
                    </form>
                    <form id="delete-bank-{{ $bank->id }}" action="{{ route('admin.payment-banks.destroy', $bank) }}" method="POST" class="d-none">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            @empty
                <div class="alert alert-info">No payment banks added yet.</div>
            @endforelse
        </div>
    </div>
@endsection
