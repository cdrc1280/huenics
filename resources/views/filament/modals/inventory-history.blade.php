<div class="space-y-3 max-h-96 overflow-y-auto">
    @forelse($transactions as $tx)
        <div class="flex items-start gap-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
            <div class="flex-shrink-0">
                @php
                    $icon = match($tx->transaction_type) {
                        'initial_stock', 'purchase_in', 'adjustment_up' => '⬆️',
                        'component_deduct', 'sales_out', 'adjustment_down' => '⬇️',
                        default => '↔️',
                    };
                    $colorClass = in_array($tx->transaction_type, ['initial_stock','purchase_in','adjustment_up'])
                        ? 'text-green-600'
                        : 'text-red-600';
                @endphp
                <span class="text-lg">{{ $icon }}</span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium {{ $colorClass }}">
                    {{ ucwords(str_replace('_', ' ', $tx->transaction_type)) }}
                    <span class="font-bold">{{ $tx->quantity > 0 ? '+' : '' }}{{ $tx->quantity }}</span>
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $tx->notes }}</p>
                <p class="text-xs text-gray-400 mt-0.5">
                    By {{ $tx->performer?->name ?? 'System' }} · {{ $tx->created_at?->diffForHumans() }}
                </p>
            </div>
        </div>
    @empty
        <p class="text-sm text-gray-400 text-center py-4">No transactions recorded yet.</p>
    @endforelse
</div>
