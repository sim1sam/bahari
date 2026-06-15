@extends('layouts.admin')

@section('title', 'Storage Link')
@section('page_title', 'Storage Link')

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card card-outline {{ $status['is_valid'] ? 'card-success' : 'card-warning' }}">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        @if ($status['is_valid'])
                            <i class="fas fa-check-circle text-success"></i> Storage link is active
                        @else
                            <i class="fas fa-exclamation-triangle text-warning"></i> Storage link is missing
                        @endif
                    </h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        Product images, API received files, and uploads are stored in
                        <code>storage/app/public</code>. The storage link makes them available at
                        <code>/storage/...</code> on your website.
                    </p>

                    <table class="table table-sm table-bordered mb-4">
                        <tbody>
                            <tr>
                                <th style="width:180px">Public link</th>
                                <td><code>{{ $status['link_path'] }}</code></td>
                            </tr>
                            <tr>
                                <th>Target folder</th>
                                <td><code>{{ $status['target_path'] }}</code></td>
                            </tr>
                            <tr>
                                <th>Link exists</th>
                                <td>{{ $status['exists'] ? 'Yes' : 'No' }}</td>
                            </tr>
                            <tr>
                                <th>Valid symlink</th>
                                <td>
                                    @if ($status['is_valid'])
                                        <span class="badge badge-success">Yes</span>
                                    @else
                                        <span class="badge badge-danger">No</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Target folder ready</th>
                                <td>{{ $status['target_exists'] ? 'Yes' : 'No' }}</td>
                            </tr>
                        </tbody>
                    </table>

                    @if (! $status['is_valid'])
                        <div class="alert alert-warning">
                            Images may not show until the storage link is created. Click the button below — no SSH or terminal needed.
                        </div>
                    @endif

                    <form action="{{ route('admin.storage-link.store') }}" method="POST" onsubmit="return confirm('Create storage link on this server?')">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-lg" @disabled($status['is_valid'])>
                            <i class="fas fa-link"></i>
                            {{ $status['is_valid'] ? 'Storage Link Active' : 'Create Storage Link' }}
                        </button>
                    </form>

                    @if ($status['is_valid'])
                        <p class="text-muted small mt-3 mb-0">
                            Test URL: <a href="{{ url('/storage') }}" target="_blank">{{ url('/storage') }}</a>
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
