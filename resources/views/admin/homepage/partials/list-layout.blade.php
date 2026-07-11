<div class="settings-page">
    <a href="{{ route('admin.homepage.index') }}" class="settings-back-link">
        <i class="fas fa-arrow-left"></i> Back to Homepage
    </a>

    <section class="settings-hero">
        <div>
            <span class="settings-eyebrow">Homepage</span>
            <h2>{{ $title }}</h2>
            <p>{{ $description }}</p>
        </div>
        <div class="settings-hero-actions">
            <a href="{{ $createRoute }}" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i> {{ $createLabel }}
            </a>
        </div>
    </section>

    @include('admin.settings.partials.nav')

    <div class="settings-card">
        <div class="settings-card-head">
            <div>
                <h3>{{ $title }}</h3>
                <p>{{ $description }}</p>
            </div>
            <a href="{{ $createRoute }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus mr-1"></i> {{ $createLabel }}
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 settings-table">
                <thead>
                    <tr>
                        {{ $tableHead }}
                    </tr>
                </thead>
                <tbody>
                    {{ $slot }}
                </tbody>
            </table>
        </div>
        @if ($items->hasPages())
            <div class="settings-card-footer">{{ $items->links() }}</div>
        @endif
    </div>
</div>

@push('styles')
    @include('admin.settings.partials.page-styles')
@endpush
