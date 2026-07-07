@extends('layouts.admin')

@section('title', 'NPM Build')
@section('page_title', 'Frontend Build')

@section('content')
    @php
        $isReady = $status['css_exists'] && $status['js_exists'];
    @endphp

    <div class="row">
        <div class="col-lg-9">
            <div class="card card-outline {{ $isReady ? 'card-success' : 'card-warning' }}">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        @if ($isReady)
                            <i class="fas fa-check-circle text-success"></i> Frontend assets are built
                        @else
                            <i class="fas fa-exclamation-triangle text-warning"></i> Frontend build is missing or incomplete
                        @endif
                    </h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        Build CSS and JavaScript for the storefront. This runs
                        <code>npm run build:deploy</code> on the server — no SSH needed.
                        After a successful build, clear your browser cache or hard refresh the site.
                    </p>

                    <table class="table table-sm table-bordered mb-4">
                        <tbody>
                            <tr>
                                <th style="width:180px">Project path</th>
                                <td><code>{{ $status['project_path'] }}</code></td>
                            </tr>
                            <tr>
                                <th>Node.js</th>
                                <td>
                                    @if ($status['node_available'])
                                        <span class="badge badge-success">Available</span>
                                        <code class="ml-2">{{ $status['node_version'] }}</code>
                                    @else
                                        <span class="badge badge-danger">Not found</span>
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
                                        <span class="badge badge-danger">Not found</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>node_modules</th>
                                <td>{{ $status['node_modules'] ? 'Yes' : 'No' }}</td>
                            </tr>
                            <tr>
                                <th>Build folder</th>
                                <td>{{ $status['build_dir'] ? 'public/build' : 'Missing' }}</td>
                            </tr>
                            <tr>
                                <th>CSS file</th>
                                <td>
                                    @if ($status['css_file'])
                                        <code>{{ $status['css_file'] }}</code>
                                        @if ($status['css_exists'])
                                            <span class="badge badge-success ml-2">OK</span>
                                        @else
                                            <span class="badge badge-danger ml-2">Missing</span>
                                        @endif
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
                                        @if ($status['js_exists'])
                                            <span class="badge badge-success ml-2">OK</span>
                                        @else
                                            <span class="badge badge-danger ml-2">Missing</span>
                                        @endif
                                    @else
                                        <span class="text-muted">Not built yet</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Asset files</th>
                                <td>{{ $status['assets_count'] }}</td>
                            </tr>
                            <tr>
                                <th>Last built</th>
                                <td>{{ $status['last_built_at'] ?? 'Never' }}</td>
                            </tr>
                        </tbody>
                    </table>

                    @if (! $status['node_modules'])
                        <div class="alert alert-warning">
                            <strong>Dependencies missing.</strong> Run <code>npm install</code> once on the server before building.
                        </div>
                    @elseif (! $status['npm_available'])
                        <div class="alert alert-warning">
                            <strong>npm not available to PHP.</strong>
                            Install Node.js on the server and ensure the web server can run npm.
                            You can also set <code>NPM_BINARY=/full/path/to/npm</code> in <code>.env</code>.
                        </div>
                    @elseif (! $isReady)
                        <div class="alert alert-warning">
                            <strong>Design may look broken</strong> until CSS and JS are built and uploaded to <code>public/build/assets/</code>.
                        </div>
                    @endif

                    <form action="{{ route('admin.frontend-build.store') }}" method="POST" onsubmit="return confirm('Run npm build on this server? This may take up to 1 minute.')">
                        @csrf
                        <button
                            type="submit"
                            class="btn btn-primary btn-lg"
                            @disabled(! $status['node_modules'] || ! $status['npm_available'])
                        >
                            <i class="fas fa-code"></i>
                            Run NPM Build
                        </button>
                    </form>

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
