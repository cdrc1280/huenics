<div class="huenics-bom-modal">
    <style>
        .huenics-bom-modal {
            --bom-bg: #ffffff;
            --bom-card-bg: #f8fafc;
            --bom-card-border: #e2e8f0;
            --bom-text-main: #0f172a;
            --bom-text-secondary: #334155;
            --bom-text-muted: #64748b;
            --bom-text-dim: #94a3b8;
            --bom-accent: #0284c7;
            --bom-accent-subtle: #e0f2fe;
            --bom-success: #16a34a;
            --bom-success-subtle: #dcfce7;
            --bom-warning: #d97706;
            --bom-warning-subtle: #fef3c7;
            --bom-danger: #dc2626;
            --bom-danger-subtle: #fee2e2;
            --bom-purple: #7c3aed;
            --bom-purple-subtle: #f3e8ff;
            --bom-table-head: #f1f5f9;
            --bom-row-border: #e2e8f0;
            --bom-row-hover: #f8fafc;
            --bom-chip-bg: #ffffff;
            font-family: inherit;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            color: var(--bom-text-main);
            box-sizing: border-box;
        }

        .dark .huenics-bom-modal,
        :is(.dark) .huenics-bom-modal {
            --bom-bg: #0b0f19;
            --bom-card-bg: #111827;
            --bom-card-border: #1f2937;
            --bom-text-main: #f8fafc;
            --bom-text-secondary: #cbd5e1;
            --bom-text-muted: #94a3b8;
            --bom-text-dim: #64748b;
            --bom-accent: #38bdf8;
            --bom-accent-subtle: rgba(56, 189, 248, 0.12);
            --bom-success: #4ade80;
            --bom-success-subtle: rgba(74, 222, 128, 0.12);
            --bom-warning: #fbbf24;
            --bom-warning-subtle: rgba(251, 191, 36, 0.12);
            --bom-danger: #f87171;
            --bom-danger-subtle: rgba(248, 113, 113, 0.12);
            --bom-purple: #c084fc;
            --bom-purple-subtle: rgba(192, 132, 252, 0.12);
            --bom-table-head: #172033;
            --bom-row-border: #1e293b;
            --bom-row-hover: rgba(255, 255, 255, 0.03);
            --bom-chip-bg: #1e293b;
        }

        .huenics-bom-modal *,
        .huenics-bom-modal *::before,
        .huenics-bom-modal *::after {
            box-sizing: border-box;
        }

        /* 1. Bento KPI Grid */
        .bom-kpi-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
        }

        @media (max-width: 860px) {
            .bom-kpi-grid {
                grid-template-columns: 1fr;
            }
        }

        .bom-card {
            background-color: var(--bom-card-bg);
            border: 1px solid var(--bom-card-border);
            border-radius: 0.75rem;
            padding: 1.125rem 1.25rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 0.875rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            position: relative;
            overflow: hidden;
        }

        .bom-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
        }

        .bom-card-label {
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--bom-text-muted);
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }

        .bom-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.25rem 0.625rem;
            border-radius: 9999px;
            font-size: 0.6875rem;
            font-weight: 700;
            line-height: 1;
            letter-spacing: 0.02em;
            white-space: nowrap;
        }

        .bom-badge-primary {
            background: var(--bom-accent-subtle);
            color: var(--bom-accent);
            border: 1px solid rgba(56, 189, 248, 0.25);
        }

        .bom-badge-purple {
            background: var(--bom-purple-subtle);
            color: var(--bom-purple);
            border: 1px solid rgba(192, 132, 252, 0.25);
        }

        .bom-badge-success {
            background: var(--bom-success-subtle);
            color: var(--bom-success);
            border: 1px solid rgba(74, 222, 128, 0.25);
        }

        .bom-badge-warning {
            background: var(--bom-warning-subtle);
            color: var(--bom-warning);
            border: 1px solid rgba(251, 191, 36, 0.25);
        }

        .bom-badge-danger {
            background: var(--bom-danger-subtle);
            color: var(--bom-danger);
            border: 1px solid rgba(248, 113, 113, 0.25);
        }

        .bom-badge-neutral {
            background: var(--bom-chip-bg);
            color: var(--bom-text-muted);
            border: 1px solid var(--bom-card-border);
        }

        .bom-badge-dot {
            width: 0.375rem;
            height: 0.375rem;
            border-radius: 9999px;
            background-color: currentColor;
        }

        .bom-card-title {
            font-size: 1.0625rem;
            font-weight: 800;
            line-height: 1.35;
            color: var(--bom-text-main);
            word-break: break-word;
        }

        .bom-card-stat {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 1.75rem;
            font-weight: 800;
            line-height: 1;
            letter-spacing: -0.03em;
            font-variant-numeric: tabular-nums;
            display: flex;
            align-items: baseline;
            gap: 0.375rem;
            color: var(--bom-text-main);
        }

        .bom-card-stat.accent {
            color: var(--bom-accent);
        }

        .bom-card-unit {
            font-family: inherit;
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--bom-text-muted);
        }

        .bom-card-meta {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .bom-pill {
            display: inline-flex;
            align-items: center;
            padding: 0.2rem 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.6875rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-weight: 600;
            background: var(--bom-chip-bg);
            color: var(--bom-text-secondary);
            border: 1px solid var(--bom-card-border);
        }

        .bom-card-footer {
            padding-top: 0.625rem;
            border-top: 1px solid var(--bom-row-border);
            font-size: 0.75rem;
            color: var(--bom-text-muted);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
        }

        /* 2. Feasibility Banner */
        .bom-feasibility-card {
            background: linear-gradient(135deg, rgba(2, 132, 199, 0.05) 0%, rgba(2, 132, 199, 0.12) 100%);
            border: 1px solid rgba(56, 189, 248, 0.25);
            border-radius: 0.75rem;
            padding: 1rem 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .bom-feasibility-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .bom-feasibility-title-wrap {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .bom-feasibility-icon {
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 0.5rem;
            background: var(--bom-accent-subtle);
            color: var(--bom-accent);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .bom-feasibility-title {
            font-size: 0.8125rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--bom-text-main);
        }

        .bom-feasibility-sub {
            font-size: 0.6875rem;
            color: var(--bom-text-muted);
            margin-top: 0.125rem;
        }

        .bom-progress-bar {
            width: 100%;
            height: 0.5rem;
            background: var(--bom-chip-bg);
            border-radius: 9999px;
            overflow: hidden;
            border: 1px solid var(--bom-card-border);
        }

        .bom-progress-fill {
            height: 100%;
            border-radius: 9999px;
            transition: width 0.4s ease;
        }

        /* 3. Section & Tables */
        .bom-section {
            background-color: var(--bom-card-bg);
            border: 1px solid var(--bom-card-border);
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }

        .bom-section-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--bom-row-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            background: var(--bom-table-head);
        }

        .bom-section-title-wrap {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .bom-section-icon {
            width: 2rem;
            height: 2rem;
            border-radius: 0.5rem;
            background: var(--bom-accent-subtle);
            color: var(--bom-accent);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .bom-section-title {
            font-size: 0.875rem;
            font-weight: 800;
            color: var(--bom-text-main);
            letter-spacing: -0.01em;
        }

        .bom-section-desc {
            font-size: 0.6875rem;
            color: var(--bom-text-muted);
            margin-top: 0.125rem;
        }

        .bom-table-wrapper {
            overflow-x: auto;
            width: 100%;
        }

        .bom-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.75rem;
            text-align: left;
        }

        .bom-table th {
            padding: 0.75rem 1rem;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--bom-text-muted);
            background: var(--bom-table-head);
            border-bottom: 1px solid var(--bom-row-border);
            white-space: nowrap;
        }

        .bom-table td {
            padding: 0.875rem 1rem;
            border-bottom: 1px solid var(--bom-row-border);
            color: var(--bom-text-main);
            vertical-align: middle;
        }

        .bom-table tbody tr:last-child td {
            border-bottom: none;
        }

        .bom-table tbody tr:hover {
            background-color: var(--bom-row-hover);
        }

        .bom-table tfoot td {
            background-color: var(--bom-table-head);
            border-top: 2px solid var(--bom-card-border);
            font-weight: 800;
        }

        .bom-part-cell {
            display: flex;
            align-items: center;
            gap: 0.625rem;
        }

        .bom-tree-icon {
            font-family: monospace;
            color: var(--bom-accent);
            font-weight: 800;
            user-select: none;
        }

        .bom-part-icon {
            width: 1.75rem;
            height: 1.75rem;
            border-radius: 0.375rem;
            background: var(--bom-accent-subtle);
            color: var(--bom-accent);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .bom-part-name {
            font-weight: 700;
            color: var(--bom-text-main);
            line-height: 1.3;
        }

        .bom-part-code {
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 0.6875rem;
            color: var(--bom-text-muted);
            margin-top: 0.125rem;
        }

        .bom-empty-box {
            padding: 2.5rem 1.5rem;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
        }

        .bom-empty-icon {
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 9999px;
            background: var(--bom-chip-bg);
            color: var(--bom-text-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--bom-card-border);
        }

        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .font-mono { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-variant-numeric: tabular-nums; }
        .font-bold { font-weight: 700; }
        .font-black { font-weight: 800; }
        .text-muted { color: var(--bom-text-muted); }
        .text-success { color: var(--bom-success); }
        .text-accent { color: var(--bom-accent); }
    </style>

    @php
        $totalBomCost = 0;
        $maxAssemblable = null;
        $bottleneckPart = null;

        if ($parentComponents->isNotEmpty()) {
            foreach ($parentComponents as $comp) {
                $uCost = (float) ($comp->cost_price ?: ($comp->additional_cost ?: ($comp->componentProduct?->base_cost_price ?? 0)));
                $reqQty = (float) ($comp->quantity ?: 1);
                $totalBomCost += ($uCost * $reqQty);

                $childStock = $comp->componentProduct?->inventoryItem?->quantity_on_hand;
                if ($childStock !== null && $reqQty > 0) {
                    $possible = (int) floor($childStock / $reqQty);
                    if ($maxAssemblable === null || $possible < $maxAssemblable) {
                        $maxAssemblable = $possible;
                        $bottleneckPart = $comp->effective_name;
                    }
                }
            }
        }

        $isHybrid = $parentComponents->isNotEmpty() && $usedInParents->isNotEmpty();
        $isAssemblyParent = $parentComponents->isNotEmpty();
        $isSubComponent = $usedInParents->isNotEmpty();
    @endphp

    {{-- 1. Bento KPI Cards Grid --}}
    <div class="bom-kpi-grid">
        {{-- Card 1: Part Profile --}}
        <div class="bom-card">
            <div class="bom-card-header">
                <span class="bom-card-label">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                    Part Identity
                </span>
                @if ($isHybrid)
                    <span class="bom-badge bom-badge-purple">
                        <span class="bom-badge-dot"></span>
                        Hybrid Part & Parent
                    </span>
                @elseif ($isAssemblyParent)
                    <span class="bom-badge bom-badge-primary">
                        <span class="bom-badge-dot"></span>
                        Assembly Parent
                    </span>
                @elseif ($isSubComponent)
                    <span class="bom-badge bom-badge-warning">
                        <span class="bom-badge-dot"></span>
                        Sub-Component
                    </span>
                @else
                    <span class="bom-badge bom-badge-neutral">Standard Item</span>
                @endif
            </div>

            <div>
                <div class="bom-card-title">
                    {{ $record->product?->canonical_name ?? 'N/A' }}
                </div>
                <div class="bom-card-meta" style="margin-top: 0.5rem;">
                    <span class="bom-pill">SKU: {{ $record->product?->sku ?? '—' }}</span>
                    <span class="bom-pill">Code: {{ $record->product?->product_code ?? '—' }}</span>
                </div>
            </div>

            <div class="bom-card-footer">
                <span>Category:</span>
                <strong style="color: var(--bom-text-main);">{{ $record->product?->category ?: 'General' }}</strong>
            </div>
        </div>

        {{-- Card 2: Warehouse Stock --}}
        <div class="bom-card">
            <div class="bom-card-header">
                <span class="bom-card-label">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="7" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                    Warehouse Balance
                </span>
                @if ($record->quantity_on_hand <= 0)
                    <span class="bom-badge bom-badge-danger">
                        <span class="bom-badge-dot"></span>
                        Out of Stock
                    </span>
                @elseif ($record->quantity_on_hand <= ($record->reorder_point ?? 10))
                    <span class="bom-badge bom-badge-warning">
                        <span class="bom-badge-dot"></span>
                        Low Stock
                    </span>
                @else
                    <span class="bom-badge bom-badge-success">
                        <span class="bom-badge-dot"></span>
                        In Stock
                    </span>
                @endif
            </div>

            <div>
                <div class="bom-card-stat">
                    {{ number_format($record->quantity_on_hand, 0) }}
                    <span class="bom-card-unit">{{ $record->unit ?: ($record->product?->unit_default ?: 'pcs') }}</span>
                </div>
            </div>

            <div class="bom-card-footer">
                <span>Reserved: <strong class="font-mono" style="color: var(--bom-text-main);">{{ number_format($record->quantity_reserved, 0) }}</strong></span>
                <span>Safety Point: <strong class="font-mono" style="color: var(--bom-text-main);">{{ number_format($record->reorder_point ?? 0, 0) }}</strong></span>
            </div>
        </div>

        {{-- Card 3: Valuation & Production Role --}}
        <div class="bom-card">
            <div class="bom-card-header">
                <span class="bom-card-label">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="2" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    {{ $isAssemblyParent ? 'Rolled-Up BOM Cost' : 'Unit Cost Valuation' }}
                </span>
                <span class="bom-badge bom-badge-primary">PHP</span>
            </div>

            <div>
                <div class="bom-card-stat accent">
                    ₱{{ number_format($isAssemblyParent ? $totalBomCost : (float) ($record->cost_price ?: ($record->product?->base_cost_price ?? 0)), 2) }}
                </div>
            </div>

            <div class="bom-card-footer">
                @if ($isAssemblyParent && $maxAssemblable !== null)
                    <span>Max Assembly Capacity:</span>
                    <strong class="font-mono" style="color: var(--bom-text-main);">{{ $maxAssemblable }} units</strong>
                @elseif ($isSubComponent)
                    <span>Reverse Usage:</span>
                    <strong style="color: var(--bom-text-main);">Used in {{ $usedInParents->count() }} assembly</strong>
                @else
                    <span>Inventory Status:</span>
                    <strong style="color: var(--bom-text-main);">Active SKU</strong>
                @endif
            </div>
        </div>
    </div>

    {{-- 2. Assembly Feasibility Banner (For Assembly Parents) --}}
    @if ($isAssemblyParent)
        <div class="bom-feasibility-card">
            <div class="bom-feasibility-header">
                <div class="bom-feasibility-title-wrap">
                    <div class="bom-feasibility-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="m9 12 2 2 4-4"/></svg>
                    </div>
                    <div>
                        <div class="bom-feasibility-title">Assembly Feasibility & Production Readiness</div>
                        <div class="bom-feasibility-sub">Evaluated against live warehouse component stock levels</div>
                    </div>
                </div>

                <div class="font-mono font-black" style="font-size: 0.9375rem; color: var(--bom-accent);">
                    {{ $maxAssemblable ?? 0 }} Finished Units Buildable
                </div>
            </div>

            @php
                $targetUnits = max(1, (int) $record->quantity_on_hand);
                $capRatio = $maxAssemblable !== null ? min(100, round(($maxAssemblable / $targetUnits) * 100)) : 0;
                $barBg = ($maxAssemblable ?? 0) > 10 ? 'var(--bom-success)' : (($maxAssemblable ?? 0) > 0 ? 'var(--bom-warning)' : 'var(--bom-danger)');
            @endphp
            <div class="bom-progress-bar">
                <div class="bom-progress-fill" style="width: {{ max(4, $capRatio) }}%; background-color: {{ $barBg }};"></div>
            </div>

            @if (($maxAssemblable ?? 0) === 0 && $bottleneckPart)
                <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.75rem; font-weight: 700; color: var(--bom-danger);">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                    <span>Critical Bottleneck: Insufficient stock for component "{{ $bottleneckPart }}".</span>
                </div>
            @endif
        </div>
    @endif

    {{-- 3. Direct Bill of Materials (Sub-Components Breakdown) --}}
    @if ($parentComponents->isNotEmpty())
        <div class="bom-section">
            <div class="bom-section-header">
                <div class="bom-section-title-wrap">
                    <div class="bom-section-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                    </div>
                    <div>
                        <div class="bom-section-title">Sub-Components Breakdown (Bill of Materials)</div>
                        <div class="bom-section-desc">Raw parts and child materials required to manufacture 1 unit of this finished product</div>
                    </div>
                </div>
                <span class="bom-badge bom-badge-primary">
                    {{ $parentComponents->count() }} Attached Part{{ $parentComponents->count() > 1 ? 's' : '' }}
                </span>
            </div>

            <div class="bom-table-wrapper">
                <table class="bom-table">
                    <thead>
                        <tr>
                            <th>Sub-Component Part</th>
                            <th>Category</th>
                            <th class="text-center">Qty / Assembly</th>
                            <th class="text-center">Warehouse Stock</th>
                            <th class="text-end">Unit Cost</th>
                            <th class="text-end">Line Total</th>
                            <th class="text-center">Cost Share</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($parentComponents as $index => $comp)
                            @php
                                $unitCost = (float) ($comp->cost_price ?: ($comp->additional_cost ?: ($comp->componentProduct?->base_cost_price ?? 0)));
                                $subtotal = $unitCost * (float) ($comp->quantity ?: 1);
                                $childInv = $comp->componentProduct?->inventoryItem;
                                $childStock = $childInv?->quantity_on_hand;
                                $costShare = $totalBomCost > 0 ? round(($subtotal / $totalBomCost) * 100, 1) : 0;
                                $isLast = $index === ($parentComponents->count() - 1);
                            @endphp
                            <tr>
                                <td>
                                    <div class="bom-part-cell">
                                        <span class="bom-tree-icon">{{ $isLast ? '└──' : '├──' }}</span>
                                        <div class="bom-part-icon">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/></svg>
                                        </div>
                                        <div>
                                            <div class="bom-part-name">{{ $comp->effective_name }}</div>
                                            <div class="bom-part-code">Code: {{ $comp->effective_code ?: '—' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="bom-pill">{{ $comp->effective_category }}</span>
                                </td>
                                <td class="text-center font-mono font-bold">
                                    {{ (float) $comp->quantity }} <span class="text-muted" style="font-size: 0.6875rem;">{{ $comp->effective_unit }}</span>
                                </td>
                                <td class="text-center">
                                    @if ($childInv)
                                        @if ($childStock >= $comp->quantity)
                                            <span class="bom-badge bom-badge-success">
                                                <span class="bom-badge-dot"></span>
                                                {{ number_format($childStock, 0) }} {{ $childInv->unit ?: 'pcs' }}
                                            </span>
                                        @elseif ($childStock > 0)
                                            <span class="bom-badge bom-badge-warning">
                                                <span class="bom-badge-dot"></span>
                                                {{ number_format($childStock, 0) }} {{ $childInv->unit ?: 'pcs' }} (Low)
                                            </span>
                                        @else
                                            <span class="bom-badge bom-badge-danger">
                                                <span class="bom-badge-dot"></span>
                                                0 {{ $childInv->unit ?: 'pcs' }} (Out)
                                            </span>
                                        @endif
                                    @else
                                        <span class="bom-badge bom-badge-neutral">Custom / Untracked</span>
                                    @endif
                                </td>
                                <td class="text-end font-mono text-muted">
                                    ₱{{ number_format($unitCost, 2) }}
                                </td>
                                <td class="text-end font-mono font-bold">
                                    ₱{{ number_format($subtotal, 2) }}
                                </td>
                                <td class="text-center font-mono font-bold">
                                    <span class="bom-pill">{{ $costShare }}%</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-end" style="font-size: 0.75rem; letter-spacing: 0.05em; text-transform: uppercase;">
                                Total Rolled-Up BOM Unit Cost:
                            </td>
                            <td class="text-end font-mono font-black" style="font-size: 0.875rem; color: var(--bom-accent);">
                                ₱{{ number_format($totalBomCost, 2) }}
                            </td>
                            <td class="text-center font-mono font-bold text-muted">
                                100.0%
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif

    {{-- 4. Reverse BOM (Parent Assemblies Consuming This Part) --}}
    @if ($usedInParents->isNotEmpty())
        <div class="bom-section">
            <div class="bom-section-header">
                <div class="bom-section-title-wrap">
                    <div class="bom-section-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 3h5v5"/><path d="M8 3H3v5"/><path d="M12 22v-8.3a4 4 0 0 0-1.172-2.872L3 3"/><path d="m15 9 6-6"/></svg>
                    </div>
                    <div>
                        <div class="bom-section-title">Parent Assemblies Consuming This Part (Reverse BOM)</div>
                        <div class="bom-section-desc">Higher-level catalog products and assemblies that incorporate this component into their manufacturing BOM</div>
                    </div>
                </div>
                <span class="bom-badge bom-badge-warning">
                    {{ $usedInParents->count() }} Parent Product{{ $usedInParents->count() > 1 ? 's' : '' }}
                </span>
            </div>

            <div class="bom-table-wrapper">
                <table class="bom-table">
                    <thead>
                        <tr>
                            <th>Parent Finished Assembly</th>
                            <th>SKU / Part Code</th>
                            <th>Category</th>
                            <th class="text-center">Parent Warehouse Stock</th>
                            <th class="text-center">Consumption per Unit</th>
                            <th class="text-end">Supported Builds from Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($usedInParents as $parent)
                            @foreach ($parent->components as $c)
                                @php
                                    $pStock = $parent->inventoryItem?->quantity_on_hand ?? 0;
                                    $cQty = (float) ($c->quantity ?: 1);
                                    $currentOnHand = (float) $record->quantity_on_hand;
                                    $supportedBuilds = $cQty > 0 ? (int) floor($currentOnHand / $cQty) : 0;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="bom-part-cell">
                                            <div class="bom-part-icon">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17 17 7"/><path d="M7 7h10v10"/></svg>
                                            </div>
                                            <div>
                                                <div class="bom-part-name">{{ $parent->canonical_name }}</div>
                                                <div class="bom-part-code">SKU: {{ $parent->sku ?: '—' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="bom-pill">{{ $parent->product_code ?: ($parent->sku ?: '—') }}</span>
                                    </td>
                                    <td>
                                        <span class="bom-pill">{{ $parent->category ?: 'General' }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if ($pStock > 0)
                                            <span class="bom-badge bom-badge-success">
                                                <span class="bom-badge-dot"></span>
                                                {{ number_format($pStock, 0) }} pcs
                                            </span>
                                        @else
                                            <span class="bom-badge bom-badge-danger">
                                                <span class="bom-badge-dot"></span>
                                                0 pcs
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center font-mono font-bold">
                                        {{ $cQty }} <span class="text-muted" style="font-size: 0.6875rem;">{{ $c->effective_unit ?: 'pcs' }}</span>
                                    </td>
                                    <td class="text-end font-mono">
                                        @if ($supportedBuilds > 0)
                                            <strong class="text-success" style="font-size: 0.8125rem;">{{ number_format($supportedBuilds, 0) }}</strong>
                                            <span class="text-muted" style="font-size: 0.6875rem;">builds</span>
                                        @else
                                            <span class="bom-badge bom-badge-danger">0 builds</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- 5. Standalone Item State --}}
    @if ($parentComponents->isEmpty() && $usedInParents->isEmpty())
        <div class="bom-section">
            <div class="bom-empty-box">
                <div class="bom-empty-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                </div>
                <div style="font-size: 0.9375rem; font-weight: 800; color: var(--bom-text-main);">
                    Standalone Catalog Item
                </div>
                <div style="font-size: 0.75rem; color: var(--bom-text-muted); max-width: 24rem; line-height: 1.4;">
                    This product is not assembled from sub-components, nor is it currently incorporated as a child part in any finished assemblies.
                </div>
            </div>
        </div>
    @endif
</div>
