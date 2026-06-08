@props(['order', 'size' => 'sm'])

@php
    $viewClass = $size === 'sm'
        ? 'inline-flex items-center px-3 py-1.5 rounded-lg border border-border text-sm font-medium text-ink-muted hover:text-brand-600 hover:border-brand-300'
        : 'flex-1 py-2.5 rounded-xl border border-border text-sm font-medium text-center text-ink-muted hover:text-brand-600';
    $deleteClass = $size === 'sm'
        ? 'inline-flex items-center px-3 py-1.5 rounded-lg border border-red-200 text-sm font-medium text-red-600 hover:bg-red-50'
        : 'flex-1 py-2.5 rounded-xl border border-red-200 text-sm font-medium text-center text-red-600 bg-red-50 hover:bg-red-100';
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center gap-2 justify-end']) }}>
    <a href="{{ route('account.orders.show', $order) }}" class="{{ $viewClass }}">View</a>
    @if ($order->canBeDeleted())
        <form
            action="{{ route('account.orders.destroy', $order) }}"
            method="POST"
            onsubmit="return confirm('Delete this order? This cannot be undone.')"
        >
            @csrf
            @method('DELETE')
            <button type="submit" class="{{ $deleteClass }}">Delete</button>
        </form>
    @endif
</div>
