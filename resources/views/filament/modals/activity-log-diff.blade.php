@php
    $old = $record->old_value ?? [];
    $new = $record->new_value ?? [];
    $props = $record->properties ?? [];
    $event = strtolower((string)$record->event);

    // Format field name helper
    $formatKey = fn($k) => ucwords(str_replace(['_', '-'], ' ', $k));

    // Format value helper
    $formatVal = function($val, $key = null) {
        if (is_null($val)) return '<empty>';
        if (is_bool($val)) return $val ? 'Yes' : 'No';
        if (is_array($val)) return json_encode($val, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        
        // Currency detection
        if (is_numeric($val) && (str_contains((string)$key, 'amount') || str_contains((string)$key, 'price') || str_contains((string)$key, 'total') || str_contains((string)$key, 'cost'))) {
            return '₱' . number_format((float)$val, 2);
        }
        return (string)$val;
    };

    // For updates, find changed keys
    $changedKeys = [];
    if ($event === 'updated' || (!empty($old) && !empty($new))) {
        $allKeys = array_unique(array_merge(array_keys($old ?: []), array_keys($new ?: [])));
        foreach ($allKeys as $k) {
            $vOld = $old[$k] ?? null;
            $vNew = $new[$k] ?? null;
            if ($vOld != $vNew) {
                $changedKeys[] = $k;
            }
        }
    }

    $eventBadge = match($event) {
        'created' => ['bg' => 'bg-emerald-50 dark:bg-emerald-950/40', 'border' => 'border-emerald-200 dark:border-emerald-800', 'text' => 'text-emerald-700 dark:text-emerald-300', 'label' => 'Record Created', 'dot' => 'bg-emerald-500'],
        'updated' => ['bg' => 'bg-sky-50 dark:bg-sky-950/40', 'border' => 'border-sky-200 dark:border-sky-800', 'text' => 'text-sky-700 dark:text-sky-300', 'label' => 'Record Updated', 'dot' => 'bg-sky-500'],
        'deleted', 'force_deleted' => ['bg' => 'bg-rose-50 dark:bg-rose-950/40', 'border' => 'border-rose-200 dark:border-rose-800', 'text' => 'text-rose-700 dark:text-rose-300', 'label' => $event === 'force_deleted' ? 'Permanently Purged' : 'Record Deleted', 'dot' => 'bg-rose-500'],
        'login' => ['bg' => 'bg-violet-50 dark:bg-violet-950/40', 'border' => 'border-violet-200 dark:border-violet-800', 'text' => 'text-violet-700 dark:text-violet-300', 'label' => 'User Sign-In', 'dot' => 'bg-violet-500'],
        'logout' => ['bg' => 'bg-gray-50 dark:bg-gray-800/40', 'border' => 'border-gray-200 dark:border-gray-700', 'text' => 'text-gray-700 dark:text-gray-300', 'label' => 'User Sign-Out', 'dot' => 'bg-gray-400'],
        'verified' => ['bg' => 'bg-amber-50 dark:bg-amber-950/40', 'border' => 'border-amber-200 dark:border-amber-800', 'text' => 'text-amber-700 dark:text-amber-300', 'label' => 'Document Verified', 'dot' => 'bg-amber-500'],
        'converted' => ['bg' => 'bg-emerald-50 dark:bg-emerald-950/40', 'border' => 'border-emerald-200 dark:border-emerald-800', 'text' => 'text-emerald-700 dark:text-emerald-300', 'label' => 'Quotation Converted', 'dot' => 'bg-emerald-500'],
        default => ['bg' => 'bg-slate-50 dark:bg-slate-900', 'border' => 'border-slate-200 dark:border-slate-800', 'text' => 'text-slate-700 dark:text-slate-300', 'label' => ucwords(str_replace('_', ' ', $event)), 'dot' => 'bg-slate-400'],
    };
@endphp

<div class="space-y-4 text-sm text-gray-800 dark:text-gray-200">
    {{-- Header Ribbon --}}
    <div class="flex flex-wrap items-center justify-between gap-3 p-3.5 rounded-xl border {{ $eventBadge['bg'] }} {{ $eventBadge['border'] }}">
        <div class="flex items-center gap-2.5">
            <span class="w-2.5 h-2.5 rounded-full {{ $eventBadge['dot'] }}"></span>
            <span class="font-bold text-xs uppercase tracking-wider {{ $eventBadge['text'] }}">
                {{ $eventBadge['label'] }}
            </span>
            <span class="text-gray-300 dark:text-gray-600">|</span>
            <span class="font-semibold text-gray-900 dark:text-white text-xs">
                {{ $record->subject_name }}
            </span>
        </div>
        <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ $record->created_at?->format('M d, Y · h:i A') }}</span>
            <span class="text-gray-400">({{ $record->created_at?->diffForHumans() }})</span>
        </div>
    </div>

    {{-- Essential Context Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
        {{-- Actor Details --}}
        <div class="p-3 bg-gray-50 dark:bg-gray-800/80 rounded-xl border border-gray-200/80 dark:border-gray-700/80 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-gray-200 dark:bg-gray-700 flex items-center justify-center font-bold text-gray-700 dark:text-gray-300 text-xs shrink-0">
                {{ strtoupper(substr($record->user?->name ?? 'SY', 0, 2)) }}
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <span class="font-bold text-gray-900 dark:text-white truncate">{{ $record->user?->name ?? 'System / Automatic' }}</span>
                    @if($record->user)
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                            {{ ucwords(str_replace('_', ' ', $record->user->role)) }}
                        </span>
                    @endif
                </div>
                <p class="text-gray-500 truncate mt-0.5">{{ $record->user?->email ?? 'Automated background trigger' }}</p>
            </div>
        </div>

        {{-- Device & Network --}}
        <div class="p-3 bg-gray-50 dark:bg-gray-800/80 rounded-xl border border-gray-200/80 dark:border-gray-700/80 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-400 shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
            <div class="min-w-0">
                <p class="font-medium text-gray-900 dark:text-white truncate">{{ $record->device_summary }}</p>
                <p class="text-gray-500 font-mono text-[11px] truncate mt-0.5">IP: {{ $record->ip_address ?? '127.0.0.1' }}</p>
            </div>
        </div>
    </div>

    {{-- Action Description --}}
    @if($record->description)
        <div class="px-3.5 py-2.5 bg-gray-100 dark:bg-gray-800 rounded-lg text-xs font-medium text-gray-800 dark:text-gray-200 flex items-start gap-2 border border-gray-200 dark:border-gray-700">
            <svg class="w-4 h-4 text-gray-400 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ $record->description }}</span>
        </div>
    @endif

    {{-- Main Inspection Body --}}
    @if(!empty($changedKeys))
        {{-- High-Signal Before vs After Diff Table (Updates) --}}
        <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden shadow-xs">
            <div class="bg-gray-100 dark:bg-gray-800 px-3.5 py-2 font-semibold text-xs text-gray-600 dark:text-gray-300 uppercase tracking-wider flex justify-between items-center">
                <span>Field Modifications</span>
                <span class="text-[11px] font-normal text-gray-500">{{ count($changedKeys) }} {{ count($changedKeys) === 1 ? 'attribute' : 'attributes' }} changed</span>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach($changedKeys as $key)
                    @php
                        $oldValStr = $formatVal($old[$key] ?? null, $key);
                        $newValStr = $formatVal($new[$key] ?? null, $key);
                    @endphp
                    <div class="p-3 bg-white dark:bg-gray-900/60 hover:bg-gray-50 dark:hover:bg-gray-800/40 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 text-xs">
                        <span class="font-semibold text-gray-700 dark:text-gray-300 sm:w-1/3 break-words">
                            {{ $formatKey($key) }}
                        </span>
                        
                        <div class="flex items-center gap-2 sm:w-2/3">
                            {{-- Old Value --}}
                            <div class="flex-1 px-2.5 py-1.5 rounded-lg bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900/50 text-rose-700 dark:text-rose-300 font-mono break-all line-through text-[11px]">
                                {{ $oldValStr }}
                            </div>

                            {{-- Arrow --}}
                            <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>

                            {{-- New Value --}}
                            <div class="flex-1 px-2.5 py-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900/50 text-emerald-700 dark:text-emerald-300 font-mono font-medium break-all text-[11px]">
                                {{ $newValStr }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @elseif(!empty($new) && ($event === 'created' || empty($old)))
        {{-- Clean Key-Value Cards for Created / Restored Records --}}
        <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden shadow-xs">
            <div class="bg-gray-100 dark:bg-gray-800 px-3.5 py-2 font-semibold text-xs text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                Initial Record Data
            </div>
            <div class="p-3 bg-white dark:bg-gray-900/60 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5">
                @foreach($new as $k => $v)
                    <div class="p-2 rounded-lg bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700/60">
                        <span class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 block">{{ $formatKey($k) }}</span>
                        <span class="text-xs font-mono font-medium text-gray-800 dark:text-gray-200 break-all">{{ $formatVal($v, $k) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @elseif(!empty($old) && ($event === 'deleted' || $event === 'force_deleted'))
        {{-- Deleted Record Snapshot --}}
        <div class="border border-rose-200 dark:border-rose-900/50 rounded-xl overflow-hidden shadow-xs">
            <div class="bg-rose-50 dark:bg-rose-950/40 px-3.5 py-2 font-semibold text-xs text-rose-700 dark:text-rose-300 uppercase tracking-wider">
                Deleted Record Snapshot
            </div>
            <div class="p-3 bg-white dark:bg-gray-900/60 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5">
                @foreach($old as $k => $v)
                    <div class="p-2 rounded-lg bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700/60">
                        <span class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 block">{{ $formatKey($k) }}</span>
                        <span class="text-xs font-mono text-gray-700 dark:text-gray-300 break-all">{{ $formatVal($v, $k) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @elseif(!empty($props))
        {{-- Event Parameters / Properties --}}
        <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-3 bg-gray-50 dark:bg-gray-800 text-xs">
            <span class="text-[10px] uppercase font-bold text-gray-400 block mb-1.5">Context Payload</span>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                @foreach($props as $pk => $pv)
                    <div class="p-2 rounded-lg bg-white dark:bg-gray-900 border border-gray-200/60 dark:border-gray-700 font-mono">
                        <span class="text-gray-400 font-sans block text-[10px]">{{ $formatKey($pk) }}</span>
                        <span class="text-gray-800 dark:text-gray-200 font-semibold">{{ is_array($pv) ? json_encode($pv) : (string)$pv }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="p-4 text-center text-xs text-gray-400 bg-gray-50 dark:bg-gray-800/50 rounded-xl">
            No attribute deltas recorded for this event.
        </div>
    @endif
</div>
