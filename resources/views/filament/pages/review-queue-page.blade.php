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
                <div class="border-b border-gray-100 dark:border-white/10" style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1rem; gap: 1rem; flex-wrap: wrap;">
                    {{-- Left: Back & Document Identity --}}
                    <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                        <x-filament::button type="button" wire:click="closeWorkspace" color="gray"
                            icon="heroicon-m-arrow-left" size="sm" outlined>
                            Back to {{ $currentDocument->document_type === 'vendors_agreement' ? 'Quotations' : 'Purchase Orders' }}
                        </x-filament::button>

                        <div class="hidden sm:block h-5 w-px bg-gray-200 dark:bg-gray-700"></div>

                        <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                            <span class="font-bold text-base tracking-tight text-gray-900 dark:text-white" style="white-space: nowrap;">
                                {{ $currentDocument->document_number ?: 'Document #' . $currentDocument->id }}
                            </span>

                            <x-filament::badge :color="match ($currentDocument->document_type) {
                                'purchase_order' => 'primary',
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

                            @if ($currentDocument->document_type === 'purchase_order' && $this->isReadOnly)
                                <x-filament::badge color="info" icon="heroicon-m-lock-closed" size="sm">
                                    Official PO (Read Only)
                                </x-filament::badge>
                            @elseif ($currentDocument->document_type === 'purchase_order' && !$this->isReadOnly)
                                <x-filament::badge color="primary" size="sm">
                                    Purchase Order
                                </x-filament::badge>
                            @elseif ($this->isReadOnly)
                                <x-filament::badge color="info" icon="heroicon-m-eye" size="sm">
                                    Viewing Mode
                                </x-filament::badge>
                            @endif
                        </div>
                    </div>

                    {{-- Right: Previous / Next Document Switcher --}}
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-left: auto;">
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
                    <div class="bg-gray-50/75 dark:bg-white/5" style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 1rem; gap: 1rem; flex-wrap: wrap;">
                        {{-- Left Tools: Re-Extract, Undo, Redo, Reset --}}
                        <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
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
                        <div class="text-gray-500 dark:text-gray-400" style="display: flex; align-items: center; gap: 0.75rem; font-size: 0.75rem; margin-left: auto;">
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

            {{-- Split-Screen Layout: PDF Left (5 cols on lg) / Reconciliation Right (7 cols on lg) --}}
            <div x-data x-on:keydown.window.ctrl.z.prevent="$wire.undo()"
                x-on:keydown.window.ctrl.y.prevent="$wire.redo()"
                class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start mt-6">

                {{-- LEFT COLUMN: Embedded PDF & Real-time Live Highlight Preview --}}
                <div class="lg:col-span-5 flex flex-col gap-4">
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
                <div class="lg:col-span-7 flex flex-col gap-6" style="display: flex; flex-direction: column; gap: 1.5rem;">

                    {{-- ANOMALY CALLOUT BOX --}}
                    @if ($currentDocument->hasMismatches())
                        <div
                            style="padding: 1rem 1.25rem; border-radius: 0.75rem; background-color: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.35); margin-bottom: 1.5rem;">
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
                    <div style="margin-top: 0.5rem; margin-bottom: 1.5rem;" class="my-6">
                        <x-filament::section>
                            <x-slot name="heading">
                                <span
                                    class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Document
                                    Information</span>
                            </x-slot>

                            <div class="grid grid-cols-1 gap-4 p-1 md:grid-cols-3" style="margin: 0.5rem 0;">
                                <x-filament-forms::field-wrapper id="documentNumber" label="Quotation / PO No." :required="true">
                                    <x-filament::input.wrapper size="sm">
                                        <x-filament::input type="text" wire:model.lazy="documentNumber"
                                            :disabled="$this->isReadOnly"
                                            class="text-xs font-semibold" />
                                    </x-filament::input.wrapper>
                                </x-filament-forms::field-wrapper>

                            <x-filament-forms::field-wrapper id="documentDate" label="Document Date" :required="true">
                                <x-filament::input.wrapper size="sm">
                                    <x-filament::input type="date" wire:model.lazy="documentDate"
                                        :disabled="$this->isReadOnly"
                                        class="text-xs" />
                                </x-filament::input.wrapper>
                            </x-filament-forms::field-wrapper>

                            <x-filament-forms::field-wrapper id="customerName" label="Customer Name" :required="true">
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

                    {{-- SECTION 1.5: TERMS & CONDITIONS TABLE (AUTHENTIC LAYOUT WITH CHECKBOXES) --}}
                    <style>
                        .tc-card {
                            border: 1px solid #cbd5e1;
                            border-radius: 0.5rem;
                            overflow-x: auto;
                            font-size: 0.75rem;
                            background-color: #ffffff;
                            color: #0f172a;
                        }
                        .dark .tc-card {
                            border-color: #334155;
                            background-color: #0f172a;
                            color: #f1f5f9;
                        }
                        .tc-header {
                            background-color: #f1f5f9;
                            font-weight: 700;
                            padding: 0.5rem 0.75rem;
                            font-size: 0.75rem;
                            border-bottom: 1px solid #cbd5e1;
                            color: #0f172a;
                            letter-spacing: 0.025em;
                        }
                        .dark .tc-header {
                            background-color: #1e293b;
                            color: #f8fafc;
                            border-bottom-color: #334155;
                        }
                        .tc-body {
                            padding: 0.75rem;
                            display: flex;
                            flex-direction: column;
                            gap: 0.75rem;
                            min-width: 620px;
                        }
                        .tc-row {
                            display: grid;
                            grid-template-columns: 150px 290px 1fr;
                            align-items: flex-start;
                            gap: 1rem;
                            border-bottom: 1px dashed #e2e8f0;
                            padding-bottom: 0.625rem;
                        }
                        .dark .tc-row {
                            border-bottom-color: #1e293b;
                        }
                        .tc-row-last {
                            display: grid;
                            grid-template-columns: 150px 290px 1fr;
                            align-items: flex-start;
                            gap: 1rem;
                        }
                        .tc-row-title {
                            font-weight: 700;
                            line-height: 1.25rem;
                            color: #0f172a;
                            font-size: 0.75rem;
                        }
                        .dark .tc-row-title {
                            color: #f8fafc;
                        }
                        .tc-options-wrap {
                            display: flex;
                            flex-wrap: wrap;
                            gap: 0.75rem 2rem;
                            align-items: flex-start;
                        }
                        .tc-label {
                            display: inline-flex;
                            align-items: flex-start;
                            gap: 0.5rem;
                            cursor: pointer;
                            user-select: none;
                        }
                        .tc-checkbox {
                            width: 1rem;
                            height: 1rem;
                            min-width: 1rem;
                            min-height: 1rem;
                            margin-top: 0.125rem;
                            flex-shrink: 0;
                            border-radius: 0.25rem;
                            cursor: pointer;
                        }
                        .tc-label-text {
                            font-size: 0.75rem;
                            line-height: 1.25rem;
                            font-weight: 500;
                            color: #1e293b;
                        }
                        .dark .tc-label-text {
                            color: #e2e8f0;
                        }
                        .tc-label-text-bold {
                            font-size: 0.75rem;
                            line-height: 1.25rem;
                            font-weight: 700;
                            color: #1d4ed8;
                        }
                        .dark .tc-label-text-bold {
                            color: #60a5fa;
                        }
                        .tc-validity-input {
                            border: none;
                            border-bottom: 1px solid #94a3b8;
                            background: transparent;
                            font-weight: 600;
                            padding: 0.125rem 0.375rem;
                            width: 140px;
                            font-size: 0.75rem;
                            line-height: 1.25rem;
                            color: #0f172a;
                        }
                        .dark .tc-validity-input {
                            border-bottom-color: #64748b;
                            color: #f8fafc;
                        }
                        .tc-notes-container {
                            border: 1px solid #f87171;
                            border-radius: 0.375rem;
                            padding: 0.625rem 0.875rem;
                            font-size: 0.725rem;
                            line-height: 1.4;
                            background-color: #fef2f2;
                        }
                        .dark .tc-notes-container {
                            border-color: #991b1b;
                            background-color: rgba(127, 29, 29, 0.15);
                        }
                        .tc-notes-heading {
                            color: #dc2626;
                            font-weight: 800;
                            font-size: 0.7rem;
                            letter-spacing: 0.05em;
                            margin-bottom: 0.25rem;
                        }
                        .dark .tc-notes-heading {
                            color: #f87171;
                        }
                        .tc-notes-body {
                            color: #334155;
                            display: flex;
                            flex-direction: column;
                            gap: 0.25rem;
                        }
                        .dark .tc-notes-body {
                            color: #cbd5e1;
                        }
                        .tc-notes-badge {
                            color: #b91c1c;
                            font-weight: 700;
                        }
                        .dark .tc-notes-badge {
                            color: #fca5a5;
                        }
                        .tc-notes-badge-underline {
                            color: #b91c1c;
                            font-weight: 700;
                            text-decoration: underline;
                        }
                        .dark .tc-notes-badge-underline {
                            color: #fca5a5;
                        }
                        .tc-po-signoff {
                            padding: 0.75rem;
                            border-radius: 0.5rem;
                            background-color: #eff6ff;
                            border: 1px solid #bfdbfe;
                            display: flex;
                            flex-direction: column;
                            gap: 0.75rem;
                        }
                        .dark .tc-po-signoff {
                            background-color: rgba(30, 58, 138, 0.15);
                            border-color: #1e3a8a;
                        }
                        .tc-po-signoff-title {
                            font-size: 0.75rem;
                            font-weight: 700;
                            color: #1d4ed8;
                        }
                        .dark .tc-po-signoff-title {
                            color: #60a5fa;
                        }
                        .tc-details-summary {
                            font-size: 0.75rem;
                            font-weight: 600;
                            cursor: pointer;
                            color: #475569;
                            transition: color 0.15s ease;
                        }
                        .dark .tc-details-summary {
                            color: #94a3b8;
                        }
                        .tc-details-summary:hover {
                            color: #2563eb;
                        }
                        .dark .tc-details-summary:hover {
                            color: #60a5fa;
                        }
                    </style>

                    <div style="margin-bottom: 1.5rem;" class="mb-6">
                        <x-filament::section>
                            <x-slot name="heading">
                                <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Terms & Conditions</span>
                            </x-slot>

                            <div style="display: flex; flex-direction: column; gap: 1rem;">
                                {{-- Authentic Terms & Conditions Card Table --}}
                                <div class="tc-card">
                                    <div class="tc-header">
                                        Terms and Conditions
                                    </div>
                                    <div class="tc-body">
                                        
                                        {{-- Row 1: Validity --}}
                                        <div class="tc-row">
                                            <div class="tc-row-title">Validity</div>
                                            <div style="grid-column: span 2; display: flex; align-items: center;">
                                                <input type="text" wire:model.lazy="tcValidity" @if($this->isReadOnly) disabled @endif
                                                    class="tc-validity-input focus:ring-0 focus:border-primary-500 dark:focus:border-primary-400"
                                                    placeholder="15 days">
                                            </div>
                                        </div>

                                        {{-- Row 2: Stock Availability --}}
                                        <div class="tc-row">
                                            <div class="tc-row-title">Stock Availability</div>
                                            <div>
                                                <label class="tc-label">
                                                    <input type="checkbox" wire:model.live="tcStock" @if($this->isReadOnly) disabled @endif
                                                        class="tc-checkbox border-slate-300 dark:border-slate-600 text-primary-600 focus:ring-primary-500 dark:bg-slate-800 dark:checked:bg-primary-600">
                                                    <span class="tc-label-text">Stock</span>
                                                </label>
                                            </div>
                                            <div>
                                                <label class="tc-label">
                                                    <input type="checkbox" wire:model.live="tcNonStock" @if($this->isReadOnly) disabled @endif
                                                        class="tc-checkbox border-slate-300 dark:border-slate-600 text-primary-600 focus:ring-primary-500 dark:bg-slate-800 dark:checked:bg-primary-600">
                                                    <span class="tc-label-text">Non-Stock / Special Items/Indent Order</span>
                                                </label>
                                            </div>
                                        </div>

                                        {{-- Row 3: Terms Of Delivery --}}
                                        <div class="tc-row">
                                            <div class="tc-row-title">Terms Of Delivery</div>
                                            <div>
                                                <label class="tc-label">
                                                    <input type="checkbox" wire:model.live="tcDelivery4To7" @if($this->isReadOnly) disabled @endif
                                                        class="tc-checkbox border-slate-300 dark:border-slate-600 text-primary-600 focus:ring-primary-500 dark:bg-slate-800 dark:checked:bg-primary-600">
                                                    <span class="tc-label-text">4-7 days</span>
                                                </label>
                                            </div>
                                            <div style="display: flex; align-items: flex-start; gap: 2rem; flex-wrap: wrap;">
                                                <label class="tc-label">
                                                    <input type="checkbox" wire:model.live="tcDelivery10To15" @if($this->isReadOnly) disabled @endif
                                                        class="tc-checkbox border-slate-300 dark:border-slate-600 text-primary-600 focus:ring-primary-500 dark:bg-slate-800 dark:checked:bg-primary-600">
                                                    <span class="tc-label-text">10-15 days</span>
                                                </label>
                                                <label class="tc-label">
                                                    <input type="checkbox" wire:model.live="tcDelivery45To60" @if($this->isReadOnly) disabled @endif
                                                        class="tc-checkbox border-slate-300 dark:border-slate-600 text-primary-600 focus:ring-primary-500 dark:bg-slate-800 dark:checked:bg-primary-600">
                                                    <span class="tc-label-text">45-60 days</span>
                                                </label>
                                            </div>
                                        </div>

                                        {{-- Row 4: Payment Terms --}}
                                        <div class="tc-row">
                                            <div class="tc-row-title">Payment Terms</div>
                                            <div>
                                                <label class="tc-label">
                                                    <input type="checkbox" wire:model.live="tcPaymentCodDp" @if($this->isReadOnly) disabled @endif
                                                        class="tc-checkbox border-slate-300 dark:border-slate-600 text-primary-600 focus:ring-primary-500 dark:bg-slate-800 dark:checked:bg-primary-600">
                                                    <span class="tc-label-text">COD / 50% DP ; 50% PDC 30 Days</span>
                                                </label>
                                            </div>
                                            <div>
                                                <label class="tc-label">
                                                    <input type="checkbox" wire:model.live="tcPaymentApproved" @if($this->isReadOnly) disabled @endif
                                                        class="tc-checkbox border-slate-300 dark:border-slate-600 text-primary-600 focus:ring-primary-500 dark:bg-slate-800 dark:checked:bg-primary-600">
                                                    <span class="tc-label-text">Approved Terms</span>
                                                </label>
                                            </div>
                                        </div>

                                        {{-- Row 5: Remarks --}}
                                        <div class="tc-row-last">
                                            <div class="tc-row-title">Remarks</div>
                                            <div>
                                                <label class="tc-label">
                                                    <input type="checkbox" wire:model.live="tcRemarksOfficialPo" @if($this->isReadOnly) disabled @endif
                                                        class="tc-checkbox border-blue-400 dark:border-blue-500 text-blue-600 focus:ring-blue-500 dark:bg-slate-800">
                                                    <span class="tc-label-text-bold">Serve as an Official P.O.</span>
                                                </label>
                                            </div>
                                            <div>
                                                <label class="tc-label">
                                                    <input type="checkbox" wire:model.live="tcRemarksNonReturnable" @if($this->isReadOnly) disabled @endif
                                                        class="tc-checkbox border-slate-300 dark:border-slate-600 text-primary-600 focus:ring-primary-500 dark:bg-slate-800 dark:checked:bg-primary-600">
                                                    <span class="tc-label-text">Non- Returnable/ Non- Cancealable</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Conditional Signature Box when "Serve as an Official P.O." is Checked --}}
                                @if ($tcRemarksOfficialPo || $isOfficialPo)
                                    <div class="tc-po-signoff">
                                        <div class="tc-po-signoff-title">
                                            Official Purchase Order Sign-off Details (Conforme / Signed PO)
                                        </div>
                                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                                            <x-filament-forms::field-wrapper id="customerSignatureName" label="Customer Name Over Signature">
                                                <x-filament::input.wrapper size="sm">
                                                    <x-filament::input type="text" wire:model.lazy="customerSignatureName"
                                                        :disabled="$this->isReadOnly"
                                                        placeholder="e.g. Engr. Juan Dela Cruz"
                                                        class="text-xs" />
                                                </x-filament::input.wrapper>
                                            </x-filament-forms::field-wrapper>
                                            <x-filament-forms::field-wrapper id="customerSignedAt" label="Date Signed">
                                                <x-filament::input.wrapper size="sm">
                                                    <x-filament::input type="date" wire:model.lazy="customerSignedAt"
                                                        :disabled="$this->isReadOnly"
                                                        class="text-xs" />
                                                </x-filament::input.wrapper>
                                            </x-filament-forms::field-wrapper>
                                        </div>
                                    </div>
                                @endif

                                {{-- NOTES CALLOUT BOX (Exact Match to PDF Form) --}}
                                <div class="tc-notes-container">
                                    <div class="tc-notes-heading">NOTES:</div>
                                    <div class="tc-notes-body">
                                        <div>* Minimum amount of order should be <strong class="tc-notes-badge">Php 20,000 .00</strong> above for Free Delivery within Metro Manila. Outside Metro Manila Shipment cost will be applied.</div>
                                        <div>* Return & Exchange of Items should be within <strong class="tc-notes-badge-underline">7 days upon delivery</strong>.</div>
                                        <div>* Gate fees or any other entrance fees not included. Additional charges shall be applied for deliveries before or after office hour.</div>
                                        <div>* Please inspect item before installation. Complaints will not be entertained after items have been installed.</div>
                                        <div>* Special order, sale/phase out and non-regular items are not allowed for return.</div>
                                    </div>
                                </div>

                                {{-- Optional Custom Terms & Additional Notes --}}
                                <details style="font-size: 0.75rem;">
                                    <summary class="tc-details-summary">
                                        <span>Show Raw / Additional Custom Terms</span>
                                    </summary>
                                    <div style="margin-top: 0.5rem; display: flex; flex-direction: column; gap: 0.5rem;">
                                        <x-filament-forms::field-wrapper id="termsAndConditions" label="Raw Terms & Conditions Notes">
                                            <x-filament::input.wrapper size="sm">
                                                <textarea wire:model.lazy="termsAndConditions"
                                                    @if($this->isReadOnly) disabled @endif
                                                    rows="3"
                                                    class="w-full rounded-lg border-gray-300 bg-white px-3 py-2 text-xs shadow-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 disabled:opacity-70 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-primary-500"
                                                    style="width: 100%; resize: vertical;"
                                                    placeholder="Optional special terms or additional clauses..."
                                                ></textarea>
                                            </x-filament::input.wrapper>
                                        </x-filament-forms::field-wrapper>
                                    </div>
                                </details>
                            </div>
                        </x-filament::section>
                    </div>
                </div>
            </div>

            {{-- BOTTOM STACK: EXTRACTED LINE ITEMS, RECONCILIATION, CROSS-REF, & ACTION BUTTONS --}}
            <div style="display: flex; flex-direction: column; gap: 1.5rem; margin-top: 1.5rem;">

                {{-- SECTION 2: EXTRACTED LINE ITEMS (Outer Encapsulating Section) --}}
                <x-filament::section icon="heroicon-o-list-bullet">
                    <x-slot name="heading">
                        <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                            <span class="text-sm font-semibold tracking-tight text-gray-950 dark:text-white">
                                Extracted Line Items ({{ count($editableItems) }})
                            </span>
                            <span class="inline-flex items-center gap-1 text-xs font-mono font-bold text-primary-700 dark:text-primary-300 bg-primary-50 dark:bg-primary-950/60 border border-primary-200 dark:border-primary-800/60 px-2.5 py-0.5 rounded-md shadow-2xs">
                                Live Subtotal: ₱{{ number_format($printedSubtotal ?? 0, 2) }} &bull; Total: ₱{{ number_format($printedTotal ?? 0, 2) }}
                            </span>
                        </div>
                    </x-slot>

                    <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                        @forelse($editableItems as $index => $item)
                            <div style="margin-top: 0.5rem; margin-bottom: 0.5rem;">
                                <x-filament::section compact>
                                    <x-slot name="heading">
                                        <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                            <span class="font-bold text-xs text-gray-800 dark:text-gray-200">
                                                Line #{{ $item['line_no'] ?? ($index + 1) }}
                                            </span>
                                            @if (!empty($item['material_code']))
                                                <span class="font-mono text-xs text-gray-500 dark:text-gray-400">[{{ $item['material_code'] }}]</span>
                                            @endif
                                            @if (!empty($item['total_mismatch']))
                                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[11px] font-bold bg-danger-600 text-white shadow-sm dark:bg-danger-500">
                                                    <x-filament::icon icon="heroicon-m-exclamation-triangle" class="h-3.5 w-3.5 inline-block text-white shrink-0" />
                                                    Discrepancy Flagged
                                                </span>
                                            @endif
                                        </div>
                                    </x-slot>

                                    <x-slot name="headerEnd">
                                        @if (!$this->isReadOnly)
                                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                                <x-filament::icon-button wire:click="cloneLineItem({{ $index }})"
                                                    icon="heroicon-m-document-duplicate" color="gray" size="sm"
                                                    tooltip="Duplicate Item" label="Duplicate" />

                                                <x-filament::button type="button"
                                                    color="danger" size="xs"
                                                    icon="heroicon-m-trash"
                                                    tooltip="Delete this line item and auto-recalculate all totals"
                                                    wire:click="confirmDeleteLineItem({{ $index }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="confirmDeleteLineItem({{ $index }})">
                                                    Delete Line
                                                </x-filament::button>
                                            </div>
                                        @endif
                                    </x-slot>

                                    {{-- ROW 1: Product Identification (1:3:8:auto proportion) --}}
                                    <div style="display: grid; grid-template-columns: 60px 180px 1fr auto; gap: 0.875rem; align-items: end; margin-bottom: 0.75rem;">
                                        <div>
                                            <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">#</label>
                                            <x-filament::input.wrapper size="sm">
                                                <x-filament::input type="number"
                                                    wire:model="editableItems.{{ $index }}.line_no"
                                                    disabled
                                                    class="text-center font-mono text-xs font-semibold bg-gray-50 dark:bg-white/5 cursor-not-allowed opacity-80" />
                                            </x-filament::input.wrapper>
                                        </div>

                                        <div>
                                            <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Item Code</label>
                                            <x-filament::input.wrapper size="sm">
                                                <x-filament::input.select wire:model.live="editableItems.{{ $index }}.material_code"
                                                    wire:change="onItemCodeSelected({{ $index }}, $event.target.value)"
                                                    :disabled="$this->isReadOnly"
                                                    class="font-mono text-xs font-semibold">
                                                    <option value="">-- None / No Code --</option>
                                                    @foreach ($this->skuOptions as $sku)
                                                        <option value="{{ $sku }}">{{ $sku }}</option>
                                                    @endforeach
                                                    @if (!empty($item['material_code']) && !isset($this->skuOptions[$item['material_code']]))
                                                        <option value="{{ $item['material_code'] }}">{{ $item['material_code'] }}</option>
                                                    @endif
                                                </x-filament::input.select>
                                            </x-filament::input.wrapper>
                                        </div>

                                        <div>
                                            <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">
                                                Product <span class="text-danger-600 dark:text-danger-500 font-bold" style="color: #ef4444; font-weight: bold;">*</span>
                                            </label>
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

                                        <div style="display: flex; flex-direction: column; align-items: center; justify-content: flex-end;">
                                            <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Photo</label>
                                            @php
                                                $thumbUrl = !empty($item['product_id']) ? ($this->productThumbnails[$item['product_id']] ?? null) : null;
                                                if (!$thumbUrl && !empty($item['material_code'])) {
                                                    $thumbUrl = $this->productThumbnails['sku:' . $item['material_code']] ?? null;
                                                }
                                                $lineTitle = !empty($item['description']) ? $item['description'] : ($this->products[$item['product_id'] ?? null] ?? ($item['material_code'] ?? 'Product Photo'));
                                                $lineSku = $item['material_code'] ?? '';
                                                $lineNo = $item['line_no'] ?? ($index + 1);
                                            @endphp
                                            @if ($thumbUrl)
                                                {{-- AVATAR: Rendered as a circular product avatar when photo exists --}}
                                                <button type="button"
                                                    wire:click="openPhotoPreview({{ $index }})"
                                                    class="group relative inline-flex items-center justify-center rounded-full ring-2 ring-primary-500/40 hover:ring-primary-500 dark:ring-primary-400/50 hover:ring-primary-400 shadow-xs hover:scale-105 active:scale-95 transition-all cursor-pointer bg-white dark:bg-gray-900 shrink-0"
                                                    style="width: 32px; height: 32px; overflow: hidden; padding: 0;"
                                                    title="Click to maximize photo: {{ $lineTitle }}">
                                                    <img src="{{ $thumbUrl }}" alt="{{ $lineTitle }}"
                                                        class="w-full h-full rounded-full object-cover select-none"
                                                        loading="lazy" />
                                                    <div class="absolute inset-0 rounded-full bg-black/0 group-hover:bg-black/35 flex items-center justify-center transition-colors">
                                                        <x-filament::icon icon="heroicon-m-arrows-pointing-out" class="h-3.5 w-3.5 text-white opacity-0 group-hover:opacity-100 transition-opacity drop-shadow-md" />
                                                    </div>
                                                </button>
                                            @else
                                                {{-- PHOTO BUTTON ONLY: Clean native icon button when no photo is attached --}}
                                                <x-filament::icon-button type="button"
                                                    color="gray" size="sm"
                                                    icon="heroicon-o-photo"
                                                    tooltip="{{ $lineTitle ? 'Photo: ' . $lineTitle . ' (No image attached)' : 'View Photo' }}"
                                                    label="Photo"
                                                    wire:click="openPhotoPreview({{ $index }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="openPhotoPreview({{ $index }})" />
                                            @endif
                                        </div>
                                    </div>

                                {{-- Visual Divider Between Header Info and Pricing --}}
                                <div style="border-top: 1px dashed rgba(148, 163, 184, 0.2); margin: 0.75rem 0 0.875rem 0;"></div>

                                {{-- ROW 2: Pricing, Quantities & Line Totals (1:1:2:2:3:auto) --}}
                                <div style="display: grid; grid-template-columns: 1fr 1fr 2fr 2fr 3fr {{ !$this->isReadOnly ? 'auto' : '' }}; gap: 0.875rem; align-items: start;">
                                    <div>
                                        <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">
                                            Qty <span class="text-danger-600 dark:text-danger-500 font-bold" style="color: #ef4444; font-weight: bold;">*</span>
                                        </label>
                                        <x-filament::input.wrapper size="sm">
                                            <x-filament::input type="number" step="any" min="0.0001"
                                                wire:model.live="editableItems.{{ $index }}.qty"
                                                :disabled="$this->isReadOnly"
                                                class="text-right font-mono text-xs font-semibold" />
                                        </x-filament::input.wrapper>
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">
                                            Unit <span class="text-danger-600 dark:text-danger-500 font-bold" style="color: #ef4444; font-weight: bold;">*</span>
                                        </label>
                                        <x-filament::input.wrapper size="sm">
                                            <x-filament::input.select wire:model.live="editableItems.{{ $index }}.unit"
                                                :disabled="$this->isReadOnly"
                                                class="text-center text-xs font-medium uppercase">
                                                @foreach ($this->unitOptions as $uVal => $uLabel)
                                                    <option value="{{ $uVal }}">{{ $uVal }}</option>
                                                @endforeach
                                            </x-filament::input.select>
                                        </x-filament::input.wrapper>
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">
                                            Unit Price (₱) <span class="text-danger-600 dark:text-danger-500 font-bold" style="color: #ef4444; font-weight: bold;">*</span>
                                        </label>
                                        <x-filament::input.wrapper size="sm" prefix="₱">
                                            <x-filament::input type="number" step="0.01"
                                                wire:model.live="editableItems.{{ $index }}.unit_price"
                                                :disabled="$this->isReadOnly"
                                                class="text-right font-mono text-xs font-semibold" />
                                        </x-filament::input.wrapper>
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-bold uppercase tracking-wider text-amber-500 dark:text-amber-400 mb-1.5">Discounted (₱)</label>
                                        <x-filament::input.wrapper size="sm" prefix="₱">
                                            <x-filament::input type="number" step="0.01"
                                                wire:model.live="editableItems.{{ $index }}.discounted_price"
                                                :disabled="$this->isReadOnly"
                                                class="text-right font-mono text-xs font-semibold text-amber-600 dark:text-amber-400" />
                                        </x-filament::input.wrapper>
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">
                                            Total (₱) <span class="text-danger-600 dark:text-danger-500 font-bold" style="color: #ef4444; font-weight: bold;">*</span>
                                        </label>
                                        <x-filament::input.wrapper size="sm" prefix="₱"
                                            :color="!empty($item['total_mismatch']) ? 'danger' : 'gray'">
                                            <x-filament::input type="number" step="0.01"
                                                wire:model="editableItems.{{ $index }}.printed_total"
                                                disabled
                                                class="text-right font-mono text-xs font-bold bg-gray-50 dark:bg-white/5 cursor-not-allowed opacity-85" />
                                        </x-filament::input.wrapper>
                                        @if (!empty($item['total_mismatch']))
                                            <span class="mt-1 block text-right font-mono font-semibold leading-tight text-[11px] text-danger-600 dark:text-danger-400">
                                                Computed: ₱{{ number_format($item['computed_total'], 2) }}
                                            </span>
                                        @endif
                                    </div>

                                    @if (!$this->isReadOnly)
                                        <div style="display: flex; flex-direction: column; align-items: center; justify-content: flex-end; padding-bottom: 2px;">
                                            <label class="block text-[11px] font-bold uppercase tracking-wider text-transparent select-none mb-1.5">&nbsp;</label>
                                             <x-filament::icon-button type="button"
                                                color="danger" size="sm"
                                                icon="heroicon-m-trash"
                                                tooltip="Delete this line item and auto-recalculate all totals"
                                                wire:click="confirmDeleteLineItem({{ $index }})"
                                                wire:loading.attr="disabled"
                                                wire:target="confirmDeleteLineItem({{ $index }})" />
                                        </div>
                                    @endif
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

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">

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
                                    style="font-family: monospace;">₱{{ number_format($currentDocument->totals?->computed_subtotal ?? $printedSubtotal ?? 0, 2) }}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #6b7280;">Computed 12% Standard VAT:</span>
                                <span
                                    style="font-family: monospace; font-weight: 600; color: #2563eb;">₱{{ number_format($currentDocument->totals?->computed_vat ?? $printedVat ?? 0, 2) }}</span>
                            </div>
                            <div
                                style="display: flex; justify-content: space-between; font-weight: 800; font-size: 0.85rem; border-top: 1px solid rgba(59, 130, 246, 0.3); padding-top: 0.35rem; color: #2563eb;">
                                <span>Authoritative Value:</span>
                                <span style="font-family: monospace;">
                                    ₱{{ number_format($negotiatedAmount ?: ($currentDocument->totals?->computed_grand_total ?? $printedTotal ?? 0), 2) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </x-filament::section>

                {{-- SECTION 4: 2-WAY CROSS-REFERENCE & LINE ITEM RECONCILIATION --}}
                @if ($crossRefQuotation || $crossRefPO || $this->reconciliation)
                    <x-filament::section>
                        <x-slot name="heading">
                            <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
                                <span
                                    class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">2-Way
                                    Cross-Referenced Documents & Line Item Reconciliation</span>
                                @if ($this->reconciliation && $this->reconciliation['has_discrepancies'])
                                    <span style="font-size: 0.75rem; font-weight: 700; padding: 0.125rem 0.5rem; border-radius: 9999px; background: rgba(245, 158, 11, 0.2);" class="text-amber-800 dark:text-amber-200">
                                        {{ $this->reconciliation['discrepancy_count'] }} Line Discrepancies
                                    </span>
                                @elseif ($this->reconciliation)
                                    <span style="font-size: 0.75rem; font-weight: 700; padding: 0.125rem 0.5rem; border-radius: 9999px; background: rgba(34, 197, 94, 0.2);" class="text-emerald-800 dark:text-emerald-200">
                                        100% Match
                                    </span>
                                @endif
                            </div>
                        </x-slot>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs" style="margin-bottom: 1rem;">
                            <div
                                style="padding: 0.75rem; border-radius: 0.375rem; border: 1px solid {{ ($crossRefQuotation || !empty($this->reconciliation['quotation_number'])) ? '#16a34a' : '#9ca3af' }}; background: {{ ($crossRefQuotation || !empty($this->reconciliation['quotation_number'])) ? 'rgba(22, 163, 74, 0.08)' : 'transparent' }};">
                                <strong style="display: block; margin-bottom: 0.25rem;">1. Quotation:</strong>
                                <span
                                    style="font-family: monospace; font-weight: 600;">{{ $crossRefQuotation ? $crossRefQuotation->document_number : ($this->reconciliation['quotation_number'] ?? 'Not yet linked') }}</span>
                            </div>
                            <div
                                style="padding: 0.75rem; border-radius: 0.375rem; border: 1px solid {{ ($crossRefPO || $this->currentDocument?->document_type === 'purchase_order') ? '#16a34a' : '#9ca3af' }}; background: {{ ($crossRefPO || $this->currentDocument?->document_type === 'purchase_order') ? 'rgba(22, 163, 74, 0.08)' : 'transparent' }};">
                                <strong style="display: block; margin-bottom: 0.25rem;">2. Purchase Order:</strong>
                                <span
                                    style="font-family: monospace; font-weight: 600;">{{ $crossRefPO ? $crossRefPO->document_number : ($this->currentDocument?->document_type === 'purchase_order' ? $this->currentDocument->document_number : 'Not yet linked') }}</span>
                            </div>
                        </div>

                        @if ($this->reconciliation && $this->reconciliation['has_linked_quotation'])
                            <div style="margin-top: 1.25rem; border-top: 1px solid rgba(156, 163, 175, 0.2); padding-top: 1.25rem;">
                                @include('filament.infolists.po-quotation-reconciliation', ['reconciliation' => $this->reconciliation, 'getRecord' => null])
                            </div>
                        @endif
                    </x-filament::section>
                @endif

                @if ($this->isUnlinkedNormalPo)
                    <div style="margin-bottom: 1rem; padding: 0.75rem 1rem; border-radius: 0.5rem; background-color: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.4); display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;" class="text-amber-800 dark:text-amber-300 text-xs">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <x-heroicon-m-exclamation-triangle style="width: 1.25rem; height: 1.25rem; flex-shrink: 0;" class="text-amber-600 dark:text-amber-400" />
                            <span>This is a <strong>Normal Purchase Order</strong> (not a Conforme PO). It must be linked to an approved quotation before it can be approved and committed to the master ledger.</span>
                        </div>
                    </div>
                @endif

                @if ($this->isPoWithDiscrepancy)
                    <div style="margin-bottom: 1rem; padding: 0.75rem 1rem; border-radius: 0.5rem; background-color: rgba(244, 63, 94, 0.1); border: 1px solid rgba(244, 63, 94, 0.4); display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;" class="text-rose-800 dark:text-rose-300 text-xs">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <x-heroicon-m-exclamation-circle style="width: 1.25rem; height: 1.25rem; flex-shrink: 0;" class="text-rose-600 dark:text-rose-400" />
                            <span><strong>Approval Restricted:</strong> Line item discrepancies detected with linked Quotation #{{ $this->reconciliation['quotation_number'] ?? '' }}. You can review and update line items above, but approval is blocked until all discrepancies are resolved.</span>
                        </div>
                    </div>
                @endif

                {{-- SECTION 5: ACTIONS BAR --}}
                <x-filament::section>
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">

                        {{-- Left Action Buttons --}}
                        <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
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
                                    :disabled="$this->isUnlinkedNormalPo || $this->isPoWithDiscrepancy"
                                    :title="$this->isPoWithDiscrepancy ? 'Approval restricted: PO has line item discrepancies with linked quotation' : ($this->isUnlinkedNormalPo ? 'Normal PO must be linked to an approved quotation before approval' : 'Approve this reconciled document and commit transaction to the master financial ledger')">
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

                {{-- UNIFORM MODAL: PRODUCT PHOTO MAXIMIZE LIGHTBOX --}}
                <x-filament::modal id="image-lightbox-modal" width="4xl" icon="heroicon-o-photo" icon-color="primary">
                    <x-slot name="heading">
                        <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                            <span class="text-sm font-bold tracking-tight text-gray-950 dark:text-white">{{ $previewPhotoTitle ?: 'Product Photo Preview' }}</span>
                            @if ($previewPhotoSku)
                                <span class="font-mono text-xs px-2 py-0.5 rounded bg-primary-50 text-primary-700 dark:bg-primary-950/70 dark:text-primary-300 border border-primary-200 dark:border-primary-800/60">[{{ $previewPhotoSku }}]</span>
                            @endif
                            @if ($previewPhotoLineNo)
                                <span class="text-xs text-gray-400 dark:text-gray-500 font-mono">(Line #{{ $previewPhotoLineNo }})</span>
                            @endif
                        </div>
                    </x-slot>

                    <div class="py-2">
                        @if ($previewPhotoUrl)
                            <div class="relative flex flex-col items-center justify-center rounded-xl bg-gray-950 border border-gray-800 p-4 shadow-2xl overflow-hidden" style="min-height: 380px; max-height: 75vh;">
                                <img src="{{ $previewPhotoUrl }}" alt="{{ $previewPhotoTitle }}"
                                    class="max-h-[68vh] w-auto max-w-full object-contain rounded-lg shadow-lg select-none transition-transform duration-200"
                                    loading="eager" />

                                <div class="absolute bottom-3 right-3 flex items-center gap-2">
                                    <a href="{{ $previewPhotoUrl }}" target="_blank" rel="noopener noreferrer"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-semibold bg-white/10 hover:bg-white/20 text-white backdrop-blur-md border border-white/20 transition-all shadow-xs"
                                        title="Open image full size in a new tab">
                                        <x-filament::icon icon="heroicon-m-arrow-top-right-on-square" class="h-3.5 w-3.5 text-white" />
                                        Open Original
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="rounded-xl border border-dashed border-gray-200 dark:border-gray-800 p-8 text-center bg-gray-50/50 dark:bg-white/2">
                                <div class="mx-auto w-14 h-14 rounded-2xl bg-primary-50 dark:bg-primary-950/60 border border-primary-200/80 dark:border-primary-900/50 flex items-center justify-center mb-3">
                                    <x-filament::icon icon="heroicon-o-photo" class="h-7 w-7 text-primary-600 dark:text-primary-400" />
                                </div>
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white">{{ $previewPhotoTitle ?: 'Selected Product' }}</h4>
                                @if ($previewPhotoSku)
                                    <p class="font-mono text-xs text-gray-500 dark:text-gray-400 mt-1">Item Code: {{ $previewPhotoSku }}</p>
                                @endif
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-2 max-w-md mx-auto">
                                    No photograph is currently attached to this catalog item. You can upload product imagery in the <strong>Products</strong> management module.
                                </p>
                            </div>
                        @endif
                    </div>

                    <x-slot name="footer">
                        <div style="display: flex; justify-content: flex-end; align-items: center; gap: 0.75rem; width: 100%;">
                            <x-filament::button type="button" color="gray"
                                x-on:click="$dispatch('close-modal', { id: 'image-lightbox-modal' })">
                                Close Preview
                            </x-filament::button>
                        </div>
                    </x-slot>
                </x-filament::modal>

                {{-- UNIFORM MODAL: DELETE EXTRACTED LINE ITEM CONFIRMATION --}}
                <x-filament::modal id="delete-line-item-modal" width="md" icon="heroicon-o-trash" icon-color="danger">
                    <x-slot name="heading">
                        Delete Extracted Line Item
                    </x-slot>

                    <x-slot name="description">
                        This action will remove the line item and recalculate all document totals.
                    </x-slot>

                    @php
                        $targetItem = ($confirmingDeleteIndex !== null && isset($editableItems[$confirmingDeleteIndex])) ? $editableItems[$confirmingDeleteIndex] : null;
                    @endphp

                    <div class="py-2 text-sm">
                        <div class="rounded-lg bg-danger-50 dark:bg-danger-950/40 p-3.5 text-xs border border-danger-200 dark:border-danger-900/60 shadow-2xs">
                            <div class="flex items-start gap-2.5">
                                <x-filament::icon icon="heroicon-m-exclamation-triangle" class="h-5 w-5 text-danger-600 dark:text-danger-400 shrink-0 mt-0.5" />
                                <div class="space-y-1">
                                    <p class="font-bold text-danger-900 dark:text-danger-200">
                                        Delete Line #{{ $targetItem['line_no'] ?? (($confirmingDeleteIndex ?? 0) + 1) }}
                                        @if (!empty($targetItem['material_code']))
                                            [{{ $targetItem['material_code'] }}]
                                        @endif
                                        ?
                                    </p>
                                    @if (!empty($targetItem['description']))
                                        <p class="text-gray-700 dark:text-gray-300 font-medium">{{ $targetItem['description'] }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="mt-3 pt-2.5 border-t border-danger-200/70 dark:border-danger-900/60 text-[11px] text-danger-800 dark:text-danger-300/90 leading-relaxed">
                                The Subtotal, 12% standard Philippine VAT, grand totals, and linked quotation figures will be recomputed immediately.
                            </div>
                        </div>
                    </div>

                    <x-slot name="footer">
                        <div style="display: flex; justify-content: flex-end; align-items: center; gap: 0.75rem; width: 100%;">
                            <x-filament::button type="button" color="gray"
                                wire:click="cancelDeleteLineItem">
                                Cancel
                            </x-filament::button>

                            <x-filament::button type="button" color="danger" icon="heroicon-m-trash"
                                wire:click="executeDeleteConfirmed"
                                wire:loading.attr="disabled"
                                wire:target="executeDeleteConfirmed">
                                Delete Line Item
                            </x-filament::button>
                        </div>
                    </x-slot>
                </x-filament::modal>

            </div>
        </div>
    @endif
</x-filament-panels::page>
