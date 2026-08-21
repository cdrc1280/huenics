<x-filament-panels::page>
    @if (!$selectedDocumentId)
        {{-- TABLE QUEUE VIEW --}}
        <div>
            {{ $this->table }}
        </div>
    @else
        {{-- ACTIVE VERIFICATION WORKSPACE --}}
        <div class="space-y-6">

            {{-- Top Navigation & Document Header Bar --}}
            {{-- Top Navigation & Document Header Bar --}}
            <div class="fi-section rounded-xl bg-white ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 shadow-xs" style="margin-bottom: 1.5rem; overflow: hidden;">
                {{-- Tier 1: Main Header (Back, Document Title, Status Badges & Document Navigation) --}}
                <div class="border-b border-gray-100 dark:border-white/10" style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1rem;">
                    {{-- Left: Back & Document Identity --}}
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <x-filament::button type="button" wire:click="closeWorkspace" color="gray"
                            icon="heroicon-m-arrow-left" size="sm" outlined>
                            Back to {{ $currentDocument->document_type === 'vendors_agreement' ? 'Quotations' : 'Purchase Orders' }}
                        </x-filament::button>

                        <div class="h-5 w-px bg-gray-200 dark:bg-gray-700"></div>

                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span class="font-bold text-base tracking-tight text-gray-900 dark:text-white">
                                {{ $currentDocument->document_number ?: 'Document #' . $currentDocument->id }}
                            </span>

                            <x-filament::badge :color="match ($currentDocument->document_type) {
                                'purchase_order' => 'primary',
                                'order_slip' => 'info',
                                'vendors_agreement' => 'warning',
                                default => 'gray',
                            }" size="sm">
                                {{ strtoupper(str_replace('_', ' ', $currentDocument->document_type)) }}
                            </x-filament::badge>

                            @if ($currentDocument->hasMismatches())
                                <x-filament::badge color="danger" icon="heroicon-m-exclamation-triangle" size="sm">
                                    Discrepancies Flagged
                                </x-filament::badge>
                            @else
                                <x-filament::badge color="success" icon="heroicon-m-check-circle" size="sm">
                                    Clean Math
                                </x-filament::badge>
                            @endif

                            @if ($currentDocument->document_type === 'purchase_order')
                                <x-filament::badge color="info" icon="heroicon-m-lock-closed" size="sm">
                                    Official PO (Read Only)
                                </x-filament::badge>
                            @elseif ($this->isReadOnly)
                                <x-filament::badge color="info" icon="heroicon-m-eye" size="sm">
                                    Viewing Mode
                                </x-filament::badge>
                            @endif
                        </div>
                    </div>

                    {{-- Right: Previous / Next Document Switcher --}}
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <x-filament::button type="button" wire:click="loadPreviousDocument" color="gray"
                            icon="heroicon-m-chevron-left" size="sm" title="Previous Document" outlined>
                            Previous
                        </x-filament::button>
                        <x-filament::button type="button" wire:click="loadNextDocument" color="gray"
                            icon="heroicon-m-chevron-right" icon-position="after" size="sm" title="Next Document" outlined>
                            Next
                        </x-filament::button>
                    </div>
                </div>

                {{-- Tier 2: Workspace Tools Toolbar --}}
                @if (!$this->isReadOnly)
                    <div class="bg-gray-50/75 dark:bg-white/5" style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 1rem;">
                        {{-- Left Tools: Re-Extract, Undo, Redo, Reset --}}
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <x-filament::button type="button" wire:click="reExtractCurrentDocument" color="primary"
                                icon="heroicon-m-sparkles" size="xs">
                                Re-Extract (AI/OCR)
                            </x-filament::button>

                            <div class="h-4 w-px bg-gray-200 dark:bg-gray-700"></div>

                            <x-filament::button type="button" wire:click="undo" color="gray"
                                icon="heroicon-m-arrow-uturn-left" size="xs" :disabled="empty($undoStack)"
                                title="Undo edit (Ctrl+Z)" outlined>
                                Undo
                                @if (count($undoStack) > 0)
                                    <span class="ml-1 inline-flex items-center justify-center rounded-full bg-primary-600 px-1.5 py-0.5 text-[9px] font-bold text-white leading-none">
                                        {{ count($undoStack) }}
                                    </span>
                                @endif
                            </x-filament::button>

                            <x-filament::button type="button" wire:click="redo" color="gray"
                                icon="heroicon-m-arrow-uturn-right" size="xs" :disabled="empty($redoStack)"
                                title="Redo edit (Ctrl+Y)" outlined>
                                Redo
                                @if (count($redoStack) > 0)
                                    <span class="ml-1 inline-flex items-center justify-center rounded-full bg-primary-600 px-1.5 py-0.5 text-[9px] font-bold text-white leading-none">
                                        {{ count($redoStack) }}
                                    </span>
                                @endif
                            </x-filament::button>

                            <x-filament::button type="button" wire:click="revertToOriginal" color="danger"
                                icon="heroicon-m-arrow-path" size="xs" outlined
                                title="Reset all edits to original extracted data">
                                Reset
                            </x-filament::button>
                        </div>

                        {{-- Right Tools: Keyboard Shortcut hints --}}
                        <div class="text-gray-500 dark:text-gray-400" style="display: flex; align-items: center; gap: 0.75rem; font-size: 0.75rem;">
                            <span style="display: inline-flex; align-items: center; gap: 0.25rem;">
                                <kbd class="rounded border border-gray-300 bg-white px-1.5 py-0.5 text-[10px] font-semibold text-gray-700 shadow-2xs dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">Ctrl</kbd>+<kbd class="rounded border border-gray-300 bg-white px-1.5 py-0.5 text-[10px] font-semibold text-gray-700 shadow-2xs dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">Z</kbd> Undo
                            </span>
                            <span style="display: inline-flex; align-items: center; gap: 0.25rem;">
                                <kbd class="rounded border border-gray-300 bg-white px-1.5 py-0.5 text-[10px] font-semibold text-gray-700 shadow-2xs dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">Ctrl</kbd>+<kbd class="rounded border border-gray-300 bg-white px-1.5 py-0.5 text-[10px] font-semibold text-gray-700 shadow-2xs dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">Y</kbd> Redo
                            </span>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Split-Screen Layout: PDF Left (5 cols) / Reconciliation Right (7 cols) --}}
            <div x-data x-on:keydown.window.ctrl.z.prevent="$wire.undo()"
                x-on:keydown.window.ctrl.y.prevent="$wire.redo()"
                style="display: grid; grid-template-columns: repeat(12, minmax(0, 1fr)); gap: 1.5rem; align-items: start; margin-top: 1.5rem;">

                {{-- LEFT COLUMN: Embedded PDF & Real-time Live Highlight Preview --}}
                <div style="grid-column: span 5 / span 5; display: flex; flex-direction: column; gap: 1rem;">
                    <x-filament::section compact>
                        <x-slot name="heading">
                            <div style="display: flex; align-items: center; gap: 0.5rem; overflow: hidden;"
                                title="{{ $currentDocument->original_filename }}">
                                <x-filament::icon icon="heroicon-o-document-text"
                                    class="h-4 w-4 shrink-0 text-gray-400" />
                                <span
                                    style="font-size: 0.8125rem; font-weight: 600; text-overflow: ellipsis; white-space: nowrap; overflow: hidden; max-width: 200px;">
                                    {{ $currentDocument->original_filename }}
                                </span>
                            </div>
                        </x-slot>

                        <x-slot name="headerEnd">
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                {{-- VIEW MODE SWITCHER TOGGLE BUTTONS --}}
                                <div
                                    style="display: inline-flex; border-radius: 0.5rem; background: rgba(156, 163, 175, 0.15); padding: 0.2rem;">
                                    <button type="button" wire:click="setPreviewMode('live')"
                                        class="{{ $previewMode === 'live' ? 'bg-primary-600 text-white shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900' }} rounded-md px-2.5 py-1 text-xs font-semibold transition">
                                        Live Preview
                                    </button>
                                    <button type="button" wire:click="setPreviewMode('pdf')"
                                        class="{{ $previewMode === 'pdf' ? 'bg-primary-600 text-white shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900' }} rounded-md px-2.5 py-1 text-xs font-semibold transition">
                                        Original PDF
                                    </button>
                                </div>

                                <a href="{{ route('documents.preview', $currentDocument) }}" target="_blank"
                                    class="text-primary-600 dark:text-primary-400 hover:underline"
                                    style="display: inline-flex; align-items: center; gap: 0.25rem; font-size: 0.75rem; font-weight: 600;">
                                    <x-filament::icon icon="heroicon-o-arrow-top-right-on-square" class="h-3.5 w-3.5" />
                                    <span>New Tab</span>
                                </a>
                            </div>
                        </x-slot>

                        {{-- CONDITIONAL PREVIEW RENDERING --}}
                        @if ($previewMode === 'pdf')
                            {{-- ORIGINAL UPLOADED PDF IFRAME --}}
                            <div
                                style="width: 100%; height: 760px; border-radius: 0.5rem; overflow: hidden; background: #1f2937; margin-top: 0.5rem;">
                                <iframe src="{{ route('documents.preview', $currentDocument) }}"
                                    style="width: 100%; height: 100%; border: none;"
                                    title="Original PDF Preview"></iframe>
                            </div>
                        @else
                            {{-- REAL-TIME EDITED PDF BINARY IFRAME WITH REAL-TIME TEXT HIGHLIGHTS --}}
                            <div
                                style="width: 100%; height: 760px; border-radius: 0.5rem; overflow: hidden; background: #1f2937; margin-top: 0.5rem;">
                                <iframe src="{{ $this->getLivePdfUrl() }}" type="application/pdf"
                                    style="width: 100%; height: 100%; border: none;"
                                    title="Live Real-time Edited PDF Preview"></iframe>
                            </div>
                        @endif

                        <x-slot name="footer">
                            <div
                                style="display: flex; justify-content: space-between; align-items: center; font-size: 0.75rem; color: #6b7280;">
                                <span>SHA-256: <code
                                        style="font-size: 0.625rem; font-family: monospace;">{{ substr($currentDocument->file_hash ?? 'N/A', 0, 16) }}...</code></span>
                                <span>Mode: <strong>{{ strtoupper($previewMode) }} PREVIEW</strong></span>
                            </div>
                        </x-slot>
                    </x-filament::section>
                </div>

                {{-- RIGHT COLUMN: Document Information --}}
                <div style="grid-column: span 7 / span 7; display: flex; flex-direction: column; gap: 1.5rem;">

                    {{-- ANOMALY CALLOUT BOX --}}
                    @if ($currentDocument->hasMismatches())
                        <div
                            style="padding: 1rem 1.25rem; border-radius: 0.75rem; background-color: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.35);">
                            <div
                                style="display: flex; align-items: center; gap: 0.5rem; color: #dc2626; font-weight: 700; font-size: 0.875rem; margin-bottom: 0.5rem;">
                                <x-filament::icon icon="heroicon-s-exclamation-triangle"
                                    class="h-5 w-5 shrink-0 text-red-600" />
                                <span>Verification Anomalies Detected</span>
                            </div>
                            <ul
                                style="font-size: 0.75rem; color: #dc2626; list-style-type: disc; padding-left: 1.25rem; line-height: 1.5rem;">
                                @if ($currentDocument->totals?->vat_mismatch)
                                    <li><strong>VAT Discrepancy:</strong> Printed VAT
                                        (₱{{ number_format($currentDocument->totals->printed_vat, 2) }}) deviates from
                                        computed 12% standard
                                        (₱{{ number_format($currentDocument->totals->computed_vat, 2) }}).</li>
                                @endif
                                @if ($currentDocument->totals?->total_mismatch)
                                    <li><strong>Grand Total Discrepancy:</strong> Printed total
                                        (₱{{ number_format($currentDocument->totals->printed_total, 2) }}) does not
                                        match computed grand sum
                                        (₱{{ number_format($currentDocument->totals->computed_grand_total, 2) }}).
                                        (Note: Line prices honor Discounted Price over regular Unit Price per company
                                        policy).</li>
                                @endif
                                @foreach ($editableItems as $idx => $line)
                                    @if (!empty($line['total_mismatch']))
                                        @php
                                            $effectivePrice =
                                                !empty($line['discounted_price']) &&
                                                (float) $line['discounted_price'] > 0
                                                    ? (float) $line['discounted_price']
                                                    : (float) $line['unit_price'];
                                            $priceLabel =
                                                !empty($line['discounted_price']) &&
                                                (float) $line['discounted_price'] > 0
                                                    ? 'Discounted Price'
                                                    : 'Unit Price';
                                        @endphp
                                        <li><strong>Line {{ $line['line_no'] }} Math Discrepancy:</strong> Printed
                                            total ₱{{ number_format($line['printed_total'] ?? 0, 2) }} vs computed sum
                                            ({{ $line['qty'] }} × {{ $priceLabel }}
                                            ₱{{ number_format($effectivePrice, 2) }} =
                                            ₱{{ number_format($line['computed_total'], 2) }}). Difference:
                                            ₱{{ number_format(abs(($line['printed_total'] ?? 0) - $line['computed_total']), 2) }}.
                                            (System honors Discounted Price per company policy).</li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- SECTION 1: DOCUMENT HEADER INFORMATION --}}
                    <x-filament::section>
                        <x-slot name="heading">
                            <span
                                class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Document
                                Information</span>
                        </x-slot>

                        <div class="grid grid-cols-1 gap-4 p-1 md:grid-cols-3">
                            <x-filament-forms::field-wrapper id="documentNumber" label="Quotation / PO No.">
                                <x-filament::input.wrapper size="sm">
                                    <x-filament::input type="text" wire:model.lazy="documentNumber"
                                        :disabled="$this->isReadOnly"
                                        class="text-xs font-semibold" />
                                </x-filament::input.wrapper>
                            </x-filament-forms::field-wrapper>

                            <x-filament-forms::field-wrapper id="documentDate" label="Document Date">
                                <x-filament::input.wrapper size="sm">
                                    <x-filament::input type="date" wire:model.lazy="documentDate"
                                        :disabled="$this->isReadOnly"
                                        class="text-xs" />
                                </x-filament::input.wrapper>
                            </x-filament-forms::field-wrapper>

                            <x-filament-forms::field-wrapper id="customerName" label="Customer Name">
                                <x-filament::input.wrapper size="sm">
                                    <x-filament::input type="text" wire:model.lazy="customerName"
                                        :disabled="$this->isReadOnly"
                                        class="text-xs" />
                                </x-filament::input.wrapper>
                            </x-filament-forms::field-wrapper>

                            <x-filament-forms::field-wrapper id="customerCompany" label="Company">
                                <x-filament::input.wrapper size="sm">
                                    <x-filament::input type="text" wire:model.lazy="customerCompany"
                                        :disabled="$this->isReadOnly"
                                        class="text-xs" />
                                </x-filament::input.wrapper>
                            </x-filament-forms::field-wrapper>

                            <x-filament-forms::field-wrapper id="projectName" label="Project Name">
                                <x-filament::input.wrapper size="sm">
                                    <x-filament::input type="text" wire:model.lazy="projectName"
                                        :disabled="$this->isReadOnly"
                                        class="text-xs" />
                                </x-filament::input.wrapper>
                            </x-filament-forms::field-wrapper>

                            <x-filament-forms::field-wrapper id="projectLocation" label="Project Location">
                                <x-filament::input.wrapper size="sm">
                                    <x-filament::input type="text" wire:model.lazy="projectLocation"
                                        :disabled="$this->isReadOnly"
                                        class="text-xs" />
                                </x-filament::input.wrapper>
                            </x-filament-forms::field-wrapper>

                            <x-filament-forms::field-wrapper id="phoneNo" label="Phone No.">
                                <x-filament::input.wrapper size="sm">
                                    <x-filament::input type="text" wire:model.lazy="phoneNo"
                                        :disabled="$this->isReadOnly"
                                        class="text-xs" />
                                </x-filament::input.wrapper>
                            </x-filament-forms::field-wrapper>

                            <x-filament-forms::field-wrapper id="vendorId" label="Vendor / Partner">
                                <x-filament::input.wrapper size="sm">
                                    <x-filament::input.select wire:model.lazy="vendorId"
                                        :disabled="$this->isReadOnly"
                                        class="text-xs">
                                        <option value="">-- Select Vendor --</option>
                                        @foreach ($this->vendors as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </x-filament::input.select>
                                </x-filament::input.wrapper>
                            </x-filament-forms::field-wrapper>

                            <x-filament-forms::field-wrapper id="projectId" label="Linked System Project">
                                <x-filament::input.wrapper size="sm">
                                    <x-filament::input.select wire:model.lazy="projectId"
                                        :disabled="$this->isReadOnly"
                                        class="text-xs">
                                        <option value="">-- Select Project --</option>
                                        @foreach ($this->projects as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </x-filament::input.select>
                                </x-filament::input.wrapper>
                            </x-filament-forms::field-wrapper>
                        </div>
                    </x-filament::section>
                </div>
            </div>

            {{-- BOTTOM STACK: EXTRACTED LINE ITEMS, RECONCILIATION, CROSS-REF, & ACTION BUTTONS --}}
            <div style="display: flex; flex-direction: column; gap: 1.5rem; margin-top: 1.5rem;">

                {{-- SECTION 2: EXTRACTED LINE ITEMS (Outer Encapsulating Section) --}}
                <x-filament::section icon="heroicon-o-list-bullet">
                    <x-slot name="heading">
                        <span class="text-sm font-semibold tracking-tight text-gray-950 dark:text-white">
                            Extracted Line Items ({{ count($editableItems) }})
                        </span>
                    </x-slot>

                    <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                        @forelse($editableItems as $index => $item)
                            <div style="margin-top: 0.5rem; margin-bottom: 0.5rem;">
                                <x-filament::section compact>
                                    @if (!empty($item['total_mismatch']))
                                        <x-slot name="heading">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-bold bg-red-50 text-red-700 border border-red-200 dark:bg-red-950/60 dark:text-red-400 dark:border-red-800/60">
                                                <x-filament::icon icon="heroicon-m-exclamation-triangle" class="h-3 w-3 text-red-500" />
                                                Discrepancy Flagged
                                            </span>
                                        </x-slot>
                                    @endif

                                    <x-slot name="headerEnd">
                                        @if (!$this->isReadOnly)
                                            <x-filament::dropdown placement="bottom-end">
                                                <x-slot name="trigger">
                                                    <x-filament::icon-button icon="heroicon-m-ellipsis-vertical" color="gray" size="sm"
                                                        tooltip="Item Actions" label="Actions" />
                                                </x-slot>

                                                <x-filament::dropdown.list>
                                                    <x-filament::dropdown.list.item wire:click="cloneLineItem({{ $index }})"
                                                        icon="heroicon-m-document-duplicate" color="info">
                                                        Duplicate Item
                                                    </x-filament::dropdown.list.item>

                                                    <x-filament::dropdown.list.item wire:click="removeLineItem({{ $index }})"
                                                        icon="heroicon-m-trash" color="danger">
                                                        Delete Item
                                                    </x-filament::dropdown.list.item>
                                                </x-filament::dropdown.list>
                                            </x-filament::dropdown>
                                        @endif
                                    </x-slot>

                                    {{-- ROW 1: Product Identification (1:2:6 = 9fr total) --}}
                                    <div style="display: grid; grid-template-columns: 1fr 2fr 6fr; gap: 0.875rem; align-items: start; margin-bottom: 0.75rem;">
                                        <div>
                                            <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">#</label>
                                            <x-filament::input.wrapper size="sm">
                                                <x-filament::input type="number"
                                                    wire:model.live="editableItems.{{ $index }}.line_no"
                                                    :disabled="$this->isReadOnly"
                                                    class="text-center font-mono text-xs font-semibold" />
                                            </x-filament::input.wrapper>
                                        </div>

                                        <div>
                                            <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Item Code</label>
                                            <x-filament::input.wrapper size="sm">
                                                <x-filament::input type="text"
                                                    wire:model.live="editableItems.{{ $index }}.material_code"
                                                    :disabled="$this->isReadOnly"
                                                    class="font-mono text-xs font-semibold"
                                                    placeholder="Item Code" />
                                            </x-filament::input.wrapper>
                                        </div>

                                        <div>
                                            <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Product</label>
                                            <x-filament::input.wrapper size="sm">
                                                <x-filament::input.select wire:model.live="editableItems.{{ $index }}.product_id"
                                                    wire:change="onProductSelected({{ $index }}, $event.target.value)"
                                                    :disabled="$this->isReadOnly"
                                                    class="text-xs">
                                                    <option value="">Select a product</option>
                                                    @foreach ($this->products as $pId => $pName)
                                                        <option value="{{ $pId }}">{{ $pName }}</option>
                                                    @endforeach
                                                </x-filament::input.select>
                                            </x-filament::input.wrapper>
                                        </div>
                                    </div>

                                {{-- Visual Divider Between Header Info and Pricing --}}
                                <div style="border-top: 1px dashed rgba(148, 163, 184, 0.2); margin: 0.75rem 0 0.875rem 0;"></div>

                                {{-- ROW 2: Pricing, Quantities & Line Totals (1:1:2:2:3) --}}
                                <div style="display: grid; grid-template-columns: 1fr 1fr 2fr 2fr 3fr; gap: 0.875rem; align-items: start;">
                                    <div>
                                        <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Qty</label>
                                        <x-filament::input.wrapper size="sm">
                                            <x-filament::input type="number" step="any" min="0.0001"
                                                wire:model.live="editableItems.{{ $index }}.qty"
                                                :disabled="$this->isReadOnly"
                                                class="text-right font-mono text-xs font-semibold" />
                                        </x-filament::input.wrapper>
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Unit</label>
                                        <x-filament::input.wrapper size="sm">
                                            <x-filament::input type="text"
                                                wire:model.live="editableItems.{{ $index }}.unit"
                                                :disabled="$this->isReadOnly"
                                                class="text-center text-xs font-medium uppercase"
                                                placeholder="pcs" />
                                        </x-filament::input.wrapper>
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Unit Price (₱)</label>
                                        <x-filament::input.wrapper size="sm" prefix="₱">
                                            <x-filament::input type="number" step="0.01"
                                                wire:model.live="editableItems.{{ $index }}.unit_price"
                                                :disabled="$this->isReadOnly"
                                                class="text-right font-mono text-xs font-semibold" />
                                        </x-filament::input.wrapper>
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-bold uppercase tracking-wider text-amber-500 dark:text-amber-400 mb-1.5">Discounted Price (₱)</label>
                                        <x-filament::input.wrapper size="sm" prefix="₱">
                                            <x-filament::input type="number" step="0.01"
                                                wire:model.live="editableItems.{{ $index }}.discounted_price"
                                                :disabled="$this->isReadOnly"
                                                class="text-right font-mono text-xs font-semibold text-amber-600 dark:text-amber-400" />
                                        </x-filament::input.wrapper>
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Total (₱)</label>
                                        <x-filament::input.wrapper size="sm" prefix="₱"
                                            :color="!empty($item['total_mismatch']) ? 'danger' : 'gray'">
                                            <x-filament::input type="number" step="0.01"
                                                wire:model.live="editableItems.{{ $index }}.printed_total"
                                                :disabled="$this->isReadOnly"
                                                class="text-right font-mono text-xs font-bold" />
                                        </x-filament::input.wrapper>
                                        @if (!empty($item['total_mismatch']))
                                            <span class="mt-1 block text-right font-mono font-semibold leading-tight text-[11px] text-red-500 dark:text-red-400">
                                                Computed: ₱{{ number_format($item['computed_total'], 2) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </x-filament::section>
                        </div>
                    @empty
                            <div class="rounded-xl border border-dashed border-gray-200 p-8 text-center text-xs italic text-gray-400 dark:border-gray-800">
                                No line items extracted.
                            </div>
                        @endforelse
                    </div>

                    {{-- Centered Add Line Item Button at Bottom --}}
                    @if (!$this->isReadOnly)
                        <div style="display: flex; justify-content: center; align-items: center; margin-top: 1.5rem;">
                            <x-filament::button type="button" wire:click="addLineItem" size="sm" color="primary"
                                icon="heroicon-m-plus">
                                Add Line Item
                            </x-filament::button>
                        </div>
                    @endif
                </x-filament::section>

                {{-- SECTION 3: TOTALS & 12% VAT RECONCILIATION SUMMARY --}}
                <x-filament::section>
                    <x-slot name="heading">
                        <span
                            class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Arithmetic
                            & 12% VAT Reconciliation</span>
                    </x-slot>

                    <div
                        style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; font-size: 0.75rem;">

                        {{-- Printed Figures --}}
                        <div
                            style="padding: 1rem; border-radius: 0.5rem; background: rgba(156, 163, 175, 0.08); display: flex; flex-direction: column; gap: 0.6rem;">
                            <span
                                style="font-weight: 700; color: #4b5563; display: block; border-bottom: 1px solid rgba(156, 163, 175, 0.2); padding-bottom: 0.35rem;">
                                Printed Document Figures
                            </span>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #6b7280;">Printed Subtotal:</span>
                                <span
                                    style="font-family: monospace;">₱{{ number_format($printedSubtotal ?? 0, 2) }}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #6b7280;">Printed 12% VAT:</span>
                                <span
                                    style="font-family: monospace; {{ $currentDocument->totals?->vat_mismatch ? 'color: #dc2626; font-weight: 700;' : '' }}">
                                    ₱{{ number_format($printedVat ?? 0, 2) }}
                                </span>
                            </div>
                            @if ($currentDocument->document_type === 'vendors_agreement')
                                <div
                                    style="display: flex; justify-content: space-between; color: #d97706; font-weight: 700;">
                                    <span>Negotiated Deal Amount:</span>
                                    <span
                                        style="font-family: monospace;">₱{{ number_format($negotiatedAmount ?? 0, 2) }}</span>
                                </div>
                            @endif
                            <div
                                style="display: flex; justify-content: space-between; font-weight: 700; border-top: 1px solid rgba(156, 163, 175, 0.3); padding-top: 0.35rem;">
                                <span>Printed Grand Total:</span>
                                <span
                                    style="font-family: monospace;">₱{{ number_format($printedTotal ?? 0, 2) }}</span>
                            </div>
                        </div>

                        {{-- System Recomputed Values --}}
                        <div
                            style="padding: 1rem; border-radius: 0.5rem; background: rgba(59, 130, 246, 0.08); border: 1px solid rgba(59, 130, 246, 0.25); display: flex; flex-direction: column; gap: 0.6rem;">
                            <span
                                style="font-weight: 700; color: #2563eb; display: block; border-bottom: 1px solid rgba(59, 130, 246, 0.2); padding-bottom: 0.35rem;">
                                System Recomputed Values
                            </span>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #6b7280;">Computed Subtotal:</span>
                                <span
                                    style="font-family: monospace;">₱{{ number_format($currentDocument->totals?->computed_subtotal ?? 0, 2) }}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #6b7280;">Computed 12% Standard VAT:</span>
                                <span
                                    style="font-family: monospace; font-weight: 600; color: #2563eb;">₱{{ number_format($currentDocument->totals?->computed_vat ?? 0, 2) }}</span>
                            </div>
                            <div
                                style="display: flex; justify-content: space-between; font-weight: 800; font-size: 0.85rem; border-top: 1px solid rgba(59, 130, 246, 0.3); padding-top: 0.35rem; color: #2563eb;">
                                <span>Authoritative Value:</span>
                                <span style="font-family: monospace;">
                                    ₱{{ number_format($negotiatedAmount ?: $currentDocument->totals?->computed_grand_total ?? 0, 2) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </x-filament::section>

                {{-- SECTION 4: 2-WAY CROSS-REFERENCE (QUOTATION & PURCHASE ORDER) --}}
                @if ($crossRefQuotation || $crossRefPO)
                    <x-filament::section>
                        <x-slot name="heading">
                            <span
                                class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">2-Way
                                Cross-Referenced Documents (Quotation & Purchase Order)</span>
                        </x-slot>

                        <div
                            style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; font-size: 0.75rem;">
                            <div
                                style="padding: 0.75rem; border-radius: 0.375rem; border: 1px solid {{ $crossRefQuotation ? '#16a34a' : '#9ca3af' }}; background: {{ $crossRefQuotation ? 'rgba(22, 163, 74, 0.08)' : 'transparent' }};">
                                <strong style="display: block; margin-bottom: 0.25rem;">1. Quotation:</strong>
                                <span
                                    style="font-family: monospace; font-weight: 600;">{{ $crossRefQuotation ? $crossRefQuotation->document_number : 'Not yet linked' }}</span>
                            </div>
                            <div
                                style="padding: 0.75rem; border-radius: 0.375rem; border: 1px solid {{ $crossRefPO ? '#16a34a' : '#9ca3af' }}; background: {{ $crossRefPO ? 'rgba(22, 163, 74, 0.08)' : 'transparent' }};">
                                <strong style="display: block; margin-bottom: 0.25rem;">2. Purchase Order:</strong>
                                <span
                                    style="font-family: monospace; font-weight: 600;">{{ $crossRefPO ? $crossRefPO->document_number : 'Not yet linked' }}</span>
                            </div>
                        </div>
                    </x-filament::section>
                @endif

                {{-- SECTION 5: ACTIONS BAR --}}
                <x-filament::section>
                    <div
                        style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">

                        {{-- Left Action Buttons --}}
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            @if (!$this->isReadOnly)
                                <x-filament::button type="button" wire:click="saveDraft" color="gray"
                                    icon="heroicon-m-arrow-path" size="sm"
                                    title="Save updated line items and re-calculate line sums, totals, and 12% Philippine VAT">
                                    Save Draft & Re-Calculate
                                </x-filament::button>
                            @endif

                            @if (auth()->user()?->canVerifyDocuments())
                                <x-filament::modal id="reject-document-modal" width="md" icon="heroicon-o-exclamation-triangle" icon-color="danger">
                                    <x-slot name="trigger">
                                        <x-filament::button type="button" color="danger"
                                            icon="heroicon-m-x-circle" size="sm"
                                            title="Reject this document and remove it from the active review queue">
                                            Reject Document
                                        </x-filament::button>
                                    </x-slot>

                                    <x-slot name="heading">
                                        Reject Document Confirmation
                                    </x-slot>

                                    <x-slot name="description">
                                        Are you sure you want to mark this document as rejected? This will remove the document from the active review queue.
                                    </x-slot>

                                    <div style="display: flex; flex-direction: column; gap: 0.5rem; margin-top: 0.5rem;">
                                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                                            Reason for Rejection (Optional)
                                        </label>
                                        <x-filament::input.wrapper size="sm">
                                            <x-filament::input type="text"
                                                wire:model="rejectionReason"
                                                placeholder="e.g. Incomplete pricing, illegible scan, duplicate upload" />
                                        </x-filament::input.wrapper>
                                    </div>

                                    <x-slot name="footer">
                                        <div style="display: flex; justify-content: flex-end; align-items: center; gap: 0.75rem; width: 100%;">
                                            <x-filament::button type="button" color="gray"
                                                x-on:click="$dispatch('close-modal', { id: 'reject-document-modal' })">
                                                Cancel
                                            </x-filament::button>

                                            <x-filament::button type="button" color="danger"
                                                wire:click="rejectDocument"
                                                icon="heroicon-m-x-circle">
                                                Confirm Rejection
                                            </x-filament::button>
                                        </div>
                                    </x-slot>
                                </x-filament::modal>
                            @endif
                        </div>

                        {{-- Right Action Button --}}
                        <div>
                            @if (auth()->user()?->canVerifyDocuments())
                                <x-filament::button type="button" wire:click="approveAndVerify" color="success"
                                    icon="heroicon-m-check-badge" size="md"
                                    title="Approve this reconciled document and commit transaction to the master financial ledger">
                                    Approve & Commit Transaction
                                </x-filament::button>
                            @else
                                <span class="text-xs font-medium text-gray-500">
                                    Viewing Mode: Formal approval reserved for Operations Managers & Admins
                                </span>
                            @endif
                        </div>
                    </div>
                </x-filament::section>

            </div>
        </div>
    @endif
</x-filament-panels::page>
