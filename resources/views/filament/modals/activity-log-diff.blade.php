@php
    $old = $record->old_value ?? [];
    $new = $record->new_value ?? [];
    $props = $record->properties ?? [];
    $event = strtolower((string)($record->event ?: $record->action));

    $formatKey = fn($k) => ucwords(str_replace(['_', '-'], ' ', (string)$k));

    $formatVal = function($val, $key = null) {
        if (is_null($val) || $val === '') return '—';
        if (is_bool($val)) return $val ? 'Yes' : 'No';
        if (is_array($val)) return json_encode($val, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        
        if (is_numeric($val) && (str_contains((string)$key, 'amount') || str_contains((string)$key, 'price') || str_contains((string)$key, 'total') || str_contains((string)$key, 'cost'))) {
            return '₱' . number_format((float)$val, 2);
        }
        return (string)$val;
    };

    // Find changed keys for updates
    $changedKeys = [];
    if (!empty($old) && !empty($new)) {
        $allKeys = array_unique(array_merge(array_keys($old ?: []), array_keys($new ?: [])));
        foreach ($allKeys as $k) {
            $vOld = $old[$k] ?? null;
            $vNew = $new[$k] ?? null;
            if ($vOld != $vNew) {
                $changedKeys[] = $k;
            }
        }
    }

    $eventTheme = match($event) {
        'created', 'converted', 'restored' => [
            'bg' => 'rgba(16, 185, 129, 0.1)',
            'border' => '#10b981',
            'color' => '#059669',
            'label' => strtoupper($event),
            'dot' => '#10b981',
        ],
        'updated', 'line_item_adjusted', 'transaction_updated' => [
            'bg' => 'rgba(14, 165, 233, 0.1)',
            'border' => '#0ea5e9',
            'color' => '#0284c7',
            'label' => 'UPDATED',
            'dot' => '#0ea5e9',
        ],
        'deleted', 'force_deleted', 'document_rejected' => [
            'bg' => 'rgba(244, 63, 94, 0.1)',
            'border' => '#f43f5e',
            'color' => '#e11d48',
            'label' => $event === 'force_deleted' ? 'PURGED' : 'DELETED',
            'dot' => '#f43f5e',
        ],
        'login' => [
            'bg' => 'rgba(139, 92, 246, 0.1)',
            'border' => '#8b5cf6',
            'color' => '#7c3aed',
            'label' => 'SIGN IN',
            'dot' => '#8b5cf6',
        ],
        'logout' => [
            'bg' => 'rgba(107, 114, 128, 0.1)',
            'border' => '#6b7280',
            'color' => '#4b5563',
            'label' => 'SIGN OUT',
            'dot' => '#6b7280',
        ],
        'verified' => [
            'bg' => 'rgba(245, 158, 11, 0.1)',
            'border' => '#f59e0b',
            'color' => '#d97706',
            'label' => 'VERIFIED',
            'dot' => '#f59e0b',
        ],
        default => [
            'bg' => 'rgba(100, 116, 139, 0.1)',
            'border' => '#64748b',
            'color' => '#475569',
            'label' => strtoupper(str_replace('_', ' ', $event)),
            'dot' => '#64748b',
        ],
    };
@endphp

<div style="font-family: inherit; font-size: 0.875rem; line-height: 1.5; color: inherit; display: flex; flex-direction: column; gap: 1rem;">
    <style>
        .act-diff-card {
            border: 1px solid rgba(156, 163, 175, 0.25);
            border-radius: 0.75rem;
            padding: 0.875rem 1rem;
            background: rgba(249, 250, 251, 0.6);
        }
        .dark .act-diff-card {
            background: rgba(30, 41, 59, 0.5);
            border-color: rgba(75, 85, 99, 0.4);
        }
        .act-grid-2 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 0.75rem;
        }
        .act-table-container {
            border: 1px solid rgba(156, 163, 175, 0.25);
            border-radius: 0.75rem;
            overflow: hidden;
            background: #ffffff;
        }
        .dark .act-table-container {
            background: rgba(15, 23, 42, 0.7);
            border-color: rgba(75, 85, 99, 0.4);
        }
        .act-pill-old {
            background: rgba(244, 63, 94, 0.1);
            color: #e11d48;
            border: 1px solid rgba(244, 63, 94, 0.25);
            border-radius: 0.375rem;
            padding: 0.35rem 0.6rem;
            font-family: monospace;
            font-size: 0.8125rem;
            text-decoration: line-through;
            word-break: break-all;
            display: inline-block;
            width: 100%;
        }
        .dark .act-pill-old {
            background: rgba(244, 63, 94, 0.18);
            color: #fb7185;
            border-color: rgba(244, 63, 94, 0.35);
        }
        .act-pill-new {
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
            border: 1px solid rgba(16, 185, 129, 0.25);
            border-radius: 0.375rem;
            padding: 0.35rem 0.6rem;
            font-family: monospace;
            font-size: 0.8125rem;
            font-weight: 600;
            word-break: break-all;
            display: inline-block;
            width: 100%;
        }
        .dark .act-pill-new {
            background: rgba(16, 185, 129, 0.18);
            color: #34d399;
            border-color: rgba(16, 185, 129, 0.35);
        }
    </style>

    {{-- 1. Main Header Strip --}}
    <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem; padding: 0.75rem 1rem; border-radius: 0.75rem; border: 1px solid {{ $eventTheme['border'] }}; background: {{ $eventTheme['bg'] }};">
        <div style="display: flex; align-items: center; gap: 0.625rem; flex-wrap: wrap;">
            <span style="display: inline-block; width: 0.625rem; height: 0.625rem; border-radius: 9999px; background: {{ $eventTheme['dot'] }};"></span>
            <span style="font-weight: 800; font-size: 0.75rem; letter-spacing: 0.05em; color: {{ $eventTheme['color'] }};">
                {{ $eventTheme['label'] }}
            </span>
            <span style="opacity: 0.35;">|</span>
            <span style="font-weight: 700; font-size: 0.875rem;">
                {{ $record->subject_name }}
            </span>
        </div>
        <div style="font-size: 0.75rem; opacity: 0.8; display: flex; align-items: center; gap: 0.375rem;">
            <span>{{ $record->created_at?->format('M d, Y · h:i A') }}</span>
            <span style="opacity: 0.6;">({{ $record->created_at?->diffForHumans() }})</span>
        </div>
    </div>

    {{-- 2. Context Summary Box --}}
    @if($record->description)
        <div class="act-diff-card" style="border-left: 4px solid {{ $eventTheme['border'] }};">
            <span style="font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; opacity: 0.6; display: block; margin-bottom: 0.25rem;">Action Summary</span>
            <p style="margin: 0; font-weight: 500; font-size: 0.875rem;">{{ $record->description }}</p>
        </div>
    @endif

    {{-- 3. Actor & Device Details --}}
    <div class="act-grid-2">
        <div class="act-diff-card">
            <span style="font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; opacity: 0.6; display: block; margin-bottom: 0.35rem;">Performed By</span>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <strong style="font-size: 0.875rem;">{{ $record->user?->name ?? 'System / Automatic' }}</strong>
                @if($record->user)
                    <span style="display: inline-block; font-size: 0.6875rem; font-weight: 700; padding: 0.15rem 0.45rem; border-radius: 0.25rem; background: rgba(156, 163, 175, 0.2);">
                        {{ ucwords(str_replace('_', ' ', $record->user->role)) }}
                    </span>
                @endif
            </div>
            <span style="font-size: 0.75rem; opacity: 0.7; display: block; margin-top: 0.15rem;">{{ $record->user?->email ?? 'Background System Process' }}</span>
        </div>

        <div class="act-diff-card">
            <span style="font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; opacity: 0.6; display: block; margin-bottom: 0.35rem;">Environment & Network</span>
            <div style="font-weight: 600; font-size: 0.875rem;">{{ $record->device_summary }}</div>
            <span style="font-family: monospace; font-size: 0.75rem; opacity: 0.7; display: block; margin-top: 0.15rem;">IP: {{ $record->ip_address ?? '127.0.0.1' }}</span>
        </div>
    </div>

    {{-- 4. Inspection Delta / Body --}}
    @if(!empty($changedKeys))
        {{-- For Updates: Before vs After Side-by-Side Table --}}
        <div class="act-table-container">
            <div style="padding: 0.625rem 1rem; border-bottom: 1px solid rgba(156, 163, 175, 0.2); font-weight: 700; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; display: flex; justify-content: space-between; align-items: center;">
                <span>Changed Fields</span>
                <span style="font-size: 0.75rem; font-weight: 500; opacity: 0.6;">{{ count($changedKeys) }} {{ count($changedKeys) === 1 ? 'attribute' : 'attributes' }} modified</span>
            </div>
            <div style="display: flex; flex-direction: column; divide-y: 1px solid rgba(156, 163, 175, 0.15);">
                @foreach($changedKeys as $key)
                    @php
                        $oldValStr = $formatVal($old[$key] ?? null, $key);
                        $newValStr = $formatVal($new[$key] ?? null, $key);
                    @endphp
                    <div style="padding: 0.75rem 1rem; border-bottom: 1px solid rgba(156, 163, 175, 0.15); display: flex; flex-direction: column; gap: 0.5rem;">
                        <div style="font-weight: 700; font-size: 0.8125rem;">
                            {{ $formatKey($key) }}
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr auto 1fr; align-items: center; gap: 0.75rem;">
                            <div>
                                <span style="font-size: 0.6875rem; font-weight: 600; text-transform: uppercase; opacity: 0.6; display: block; margin-bottom: 0.15rem;">Previous</span>
                                <div class="act-pill-old">{{ $oldValStr }}</div>
                            </div>
                            <div style="font-size: 1rem; opacity: 0.5; text-align: center; padding-top: 1rem;">→</div>
                            <div>
                                <span style="font-size: 0.6875rem; font-weight: 600; text-transform: uppercase; opacity: 0.6; display: block; margin-bottom: 0.15rem;">New</span>
                                <div class="act-pill-new">{{ $newValStr }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @elseif(!empty($new) && ($event === 'created' || empty($old)))
        {{-- For Creation: Clean Data Grid --}}
        <div class="act-table-container">
            <div style="padding: 0.625rem 1rem; border-bottom: 1px solid rgba(156, 163, 175, 0.2); font-weight: 700; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">
                Populated Record Attributes
            </div>
            <div style="padding: 1rem; display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 0.75rem;">
                @foreach($new as $k => $v)
                    <div style="padding: 0.625rem 0.75rem; border-radius: 0.5rem; background: rgba(156, 163, 175, 0.08); border: 1px solid rgba(156, 163, 175, 0.15);">
                        <span style="font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; opacity: 0.6; display: block; margin-bottom: 0.2rem;">{{ $formatKey($k) }}</span>
                        <span style="font-family: monospace; font-size: 0.8125rem; font-weight: 600; word-break: break-all;">{{ $formatVal($v, $k) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @elseif(!empty($old) && ($event === 'deleted' || $event === 'force_deleted'))
        {{-- For Deletion: Clean Snapshot --}}
        <div class="act-table-container" style="border-color: rgba(244, 63, 94, 0.3);">
            <div style="padding: 0.625rem 1rem; background: rgba(244, 63, 94, 0.08); border-bottom: 1px solid rgba(244, 63, 94, 0.2); font-weight: 700; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #e11d48;">
                Deleted Record Snapshot
            </div>
            <div style="padding: 1rem; display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 0.75rem;">
                @foreach($old as $k => $v)
                    <div style="padding: 0.625rem 0.75rem; border-radius: 0.5rem; background: rgba(156, 163, 175, 0.08); border: 1px solid rgba(156, 163, 175, 0.15);">
                        <span style="font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; opacity: 0.6; display: block; margin-bottom: 0.2rem;">{{ $formatKey($k) }}</span>
                        <span style="font-family: monospace; font-size: 0.8125rem; opacity: 0.85; word-break: break-all;">{{ $formatVal($v, $k) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @elseif(!empty($props))
        {{-- For Context Props --}}
        <div class="act-diff-card">
            <span style="font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; opacity: 0.6; display: block; margin-bottom: 0.5rem;">Event Properties</span>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 0.5rem;">
                @foreach($props as $pk => $pv)
                    <div style="padding: 0.5rem 0.75rem; border-radius: 0.375rem; background: rgba(156, 163, 175, 0.08); font-family: monospace; font-size: 0.75rem;">
                        <span style="font-family: inherit; font-size: 0.6875rem; opacity: 0.6; display: block;">{{ $formatKey($pk) }}</span>
                        <strong>{{ is_array($pv) ? json_encode($pv) : (string)$pv }}</strong>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div style="padding: 2rem 1rem; text-align: center; font-size: 0.8125rem; opacity: 0.5; border-radius: 0.75rem; background: rgba(156, 163, 175, 0.05);">
            No attribute modifications recorded for this event.
        </div>
    @endif
</div>
