@extends('layouts.admin')

@section('title', 'Migration')
@section('page_title', 'Database Migration')

@section('content')
    <div class="row">
        <div class="col-lg-9">
            <div class="card card-outline {{ $status['pending_count'] === 0 ? 'card-success' : 'card-warning' }}">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        @if ($status['pending_count'] === 0)
                            <i class="fas fa-check-circle text-success"></i> Database is up to date
                        @else
                            <i class="fas fa-exclamation-triangle text-warning"></i> {{ $status['pending_count'] }} migration(s) pending
                        @endif
                    </h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        After uploading new code to the server, run migrations here to update the database.
                        No SSH or terminal needed.
                    </p>

                    <table class="table table-sm table-bordered mb-4">
                        <tbody>
                            <tr>
                                <th style="width:180px">Database</th>
                                <td><code>{{ $status['database'] }}</code></td>
                            </tr>
                            <tr>
                                <th>Total migrations</th>
                                <td>{{ $status['total'] }}</td>
                            </tr>
                            <tr>
                                <th>Already run</th>
                                <td>{{ $status['ran'] }}</td>
                            </tr>
                            <tr>
                                <th>Pending</th>
                                <td>
                                    @if ($status['pending_count'] === 0)
                                        <span class="badge badge-success">0</span>
                                    @else
                                        <span class="badge badge-warning">{{ $status['pending_count'] }}</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    @if ($status['pending_count'] > 0)
                        <div class="alert alert-warning">
                            <strong>Pending migrations:</strong>
                            <ul class="mb-0 mt-2 pl-3">
                                @foreach ($status['pending'] as $migration)
                                    <li><code class="small">{{ $migration }}</code></li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.migration.store') }}" method="POST" onsubmit="return confirm('Run pending database migrations on this server?')">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-lg" @disabled($status['pending_count'] === 0)>
                            <i class="fas fa-database"></i>
                            {{ $status['pending_count'] === 0 ? 'All Migrations Applied' : 'Run Migrations' }}
                        </button>
                    </form>

                    @if ($lastOutput)
                        <div class="mt-4">
                            <label class="font-weight-bold">Last output</label>
                            <pre class="bg-light border rounded p-3 small mb-0" style="max-height:280px;overflow:auto">{{ $lastOutput }}</pre>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
