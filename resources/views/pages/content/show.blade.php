@extends('layouts.ecommerce')

@section('title', $page->title)
@section('meta_description', $page->meta_description ?: \Illuminate\Support\Str::limit(strip_tags($page->content ?? ''), 160))

@section('content')
    <div class="bg-surface border-b border-border">
        <div class="container-store py-8 lg:py-12">
            <nav class="text-sm text-ink-muted mb-3 flex flex-wrap items-center gap-2">
                <a href="{{ route('home') }}" class="hover:text-brand-600">Home</a>
                <span>/</span>
                <span class="text-ink">{{ $page->title }}</span>
            </nav>
            <h1 class="text-3xl lg:text-4xl font-semibold text-ink tracking-tight">{{ $page->title }}</h1>
        </div>
    </div>

    <div class="container-store py-10 lg:py-14">
        <article class="prose prose-stone max-w-3xl page-content text-ink">
            {!! $page->content !!}
        </article>
    </div>
@endsection

@push('styles')
<style>
    .page-content h2 { font-size: 1.35rem; font-weight: 600; margin: 1.5rem 0 .75rem; }
    .page-content h3 { font-size: 1.15rem; font-weight: 600; margin: 1.25rem 0 .5rem; }
    .page-content p { margin: 0 0 1rem; line-height: 1.7; color: inherit; }
    .page-content ul, .page-content ol { margin: 0 0 1rem 1.25rem; }
    .page-content li { margin-bottom: .35rem; }
    .page-content a { color: var(--color-brand-600, #0e7490); text-decoration: underline; }
    .page-content img { max-width: 100%; height: auto; border-radius: .75rem; }
</style>
@endpush
