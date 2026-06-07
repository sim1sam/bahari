@props(['features' => []])

<section class="py-16 border-t border-border">
    <div class="container-store">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            @foreach ($features as $feature)
                <div class="flex items-start gap-4">
                    <div class="shrink-0 w-12 h-12 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center">
                        {!! $feature['icon'] !!}
                    </div>
                    <div>
                        <h3 class="font-semibold text-ink">{{ $feature['title'] }}</h3>
                        <p class="mt-1 text-sm text-ink-muted leading-relaxed">{{ $feature['description'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
