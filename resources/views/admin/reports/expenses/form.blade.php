@extends('layouts.admin')

@section('title', $expense->exists ? 'Edit Expense' : 'Add Expense')
@section('page_title', $expense->exists ? 'Edit Expense' : 'Add Expense')

@section('content')
    @include('admin.reports.partials.nav')

    <form action="{{ $expense->exists ? route('admin.reports.expenses.update', $expense) : route('admin.reports.expenses.store') }}" method="POST">
        @csrf
        @if ($expense->exists) @method('PUT') @endif

        <div class="card">
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Date *</label>
                            <input type="date" name="expense_date" class="form-control" value="{{ old('expense_date', $expense->expense_date?->format('Y-m-d')) }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Category *</label>
                            <select name="category" class="form-control" required>
                                @foreach ($categories as $key => $label)
                                    <option value="{{ $key }}" @selected(old('category', $expense->category) === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Amount *</label>
                            <input type="number" step="0.01" min="0.01" name="amount" class="form-control" value="{{ old('amount', $expense->amount) }}" required>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label>Title *</label>
                            <input type="text" name="title" class="form-control" value="{{ old('title', $expense->title) }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Reference</label>
                            <input type="text" name="reference" class="form-control" value="{{ old('reference', $expense->reference) }}" placeholder="Invoice #, receipt #">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Payment Method</label>
                            <input type="text" name="payment_method" class="form-control" value="{{ old('payment_method', $expense->payment_method) }}" placeholder="Cash, bank, bKash">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group mb-0">
                            <label>Notes</label>
                            <textarea name="notes" class="form-control" rows="3">{{ old('notes', $expense->notes) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">{{ $expense->exists ? 'Update' : 'Save' }} Expense</button>
                <a href="{{ route('admin.reports.expenses.index') }}" class="btn btn-default">Cancel</a>
            </div>
        </div>
    </form>
@endsection
