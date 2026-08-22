<?php

namespace App\Filament\Pages;

use App\Actions\CrossReferenceDocuments;
use App\Actions\ReconcileDocumentTotals;
use App\Actions\VerifyDocument;
use App\Models\Document;
use App\Models\Product;
use App\Models\ProductAlias;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\Transaction;
use App\Models\Vendor;
use App\Services\DocumentParsers\DynamicDocumentParser;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ReviewQueuePage extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static bool $shouldRegisterNavigation = false;
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static \UnitEnum|string|null $navigationGroup = 'Sales & Order Lifecycle';
    protected static ?string $navigationLabel = 'Document Verification Queue';
    protected static ?string $title = 'Document Verification & Review';
    protected string $view = 'filament.pages.review-queue-page';
    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        return $user->canVerifyDocuments() || $user->isSalesExecutive() || $user->canCreateDocuments();
    }


    protected function getHeaderWidgets(): array
    {
        if ($this->selectedDocumentId) {
            return [];
        }

        return [
            \App\Filament\Widgets\ReviewQueueStatsWidget::class,
        ];
    }

    // Active Verification Workspace State
    public ?int $selectedDocumentId = null;
    public ?Document $currentDocument = null;

    // Editable Line Items
    public array $editableItems = [];

    // Editable Totals & Header Metadata
    public ?string $documentNumber = null;
    public ?string $documentDate = null;
    public ?string $customerName = null;
    public ?string $customerCompany = null;
    public ?string $projectName = null;
    public ?string $projectLocation = null;
    public ?string $phoneNo = null;
    public ?int $vendorId = null;
    public ?int $projectId = null;
    public ?float $printedSubtotal = null;
    public ?float $printedVat = null;
    public ?float $printedTotal = null;
    public ?float $negotiatedAmount = null;

    // Real-Time Synchronized Preview State
    public string $previewMode = 'live'; // 'live' or 'pdf'
    public array $originalState = [];
    public ?string $rejectionReason = '';

    public function setPreviewMode(string $mode): void
    {
        $this->previewMode = $mode;
    }

    public function isFieldModified(string $field): bool
    {
        $orig = $this->originalState[$field] ?? null;
        $curr = $this->{$field} ?? null;
        return (string) $orig !== (string) $curr;
    }

    public function isLineModified(int $index, ?string $key = null): bool
    {
        $origLine = $this->originalState['editableItems'][$index] ?? null;
        $currLine = $this->editableItems[$index] ?? null;

        if (!$origLine || !$currLine) {
            return true;
        }

        if ($key !== null) {
            return (string) ($origLine[$key] ?? '') !== (string) ($currLine[$key] ?? '');
        }

        return json_encode($origLine) !== json_encode($currLine);
    }

    public function getLivePdfUrl(): string
    {
        if (!$this->currentDocument) {
            return '#';
        }

        $mod = [
            'documentNumber' => $this->isFieldModified('documentNumber'),
            'documentDate' => $this->isFieldModified('documentDate'),
            'customerName' => $this->isFieldModified('customerName'),
            'customerCompany' => $this->isFieldModified('customerCompany'),
            'projectName' => $this->isFieldModified('projectName'),
            'projectLocation' => $this->isFieldModified('projectLocation'),
            'phoneNo' => $this->isFieldModified('phoneNo'),
            'items' => [],
        ];

        foreach ($this->editableItems as $index => $item) {
            $mod['items'][$index] = [
                'material_code' => $this->isLineModified($index, 'material_code'),
                'description' => $this->isLineModified($index, 'description'),
                'qty' => $this->isLineModified($index, 'qty'),
                'unit' => $this->isLineModified($index, 'unit'),
                'unit_price' => $this->isLineModified($index, 'unit_price'),
                'discounted_price' => $this->isLineModified($index, 'discounted_price'),
                'printed_total' => $this->isLineModified($index, 'printed_total'),
            ];
        }

        $payload = [
            'documentNumber' => $this->documentNumber,
            'documentDate' => $this->documentDate,
            'customerName' => $this->customerName,
            'customerCompany' => $this->customerCompany,
            'projectName' => $this->projectName,
            'projectLocation' => $this->projectLocation,
            'phoneNo' => $this->phoneNo,
            'items' => $this->editableItems,
            'mod' => $mod,
        ];

        $encoded = base64_encode(json_encode($payload));
        return route('documents.live-pdf', ['document' => $this->currentDocument->id, 'payload' => $encoded, 'v' => substr(md5($encoded), 0, 8)]);
    }

    // Cross reference details
    public ?Document $crossRefQuotation = null;
    public ?Document $crossRefPO = null;
    public ?Transaction $existingTransaction = null;

    public function mount(): void
    {
        $docId = request()->query('document_id');

        if ($docId) {
            $this->loadDocument((int) $docId);
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Document::query()
                    ->with(['vendor', 'project', 'totals', 'lineItems', 'uploader'])
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('document_number')
                    ->label('Doc #')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->default('—')
                    ->description(fn(Document $record): string => $record->original_filename ? \Illuminate\Support\Str::limit($record->original_filename, 30) : '')
                    ->tooltip(fn(Document $record): string => "Document #{$record->document_number} — File: {$record->original_filename}"),

                Tables\Columns\TextColumn::make('document_type')
                    ->label('Document Type')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        Document::TYPE_PURCHASE_ORDER => 'Purchase Order',
                        Document::TYPE_VENDORS_AGREEMENT => 'Vendors Agreement',
                        default => 'Purchase Order',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        Document::TYPE_PURCHASE_ORDER => 'primary',
                        Document::TYPE_VENDORS_AGREEMENT => 'warning',
                        default => 'gray',
                    })
                    ->tooltip(fn(Document $record): string => "Document category: " . strtoupper(str_replace('_', ' ', $record->document_type))),

                Tables\Columns\TextColumn::make('vendor.name')
                    ->label('Vendor')
                    ->searchable()
                    ->sortable()
                    ->default('—')
                    ->tooltip(fn(Document $record): string => "Vendor / Supplier: " . ($record->vendor?->name ?? 'Unassigned')),

                Tables\Columns\TextColumn::make('project.name')
                    ->label('Project / Site')
                    ->searchable()
                    ->sortable()
                    ->default('—')
                    ->tooltip(fn(Document $record): string => "Project Site: " . ($record->project?->name ?? 'Unassigned')),

                Tables\Columns\TextColumn::make('totals.printed_total')
                    ->label('Printed Total')
                    ->money('PHP')
                    ->sortable()
                    ->default('—')
                    ->tooltip(fn(Document $record): string => "Printed Gross Amount: ₱" . number_format($record->totals?->printed_total ?? 0, 2)),

                Tables\Columns\TextColumn::make('reconciliation_status')
                    ->label('Math & VAT Check')
                    ->state(function (Document $record): string {
                        if ($record->hasMismatches()) {
                            $issues = [];
                            if ($record->totals?->vat_mismatch) {
                                $issues[] = 'VAT Mismatch';
                            }
                            if ($record->totals?->total_mismatch) {
                                $issues[] = 'Total Mismatch';
                            }
                            if ($record->lineItems()->where('total_mismatch', true)->exists()) {
                                $issues[] = 'Line Math Error';
                            }
                            return count($issues) > 0 ? implode(', ', $issues) : 'Discrepancy Detected';
                        }
                        return 'Clean Math';
                    })
                    ->badge()
                    ->color(fn(string $state): string => $state === 'Clean Math' ? 'success' : 'danger')
                    ->icon(fn(string $state): string => $state === 'Clean Math' ? 'heroicon-m-check-circle' : 'heroicon-m-exclamation-triangle')
                    ->tooltip(fn(Document $record): string => $record->hasMismatches()
                        ? 'Discrepancies detected: Click "Verify & Reconcile" to review line arithmetic (.85 error) and 12% PH VAT accuracy.'
                        : 'All line calculations, arithmetic sums, and 12% Philippine VAT match perfectly.'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        Document::STATUS_REQUIRES_REVIEW => 'Needs Review',
                        Document::STATUS_VERIFIED => 'Verified',
                        Document::STATUS_FAILED => 'Failed',
                        Document::STATUS_PROCESSING => 'Processing',
                        Document::STATUS_UPLOADED => 'Uploaded',
                        Document::STATUS_REJECTED => 'Rejected',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        Document::STATUS_REQUIRES_REVIEW => 'warning',
                        Document::STATUS_VERIFIED => 'success',
                        Document::STATUS_FAILED, Document::STATUS_REJECTED => 'danger',
                        Document::STATUS_PROCESSING => 'info',
                        default => 'gray',
                    })
                    ->tooltip(fn(Document $record): string => "Current document lifecycle status: {$record->status}"),

                Tables\Columns\TextColumn::make('document_date')
                    ->label('Doc Date')
                    ->date('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('extraction_confidence')
                    ->label('Confidence')
                    ->formatStateUsing(fn($state) => $state ? "{$state}%" : '—')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->tooltip(fn(Document $record): string => "AI extraction parser confidence score: {$record->extraction_confidence}%"),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Queue Status')
                    ->options([
                        Document::STATUS_REQUIRES_REVIEW => 'Needs Review',
                        Document::STATUS_VERIFIED => 'Verified',
                        Document::STATUS_FAILED => 'Failed Ingestions',
                        Document::STATUS_REJECTED => 'Rejected',
                    ])
                    ->default(Document::STATUS_REQUIRES_REVIEW),

                Tables\Filters\SelectFilter::make('document_type')
                    ->label('Document Type')
                    ->options([
                        Document::TYPE_PURCHASE_ORDER => 'Purchase Order',
                        Document::TYPE_ORDER_SLIP => 'Order Slip',
                        Document::TYPE_VENDORS_AGREEMENT => 'Vendors Agreement Form',
                    ]),

                Tables\Filters\SelectFilter::make('vendor_id')
                    ->label('Vendor')
                    ->options(fn() => $this->vendors),

                Tables\Filters\SelectFilter::make('project_id')
                    ->label('Project')
                    ->options(fn() => $this->projects),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('verify_workspace')
                        ->label(fn() => auth()->user()?->canVerifyDocuments() ? 'Verify & Reconcile' : 'View & Check Math')
                        ->icon(fn() => auth()->user()?->canVerifyDocuments() ? 'heroicon-m-check-badge' : 'heroicon-m-eye')
                        ->color(fn() => auth()->user()?->canVerifyDocuments() ? 'primary' : 'info')
                        ->tooltip(fn() => auth()->user()?->canVerifyDocuments() ? 'Open the interactive split-screen PDF verification workspace' : 'View document extraction and arithmetic status (Viewing Mode)')
                        ->action(fn(Document $record) => $this->loadDocument($record->id)),

                    Action::make('quick_approve')
                        ->label('Quick Approve')
                        ->icon('heroicon-m-check')
                        ->color('success')
                        ->tooltip('Fast-track approve clean document and post directly to financial ledger')
                        ->visible(fn(Document $record): bool => (auth()->user()?->canVerifyDocuments() ?? false) && $record->status === Document::STATUS_REQUIRES_REVIEW && !$record->hasMismatches())
                        ->requiresConfirmation()
                        ->modalHeading('Quick Approve Document')
                        ->modalDescription('Are you sure you want to approve this document? It will create a transaction and mark the document as verified.')
                        ->action(function (Document $record) {
                            $docType = $record->document_type;
                            $verifier = app(VerifyDocument::class);
                            $trx = $verifier->execute($record, auth()->user() ?: \App\Models\User::first());

                            Notification::make()
                                ->title('Document Verified')
                                ->body("Transaction {$trx->transaction_code} created for ₱" . number_format($trx->final_amount, 2))
                                ->success()
                                ->send();

                            if ($docType === Document::TYPE_VENDORS_AGREEMENT) {
                                $this->redirect(\App\Filament\Resources\QuotationResource::getUrl());
                            } elseif (in_array($docType, [Document::TYPE_PURCHASE_ORDER, Document::TYPE_ORDER_SLIP])) {
                                $this->redirect(\App\Filament\Resources\PurchaseOrderResource::getUrl());
                            }
                        }),

                    Action::make('re_extract')
                        ->label('Re-Parse')
                        ->icon('heroicon-m-arrow-path')
                        ->color('gray')
                        ->tooltip('Re-run dynamic per-vendor template parsing on original PDF')
                        ->action(function (Document $record) {
                            try {
                                $parser = app(DynamicDocumentParser::class);
                                $reconciler = app(ReconcileDocumentTotals::class);

                                $parser->parseDocument($record);
                                $reconciler->execute($record);

                                Notification::make()
                                    ->title('Re-Extraction Completed')
                                    ->body("Extracted with latest dynamic layout rules.")
                                    ->success()
                                    ->send();
                            } catch (\Throwable $e) {
                                Notification::make()
                                    ->title('Extraction Failed')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Action::make('preview_pdf')
                        ->label('View PDF')
                        ->icon('heroicon-m-document-text')
                        ->color('gray')
                        ->tooltip('Open original uploaded PDF in a new tab')
                        ->url(fn(Document $record): string => route('documents.preview', $record), shouldOpenInNewTab: true),
                ]),
            ], position: RecordActionsPosition::BeforeColumns)

            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('re_extract_bulk')
                        ->label('Re-Parse Selected')
                        ->icon('heroicon-m-arrow-path')
                        ->action(function (Collection $records) {
                            $parser = app(DynamicDocumentParser::class);
                            $reconciler = app(ReconcileDocumentTotals::class);

                            foreach ($records as $doc) {
                                try {
                                    $parser->parseDocument($doc);
                                    $reconciler->execute($doc);
                                } catch (\Throwable $e) {
                                    // continue
                                }
                            }

                            Notification::make()
                                ->title('Bulk Re-Extraction Completed')
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }

    public function loadDocument(int $documentId): void
    {
        $doc = Document::with(['lineItems', 'totals', 'vendor', 'project'])->find($documentId);
        if (!$doc) {
            Notification::make()->title('Document not found')->danger()->send();
            return;
        }

        $this->selectedDocumentId = $doc->id;
        $this->currentDocument = $doc;

        $this->documentNumber = $doc->document_number;
        $this->documentDate = $doc->document_date ? $doc->document_date->format('Y-m-d') : null;
        $this->customerName = $doc->customer_name ?: ($doc->project?->customer_name ?: 'Engr. Ronald Rey Sandoval');
        $this->customerCompany = $doc->customer_company ?: 'MGS CONSTRUCTION, INC.';
        $this->projectName = $doc->project_name ?: ($doc->project?->name ?: 'Palanza Tower');
        $this->projectLocation = $doc->project_location ?: 'Palanza St. corner Guirayan st., Dona Imelda, Q.C';
        $this->phoneNo = $doc->phone_no ?: '0906-144-2553';
        $this->vendorId = $doc->vendor_id;
        $this->projectId = $doc->project_id;

        $totals = $doc->totals;
        $this->printedSubtotal = $totals ? (float) $totals->printed_subtotal : null;
        $this->printedVat = $totals ? (float) $totals->printed_vat : null;
        $this->printedTotal = $totals ? (float) $totals->printed_total : null;
        $this->negotiatedAmount = $totals ? (float) $totals->negotiated_amount : null;

        // Load line items into editable array
        $this->editableItems = [];
        foreach ($doc->lineItems as $item) {
            $this->editableItems[] = [
                'id' => $item->id,
                'line_no' => $item->line_no,
                'material_code' => $item->material_code,
                'description' => $item->description,
                'qty' => (float) $item->qty,
                'unit' => $item->unit ?: 'pcs',
                'unit_price' => (float) $item->unit_price,
                'discounted_price' => $item->discounted_price !== null ? (float) $item->discounted_price : (float) $item->unit_price,
                'printed_total' => $item->printed_total !== null ? (float) $item->printed_total : null,
                'computed_total' => (float) $item->computed_total,
                'total_mismatch' => (bool) $item->total_mismatch,
                'product_id' => $item->product_id,
            ];
        }

        // Save initial snapshot for real-time modification tracking
        $this->originalState = [
            'documentNumber' => $this->documentNumber,
            'documentDate' => $this->documentDate,
            'customerName' => $this->customerName,
            'customerCompany' => $this->customerCompany,
            'projectName' => $this->projectName,
            'projectLocation' => $this->projectLocation,
            'phoneNo' => $this->phoneNo,
            'vendorId' => $this->vendorId,
            'projectId' => $this->projectId,
            'printedSubtotal' => $this->printedSubtotal,
            'printedVat' => $this->printedVat,
            'printedTotal' => $this->printedTotal,
            'negotiatedAmount' => $this->negotiatedAmount,
            'editableItems' => $this->editableItems,
        ];

        // Cross-reference lookup
        $crossRef = app(CrossReferenceDocuments::class)->execute($doc);
        $this->crossRefQuotation = $crossRef['quotation'];
        $this->crossRefPO = $crossRef['purchase_order'];
        $this->existingTransaction = $crossRef['existing_transaction'];
    }

    public function closeWorkspace(): void
    {
        $docType = $this->currentDocument?->document_type;
        $this->selectedDocumentId = null;
        $this->currentDocument = null;

        if ($docType === Document::TYPE_VENDORS_AGREEMENT) {
            $this->redirect(\App\Filament\Resources\QuotationResource::getUrl());
            return;
        } elseif (in_array($docType, [Document::TYPE_PURCHASE_ORDER, Document::TYPE_ORDER_SLIP])) {
            $this->redirect(\App\Filament\Resources\PurchaseOrderResource::getUrl());
            return;
        }

        $this->redirect(\App\Filament\Resources\QuotationResource::getUrl());
    }

    public function loadNextDocument(): void
    {
        $next = Document::where('status', Document::STATUS_REQUIRES_REVIEW)
            ->where('id', '!=', $this->selectedDocumentId)
            ->first();

        if ($next) {
            $this->loadDocument($next->id);
        } else {
            $this->closeWorkspace();
            Notification::make()->title('Queue Completed')->body('No more pending review documents.')->success()->send();
        }
    }

    public function loadPreviousDocument(): void
    {
        $prev = Document::where('status', Document::STATUS_REQUIRES_REVIEW)
            ->where('id', '<', $this->selectedDocumentId)
            ->latest('id')
            ->first();

        if ($prev) {
            $this->loadDocument($prev->id);
        }
    }

    public function getIsReadOnlyProperty(): bool
    {
        if (!$this->currentDocument) {
            return false;
        }

        $user = auth()->user();
        if (!$user) {
            return true;
        }

        // 1. Purchase Orders are ALWAYS read-only in the verification workspace per business policy
        if ($this->currentDocument->document_type === Document::TYPE_PURCHASE_ORDER) {
            return true;
        }

        // 2. Only Sales Executive, Operations Manager, and Admin can edit quotations
        if (!$user->canEditQuotationDocument()) {
            return true;
        }

        return false;
    }

    // Undo / Redo History Stacks
    public array $undoStack = [];
    public array $redoStack = [];

    public function getCurrentStateSnapshot(): array
    {
        return [
            'documentNumber' => $this->documentNumber,
            'documentDate' => $this->documentDate,
            'customerName' => $this->customerName,
            'customerCompany' => $this->customerCompany,
            'projectName' => $this->projectName,
            'projectLocation' => $this->projectLocation,
            'phoneNo' => $this->phoneNo,
            'vendorId' => $this->vendorId,
            'projectId' => $this->projectId,
            'printedSubtotal' => $this->printedSubtotal,
            'printedVat' => $this->printedVat,
            'printedTotal' => $this->printedTotal,
            'negotiatedAmount' => $this->negotiatedAmount,
            'editableItems' => $this->editableItems,
        ];
    }

    public function pushStateToUndo(): void
    {
        $current = $this->getCurrentStateSnapshot();
        $last = end($this->undoStack);
        if ($last !== $current) {
            $this->undoStack[] = $current;
            if (count($this->undoStack) > 30) {
                array_shift($this->undoStack);
            }
            $this->redoStack = [];
        }
    }

    public function undo(): void
    {
        if (empty($this->undoStack)) {
            Notification::make()->title('Nothing to undo')->info()->send();
            return;
        }

        $this->redoStack[] = $this->getCurrentStateSnapshot();
        $previousState = array_pop($this->undoStack);
        $this->applyStateSnapshot($previousState);
        Notification::make()->title('Changes undone')->icon('heroicon-m-arrow-uturn-left')->success()->send();
    }

    public function redo(): void
    {
        if (empty($this->redoStack)) {
            Notification::make()->title('Nothing to redo')->info()->send();
            return;
        }

        $this->undoStack[] = $this->getCurrentStateSnapshot();
        $nextState = array_pop($this->redoStack);
        $this->applyStateSnapshot($nextState);
        Notification::make()->title('Changes redone')->icon('heroicon-m-arrow-uturn-right')->success()->send();
    }

    public function revertToOriginal(): void
    {
        if (empty($this->originalState)) {
            return;
        }

        $this->pushStateToUndo();
        $this->applyStateSnapshot($this->originalState);
        Notification::make()->title('Reverted to original extracted data')->warning()->send();
    }

    public function reExtractCurrentDocument(): void
    {
        if (!$this->currentDocument) {
            return;
        }

        try {
            $this->pushStateToUndo();

            $parser = app(DynamicDocumentParser::class);
            $reconciler = app(ReconcileDocumentTotals::class);

            $parser->parseDocument($this->currentDocument);
            $reconciler->execute($this->currentDocument);

            $this->loadDocument($this->currentDocument->id);

            Notification::make()
                ->title('Re-Extracted Successfully')
                ->body('Parsed with the latest AI & OCR extraction rules.')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Extraction Notice')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function applyStateSnapshot(array $state): void
    {
        $this->documentNumber = $state['documentNumber'] ?? null;
        $this->documentDate = $state['documentDate'] ?? null;
        $this->customerName = $state['customerName'] ?? null;
        $this->customerCompany = $state['customerCompany'] ?? null;
        $this->projectName = $state['projectName'] ?? null;
        $this->projectLocation = $state['projectLocation'] ?? null;
        $this->phoneNo = $state['phoneNo'] ?? null;
        $this->vendorId = $state['vendorId'] ?? null;
        $this->projectId = $state['projectId'] ?? null;
        $this->printedSubtotal = $state['printedSubtotal'] ?? null;
        $this->printedVat = $state['printedVat'] ?? null;
        $this->printedTotal = $state['printedTotal'] ?? null;
        $this->negotiatedAmount = $state['negotiatedAmount'] ?? null;
        $this->editableItems = $state['editableItems'] ?? [];

        $this->updatedEditableItems();
    }

    public function updating($property): void
    {
        if (
            in_array($property, [
                'documentNumber',
                'documentDate',
                'customerName',
                'customerCompany',
                'projectName',
                'projectLocation',
                'phoneNo',
                'vendorId',
                'projectId',
                'printedSubtotal',
                'printedVat',
                'printedTotal',
                'negotiatedAmount'
            ]) || str_starts_with($property, 'editableItems')
        ) {
            $this->pushStateToUndo();
        }
    }

    public function updatedEditableItems(): void
    {
        // Dynamically recompute lines when user edits qty or unit price or discounted price
        foreach ($this->editableItems as $index => $item) {
            $qty = isset($item['qty']) ? (float) $item['qty'] : 0.0;
            $unitPrice = isset($item['unit_price']) ? (float) $item['unit_price'] : 0.0;
            $discPrice = isset($item['discounted_price']) && (float) $item['discounted_price'] > 0 ? (float) $item['discounted_price'] : $unitPrice;
            $computed = round($qty * $discPrice, 2);

            $this->editableItems[$index]['computed_total'] = $computed;
            $this->editableItems[$index]['printed_total'] = $computed;
            $this->editableItems[$index]['total_mismatch'] = false;
        }
    }

    public function addLineItem(): void
    {
        $this->pushStateToUndo();

        $this->editableItems[] = [
            'id' => null,
            'line_no' => count($this->editableItems) + 1,
            'material_code' => null,
            'description' => '',
            'qty' => 1,
            'unit' => 'pcs',
            'unit_price' => 0.0,
            'discounted_price' => 0.0,
            'printed_total' => 0.0,
            'computed_total' => 0.0,
            'total_mismatch' => false,
            'product_id' => null,
        ];
    }

    public function cloneLineItem(int $index): void
    {
        if (isset($this->editableItems[$index])) {
            $this->pushStateToUndo();
            $item = $this->editableItems[$index];
            $item['id'] = null;

            array_splice($this->editableItems, $index + 1, 0, [$item]);
            $this->reindexLineNumbers();
            $this->updatedEditableItems();
        }
    }

    public function onItemCodeSelected(int $index, ?string $sku): void
    {
        if (isset($this->editableItems[$index])) {
            $this->pushStateToUndo();
            $this->editableItems[$index]['material_code'] = $sku;
            if (!empty($sku)) {
                $product = Product::where('sku', $sku)->first();
                if ($product) {
                    $this->editableItems[$index]['product_id'] = $product->id;
                    $this->editableItems[$index]['description'] = $product->canonical_name;
                    $this->editableItems[$index]['unit_price'] = (float) ($product->selling_price ?? $product->default_price ?? 0);
                    $this->editableItems[$index]['unit'] = $product->unit_default ?? 'pcs';
                    if (empty($this->editableItems[$index]['discounted_price'])) {
                        $this->editableItems[$index]['discounted_price'] = (float) ($product->selling_price ?? $product->default_price ?? 0);
                    }
                }
            }
            $this->updatedEditableItems();
        }
    }

    public function onProductSelected(int $index, $productId): void
    {
        if (isset($this->editableItems[$index]) && $productId) {
            $product = Product::find($productId);
            if ($product) {
                $this->pushStateToUndo();
                $this->editableItems[$index]['product_id'] = $product->id;
                $this->editableItems[$index]['description'] = $product->canonical_name;
                if ($product->sku) {
                    $this->editableItems[$index]['material_code'] = $product->sku;
                }
                $this->editableItems[$index]['unit_price'] = (float) ($product->selling_price ?? $product->default_price ?? 0);
                $this->editableItems[$index]['unit'] = $product->unit_default ?? 'pcs';
                if (empty($this->editableItems[$index]['discounted_price'])) {
                    $this->editableItems[$index]['discounted_price'] = (float) ($product->selling_price ?? $product->default_price ?? 0);
                }
                $this->updatedEditableItems();
            }
        }
    }

    public function reindexLineNumbers(): void
    {
        foreach ($this->editableItems as $idx => &$item) {
            $item['line_no'] = $idx + 1;
        }
    }

    public function removeLineItem(int $index): void
    {
        if (isset($this->editableItems[$index])) {
            $this->pushStateToUndo();

            $itemId = $this->editableItems[$index]['id'] ?? null;
            if ($itemId && $this->currentDocument) {
                $this->currentDocument->lineItems()->where('id', $itemId)->delete();
            }
            unset($this->editableItems[$index]);
            $this->editableItems = array_values($this->editableItems);
            $this->reindexLineNumbers();
        }
    }

    public function saveDraft(): void
    {
        if (!$this->currentDocument) {
            return;
        }

        $this->currentDocument->update([
            'document_number' => $this->documentNumber,
            'document_date' => $this->documentDate,
            'customer_name' => $this->customerName,
            'customer_company' => $this->customerCompany,
            'project_name' => $this->projectName,
            'project_location' => $this->projectLocation,
            'phone_no' => $this->phoneNo,
            'vendor_id' => $this->vendorId,
            'project_id' => $this->projectId,
        ]);

        // Save totals
        $this->currentDocument->totals()->updateOrCreate(
            ['document_id' => $this->currentDocument->id],
            [
                'printed_subtotal' => $this->printedSubtotal,
                'printed_vat' => $this->printedVat,
                'printed_total' => $this->printedTotal,
                'negotiated_amount' => $this->negotiatedAmount,
            ]
        );

        // Sync line items
        foreach ($this->editableItems as $item) {
            $desc = !empty($item['description']) 
                ? $item['description'] 
                : (!empty($item['product_id']) ? (Product::find($item['product_id'])?->canonical_name ?? '') : '');

            if (!empty($item['id'])) {
                $this->currentDocument->lineItems()->where('id', $item['id'])->update([
                    'line_no' => $item['line_no'],
                    'material_code' => $item['material_code'],
                    'description' => $desc,
                    'qty' => $item['qty'],
                    'unit' => $item['unit'],
                    'unit_price' => $item['unit_price'],
                    'discounted_price' => $item['discounted_price'] ?? null,
                    'printed_total' => $item['printed_total'],
                    'product_id' => $item['product_id'],
                ]);
            } else {
                $this->currentDocument->lineItems()->create([
                    'line_no' => $item['line_no'],
                    'material_code' => $item['material_code'],
                    'description' => $desc,
                    'qty' => $item['qty'],
                    'unit' => $item['unit'],
                    'unit_price' => $item['unit_price'],
                    'discounted_price' => $item['discounted_price'] ?? null,
                    'printed_total' => $item['printed_total'],
                    'product_id' => $item['product_id'],
                ]);
            }
        }

        app(ReconcileDocumentTotals::class)->execute($this->currentDocument);
        $this->loadDocument($this->currentDocument->id);

        Notification::make()->title('Draft saved and math re-calculated.')->success()->send();
    }

    public function approveAndVerify(): void
    {
        if (!auth()->user()?->canVerifyDocuments()) {
            Notification::make()
                ->title('Viewing Mode Only')
                ->body('Only Operations Managers and Administrators can commit verified transactions to the master ledger.')
                ->warning()
                ->send();
            return;
        }

        if (!$this->currentDocument) {
            return;
        }

        $docType = $this->currentDocument->document_type;
        $this->saveDraft();

        try {
            $verifier = app(VerifyDocument::class);
            $transaction = $verifier->execute(
                $this->currentDocument,
                auth()->user() ?: \App\Models\User::first(),
                $this->editableItems
            );

            Notification::make()
                ->title('Document Verified & Committed')
                ->body("Reconciled into Transaction {$transaction->transaction_code} for ₱" . number_format($transaction->final_amount, 2))
                ->success()
                ->send();

            if ($docType === Document::TYPE_VENDORS_AGREEMENT) {
                $this->redirect(\App\Filament\Resources\QuotationResource::getUrl());
                return;
            } elseif (in_array($docType, [Document::TYPE_PURCHASE_ORDER, Document::TYPE_ORDER_SLIP])) {
                $this->redirect(\App\Filament\Resources\PurchaseOrderResource::getUrl());
                return;
            }

            $this->loadNextDocument();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Verification Error')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function rejectDocument(): void
    {
        if (!auth()->user()?->canVerifyDocuments()) {
            Notification::make()
                ->title('Viewing Mode Only')
                ->body('Only Operations Managers and Administrators can reject ingested documents.')
                ->warning()
                ->send();
            return;
        }

        if (!$this->currentDocument) {
            return;
        }

        $reason = !empty(trim($this->rejectionReason ?? ''))
            ? trim($this->rejectionReason)
            : 'Rejected by reviewer during verification.';

        $this->currentDocument->update([
            'status' => Document::STATUS_REJECTED,
            'failure_reason' => $reason,
        ]);

        Quotation::where('document_id', $this->currentDocument->id)->update([
            'status' => Quotation::STATUS_REJECTED,
            'rejection_reason' => $reason,
        ]);

        PurchaseOrder::where('document_id', $this->currentDocument->id)->update([
            'status' => PurchaseOrder::STATUS_REJECTED,
        ]);

        $this->rejectionReason = '';

        Notification::make()
            ->title('Document Marked as Rejected')
            ->body('The document has been rejected and removed from the active review queue.')
            ->warning()
            ->send();

        $this->dispatch('close-modal', id: 'reject-document-modal');

        $this->loadNextDocument();
    }


    public function getPendingCountProperty(): int
    {
        return Document::where('status', Document::STATUS_REQUIRES_REVIEW)->count();
    }

    public function getMismatchCountProperty(): int
    {
        return Document::where('status', Document::STATUS_REQUIRES_REVIEW)
            ->whereHas('totals', function ($q) {
                $q->where('vat_mismatch', true)->orWhere('total_mismatch', true);
            })
            ->count();
    }

    public function getVendorsProperty(): array
    {
        return \Illuminate\Support\Facades\Cache::remember('lookup_vendors_list', 120, fn() => Vendor::pluck('name', 'id')->toArray());
    }

    public function getProjectsProperty(): array
    {
        return \Illuminate\Support\Facades\Cache::remember('lookup_projects_list', 120, fn() => Project::pluck('name', 'id')->toArray());
    }

    public function getProductsProperty(): array
    {
        return \Illuminate\Support\Facades\Cache::remember('lookup_products_list', 120, fn() => Product::pluck('canonical_name', 'id')->toArray());
    }

    public function getSkuOptionsProperty(): array
    {
        return Product::getSkuOptions();
    }

    public function getUnitOptionsProperty(): array
    {
        return \App\Enums\UnitOfMeasure::options();
    }
}

