@extends('layouts.admin')

@section('title', 'Storage Link')
@section('page_title', 'Storage Link')

@section('content')
    <div class="settings-page">
        <a href="{{ route('admin.terminal.index') }}" class="settings-back-link">
            <i class="fas fa-arrow-left"></i> Back to Terminal
        </a>

        <section class="settings-hero">
            <div>
                <span class="settings-eyebrow">Terminal</span>
                <h2>Storage Link</h2>
                <p>Link public storage so product images and uploads are accessible at <code>/storage/...</code></p>
                <div class="settings-hero-meta">
                    <span class="settings-hero-chip">
                        <i class="fas fa-{{ $status['is_valid'] ? 'check-circle' : 'exclamation-triangle' }}"></i>
                        {{ $status['is_valid'] ? 'Active' : 'Missing' }}
                    </span>
                </div>
            </div>
        </section>

        <section class="row mb-3">
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="settings-stat settings-stat--{{ $status['exists'] ? 'green' : 'amber' }}">
                    <span class="settings-stat-icon"><i class="fas fa-link"></i></span>
                    <div>
                        <div class="settings-stat-value">{{ $status['exists'] ? 'Yes' : 'No' }}</div>
                        <div class="settings-stat-label">Link Exists</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="settings-stat settings-stat--{{ $status['is_valid'] ? 'green' : 'rose' }}">
                    <span class="settings-stat-icon"><i class="fas fa-check-circle"></i></span>
                    <div>
                        <div class="settings-stat-value">{{ $status['is_valid'] ? 'Valid' : 'Invalid' }}</div>
                        <div class="settings-stat-label">Symlink</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="settings-stat settings-stat--teal">
                    <span class="settings-stat-icon"><i class="fas fa-folder"></i></span>
                    <div>
                        <div class="settings-stat-value">{{ $status['target_exists'] ? 'Ready' : 'Missing' }}</div>
                        <div class="settings-stat-label">Target Folder</div>
                    </div>
                </article>
            </div>
        </section>

        @include('admin.terminal.partials.nav')

        <div class="row">
            <div class="col-lg-8 mb-3">
                <div class="settings-card">
                    <div class="settings-card-head">
                        <div>
                            <h3>Storage Link Status</h3>
                            <p>Product images, API files, and uploads use this public link.</p>
                        </div>
                        <span class="terminal-status terminal-status--{{ $status['is_valid'] ? 'ready' : 'action' }}">
                            {{ $status['is_valid'] ? 'Active' : 'Missing' }}
                        </span>
                    </div>
                    <div class="settings-card-body">
                        <div class="terminal-info-grid mb-3">
                            <div class="terminal-info-row">
                                <strong>Public Link</strong>
                                <code>{{ $status['link_path'] }}</code>
                            </div>
                            <div class="terminal-info-row">
                                <strong>Target Folder</strong>
                                <code>{{ $status['target_path'] }}</code>
                            </div>
                            <div class="terminal-info-row">
                                <strong>Link Exists</strong>
                                <span>{{ $status['exists'] ? 'Yes' : 'No' }}</span>
                            </div>
                            <div class="terminal-info-row">
                                <strong>Valid Symlink</strong>
                                <span class="terminal-status terminal-status--{{ $status['is_valid'] ? 'ready' : 'error' }}">
                                    {{ $status['is_valid'] ? 'Yes' : 'No' }}
                                </span>
                            </div>
                            <div class="terminal-info-row">
                                <strong>Target Ready</strong>
                                <span>{{ $status['target_exists'] ? 'Yes' : 'No' }}</span>
                            </div>
                        </div>

                        @unless ($status['is_valid'])
                            <div class="terminal-alert terminal-alert--warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <div>
                                    @if ($status['blocking_path'])
                                        <strong>Old folder detected.</strong>
                                        <code>public/storage</code> exists as a regular folder. Click the button below — it will be renamed automatically and the correct link will be created.
                                    @else
                                        <strong>Images may not show</strong> until the storage link is created. Click the button below — no SSH needed.
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="terminal-alert terminal-alert--success">
                                <i class="fas fa-check-circle"></i>
                                <div>
                                    <strong>Storage link is active</strong>
                                    <span>Test URL: <a href="{{ url('/storage') }}" target="_blank" rel="noopener noreferrer">{{ url('/storage') }}</a></span>
                                </div>
                            </div>
                        @endunless

                        <form action="{{ route('admin.storage-link.store') }}" method="POST" onsubmit="return confirm('{{ $status['blocking_path'] ? 'Rename old public/storage folder and create storage link?' : 'Create storage link on this server?' }}')">
                            @csrf
                            <button type="submit" class="btn btn-success btn-lg" @disabled($status['is_valid'])>
                                <i class="fas fa-link mr-1"></i>
                                {{ $status['is_valid'] ? 'Storage Link Active' : 'Create Storage Link' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-3">
                <div class="settings-side-card">
                    <div class="settings-side-head">
                        <span class="settings-side-icon settings-side-icon--info"><i class="fas fa-image"></i></span>
                        <div>
                            <h4>Why It Matters</h4>
                            <p>Without this link, uploads break.</p>
                        </div>
                    </div>
                    <div class="settings-side-body">
                        <p class="settings-side-text">Files are stored in <code>storage/app/public</code> but must be served from <code>public/storage</code>.</p>
                        <p class="settings-side-text mb-0">Equivalent to <code>php artisan storage:link</code>.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    @include('admin.terminal.partials.page-styles')
@endpush
