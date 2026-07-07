@extends('layouts.admin')

@section('title', 'Terminal')
@section('page_title', 'Terminal')

@section('content')
    <div class="row">
        <div class="col-lg-10">
            <div class="card card-outline card-secondary">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-terminal mr-1"></i> Server tools
                    </h3>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">
                        Run common server tasks from the admin panel — no SSH needed.
                    </p>

                    @if (count($tools) === 0)
                        <div class="alert alert-warning mb-0">
                            You do not have access to any terminal tools. Ask an administrator to grant Terminal permissions on your role.
                        </div>
                    @else
                        <div class="row">
                            @foreach ($tools as $tool)
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="card h-100 border {{ $tool['status'] === 'ready' ? 'border-success' : 'border-warning' }}">
                                        <div class="card-body d-flex flex-column">
                                            <div class="d-flex align-items-start justify-content-between mb-2">
                                                <h5 class="card-title mb-0">
                                                    <i class="{{ $tool['icon'] }} mr-1"></i> {{ $tool['label'] }}
                                                </h5>
                                                <span class="badge badge-{{ $tool['status'] === 'ready' ? 'success' : 'warning' }}">
                                                    {{ $tool['status_label'] }}
                                                </span>
                                            </div>
                                            <p class="text-muted small flex-grow-1">{{ $tool['description'] }}</p>
                                            <a href="{{ route($tool['route']) }}" class="btn btn-primary btn-sm mt-2 align-self-start">
                                                Open
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
