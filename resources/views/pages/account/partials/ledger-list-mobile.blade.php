@if ($entries === [])
    <div class="rounded-2xl bg-surface-elevated border border-border p-8 text-center">
        <p class="font-medium text-ink">No ledger entries yet</p>
        <p class="text-sm text-ink-muted mt-1">Your order charges and payments will appear here</p>
        <a href="{{ route('account.orders') }}" class="inline-block mt-4 px-5 py-2.5 rounded-xl bg-brand-600 text-white text-sm font-semibold">View Orders</a>
    </div>
@else
    <div class="space-y-3">
        @foreach ($entries as $entry)
            @php
                $typeClass = match ($entry['type']) {
                    'Order' => 'bg-amber-50 text-amber-700',
                    'Payment', 'SSLCommerz', 'Bank Payment' => 'bg-emerald-50 text-emerald-700',
                    default => 'bg-surface text-ink-muted',
                };
            @endphp
            <div class="rounded-2xl bg-surface-elevated border border-border p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-semibold text-sm truncate">
                            @if ($entry['order_id'])
                                <a href="{{ route('account.orders.show', $entry['order_id']) }}" class="text-brand-600 hover:underline">{{ $entry['reference'] }}</a>
                            @else
                                {{ $entry['reference'] }}
                            @endif
                        </p>
                        <p class="text-xs text-ink-muted mt-0.5">{{ $entry['date'] }}</p>
                    </div>
                    <span class="shrink-0 px-2.5 py-1 rounded-lg text-[10px] font-semibold {{ $typeClass }}">{{ $entry['type'] }}</span>
                </div>

                <p class="text-sm text-ink-muted mt-2">{{ $entry['description'] }}</p>

                <div class="grid grid-cols-3 gap-2 mt-3 pt-3 border-t border-border">
                    <div>
                        <p class="text-[10px] uppercase tracking-wide text-ink-muted">Debit</p>
                        <p class="text-sm font-semibold mt-0.5 {{ $entry['debit'] > 0 ? 'text-red-600' : 'text-ink-muted' }}">
                            {{ $entry['debit'] > 0 ? money($entry['debit']) : '—' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase tracking-wide text-ink-muted">Credit</p>
                        <p class="text-sm font-semibold mt-0.5 {{ $entry['credit'] > 0 ? 'text-emerald-600' : 'text-ink-muted' }}">
                            {{ $entry['credit'] > 0 ? money($entry['credit']) : '—' }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] uppercase tracking-wide text-ink-muted">Balance</p>
                        <p class="text-sm font-bold mt-0.5 {{ $entry['balance'] > 0 ? 'text-red-600' : 'text-ink' }}">{{ money($entry['balance']) }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
