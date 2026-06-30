@extends('layouts.admin')

@section('title', 'Expenses')
@section('page_title', 'Operating Expenses')

@section('content')
    @include('admin.reports.partials.nav')

    <div class="mb-3">
        <a href="{{ route('admin.reports.expenses.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus mr-1"></i> Add Expense</a>
    </div>

    <div class="card card-outline card-secondary mb-3">
        <div class="card-body pb-2">
            <form method="GET" class="row">
                <div class="col-md-3">
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $expenseFilters['date_from'] ?? '' }}" placeholder="From">
                </div>
                <div class="col-md-3">
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $expenseFilters['date_to'] ?? '' }}" placeholder="To">
                </div>
                <div class="col-md-3">
                    <select name="category" class="form-control form-control-sm">
                        <option value="">All categories</option>
                        @foreach ($categories as $key => $label)
                            <option value="{{ $key }}" @selected(($expenseFilters['category'] ?? '') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Title</th>
                        <th>Reference</th>
                        <th class="text-right">Amount</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($expenses as $expense)
                        <tr>
                            <td>{{ $expense->expense_date->format('M d, Y') }}</td>
                            <td>{{ $expense->categoryLabel() }}</td>
                            <td>
                                {{ $expense->title }}
                                @if ($expense->product)
                                    <br><a href="{{ route('admin.products.edit', $expense->product) }}" class="small">View product</a>
                                @endif
                            </td>
                            <td>{{ $expense->reference ?: '—' }}</td>
                            <td class="text-right font-weight-bold">{{ money($expense->amount) }}</td>
                            <td class="text-nowrap">
                                <a href="{{ route('admin.reports.expenses.edit', $expense) }}" class="btn btn-xs btn-info">Edit</a>
                                <form action="{{ route('admin.reports.expenses.destroy', $expense) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this expense?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No expenses recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($expenses->hasPages())
            <div class="card-footer">{{ $expenses->links() }}</div>
        @endif
    </div>
@endsection
