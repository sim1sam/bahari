@extends('layouts.admin')

@section('title', 'Terminal')
@section('page_title', 'Terminal')

@section('content')
    @php
        $readyCount = collect($tools)->where('status', 'ready')->count();
        $actionCount = collect($tools)->where('status', 'action')->count();
    @endphp

    <div class="settings-page">
        <section class="settings-hero">
            <div>
                <span class="settings-eyebrow">Terminal</span>
                <h2>Server Tools</h2>
                <p>Run common server tasks from the admin panel — no SSH needed.</p>
                <div class="settings-hero-meta">
                    <span class="settings-hero-chip"><i class="fas fa-tools"></i> {{ count($tools) }} tool{{ count($tools) === 1 ? '' : 's' }}</span>
                    @if ($actionCount > 0)
                        <span class="settings-hero-chip"><i class="fas fa-exclamation-circle"></i> {{ $actionCount }} need{{ $actionCount === 1 ? 's' : '' }} action</span>
                    @else
                        <span class="settings-hero-chip"><i class="fas fa-check-circle"></i> All ready</span>
                    @endif
                </div>
            </div>
        </section>

        @if (count($tools) > 0)
            <section class="row mb-3">
                <div class="col-xl-3 col-sm-6 mb-3">
                    <article class="settings-stat settings-stat--teal">
                        <span class="settings-stat-icon"><i class="fas fa-terminal"></i></span>
                        <div>
                            <div class="settings-stat-value">{{ count($tools) }}</div>
                            <div class="settings-stat-label">Available Tools</div>
                        </div>
                    </article>
                </div>
                <div class="col-xl-3 col-sm-6 mb-3">
                    <article class="settings-stat settings-stat--green">
                        <span class="settings-stat-icon"><i class="fas fa-check-circle"></i></span>
                        <div>
                            <div class="settings-stat-value">{{ $readyCount }}</div>
                            <div class="settings-stat-label">Ready</div>
                        </div>
                    </article>
                </div>
                <div class="col-xl-3 col-sm-6 mb-3">
                    <article class="settings-stat settings-stat--amber">
                        <span class="settings-stat-icon"><i class="fas fa-exclamation-triangle"></i></span>
                        <div>
                            <div class="settings-stat-value">{{ $actionCount }}</div>
                            <div class="settings-stat-label">Needs Action</div>
                        </div>
                    </article>
                </div>
            </section>
        @endif

        @include('admin.terminal.partials.nav')

        @if (count($tools) === 0)
            <div class="terminal-alert terminal-alert--warning">
                <i class="fas fa-lock"></i>
                <div>
                    <strong>No terminal access</strong>
                    <span>You do not have access to any terminal tools. Ask an administrator to grant Terminal permissions on your role.</span>
                </div>
            </div>
        @else
            <div class="terminal-tool-grid">
                @foreach ($tools as $tool)
                    @php
                        $iconClass = match ($tool['key']) {
                            'database_migration' => 'terminal-tool-icon--migration',
                            'npm_build' => 'terminal-tool-icon--build',
                            'storage_link' => 'terminal-tool-icon--storage',
                            default => 'terminal-tool-icon--migration',
                        };
                        $btnClass = match ($tool['key']) {
                            'database_migration' => 'btn-primary',
                            'npm_build' => 'btn-info',
                            'storage_link' => 'btn-success',
                            default => 'btn-primary',
                        };
                    @endphp
                    <article class="terminal-tool-card terminal-tool-card--{{ $tool['status'] }}">
                        <div class="terminal-tool-head">
                            <span class="terminal-tool-icon {{ $iconClass }}"><i class="{{ $tool['icon'] }}"></i></span>
                            <span class="terminal-status terminal-status--{{ $tool['status'] }}">{{ $tool['status_label'] }}</span>
                        </div>
                        <h5 class="terminal-tool-title">{{ $tool['label'] }}</h5>
                        <p class="terminal-tool-desc">{{ $tool['description'] }}</p>
                        <a href="{{ route($tool['route']) }}" class="btn {{ $btnClass }} btn-sm mt-auto align-self-start">
                            <i class="fas fa-arrow-right mr-1"></i> Open
                        </a>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
@endsection

@push('styles')
    @include('admin.terminal.partials.page-styles')
@endpush
