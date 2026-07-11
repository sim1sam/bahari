@extends('layouts.admin')

@section('title', 'NPM Build')
@section('page_title', 'Frontend Build')

@section('content')
    @php
        $isReady = $status['css_exists'] && $status['js_exists'];
    @endphp

    <div class="settings-page">
        <a href="{{ route('admin.terminal.index') }}" class="settings-back-link">
            <i class="fas fa-arrow-left"></i> Back to Terminal
        </a>

        <section class="settings-hero">
            <div>
                <span class="settings-eyebrow">Terminal</span>
                <h2>NPM Build</h2>
                <p>Build or upload storefront CSS and JavaScript assets for the live site.</p>
                <div class="settings-hero-meta">
                    <span class="settings-hero-chip">
                        <i class="fas fa-{{ $isReady ? 'check-circle' : 'exclamation-triangle' }}"></i>
                        {{ $isReady ? 'Assets built' : 'Build needed' }}
                    </span>
                    @if ($status['assets_count'] > 0)
                        <span class="settings-hero-chip"><i class="fas fa-file"></i> {{ $status['assets_count'] }} files</span>
                    @endif
                </div>
            </div>
        </section>

        <section class="row mb-3">
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="settings-stat settings-stat--{{ $status['css_exists'] ? 'green' : 'rose' }}">
                    <span class="settings-stat-icon"><i class="fas fa-paint-brush"></i></span>
                    <div>
                        <div class="settings-stat-value">{{ $status['css_exists'] ? 'OK' : 'Missing' }}</div>
                        <div class="settings-stat-label">CSS File</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="settings-stat settings-stat--{{ $status['js_exists'] ? 'green' : 'rose' }}">
                    <span class="settings-stat-icon"><i class="fas fa-code"></i></span>
                    <div>
                        <div class="settings-stat-value">{{ $status['js_exists'] ? 'OK' : 'Missing' }}</div>
                        <div class="settings-stat-label">JS File</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="settings-stat settings-stat--teal">
                    <span class="settings-stat-icon"><i class="fas fa-folder"></i></span>
                    <div>
                        <div class="settings-stat-value">{{ $status['assets_count'] }}</div>
                        <div class="settings-stat-label">Asset Files</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="settings-stat settings-stat--{{ $status['npm_available'] ? 'blue' : 'amber' }}">
                    <span class="settings-stat-icon"><i class="fab fa-node-js"></i></span>
                    <div>
                        <div class="settings-stat-value">{{ $status['npm_available'] ? 'Yes' : 'No' }}</div>
                        <div class="settings-stat-label">npm on Server</div>
                    </div>
                </article>
            </div>
        </section>

        @include('admin.terminal.partials.nav')

        <div class="row">
            <div class="col-lg-8 mb-3">
                @if ($status['is_broken'])
                    <div class="terminal-alert terminal-alert--danger mb-3">
                        <i class="fas fa-exclamation-circle"></i>
                        <div>
                            <strong>Problem found:</strong>
                            <code>public/build/manifest.json</code> exists but
                            <code>public/build/assets/</code> files are missing (CSS/JS return 404).
                            Upload <strong>deploy-build.zip</strong> below to fix the design.
                        </div>
                    </div>
                @endif

                <div class="settings-card mb-3">
                    <div class="settings-card-head">
                        <div>
                            <h3>Build Status</h3>
                            <p>Current frontend asset state on this server.</p>
                        </div>
                        <span class="terminal-status terminal-status--{{ $isReady ? 'ready' : 'error' }}">
                            {{ $isReady ? 'Built' : 'Missing' }}
                        </span>
                    </div>
                    <div class="settings-card-body">
                        <div class="terminal-info-grid">
                            <div class="terminal-info-row">
                                <strong>Build Folder</strong>
                                <span>{{ $status['build_dir'] ? 'public/build' : 'Missing' }}</span>
                            </div>
                            <div class="terminal-info-row">
                                <strong>Assets Folder</strong>
                                <span>{{ $status['assets_dir'] ? 'public/build/assets' : 'Missing' }} ({{ $status['assets_count'] }} files)</span>
                            </div>
                            <div class="terminal-info-row">
                                <strong>CSS File</strong>
                                <span>
                                    @if ($status['css_file'])
                                        <code>{{ $status['css_file'] }}</code>
                                        <span class="terminal-status terminal-status--{{ $status['css_exists'] ? 'ready' : 'error' }} ml-1">{{ $status['css_exists'] ? 'OK' : 'Missing' }}</span>
                                    @else
                                        Not built yet
                                    @endif
                                </span>
                            </div>
                            <div class="terminal-info-row">
                                <strong>JS File</strong>
                                <span>
                                    @if ($status['js_file'])
                                        <code>{{ $status['js_file'] }}</code>
                                        <span class="terminal-status terminal-status--{{ $status['js_exists'] ? 'ready' : 'error' }} ml-1">{{ $status['js_exists'] ? 'OK' : 'Missing' }}</span>
                                    @else
                                        Not built yet
                                    @endif
                                </span>
                            </div>
                            <div class="terminal-info-row">
                                <strong>Last Built</strong>
                                <span>{{ $status['last_built_at'] ?? 'Never' }}</span>
                            </div>
                            <div class="terminal-info-row">
                                <strong>Node.js</strong>
                                <span>
                                    @if ($status['node_available'])
                                        <span class="terminal-status terminal-status--ready">Available</span>
                                        <code class="ml-1">{{ $status['node_version'] }}</code>
                                    @else
                                        <span class="terminal-status terminal-status--action">Not found</span>
                                    @endif
                                </span>
                            </div>
                            <div class="terminal-info-row">
                                <strong>npm</strong>
                                <span>
                                    @if ($status['npm_available'])
                                        <span class="terminal-status terminal-status--ready">Available</span>
                                        <code class="ml-1">{{ $status['npm_version'] }}</code>
                                    @else
                                        <span class="terminal-status terminal-status--action">Not found</span>
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="settings-card mb-3">
                    <div class="settings-card-head">
                        <div>
                            <h3><i class="fas fa-file-archive mr-1"></i> Upload Build Zip</h3>
                            <p>Recommended — run <code>npm run deploy</code> locally, then upload the zip.</p>
                        </div>
                    </div>
                    <div class="settings-card-body">
                        <form action="{{ route('admin.frontend-build.upload') }}" method="POST" enctype="multipart/form-data" onsubmit="return confirm('Replace public/build files with this zip?')">
                            @csrf
                            <div class="terminal-upload-zone mb-3">
                                <div class="settings-field mb-0">
                                    <label for="build_zip">deploy-build.zip</label>
                                    <input type="file" name="build_zip" id="build_zip" class="form-control-file" accept=".zip,application/zip" required>
                                    @error('build_zip')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-upload mr-1"></i> Upload deploy-build.zip
                            </button>
                        </form>
                    </div>
                </div>

                <div class="settings-card">
                    <div class="settings-card-head">
                        <div>
                            <h3><i class="fas fa-play mr-1"></i> Build on Server</h3>
                            <p>Optional — only if Node.js and npm are installed on this server.</p>
                        </div>
                    </div>
                    <div class="settings-card-body">
                        <form action="{{ route('admin.frontend-build.store') }}" method="POST" onsubmit="return confirm('Run npm build on this server?')">
                            @csrf
                            <button
                                type="submit"
                                class="btn btn-primary btn-lg"
                                @disabled(! $status['node_modules'] || ! $status['npm_available'])
                            >
                                <i class="fas fa-play mr-1"></i> Run NPM Build
                            </button>
                            @unless ($status['node_modules'] && $status['npm_available'])
                                <small class="settings-field-hint d-block mt-2">Requires <code>node_modules</code> and npm on the server.</small>
                            @endunless
                        </form>

                        @if ($lastOutput)
                            <span class="terminal-console-label mt-4">Last output</span>
                            <pre class="terminal-console mb-0">{{ $lastOutput }}</pre>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-3">
                <div class="settings-side-card">
                    <div class="settings-side-head">
                        <span class="settings-side-icon settings-side-icon--info"><i class="fas fa-lightbulb"></i></span>
                        <div>
                            <h4>Recommended Workflow</h4>
                            <p>Best way to deploy frontend assets.</p>
                        </div>
                    </div>
                    <div class="settings-side-body">
                        <ol class="settings-side-list">
                            <li>On your computer, run <code>npm run deploy</code>.</li>
                            <li>Upload the generated <code>deploy-build.zip</code> here.</li>
                            <li>Refresh the storefront to verify CSS/JS load correctly.</li>
                        </ol>
                    </div>
                </div>

                <div class="settings-side-card">
                    <div class="settings-side-head">
                        <span class="settings-side-icon settings-side-icon--check"><i class="fas fa-server"></i></span>
                        <div>
                            <h4>Server Build</h4>
                            <p>When to use Run NPM Build.</p>
                        </div>
                    </div>
                    <div class="settings-side-body">
                        <p class="settings-side-text mb-0">Only use server-side build if Node.js and npm are available on production. Most shared hosts do not support this — upload zip instead.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    @include('admin.terminal.partials.page-styles')
@endpush
