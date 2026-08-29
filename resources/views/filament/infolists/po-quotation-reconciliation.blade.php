@php
    $reconciliation = $reconciliation ?? ($getRecord ? $getRecord()->getReconciliationReport() : null);
@endphp

@if ($reconciliation && $reconciliation['has_linked_quotation'])
    <div style="font-family: inherit; width: 100%; display: flex; flex-direction: column; gap: 1.25rem;">
        {{-- 1. Status Banner --}}
        @if ($reconciliation['has_discrepancies'])
            <div style="padding: 1rem 1.25rem; border-radius: 0.5rem; background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.35); display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
                <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
                    <div style="background: rgba(245, 158, 11, 0.2); padding: 0.375rem; border-radius: 0.375rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <x-heroicon-m-exclamation-triangle style="width: 1.5rem; height: 1.5rem;" class="text-amber-600 dark:text-amber-400" />
                    </div>
                    <div>
                        <h4 style="margin: 0; font-size: 0.95rem; font-weight: 700;" class="text-amber-900 dark:text-amber-200">
                            Line Item Discrepancies Detected ({{ $reconciliation['discrepancy_count'] }} Found)
                        </h4>
                        <p style="margin: 0.25rem 0 0 0; font-size: 0.8125rem;" class="text-amber-800 dark:text-amber-300">
                            Differences found between this Purchase Order and linked Quotation <strong>#{{ $reconciliation['quotation_number'] }}</strong>. Please review line quantities, pricing, and item specifications below before approval.
                        </p>
                    </div>
                </div>
                {{-- Quick Summary Badges --}}
                <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                    @if ($reconciliation['qty_mismatches_count'] > 0)
                        <span style="font-size: 0.75rem; font-weight: 600; padding: 0.25rem 0.625rem; border-radius: 9999px; background: rgba(245, 158, 11, 0.2);" class="text-amber-800 dark:text-amber-200 border border-amber-400/30">
                            {{ $reconciliation['qty_mismatches_count'] }} Qty Mismatch
                        </span>
                    @endif
                    @if ($reconciliation['price_mismatches_count'] > 0)
                        <span style="font-size: 0.75rem; font-weight: 600; padding: 0.25rem 0.625rem; border-radius: 9999px; background: rgba(244, 63, 94, 0.15);" class="text-rose-800 dark:text-rose-200 border border-rose-400/30">
                            {{ $reconciliation['price_mismatches_count'] }} Price Mismatch
                        </span>
                    @endif
                    @if ($reconciliation['missing_in_quotation_count'] > 0)
                        <span style="font-size: 0.75rem; font-weight: 600; padding: 0.25rem 0.625rem; border-radius: 9999px; background: rgba(239, 68, 68, 0.15);" class="text-red-800 dark:text-red-200 border border-red-400/30">
                            {{ $reconciliation['missing_in_quotation_count'] }} Not in Quotation
                        </span>
                    @endif
                    @if ($reconciliation['missing_in_po_count'] > 0)
                        <span style="font-size: 0.75rem; font-weight: 600; padding: 0.25rem 0.625rem; border-radius: 9999px; background: rgba(107, 114, 128, 0.15);" class="text-gray-800 dark:text-gray-200 border border-gray-400/30">
                            {{ $reconciliation['missing_in_po_count'] }} Quoted Not in PO
                        </span>
                    @endif
                </div>
            </div>
        @else
            <div style="padding: 0.875rem 1.25rem; border-radius: 0.5rem; background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.35); display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <div style="background: rgba(34, 197, 94, 0.2); padding: 0.375rem; border-radius: 0.375rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <x-heroicon-m-check-badge style="width: 1.5rem; height: 1.5rem;" class="text-emerald-600 dark:text-emerald-400" />
                    </div>
                    <div>
                        <h4 style="margin: 0; font-size: 0.95rem; font-weight: 700;" class="text-emerald-900 dark:text-emerald-200">
                            100% Line Item & Pricing Match Verified
                        </h4>
                        <p style="margin: 0.25rem 0 0 0; font-size: 0.8125rem;" class="text-emerald-800 dark:text-emerald-300">
                            All {{ $reconciliation['exact_matches_count'] }} line items in this PO perfectly match Quotation <strong>#{{ $reconciliation['quotation_number'] }}</strong>.
                        </p>
                    </div>
                </div>
                <span style="font-size: 0.75rem; font-weight: 700; padding: 0.25rem 0.75rem; border-radius: 9999px; background: rgba(34, 197, 94, 0.2);" class="text-emerald-800 dark:text-emerald-200">
                    Reconciliation Clean
                </span>
            </div>
        @endif

        {{-- 2. Financial Variance Summary Cards --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.75rem;">
            <div style="padding: 0.875rem 1rem; border-radius: 0.5rem; border: 1px solid rgba(156, 163, 175, 0.25); background: rgba(249, 250, 251, 0.6);" class="dark:bg-gray-800/60 dark:border-gray-700">
                <span style="font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;" class="text-gray-500 dark:text-gray-400">PO Order Amount</span>
                <div style="font-size: 1.15rem; font-weight: 700; margin-top: 0.25rem;" class="text-gray-900 dark:text-gray-100">
                    ₱{{ number_format($reconciliation['totals']['po_total'], 2) }}
                </div>
            </div>

            <div style="padding: 0.875rem 1rem; border-radius: 0.5rem; border: 1px solid rgba(156, 163, 175, 0.25); background: rgba(249, 250, 251, 0.6);" class="dark:bg-gray-800/60 dark:border-gray-700">
                <span style="font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;" class="text-gray-500 dark:text-gray-400">Quotation Total Amount</span>
                <div style="font-size: 1.15rem; font-weight: 700; margin-top: 0.25rem;" class="text-gray-900 dark:text-gray-100">
                    ₱{{ number_format($reconciliation['totals']['quotation_total'], 2) }}
                </div>
            </div>

            <div style="padding: 0.875rem 1rem; border-radius: 0.5rem; border: 1px solid rgba(156, 163, 175, 0.25); background: rgba(249, 250, 251, 0.6);" class="dark:bg-gray-800/60 dark:border-gray-700">
                <span style="font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;" class="text-gray-500 dark:text-gray-400">Financial Variance</span>
                @php $variance = $reconciliation['totals']['variance']; @endphp
                <div style="font-size: 1.15rem; font-weight: 700; margin-top: 0.25rem;" class="{{ abs($variance) < 0.01 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                    {{ $variance > 0 ? '+' : '' }}₱{{ number_format($variance, 2) }}
                    @if (abs($variance) < 0.01)
                        <span style="font-size: 0.75rem; font-weight: 500;" class="text-emerald-600 dark:text-emerald-400">(Balanced)</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- 3. Side-by-Side Comparison Table --}}
        <div style="overflow-x: auto; border-radius: 0.5rem; border: 1px solid rgba(156, 163, 175, 0.25);" class="dark:border-gray-700">
            <table style="width: 100%; border-collapse: collapse; font-size: 0.8125rem; text-align: left;">
                <thead>
                    <tr style="border-bottom: 2px solid rgba(156, 163, 175, 0.25);" class="bg-gray-100 dark:bg-gray-800/80 text-gray-700 dark:text-gray-300">
                        <th style="padding: 0.75rem 0.875rem; font-weight: 700; width: 40px;">#</th>
                        <th style="padding: 0.75rem 0.875rem; font-weight: 700; min-width: 200px;">Product / Description</th>
                        <th style="padding: 0.75rem 0.875rem; font-weight: 700; text-align: right; min-width: 140px;">PO Qty vs Quoted</th>
                        <th style="padding: 0.75rem 0.875rem; font-weight: 700; text-align: right; min-width: 150px;">PO Price vs Quoted</th>
                        <th style="padding: 0.75rem 0.875rem; font-weight: 700; text-align: right; min-width: 130px;">PO Total vs Quoted</th>
                        <th style="padding: 0.75rem 0.875rem; font-weight: 700; min-width: 150px;">Status</th>
                    </tr>
                </thead>
                <tbody style="divide-y: 1px solid rgba(156, 163, 175, 0.15);">
                    @foreach ($reconciliation['rows'] as $index => $row)
                        @php
                            $isMatch = $row['status'] === 'exact_match';
                            $rowBg = match ($row['status']) {
                                'exact_match' => 'rgba(34, 197, 94, 0.03)',
                                'qty_mismatch' => 'rgba(245, 158, 11, 0.06)',
                                'price_mismatch' => 'rgba(244, 63, 94, 0.06)',
                                'both_mismatch' => 'rgba(239, 68, 68, 0.08)',
                                'missing_in_quotation' => 'rgba(239, 68, 68, 0.08)',
                                'missing_in_po' => 'rgba(107, 114, 128, 0.06)',
                                default => 'transparent',
                            };
                            $statusColor = match ($row['status']) {
                                'exact_match' => 'background: rgba(34, 197, 94, 0.15); color: #166534;',
                                'qty_mismatch' => 'background: rgba(245, 158, 11, 0.2); color: #92400e;',
                                'price_mismatch' => 'background: rgba(244, 63, 94, 0.15); color: #9f1239;',
                                'both_mismatch' => 'background: rgba(239, 68, 68, 0.2); color: #991b1b;',
                                'missing_in_quotation' => 'background: rgba(239, 68, 68, 0.15); color: #991b1b;',
                                'missing_in_po' => 'background: rgba(107, 114, 128, 0.2); color: #374151;',
                                default => 'background: rgba(107, 114, 128, 0.1); color: #4b5563;',
                            };
                        @endphp
                        <tr style="background: {{ $rowBg }}; border-bottom: 1px solid rgba(156, 163, 175, 0.15);" class="hover:bg-gray-50 dark:hover:bg-gray-800/40">
                            <td style="padding: 0.75rem 0.875rem; vertical-align: top; font-weight: 600;" class="text-gray-500 dark:text-gray-400">
                                {{ $index + 1 }}
                            </td>
                            <td style="padding: 0.75rem 0.875rem; vertical-align: top;">
                                @if (!empty($row['item_code']))
                                    <span style="font-family: monospace; font-size: 0.7rem; padding: 0.125rem 0.375rem; border-radius: 0.25rem; background: rgba(156, 163, 175, 0.2);" class="text-gray-700 dark:text-gray-300">
                                        {{ $row['item_code'] }}
                                    </span>
                                @endif
                                <div style="font-weight: 600; margin-top: 0.25rem;" class="text-gray-900 dark:text-gray-100">
                                    {{ $row['description'] }}
                                </div>
                                @if (!empty($row['discrepancy_notes']))
                                    <div style="margin-top: 0.375rem; display: flex; flex-direction: column; gap: 0.25rem;">
                                        @foreach ($row['discrepancy_notes'] as $note)
                                            <span style="font-size: 0.75rem; font-weight: 500; display: inline-flex; align-items: center; gap: 0.25rem;" class="text-rose-700 dark:text-rose-300">
                                                <x-heroicon-m-arrow-right style="width: 0.875rem; height: 0.875rem; flex-shrink: 0;" />
                                                {{ $note }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            {{-- Quantity Comparison --}}
                            <td style="padding: 0.75rem 0.875rem; vertical-align: top; text-align: right;">
                                @if ($row['po_qty'] !== null)
                                    <div style="font-weight: 700;" class="{{ $row['qty_match'] ? 'text-gray-900 dark:text-gray-100' : 'text-amber-700 dark:text-amber-300' }}">
                                        PO: {{ number_format($row['po_qty']) }} {{ $row['unit'] }}
                                    </div>
                                @else
                                    <div style="font-size: 0.75rem;" class="text-gray-400 italic">Not in PO</div>
                                @endif

                                @if ($row['quotation_qty'] !== null)
                                    <div style="font-size: 0.75rem; margin-top: 0.125rem;" class="text-gray-500 dark:text-gray-400">
                                        Qtn: {{ number_format($row['quotation_qty']) }} {{ $row['unit'] }}
                                    </div>
                                @else
                                    <div style="font-size: 0.75rem; margin-top: 0.125rem;" class="text-rose-500 italic">Unquoted</div>
                                @endif

                                @if (!$row['qty_match'] && $row['po_qty'] !== null && $row['quotation_qty'] !== null)
                                    <span style="display: inline-block; margin-top: 0.25rem; font-size: 0.7rem; font-weight: 700; padding: 0.125rem 0.375rem; border-radius: 0.25rem; background: rgba(245, 158, 11, 0.2);" class="text-amber-800 dark:text-amber-200">
                                        Δ {{ $row['qty_diff'] > 0 ? '+' : '' }}{{ number_format($row['qty_diff']) }}
                                    </span>
                                @endif
                            </td>
                            {{-- Unit Price Comparison --}}
                            <td style="padding: 0.75rem 0.875rem; vertical-align: top; text-align: right;">
                                @if ($row['po_unit_price'] !== null)
                                    <div style="font-weight: 700;" class="{{ $row['price_match'] ? 'text-gray-900 dark:text-gray-100' : 'text-rose-700 dark:text-rose-300' }}">
                                        PO: ₱{{ number_format($row['po_unit_price'], 2) }}
                                    </div>
                                @else
                                    <div style="font-size: 0.75rem;" class="text-gray-400 italic">—</div>
                                @endif

                                @if ($row['effective_quotation_price'] !== null)
                                    <div style="font-size: 0.75rem; margin-top: 0.125rem;" class="text-gray-500 dark:text-gray-400">
                                        Qtn: ₱{{ number_format($row['effective_quotation_price'], 2) }}
                                    </div>
                                @else
                                    <div style="font-size: 0.75rem; margin-top: 0.125rem;" class="text-rose-500 italic">Unquoted</div>
                                @endif

                                @if (!$row['price_match'] && $row['po_unit_price'] !== null && $row['effective_quotation_price'] !== null)
                                    <span style="display: inline-block; margin-top: 0.25rem; font-size: 0.7rem; font-weight: 700; padding: 0.125rem 0.375rem; border-radius: 0.25rem; background: rgba(244, 63, 94, 0.2);" class="text-rose-800 dark:text-rose-200">
                                        Δ {{ $row['price_diff'] > 0 ? '+' : '' }}₱{{ number_format($row['price_diff'], 2) }}
                                    </span>
                                @endif
                            </td>
                            {{-- Total Line Comparison --}}
                            <td style="padding: 0.75rem 0.875rem; vertical-align: top; text-align: right;">
                                <div style="font-weight: 700;" class="{{ $row['total_match'] ? 'text-gray-900 dark:text-gray-100' : 'text-amber-800 dark:text-amber-200' }}">
                                    ₱{{ number_format($row['po_total'], 2) }}
                                </div>
                                <div style="font-size: 0.75rem; margin-top: 0.125rem;" class="text-gray-500 dark:text-gray-400">
                                    ₱{{ number_format($row['quotation_total'], 2) }}
                                </div>
                            </td>
                            {{-- Status Badge --}}
                            <td style="padding: 0.75rem 0.875rem; vertical-align: top;">
                                <span style="display: inline-flex; align-items: center; gap: 0.25rem; font-size: 0.75rem; font-weight: 700; padding: 0.25rem 0.625rem; border-radius: 9999px; {{ $statusColor }}">
                                    @if ($row['status'] === 'exact_match')
                                        <x-heroicon-m-check style="width: 0.875rem; height: 0.875rem;" />
                                    @else
                                        <x-heroicon-m-exclamation-circle style="width: 0.875rem; height: 0.875rem;" />
                                    @endif
                                    {{ $row['status_label'] }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@else
    <div style="padding: 1rem 1.25rem; border-radius: 0.5rem; background: rgba(107, 114, 128, 0.08); border: 1px dashed rgba(107, 114, 128, 0.3); display: flex; align-items: center; gap: 0.75rem;" class="text-gray-600 dark:text-gray-400 text-xs">
        <x-heroicon-m-link style="width: 1.25rem; height: 1.25rem; flex-shrink: 0;" />
        <span>This purchase order has not been linked to an approved quotation yet. Click <strong>"Link to Approved Quotation"</strong> in the actions bar above to pair and cross-verify line items.</span>
    </div>
@endif
