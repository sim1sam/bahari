@extends('layouts.admin')

@section('title', 'NPM Build')
@section('page_title', 'Frontend Build')

@section('content')
    @php
        $isReady = $status['css_exists'] && $status['js_exists'];
    @endphp

    <div class="row">
        <div class="col-lg-9">
            <div class="card card-outline {{ $isReady ? 'card-success' : 'card-danger' }}">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        @if ($isReady)
                            <i class="fas fa-check-circle text-success"></i> Frontend assets are built
                        @else
                            <i class="fas fa-exclamation-triangle text-danger"></i> Storefront design is broken — build files missing
                        @endif
                    </h3>
                </div>
                <div class="card-body">
                    @if ($status['is_broken'])
                        <div class="alert alert-danger">
                            <strong>Problem found:</strong>
                            <code>public/build/manifest.json</code> exists but
                            <code>public/build/assets/</code> files are missing (CSS/JS return 404).
                            Upload <strong>deploy-build.zip</strong> below to fix the design.
                        </div>
                    @endif

                    <table class="table table-sm table-bordered mb-4">
                        <tbody>
                            <tr>
                                <th style="width:180px">Build folder</th>
                                <td>{{ $status['build_dir'] ? 'public/build' : 'Missing' }}</td>
                            </tr>
                            <tr>
                                <th>Assets folder</th>
                                <td>
                                    {{ $status['assets_dir'] ? 'public/build/assets' : 'Missing' }}
                                    <span class="badge badge-{{ $status['assets_count'] > 0 ? 'success' : 'danger' }} ml-2">
                                        {{ $status['assets_count'] }} files
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>CSS file</th>
                                <td>
                                    @if ($status['css_file'])
                                        <code>{{ $status['css_file'] }}</code>
                                        <span class="badge badge-{{ $status['css_exists'] ? 'success' : 'danger' }} ml-2">
                                            {{ $status['css_exists'] ? 'OK' : 'Missing' }}
                                        </span>
                                    @else
                                        <span class="text-muted">Not built yet</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>JS file</th>
                                <td>
                                    @if ($status['js_file'])
                                        <code>{{ $status['js_file'] }}</code>
                                        <span class="badge badge-{{ $status['js_exists'] ? 'success' : 'danger' }} ml-2">
                                            {{ $status['js_exists'] ? 'OK' : 'Missing' }}
                                        </span>
                                    @else
                                        <span class="text-muted">Not built yet</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Last built</th>
                                <td>{{ $status['last_built_at'] ?? 'Never' }}</td>
                            </tr>
                            <tr>
                                <th>Node.js</th>
                                <td>
                                    @if ($status['node_available'])
                                        <span class="badge badge-success">Available</span>
                                        <code class="ml-2">{{ $status['node_version'] }}</code>
                                    @else
                                        <span class="badge badge-secondary">Not found</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>npm</th>
                                <td>
                                    @if ($status['npm_available'])
                                        <span class="badge badge-success">Available</span>
                                        <code class="ml-2">{{ $status['npm_version'] }}</code>
                                    @else
                                        <span class="badge badge-secondary">Not found</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="card card-outline card-primary mb-4">
                        <div class="card-header">
                            <h3 class="card-title mb-0">
                                <i class="fas fa-file-archive mr-1"></i> Upload build zip (recommended)
                            </h3>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-3">
                                On your computer run <code>npm run deploy</code>, then upload the
                                <code>deploy-build.zip</code> file from the project folder.
                            </p>
                            <form action="{{ route('admin.frontend-build.upload') }}" method="POST" enctype="multipart/form-data" onsubmit="return confirm('Replace public/build files with this zip?')">
                                @csrf
                                <div class="form-group">
                                    <input type="file" name="build_zip" class="form-control-file" accept=".zip,application/zip" required>
                                    @error('build_zip')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-upload"></i> Upload deploy-build.zip
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="card card-outline card-secondary">
                        <div class="card-header">
                            <h3 class="card-title mb-0">
                                <i class="fas fa-code mr-1"></i> Build on server (optional)
                            </h3>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">
                                Only works if Node.js and npm are installed on this server.
                            </p>
                            <form action="{{ route('admin.frontend-build.store') }}" method="POST" onsubmit="return confirm('Run npm build on this server?')">
                                @csrf
                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                    @disabled(! $status['node_modules'] || ! $status['npm_available'])
                                >
                                    <i class="fas fa-play"></i> Run NPM Build
                                </button>
                            </form>
                        </div>
                    </div>

                    @if ($lastOutput)
                        <div class="mt-4">
                            <label class="font-weight-bold">Last output</label>
                            <pre class="bg-light border rounded p-3 small mb-0" style="max-height:320px;overflow:auto">{{ $lastOutput }}</pre>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
