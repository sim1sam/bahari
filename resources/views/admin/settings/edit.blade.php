@extends('layouts.admin')

@section('title', $sectionLabel)
@section('page_title', $sectionLabel)

@section('content')
    @php $sectionRoute = str_replace('-', '_', $section); @endphp
    <form action="{{ route('admin.settings.'.$sectionRoute.'.update') }}" method="POST" @if ($section === 'branding') enctype="multipart/form-data" @endif>
        @csrf
        @method('PUT')

        @include('admin.settings.partials.'.$section)

        <div class="card">
            <div class="card-body">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save {{ $sectionLabel }}</button>
            </div>
        </div>
    </form>
@endsection
