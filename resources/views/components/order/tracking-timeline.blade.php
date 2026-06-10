@props(['order'])

@php
    $steps = \App\Models\Order::trackingSteps();
    $currentIndex = $order->trackingStepIndex();
    $isCancelled = $order->isCancelled();
    $progress = $order->trackingProgressPercent();
@endphp

<div class="order-track-timeline" data-progress="{{ $progress }}" data-cancelled="{{ $isCancelled ? '1' : '0' }}">
    @if ($isCancelled)
        <div class="order-track-cancelled mb-8 flex items-start gap-4 rounded-2xl border border-red-200 bg-red-50 p-5">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
            <div>
                <p class="font-semibold text-red-800">Order Cancelled</p>
                <p class="mt-1 text-sm text-red-700">This order was cancelled and will not be processed further.</p>
            </div>
        </div>
    @endif

    {{-- Desktop horizontal timeline --}}
    <div class="hidden md:block">
        <div class="relative px-4 pt-2 pb-4">
            <div class="order-track-rail absolute left-[12.5%] right-[12.5%] top-8 h-1 rounded-full bg-border"></div>
            <div class="order-track-rail-fill absolute left-[12.5%] top-8 h-1 rounded-full bg-brand-600" style="width: 0%; --track-target-width: {{ $isCancelled ? 0 : $progress }}%;"></div>

            <ol class="relative grid grid-cols-4 gap-2">
                @foreach ($steps as $index => $step)
                    @php
                        $isComplete = ! $isCancelled && $index < $currentIndex;
                        $isCurrent = ! $isCancelled && $index === $currentIndex;
                        $isUpcoming = ! $isCancelled && $index > $currentIndex;
                    @endphp
                    <li class="order-track-step flex flex-col items-center text-center" style="--step-delay: {{ $index * 0.15 }}s">
                        <div @class([
                            'order-track-node relative z-10 flex h-14 w-14 items-center justify-center rounded-full border-2 transition-all duration-500',
                            'border-brand-600 bg-brand-600 text-white shadow-lg shadow-brand-600/30 order-track-node--current' => $isCurrent,
                            'border-brand-600 bg-brand-600 text-white order-track-node--done' => $isComplete,
                            'border-border bg-surface-elevated text-ink-muted' => $isUpcoming || $isCancelled,
                        ])>
                            @if ($isComplete)
                                <svg class="order-track-check h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                            @elseif ($step['icon'] === 'clipboard')
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            @elseif ($step['icon'] === 'cog')
                                <svg class="h-6 w-6 {{ $isCurrent ? 'order-track-spin' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            @elseif ($step['icon'] === 'truck')
                                <svg class="h-6 w-6 {{ $isCurrent ? 'order-track-bounce' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10m10 0h2m-2 0a2 2 0 104 0m-6 0a2 2 0 11-4 0m8-6h2.586a1 1 0 01.707.293l2.414 2.414a1 1 0 01.293.707V16h-4"/></svg>
                            @else
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            @endif

                            @if ($isCurrent)
                                <span class="order-track-pulse absolute inset-0 rounded-full border-2 border-brand-400"></span>
                            @endif
                        </div>
                        <p @class([
                            'mt-4 text-sm font-semibold',
                            'text-brand-700' => $isComplete || $isCurrent,
                            'text-ink-muted' => $isUpcoming || $isCancelled,
                        ])>{{ $step['label'] }}</p>
                        <p class="mt-1 max-w-[9rem] text-xs text-ink-muted">{{ $step['description'] }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </div>

    {{-- Mobile vertical timeline --}}
    <div class="md:hidden">
        <ol class="relative space-y-0 pl-2">
            @foreach ($steps as $index => $step)
                @php
                    $isComplete = ! $isCancelled && $index < $currentIndex;
                    $isCurrent = ! $isCancelled && $index === $currentIndex;
                    $isUpcoming = ! $isCancelled && $index > $currentIndex;
                    $isLast = $index === count($steps) - 1;
                @endphp
                <li class="order-track-step relative flex gap-4 pb-8 last:pb-0" style="--step-delay: {{ $index * 0.12 }}s">
                    @if (! $isLast)
                        <div class="absolute left-[1.65rem] top-14 bottom-0 w-0.5 bg-border">
                            <div @class([
                                'order-track-vertical-fill h-full w-full origin-top bg-brand-600',
                                'scale-y-100' => $isComplete,
                                'scale-y-0' => ! $isComplete,
                            ])></div>
                        </div>
                    @endif

                    <div @class([
                        'relative z-10 flex h-12 w-12 shrink-0 items-center justify-center rounded-full border-2',
                        'border-brand-600 bg-brand-600 text-white shadow-lg shadow-brand-600/30 order-track-node--current' => $isCurrent,
                        'border-brand-600 bg-brand-600 text-white order-track-node--done' => $isComplete,
                        'border-border bg-surface-elevated text-ink-muted' => $isUpcoming || $isCancelled,
                    ])>
                        @if ($isComplete)
                            <svg class="order-track-check h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                        @else
                            <span class="text-sm font-bold">{{ $index + 1 }}</span>
                        @endif
                        @if ($isCurrent)
                            <span class="order-track-pulse absolute inset-0 rounded-full border-2 border-brand-400"></span>
                        @endif
                    </div>

                    <div class="min-w-0 pt-1">
                        <p @class([
                            'font-semibold',
                            'text-brand-700' => $isComplete || $isCurrent,
                            'text-ink-muted' => $isUpcoming || $isCancelled,
                        ])>{{ $step['label'] }}</p>
                        <p class="mt-0.5 text-sm text-ink-muted">{{ $step['description'] }}</p>
                        @if ($isCurrent)
                            <span class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-brand-50 px-2.5 py-1 text-xs font-medium text-brand-700">
                                <span class="order-track-dot h-1.5 w-1.5 rounded-full bg-brand-600"></span>
                                In progress
                            </span>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
    </div>
</div>
