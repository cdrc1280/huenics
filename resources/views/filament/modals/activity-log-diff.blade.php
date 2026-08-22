@php
    $old = $record->old_value ?? [];
    $new = $record->new_value ?? [];
    $props = $record->properties ?? [];
    $allKeys = array_unique(array_merge(array_keys($old ?: []), array_keys($new ?: [])));
@endphp

<div class="space-y-4 text-sm text-gray-800 dark:text-gray-200">
    {{-- Header Info Card --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 p-3.5 bg-gray-50 dark:bg-gray-800/80 rounded-xl border border-gray-200 dark:border-gray-700">
        <div>
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Actor</span>
            <div class="mt-0.5 flex items-center gap-2">
                <span class="font-medium text-gray-900 dark:text-white">{{ $record->user?->name ?? 'System / Anonymous' }}</span>
                @if($record->user)
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                        {{ ucwords(str_replace('_', ' ', $record->user->role)) }}
                    </span>
                @endif
            </div>
            <span class="text-xs text-gray-500">{{ $record->user?->email ?? 'N/A' }}</span>
        </div>

        <div>
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Target Subject</span>
            <div class="mt-0.5 font-medium text-gray-900 dark:text-white">
                {{ $record->subject_name }}
            </div>
            <span class="text-xs text-gray-500 font-mono">{{ $record->auditable_type ? class_basename($record->auditable_type) . ' #' . $record->auditable_id : 'System-level' }}</span>
        </div>

        <div>
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">IP & Device</span>
            <div class="mt-0.5 font-mono text-xs text-gray-700 dark:text-gray-300">
                {{ $record->ip_address ?? '127.0.0.1' }}
            </div>
            @if($record->user_agent)
                <p class="text-[11px] text-gray-400 truncate max-w-xs mt-0.5" title="{{ $record->user_agent }}">
                    {{ $record->user_agent }}
                </p>
            @endif
        </div>

        <div>
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Timestamp</span>
            <div class="mt-0.5 text-xs text-gray-900 dark:text-gray-200 font-medium">
                {{ $record->created_at?->format('F d, Y h:i:s A') }}
            </div>
            <span class="text-xs text-gray-400">({{ $record->created_at?->diffForHumans() }})</span>
        </div>
    </div>

    {{-- Description --}}
    <div class="p-3 bg-blue-50/60 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800/60 rounded-lg">
        <span class="text-xs font-bold uppercase tracking-wider text-blue-700 dark:text-blue-300">Summary</span>
        <p class="mt-1 text-sm text-blue-900 dark:text-blue-100 font-medium">{{ $record->description ?: $record->action }}</p>
    </div>

    {{-- Diffs / Values Table --}}
    @if(!empty($allKeys))
        <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden shadow-xs">
            <div class="bg-gray-100 dark:bg-gray-800 px-3.5 py-2 font-semibold text-xs text-gray-600 dark:text-gray-300 uppercase tracking-wider flex justify-between">
                <span>Attribute Changes (Before vs After)</span>
                <span class="text-[10px] text-gray-400 font-normal">{{ count($allKeys) }} fields affected</span>
            </div>
            <div class="overflow-x-auto max-h-72">
                <table class="w-full text-xs text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-900/60 border-b border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 font-medium">
                            <th class="py-2 px-3 w-1/4">Field</th>
                            <th class="py-2 px-3 w-3/8 text-red-600 dark:text-red-400 bg-red-50/40 dark:bg-red-950/20">Previous Value</th>
                            <th class="py-2 px-3 w-3/8 text-emerald-600 dark:text-emerald-400 bg-emerald-50/40 dark:bg-emerald-950/20">New Value</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 font-mono">
                        @foreach($allKeys as $key)
                            @php
                                $valOld = $old[$key] ?? null;
                                $valNew = $new[$key] ?? null;
                                $oldStr = is_array($valOld) ? json_encode($valOld, JSON_PRETTY_PRINT) : (is_bool($valOld) ? ($valOld ? 'true' : 'false') : (is_null($valOld) ? '<null>' : (string)$valOld));
                                $newStr = is_array($valNew) ? json_encode($valNew, JSON_PRETTY_PRINT) : (is_bool($valNew) ? ($valNew ? 'true' : 'false') : (is_null($valNew) ? '<null>' : (string)$valNew));
                                $isChanged = $oldStr !== $newStr;
                            @endphp
                            <tr class="{{ $isChanged ? 'bg-amber-50/20 dark:bg-amber-950/10' : '' }} hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="py-2 px-3 font-sans font-medium text-gray-700 dark:text-gray-300">
                                    {{ $key }}
                                </td>
                                <td class="py-2 px-3 text-red-700 dark:text-red-400 bg-red-50/30 dark:bg-red-950/10 break-all whitespace-pre-wrap">
                                    {{ $oldStr }}
                                </td>
                                <td class="py-2 px-3 text-emerald-700 dark:text-emerald-400 bg-emerald-50/30 dark:bg-emerald-950/10 break-all whitespace-pre-wrap">
                                    {{ $newStr }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @elseif(!empty($props))
        <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-3 bg-gray-50 dark:bg-gray-800 font-mono text-xs">
            <span class="text-[10px] uppercase font-bold text-gray-400 block mb-1">Event Payload / Properties</span>
            <pre class="overflow-x-auto text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ json_encode($props, JSON_PRETTY_PRINT) }}</pre>
        </div>
    @else
        <div class="p-4 text-center text-xs text-gray-400 bg-gray-50 dark:bg-gray-800/50 rounded-xl">
            No attribute delta recorded for this event.
        </div>
    @endif
</div>
