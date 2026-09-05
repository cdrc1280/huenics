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

    // Terms & Official PO / Conforme
    public ?string $termsAndConditions = '';
    public ?string $paymentTerms = '';
    public ?string $deliveryTerms = '';
    public bool $isOfficialPo = false;
    public ?string $customerSignatureName = '';
    public ?string $customerSignedAt = '';

    // Structured Terms & Conditions Checkboxes (Matching authentic Huenics Quotation / VAF form)
    public string $tcValidity = '15 days';
    public bool $tcStock = false;
    public bool $tcNonStock = true;
    public bool $tcDelivery4To7 = false;
    public bool $tcDelivery10To15 = false;
    public bool $tcDelivery45To60 = true;
    public bool $tcPaymentCodDp = true;
    public bool $tcPaymentApproved = false;
    public bool $tcRemarksOfficialPo = false;
    public bool $tcRemarksNonReturnable = true;

    // Real-Time Synchronized Preview State
    public string $previewMode = 'live'; // 'live' or 'pdf'
    public array $originalState = [];
    public ?string $rejectionReason = '';

    // Deletion Modal State
    public ?int $confirmingDeleteIndex = null;

    // Photo Preview Modal State
    public ?string $previewPhotoUrl = null;
    public ?string $previewPhotoTitle = null;
    public ?string $previewPhotoSku = null;
    public ?int $previewPhotoLineNo = null;

    public function setPreviewMode(string $mode): void
    {
        $this->previewMode = $mode;
    }

    public function updated($propertyName): void
    {
        if (str_starts_with((string) $propertyName, 'tc')) {
            $this->syncTermsData();
        }
        if ($propertyName === 'isOfficialPo') {
            $this->tcRemarksOfficialPo = (bool) $this->isOfficialPo;
            $this->syncTermsData();
        }
    }

    public function syncTermsData(): void
    {
        // 1. Sync Official PO flag with remarks
        $this->isOfficialPo = (bool) $this->tcRemarksOfficialPo;

        // 2. Format Payment Terms string
        $payments = [];
        if ($this->tcPaymentCodDp) {
            $payments[] = 'COD / 50% DP ; 50% PDC 30 Days';
        }
        if ($this->tcPaymentApproved) {
            $payments[] = 'Approved Terms';
        }
        if (!empty($payments)) {
            $this->paymentTerms = implode(', ', $payments);
        }

        // 3. Format Delivery Terms string
        $deliveries = [];
        if ($this->tcDelivery4To7) {
            $deliveries[] = '4-7 days';
        }
        if ($this->tcDelivery10To15) {
            $deliveries[] = '10-15 days';
        }
        if ($this->tcDelivery45To60) {
            $deliveries[] = '45-60 days';
        }
        if (!empty($deliveries)) {
            $this->deliveryTerms = implode(', ', $deliveries);
        }

        // 4. Structured JSON data for persistence
        $structured = [
            'validity' => $this->tcValidity,
            'stock' => $this->tcStock,
            'non_stock' => $this->tcNonStock,
            'delivery_4_7' => $this->tcDelivery4To7,
            'delivery_10_15' => $this->tcDelivery10To15,
            'delivery_45_60' => $this->tcDelivery45To60,
            'payment_cod_dp' => $this->tcPaymentCodDp,
            'payment_approved' => $this->tcPaymentApproved,
            'remarks_official_po' => $this->tcRemarksOfficialPo,
            'remarks_non_returnable' => $this->tcRemarksNonReturnable,
        ];
        $this->termsAndConditions = json_encode($structured);
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
            'tcValidity' => $this->tcValidity,
            'tcStock' => $this->tcStock,
            'tcNonStock' => $this->tcNonStock,
            'tcDelivery4To7' => $this->tcDelivery4To7,
            'tcDelivery10To15' => $this->tcDelivery10To15,
            'tcDelivery45To60' => $this->tcDelivery45To60,
            'tcPaymentCodDp' => $this->tcPaymentCodDp,
            'tcPaymentApproved' => $this->tcPaymentApproved,
            'tcRemarksOfficialPo' => $this->tcRemarksOfficialPo,
            'tcRemarksNonReturnable' => $this->tcRemarksNonReturnable,
        ];

        $json = json_encode($payload);
        $encoded = rtrim(strtr(base64_encode($json), '+/', '-_'), '=');
        return route('documents.live-pdf', ['document' => $this->currentDocument->id, 'payload' => $encoded, 'v' => substr(md5($encoded), 0, 8)]);
    }

    // Cross reference details
    public ?Document $crossRefQuotation = null;
    public ?Document $crossRefPO = null;
    public ?Transaction $existingTransaction = null;

    public function mount(?int $document_id = null): void
    {
        $docId = $document_id ?: request()->query('document_id');

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
                            } elseif ($docType === Document::TYPE_PURCHASE_ORDER) {
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

        $this->termsAndConditions = $doc->terms_and_conditions ?? '';
        $this->paymentTerms = $doc->payment_terms ?? '';
        $this->deliveryTerms = $doc->delivery_terms ?? '';

        if ($doc->document_type === Document::TYPE_VENDORS_AGREEMENT) {
            $quotation = Quotation::where('document_id', $doc->id)->first();
            if ($quotation) {
                $this->isOfficialPo = (bool) $quotation->is_official_po;
                $this->customerSignatureName = $quotation->customer_signature_name ?? '';
                $this->customerSignedAt = $quotation->customer_signed_at ? $quotation->customer_signed_at->format('Y-m-d') : '';
            } else {
                $this->isOfficialPo = false;
                $this->customerSignatureName = '';
                $this->customerSignedAt = '';
            }
        } else {
            $this->isOfficialPo = false;
            $this->customerSignatureName = '';
            $this->customerSignedAt = '';
        }

        // Parse Structured Terms & Conditions Checkboxes
        $rawTc = (string) ($doc->terms_and_conditions ?? '');
        $tcJson = json_decode($rawTc, true);

        if (is_array($tcJson) && (isset($tcJson['stock']) || isset($tcJson['non_stock']) || isset($tcJson['validity']))) {
            $this->tcValidity = $tcJson['validity'] ?? '15 days';
            $this->tcStock = (bool) ($tcJson['stock'] ?? false);
            $this->tcNonStock = (bool) ($tcJson['non_stock'] ?? true);
            $this->tcDelivery4To7 = (bool) ($tcJson['delivery_4_7'] ?? false);
            $this->tcDelivery10To15 = (bool) ($tcJson['delivery_10_15'] ?? false);
            $this->tcDelivery45To60 = (bool) ($tcJson['delivery_45_60'] ?? true);
            $this->tcPaymentCodDp = (bool) ($tcJson['payment_cod_dp'] ?? true);
            $this->tcPaymentApproved = (bool) ($tcJson['payment_approved'] ?? false);
            $this->tcRemarksOfficialPo = (bool) ($tcJson['remarks_official_po'] ?? false);
            $this->tcRemarksNonReturnable = (bool) ($tcJson['remarks_non_returnable'] ?? true);
        } else {
            $combinedText = ($doc->raw_extracted_text ?? '') . ' ' . $rawTc;

            // Validity
            if (preg_match('/Validity\s*[:\.]?\s*(\d+\s*days)/i', $combinedText, $m)) {
                $this->tcValidity = trim($m[1]);
            } else {
                $this->tcValidity = '15 days';
            }

            // Stock Availability
            $this->tcStock = (bool) preg_match('/(?:[✔✓v\[x\]■]\s*Stock\b|Stock\s*[✔✓v\[x\]■])/i', $combinedText);
            $this->tcNonStock = (bool) (preg_match('/(?:[✔✓v\[x\]■]\s*Non-Stock|Non-Stock.*?Special.*?Items)/i', $combinedText) || !$this->tcStock);

            // Terms of Delivery
            $this->tcDelivery4To7 = (bool) preg_match('/(?:[✔✓v\[x\]■]\s*4-7\s*days|4-7\s*days\s*[✔✓v\[x\]■])/i', $combinedText);
            $this->tcDelivery10To15 = (bool) preg_match('/(?:[✔✓v\[x\]■]\s*10-15\s*days|10-15\s*days\s*[✔✓v\[x\]■])/i', $combinedText);
            $this->tcDelivery45To60 = (bool) (preg_match('/(?:[✔✓v\[x\]■]\s*(?:45-60|30-45)\s*days|(?:45-60|30-45)\s*days\s*[✔✓v\[x\]■])/i', $combinedText) || (!$this->tcDelivery4To7 && !$this->tcDelivery10To15));

            // Payment Terms
            $this->tcPaymentCodDp = (bool) (preg_match('/(?:[✔✓v\[x\]■]\s*(?:COD|50\%\s*DP)|(?:COD|50\%\s*DP).*?PDC)/i', $combinedText) || true);
            $this->tcPaymentApproved = (bool) preg_match('/(?:[✔✓v\[x\]■]\s*Approved\s*Terms|Approved\s*Terms\s*[✔✓v\[x\]■])/i', $combinedText);

            // Remarks
            $this->tcRemarksOfficialPo = (bool) (preg_match('/(?:[✔✓v\[x\]■]\s*Serve\s*as\s*(?:an\s*)?Official\s*P\.?O\.?|Serve\s*as\s*(?:an\s*)?Official\s*P\.?O\.?\s*[✔✓v\[x\]■])/i', $combinedText) || $this->isOfficialPo);
            $this->tcRemarksNonReturnable = (bool) (preg_match('/(?:[✔✓v\[x\]■]\s*Non-\s*Returnable|Non-\s*Returnable.*?[✔✓v\[x\]■]|Non-\s*Cancealable)/i', $combinedText) || true);
        }

        if ($this->isOfficialPo) {
            $this->tcRemarksOfficialPo = true;
        } elseif ($this->tcRemarksOfficialPo) {
            $this->isOfficialPo = true;
        }

        $this->syncTermsData();

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
        } elseif ($docType === Document::TYPE_PURCHASE_ORDER) {
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

        // Only Sales Executive, Operations Manager, and Admin can edit
        if (!$user->canEditQuotationDocument()) {
            return true;
        }

        return false;
    }

    public function getIsUnlinkedNormalPoProperty(): bool
    {
        if (!$this->currentDocument || $this->currentDocument->document_type !== \App\Models\Document::TYPE_PURCHASE_ORDER) {
            return false;
        }

        $po = \App\Models\PurchaseOrder::where('document_id', $this->currentDocument->id)->first();
        if ($po) {
            return !$po->is_conforme_po && !$po->quotation_id;
        }

        return false;
    }

    public function getReconciliationProperty(): ?array
    {
        if (!$this->currentDocument || $this->currentDocument->document_type !== \App\Models\Document::TYPE_PURCHASE_ORDER) {
            return null;
        }

        $po = \App\Models\PurchaseOrder::where('document_id', $this->currentDocument->id)->first();
        if ($po && $po->quotation_id) {
            $po->unsetRelation('quotation');
            $po->unsetRelation('lineItems');
            return $po->getReconciliationReport();
        }

        return null;
    }

    public function getIsPoWithDiscrepancyProperty(): bool
    {
        return (bool) ($this->reconciliation['has_discrepancies'] ?? false);
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

    public function recalculateAllFigures(): void
    {
        $subtotal = 0.0;
        foreach ($this->editableItems as $index => $item) {
            $qty = isset($item['qty']) ? (float) $item['qty'] : 0.0;
            $unitPrice = isset($item['unit_price']) ? (float) $item['unit_price'] : 0.0;
            $discPrice = isset($item['discounted_price']) && (float) $item['discounted_price'] > 0 ? (float) $item['discounted_price'] : $unitPrice;
            $computed = round($qty * $discPrice, 2);

            $this->editableItems[$index]['computed_total'] = $computed;
            $this->editableItems[$index]['printed_total'] = $computed;
            $this->editableItems[$index]['total_mismatch'] = false;
            $subtotal += $computed;
        }

        $this->printedSubtotal = round($subtotal, 2);
        $this->printedVat = round($subtotal * 0.12, 2);
        $this->printedTotal = round($this->printedSubtotal + $this->printedVat, 2);

        if ($this->negotiatedAmount !== null && $this->negotiatedAmount > $this->printedTotal) {
            $this->negotiatedAmount = $this->printedTotal;
        }

        if ($this->currentDocument) {
            $this->currentDocument->totals()->updateOrCreate(
                ['document_id' => $this->currentDocument->id],
                [
                    'printed_subtotal' => $this->printedSubtotal,
                    'printed_vat' => $this->printedVat,
                    'printed_total' => $this->printedTotal,
                    'computed_subtotal' => $this->printedSubtotal,
                    'computed_vat' => $this->printedVat,
                    'computed_grand_total' => $this->printedTotal,
                    'negotiated_amount' => $this->negotiatedAmount,
                    'vat_mismatch' => false,
                    'total_mismatch' => false,
                ]
            );

            // Synchronize linked Quotation record and items
            if ($this->currentDocument->document_type === Document::TYPE_VENDORS_AGREEMENT) {
                $quotation = Quotation::where('document_id', $this->currentDocument->id)->first();
                if ($quotation) {
                    $quotation->update([
                        'total_amount' => $this->printedTotal,
                        'total_cost' => round($this->printedTotal * 0.7, 2),
                        'estimated_profit' => round($this->printedTotal * 0.3, 2),
                        'negotiated_amount' => $this->negotiatedAmount,
                    ]);

                    $quotation->lineItems()->delete();
                    foreach ($this->editableItems as $idx => $line) {
                        $lineTot = (float) ($line['printed_total'] ?? $line['computed_total']);
                        $effPrice = (float) (!empty($line['discounted_price']) && (float) $line['discounted_price'] > 0 ? $line['discounted_price'] : ($line['unit_price'] ?? 0));
                        $baseCost = round($effPrice * 0.7, 2);
                        $quotation->lineItems()->create([
                            'line_no' => $line['line_no'] ?? ($idx + 1),
                            'item_code' => $line['material_code'] ?? null,
                            'product_id' => $line['product_id'] ?? null,
                            'description' => $line['description'] ?? '',
                            'qty' => $line['qty'] ?? 1,
                            'unit' => $line['unit'] ?? 'pcs',
                            'unit_price' => $line['unit_price'] ?? 0,
                            'discounted_price' => $line['discounted_price'] ?? null,
                            'base_cost' => $baseCost,
                            'line_total' => $lineTot,
                            'gross_profit' => round($lineTot - (($line['qty'] ?? 1) * $baseCost), 2),
                        ]);
                    }
                }
            }

            // Synchronize linked Purchase Order record and items
            if ($this->currentDocument->document_type === Document::TYPE_PURCHASE_ORDER) {
                $po = PurchaseOrder::where('document_id', $this->currentDocument->id)->first();
                if ($po) {
                    $po->update([
                        'order_amount' => $this->printedTotal,
                    ]);

                    $po->lineItems()->delete();
                    foreach ($this->editableItems as $idx => $line) {
                        $lineTot = (float) ($line['printed_total'] ?? $line['computed_total']);
                        $po->lineItems()->create([
                            'line_no' => $line['line_no'] ?? ($idx + 1),
                            'item_code' => $line['material_code'] ?? null,
                            'product_id' => $line['product_id'] ?? null,
                            'description' => $line['description'] ?? '',
                            'qty' => $line['qty'] ?? 1,
                            'unit' => $line['unit'] ?? 'pcs',
                            'unit_price' => $line['unit_price'] ?? 0,
                            'discounted_price' => $line['discounted_price'] ?? null,
                            'line_total' => $lineTot,
                        ]);
                    }
                }
            }

            app(ReconcileDocumentTotals::class)->execute($this->currentDocument);

            $this->currentDocument->refresh();
            $this->currentDocument->load(['totals', 'lineItems']);
        }
    }

    public function updatedEditableItems(): void
    {
        $this->recalculateAllFigures();
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

        $this->recalculateAllFigures();
    }

    public function cloneLineItem(int $index): void
    {
        if (isset($this->editableItems[$index])) {
            $this->pushStateToUndo();
            $item = $this->editableItems[$index];
            $item['id'] = null;

            array_splice($this->editableItems, $index + 1, 0, [$item]);
            $this->reindexLineNumbers();
            $this->recalculateAllFigures();
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

    public function confirmDeleteLineItem(int $index): void
    {
        $this->confirmingDeleteIndex = $index;
        $this->dispatch('open-modal', id: 'delete-line-item-modal');
    }

    public function cancelDeleteLineItem(): void
    {
        $this->confirmingDeleteIndex = null;
        $this->dispatch('close-modal', id: 'delete-line-item-modal');
    }

    public function executeDeleteConfirmed(): void
    {
        if ($this->confirmingDeleteIndex !== null && isset($this->editableItems[$this->confirmingDeleteIndex])) {
            $index = $this->confirmingDeleteIndex;
            $this->confirmingDeleteIndex = null;
            $this->dispatch('close-modal', id: 'delete-line-item-modal');
            $this->removeLineItem($index);
        }
    }

    public function openPhotoPreview(int $index): void
    {
        if (isset($this->editableItems[$index])) {
            $item = $this->editableItems[$index];
            $productId = $item['product_id'] ?? null;
            $sku = $item['material_code'] ?? null;

            $thumbUrl = !empty($productId) ? ($this->productThumbnails[$productId] ?? null) : null;
            if (!$thumbUrl && !empty($sku)) {
                $thumbUrl = $this->productThumbnails['sku:' . $sku] ?? null;
            }

            $this->previewPhotoUrl = $thumbUrl;
            $this->previewPhotoTitle = !empty($item['description']) ? $item['description'] : ($this->products[$productId] ?? ($sku ?? 'Product Photo'));
            $this->previewPhotoSku = $sku;
            $this->previewPhotoLineNo = $item['line_no'] ?? ($index + 1);

            $this->dispatch('open-modal', id: 'image-lightbox-modal');
        }
    }

    public function removeLineItem(int $index): void
    {
        if (isset($this->editableItems[$index])) {
            $this->pushStateToUndo();

            $deletedItem = $this->editableItems[$index];
            $deletedDesc = !empty($deletedItem['description']) ? $deletedItem['description'] : ($deletedItem['material_code'] ?? 'Line Item');
            $lineNo = $deletedItem['line_no'] ?? ($index + 1);

            $itemId = $deletedItem['id'] ?? null;
            if ($itemId && $this->currentDocument) {
                $this->currentDocument->lineItems()->where('id', $itemId)->delete();
            }

            unset($this->editableItems[$index]);
            $this->editableItems = array_values($this->editableItems);
            $this->reindexLineNumbers();
            $this->recalculateAllFigures();

            Notification::make()
                ->title("Line #{$lineNo} Deleted")
                ->body("Deleted: {$deletedDesc}. Figures recomputed: Subtotal ₱" . number_format($this->printedSubtotal, 2) . ", 12% VAT ₱" . number_format($this->printedVat, 2) . ", Grand Total ₱" . number_format($this->printedTotal, 2) . ".")
                ->success()
                ->send();
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
            'terms_and_conditions' => $this->termsAndConditions,
            'payment_terms' => $this->paymentTerms,
            'delivery_terms' => $this->deliveryTerms,
        ]);

        if ($this->currentDocument->document_type === Document::TYPE_VENDORS_AGREEMENT) {
            $quotation = Quotation::where('document_id', $this->currentDocument->id)->first();
            if ($quotation) {
                $quotation->update([
                    'is_official_po' => (bool) $this->isOfficialPo,
                    'customer_signature_name' => $this->customerSignatureName ?: null,
                    'customer_signed_at' => $this->customerSignedAt ? \Carbon\Carbon::parse($this->customerSignedAt) : null,
                    'terms_and_conditions' => $this->termsAndConditions,
                    'payment_terms' => $this->paymentTerms,
                    'delivery_terms' => $this->deliveryTerms,
                ]);
            }
        }

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

        if ($this->isUnlinkedNormalPo) {
            Notification::make()
                ->title('Quotation Link Required')
                ->body('This is a normal purchase order and must be linked to an approved quotation before it can be verified and committed.')
                ->danger()
                ->send();
            return;
        }

        if ($this->isPoWithDiscrepancy) {
            Notification::make()
                ->title('Approval Blocked: Line Item Discrepancies')
                ->body('This purchase order has line item or pricing discrepancies with its linked quotation. Discrepancies must be resolved before verification and approval.')
                ->danger()
                ->send();
            return;
        }

        $docType = $this->currentDocument->document_type;
        $this->saveDraft();

        try {
            $verifier = app(VerifyDocument::class);
            $transaction = $verifier->execute(
                $this->currentDocument,
                auth()->user() ?: \App\Models\User::first(),
                $this->editableItems,
                [
                    'is_official_po' => $this->isOfficialPo,
                    'customer_signature_name' => $this->customerSignatureName,
                    'customer_signed_at' => $this->customerSignedAt,
                ]
            );

            Notification::make()
                ->title('Document Verified & Committed')
                ->body("Reconciled into Transaction {$transaction->transaction_code} for ₱" . number_format($transaction->final_amount, 2))
                ->success()
                ->send();

            if ($docType === Document::TYPE_VENDORS_AGREEMENT) {
                $this->redirect(\App\Filament\Resources\QuotationResource::getUrl());
                return;
            } elseif ($docType === Document::TYPE_PURCHASE_ORDER) {
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

    public function getProductThumbnailsProperty(): array
    {
        return \Illuminate\Support\Facades\Cache::remember('lookup_product_thumbnails', 120, function () {
            $map = [];
            foreach (Product::all(['id', 'sku', 'image_path']) as $p) {
                $url = $p->image_url;
                if ($url) {
                    $map[$p->id] = $url;
                    if (!empty($p->sku)) {
                        $map['sku:' . $p->sku] = $url;
                    }
                }
            }
            return $map;
        });
    }
}

