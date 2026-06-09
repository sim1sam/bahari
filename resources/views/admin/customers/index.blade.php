@extends('layouts.admin')

@section('title', 'Customers')
@section('page_title', 'Customers')

@section('content')
    <div class="card">
        <div class="card-header">
            <a href="{{ route('admin.customers.create') }}" class="btn btn-primary btn-sm float-right"><i class="fas fa-plus"></i> Add Customer</a>
            <h3 class="card-title">All Customers</h3>
        </div>
        <div class="card-body border-bottom pb-3">
            <form action="{{ route('admin.customers.index') }}" method="GET" class="form-inline">
                <div class="input-group input-group-sm">
                    <input type="text" name="search" class="form-control" placeholder="Search by name or email..." value="{{ $search }}">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-default"><i class="fas fa-search"></i></button>
                        @if ($search)
                            <a href="{{ route('admin.customers.index') }}" class="btn btn-default">Clear</a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Email</th>
                        <th>Orders</th>
                        <th>Joined</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $customer)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if ($customer->avatarUrl())
                                        <img src="{{ $customer->avatarUrl() }}" alt="" class="img-circle mr-2" style="width:36px;height:36px;object-fit:cover">
                                    @else
                                        <span class="img-circle bg-info d-inline-flex align-items-center justify-content-center text-white font-weight-bold mr-2" style="width:36px;height:36px;font-size:14px">{{ $customer->initials() }}</span>
                                    @endif
                                    {{ $customer->name }}
                                </div>
                            </td>
                            <td>{{ $customer->email }}</td>
                            <td>{{ $customer->orders_count }}</td>
                            <td>{{ $customer->created_at->format('M d, Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted">No customers found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($customers->hasPages())
            <div class="card-footer">{{ $customers->links() }}</div>
        @endif
    </div>
@endsection
