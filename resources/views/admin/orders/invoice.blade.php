@extends('layouts.invoice')

@section('title', 'Invoice '.$order->number)

@section('toolbar')
    <div class="invoice-toolbar">
        <a href="{{ route('admin.orders.invoice.download', $order) }}" class="btn btn-primary">Download PDF</a>
        <button type="button" class="btn" onclick="window.print()">Print Invoice</button>
        <a href="{{ route('admin.orders.show', $order) }}" class="btn">Back to Order</a>
    </div>
@endsection

@section('content')
    @include('admin.orders.partials.invoice-content')
@endsection
