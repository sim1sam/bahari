@extends('layouts.admin')

@section('title', 'Newsletter Subscribers')
@section('page_title', 'Newsletter Subscribers')

@section('content')
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $activeCount }}</h3>
                    <p>Active Subscribers</p>
                </div>
                <div class="icon"><i class="fas fa-envelope"></i></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Subscribers</h3>
            <a href="{{ route('admin.settings.edit') }}" class="btn btn-default btn-sm float-right">Newsletter Settings</a>
        </div>
        <div class="card-body border-bottom pb-3">
            <form action="{{ route('admin.newsletter.index') }}" method="GET" class="form-inline">
                <div class="input-group input-group-sm">
                    <input type="text" name="search" class="form-control" placeholder="Search by email..." value="{{ $search }}">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-default"><i class="fas fa-search"></i></button>
                        @if ($search)
                            <a href="{{ route('admin.newsletter.index') }}" class="btn btn-default">Clear</a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Subscribed</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($subscribers as $subscriber)
                        <tr>
                            <td>{{ $subscriber->email }}</td>
                            <td>
                                @if ($subscriber->isActive())
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-secondary">Unsubscribed</span>
                                @endif
                            </td>
                            <td>{{ $subscriber->subscribed_at?->format('M d, Y g:i A') }}</td>
                            <td>
                                <form action="{{ route('admin.newsletter.destroy', $subscriber) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this subscriber?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">No subscribers yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($subscribers->hasPages())
            <div class="card-footer">{{ $subscribers->links() }}</div>
        @endif
    </div>
@endsection
