@extends('layouts.admin')

@section('title', 'Inter Transfer')
@section('page_title', 'Inter Transfer')

@section('content')
    <div class="settings-page">
        <section class="settings-hero">
            <div>
                <span class="settings-eyebrow">Account</span>
                <h2>Inter Transfer</h2>
                <p>Move money between bank accounts. This only records transactions and updates bank balances — not profit or loss.</p>
            </div>
        </section>

        @include('admin.account.partials.nav')

        <div class="row">
            <div class="col-xl-7">
                <form action="{{ route('admin.bank-inter-transfers.store') }}" method="POST" class="inter-transfer-form">
                    @csrf
                    <div class="settings-card">
                        <div class="settings-card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="settings-field">
                                        <label for="from_bank_id">From Bank *</label>
                                        <select name="from_bank_id" id="from_bank_id" class="form-control settings-textarea @error('from_bank_id') is-invalid @enderror" required>
                                            <option value="" disabled @selected(! old('from_bank_id'))>Select source bank</option>
                                            @foreach ($banks as $bank)
                                                <option value="{{ $bank->id }}" data-balance="{{ $bankBalances[$bank->id] ?? 0 }}" @selected((string) old('from_bank_id') === (string) $bank->id)>
                                                    {{ $bank->displayName() }} — Bal {{ money($bankBalances[$bank->id] ?? 0) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('from_bank_id')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="settings-field">
                                        <label for="to_bank_id">To Bank *</label>
                                        <select name="to_bank_id" id="to_bank_id" class="form-control settings-textarea @error('to_bank_id') is-invalid @enderror" required>
                                            <option value="" disabled @selected(! old('to_bank_id'))>Select destination bank</option>
                                            @foreach ($banks as $bank)
                                                <option value="{{ $bank->id }}" @selected((string) old('to_bank_id') === (string) $bank->id)>
                                                    {{ $bank->displayName() }} — Bal {{ money($bankBalances[$bank->id] ?? 0) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('to_bank_id')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="settings-field">
                                        <label for="amount">Amount (BDT) *</label>
                                        <input type="number" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror" min="0.01" step="0.01" value="{{ old('amount') }}" required>
                                        @error('amount')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="settings-field">
                                        <label for="transfer_date">Transfer Date *</label>
                                        <input type="date" name="transfer_date" id="transfer_date" class="form-control @error('transfer_date') is-invalid @enderror" value="{{ old('transfer_date', now()->toDateString()) }}" required>
                                        @error('transfer_date')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="settings-field mb-0">
                                        <label for="notes">Notes</label>
                                        <textarea name="notes" id="notes" class="form-control settings-textarea" rows="3" placeholder="Optional reference or remarks">{{ old('notes') }}</textarea>
                                        @error('notes')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="settings-card-footer d-flex align-items-center flex-wrap gap-2">
                            <button type="submit" class="btn btn-info btn-lg">
                                <i class="fas fa-exchange-alt mr-1"></i> Transfer Amount
                            </button>
                            <a href="{{ route('admin.bank-inter-transfers.index') }}" class="btn btn-secondary btn-lg">View History</a>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-xl-5">
                <div class="settings-card">
                    <div class="settings-card-body">
                        <h4 class="mb-3">Bank Balances</h4>
                        <div class="table-responsive">
                            <table class="table settings-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Bank</th>
                                        <th class="text-right">Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($banks as $bank)
                                        <tr>
                                            <td>{{ $bank->displayName() }}</td>
                                            <td class="text-right"><strong>{{ money($bankBalances[$bank->id] ?? 0) }}</strong></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    @include('admin.settings.partials.page-styles')
    <style>
        .inter-transfer-form .settings-card-body {
            padding: 1.5rem 1.5rem 1.35rem;
        }

        .inter-transfer-form .settings-field {
            margin-bottom: 1.35rem;
        }

        .inter-transfer-form .settings-field label {
            margin-bottom: 0.55rem;
            display: block;
        }

        .inter-transfer-form .row {
            margin-left: -0.75rem;
            margin-right: -0.75rem;
        }

        .inter-transfer-form .row > [class*="col-"] {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
            margin-bottom: 0.35rem;
        }

        .inter-transfer-form .settings-field.mb-0 {
            margin-bottom: 0.35rem;
        }

        .inter-transfer-form .form-control,
        .inter-transfer-form .settings-textarea {
            min-height: 2.55rem;
        }

        .inter-transfer-form textarea.form-control {
            min-height: 6rem;
        }
    </style>
@endpush
