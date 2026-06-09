@extends('layouts.admin')

@section('title', $customer->exists ? 'Edit Customer' : 'Add Customer')
@section('page_title', $customer->exists ? 'Edit Customer' : 'Add Customer')

@section('content')
    <form action="{{ $customer->exists ? route('admin.customers.update', $customer) : route('admin.customers.store') }}" method="POST">
        @csrf
        @if ($customer->exists) @method('PUT') @endif

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Name *</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $customer->name) }}" required>
                            @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Email *</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $customer->email) }}" required>
                            @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Password {{ $customer->exists ? '' : '*' }}</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" {{ $customer->exists ? '' : 'required' }}>
                            @if ($customer->exists)
                                <small class="text-muted">Leave blank to keep current password.</small>
                            @endif
                            @error('password')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Confirm Password {{ $customer->exists ? '' : '*' }}</label>
                            <input type="password" name="password_confirmation" class="form-control" {{ $customer->exists ? '' : 'required' }}>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">{{ $customer->exists ? 'Update Customer' : 'Create Customer' }}</button>
                <a href="{{ route('admin.customers.index') }}" class="btn btn-default">Cancel</a>
            </div>
        </div>
    </form>
@endsection
