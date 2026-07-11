@extends('layouts.admin')

@section('title', 'Newsletter Subscribers')
@section('page_title', 'Newsletter Subscribers')

@section('content')
    <div class="settings-page">
        <a href="{{ route('admin.homepage.index') }}" class="settings-back-link">
            <i class="fas fa-arrow-left"></i> Back to Homepage
        </a>

        <section class="settings-hero">
            <div>
                <span class="settings-eyebrow">Homepage</span>
                <h2>Newsletter Subscribers</h2>
                <p>View and manage email subscribers from the storefront footer signup form.</p>
                <div class="settings-hero-meta">
                    <span class="settings-hero-chip"><i class="fas fa-envelope"></i> {{ $activeCount }} active</span>
                </div>
            </div>
            <div class="settings-hero-actions">
                <a href="{{ route('admin.settings.footer.edit') }}" class="btn btn-primary">
                    <i class="fas fa-cog mr-1"></i> Newsletter Settings
                </a>
            </div>
        </section>

        <section class="row mb-3">
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="settings-stat settings-stat--green">
                    <span class="settings-stat-icon"><i class="fas fa-envelope"></i></span>
                    <div>
                        <div class="settings-stat-value">{{ $activeCount }}</div>
                        <div class="settings-stat-label">Active Subscribers</div>
                    </div>
                </article>
            </div>
        </section>

        @include('admin.settings.partials.nav')

        <div class="settings-card">
            <div class="settings-card-head">
                <div>
                    <h3>All Subscribers</h3>
                    <p>Emails collected from the footer newsletter signup form.</p>
                </div>
            </div>
            <div class="settings-filter-bar">
                <form action="{{ route('admin.newsletter.index') }}" method="GET" class="d-flex align-items-center gap-2 flex-wrap">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by email..." value="{{ $search }}">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search mr-1"></i> Search</button>
                    @if ($search)
                        <a href="{{ route('admin.newsletter.index') }}" class="btn btn-secondary btn-sm">Clear</a>
                    @endif
                </form>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 settings-table">
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Subscribed</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($subscribers as $subscriber)
                            <tr>
                                <td><strong>{{ $subscriber->email }}</strong></td>
                                <td>
                                    @if ($subscriber->isActive())
                                        <span class="settings-status settings-status--live">Active</span>
                                    @else
                                        <span class="settings-status settings-status--hidden">Unsubscribed</span>
                                    @endif
                                </td>
                                <td>{{ $subscriber->subscribed_at?->format('M d, Y g:i A') }}</td>
                                <td class="text-right">
                                    <div class="settings-actions">
                                        <form action="{{ route('admin.newsletter.destroy', $subscriber) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this subscriber?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="settings-empty">
                                    <i class="fas fa-envelope"></i>
                                    <strong>No subscribers yet</strong>
                                    <p>Subscribers will appear here when customers sign up via the footer form.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($subscribers->hasPages())
                <div class="settings-card-footer">{{ $subscribers->links() }}</div>
            @endif
        </div>
    </div>
@endsection

@push('styles')
    @include('admin.settings.partials.page-styles')
@endpush
