@extends('layouts.admin')

@section('title', 'Migration')
@section('page_title', 'Database Migration')

@section('content')
    @php $isUpToDate = $status['pending_count'] === 0; @endphp

    <div class="settings-page">
        <a href="{{ route('admin.terminal.index') }}" class="settings-back-link">
            <i class="fas fa-arrow-left"></i> Back to Terminal
        </a>

        <section class="settings-hero">
            <div>
                <span class="settings-eyebrow">Terminal</span>
                <h2>Database Migration</h2>
                <p>Run pending migrations after uploading new code — no SSH required.</p>
                <div class="settings-hero-meta">
                    <span class="settings-hero-chip"><i class="fas fa-database"></i> {{ $status['database'] }}</span>
                    <span class="settings-hero-chip">
                        <i class="fas fa-{{ $isUpToDate ? 'check-circle' : 'exclamation-triangle' }}"></i>
                        {{ $isUpToDate ? 'Up to date' : $status['pending_count'].' pending' }}
                    </span>
                </div>
            </div>
        </section>

        <section class="row mb-3">
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="settings-stat settings-stat--teal">
                    <span class="settings-stat-icon"><i class="fas fa-list"></i></span>
                    <div>
                        <div class="settings-stat-value">{{ $status['total'] }}</div>
                        <div class="settings-stat-label">Total Migrations</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="settings-stat settings-stat--green">
                    <span class="settings-stat-icon"><i class="fas fa-check"></i></span>
                    <div>
                        <div class="settings-stat-value">{{ $status['ran'] }}</div>
                        <div class="settings-stat-label">Already Run</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <article class="settings-stat settings-stat--{{ $isUpToDate ? 'green' : 'amber' }}">
                    <span class="settings-stat-icon"><i class="fas fa-clock"></i></span>
                    <div>
                        <div class="settings-stat-value">{{ $status['pending_count'] }}</div>
                        <div class="settings-stat-label">Pending</div>
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
                            <h3>Migration Status</h3>
                            <p>Current database migration state on this server.</p>
                        </div>
                        <span class="terminal-status terminal-status--{{ $isUpToDate ? 'ready' : 'action' }}">
                            {{ $isUpToDate ? 'Up to date' : 'Action needed' }}
                        </span>
                    </div>
                    <div class="settings-card-body">
                        <div class="terminal-info-grid mb-3">
                            <div class="terminal-info-row">
                                <strong>Database</strong>
                                <code>{{ $status['database'] }}</code>
                            </div>
                            <div class="terminal-info-row">
                                <strong>Total</strong>
                                <span>{{ $status['total'] }}</span>
                            </div>
                            <div class="terminal-info-row">
                                <strong>Already Run</strong>
                                <span>{{ $status['ran'] }}</span>
                            </div>
                            <div class="terminal-info-row">
                                <strong>Pending</strong>
                                <span class="terminal-status terminal-status--{{ $isUpToDate ? 'ready' : 'action' }}">{{ $status['pending_count'] }}</span>
                            </div>
                        </div>

                        @unless ($isUpToDate)
                            <div class="terminal-alert terminal-alert--warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <div>
                                    <strong>Pending migrations</strong>
                                    <ul class="terminal-pending-list mb-0">
                                        @foreach ($status['pending'] as $migration)
                                            <li><code>{{ $migration }}</code></li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @else
                            <div class="terminal-alert terminal-alert--success">
                                <i class="fas fa-check-circle"></i>
                                <div>
                                    <strong>Database is up to date</strong>
                                    <span>All migrations have been applied on this server.</span>
                                </div>
                            </div>
                        @endunless

                        <form action="{{ route('admin.migration.store') }}" method="POST" onsubmit="return confirm('Run pending database migrations on this server?')">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-lg" @disabled($isUpToDate)>
                                <i class="fas fa-database mr-1"></i>
                                {{ $isUpToDate ? 'All Migrations Applied' : 'Run Migrations' }}
                            </button>
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
                        <span class="settings-side-icon settings-side-icon--info"><i class="fas fa-info-circle"></i></span>
                        <div>
                            <h4>When to Run</h4>
                            <p>After deploying new code.</p>
                        </div>
                    </div>
                    <div class="settings-side-body">
                        <ul class="settings-side-list">
                            <li>Run migrations after uploading new code that includes database changes.</li>
                            <li>Only pending migrations will be executed.</li>
                            <li>Equivalent to <code>php artisan migrate --force</code>.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    @include('admin.terminal.partials.page-styles')
@endpush
