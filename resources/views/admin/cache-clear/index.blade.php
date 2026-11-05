@extends('layouts.admin')

@section('title', 'Cache Clear')
@section('page_title', 'Cache Clear')

@section('content')
    <div class="settings-page">
        <a href="{{ route('admin.terminal.index') }}" class="settings-back-link">
            <i class="fas fa-arrow-left"></i> Back to Terminal
        </a>

        <section class="settings-hero">
            <div>
                <span class="settings-eyebrow">Terminal</span>
                <h2>Cache Clear</h2>
                <p>Clear application cache, compiled views, routes, and config after uploading code — no SSH needed.</p>
                <div class="settings-hero-meta">
                    <span class="settings-hero-chip"><i class="fas fa-broom"></i> cache · view · route · config</span>
                </div>
            </div>
        </section>

        @include('admin.terminal.partials.nav')

        <div class="row">
            <div class="col-lg-8 mb-3">
                <div class="settings-card">
                    <div class="settings-card-head">
                        <div>
                            <h3>Run Cache Clear</h3>
                            <p>Runs <code>php artisan cache:clear</code>, <code>view:clear</code>, <code>route:clear</code>, and <code>config:clear</code>.</p>
                        </div>
                    </div>
                    <div class="settings-card-body">
                        <ul class="mb-3 text-muted small pl-3">
                            <li>Use after deploying new Blade/PHP files</li>
                            <li>Use after migration if footer/pages/status still look old</li>
                            <li>Safe to run anytime — does not delete database data</li>
                        </ul>

                        <form action="{{ route('admin.cache-clear.store') }}" method="POST" onsubmit="return confirm('Clear all application caches now?')">
                            @csrf
                            <button type="submit" class="btn btn-warning btn-lg">
                                <i class="fas fa-broom mr-1"></i> Clear Cache Now
                            </button>
                        </form>
                    </div>
                </div>

                @if ($lastOutput)
                    <div class="settings-card mt-3">
                        <div class="settings-card-head">
                            <div>
                                <h3>Last Output</h3>
                                <p>Result from the most recent cache clear.</p>
                            </div>
                        </div>
                        <div class="settings-card-body p-0">
<pre class="mb-0 p-3 bg-dark text-white" style="white-space:pre-wrap;border-radius:0 0 .75rem .75rem;">{{ $lastOutput }}</pre>
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-lg-4 mb-3">
                <div class="settings-side-card">
                    <div class="settings-side-head">
                        <span class="settings-side-icon"><i class="fas fa-database"></i></span>
                        <div>
                            <h4>Also run Migration</h4>
                            <p>If new tables/columns were added, migrate first, then clear cache.</p>
                        </div>
                    </div>
                    <div class="settings-side-body">
                        <a href="{{ route('admin.migration.index') }}" class="btn btn-primary btn-sm btn-block">
                            <i class="fas fa-database mr-1"></i> Open Migration
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    @include('admin.terminal.partials.page-styles')
@endpush
