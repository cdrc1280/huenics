<?php

namespace App\Filament\Resources\PurchaseOrderResource\Pages;

use App\Filament\Pages\DeliveryMonitoringPage;
use App\Filament\Resources\PurchaseOrderResource;
use App\Models\DeliveryReceipt;
use App\Models\PurchaseOrder;
use App\Models\SalesInvoice;
use App\Services\OrderFulfillmentService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewPurchaseOrder extends ViewRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    public function getContentTabLabel(): ?string
    {
        return 'Order Details & Verification';
    }

    public function getContentTabIcon(): string | \BackedEnum | \Illuminate\Contracts\Support\Htmlable | null
    {
        return 'heroicon-o-shopping-bag';
    }

    protected function getHeaderActions(): array
    {
        return [
            PurchaseOrderResource::getLinkToQuotationAction(),

            Action::make('view_discrepancies')
                ->label(fn(): string => $this->record->hasLineItemDiscrepancies() ? 'Line Item Discrepancies' : 'Reconciliation Report')
                ->icon('heroicon-m-scale')
                ->color(fn(): string => $this->record->hasLineItemDiscrepancies() ? 'warning' : 'info')
                ->tooltip('View side-by-side line item comparison and discrepancy analysis against linked quotation')
                ->visible(fn(): bool => (bool) $this->record->quotation_id)
                ->modalHeading(fn(): string => "PO #{$this->record->po_number} vs Quotation #{$this->record->quotation?->quotation_number} Line Item Reconciliation")
                ->modalDescription('Detailed comparison of quantities, unit prices, and line items between this Purchase Order and its connected Quotation.')
                ->modalWidth('5xl')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close')
                ->modalContent(fn() => view('filament.infolists.po-quotation-reconciliation', [
                    'reconciliation' => $this->record->getReconciliationReport(),
                    'getRecord' => fn() => $this->record,
                ])),

            Action::make('toggle_conforme')
                ->label(fn(): string => $this->record->is_conforme_po ? 'Switch to Normal PO' : 'Switch to Conforme PO')
                ->icon('heroicon-m-arrows-right-left')
                ->color('gray')
                ->tooltip(fn(): string => $this->record->is_conforme_po 
                    ? 'Convert to Normal PO (requires linking to an approved quotation)' 
                    : 'Convert to Conforme PO (exempt from quotation matching)')
                ->visible(fn(): bool => !$this->record->trashed() && !$this->record->isApproved() && $this->record->status !== PurchaseOrder::STATUS_CANCELLED && $this->record->status !== PurchaseOrder::STATUS_REJECTED)
                ->requiresConfirmation()
                ->modalHeading(fn(): string => $this->record->is_conforme_po ? 'Switch to Normal Purchase Order' : 'Switch to Conforme Purchase Order')
                ->modalDescription(fn(): string => $this->record->is_conforme_po 
                    ? 'Switching to Normal PO will require this purchase order to be linked to an approved quotation before Review and Approval.' 
                    : 'Switching to Conforme PO exempts this purchase order from quotation matching, immediately unlocking Review and Approval.')
                ->action(function () {
                    $newVal = !$this->record->is_conforme_po;
                    $this->record->update(['is_conforme_po' => $newVal]);
                    $typeLabel = $newVal ? 'Conforme PO' : 'Normal PO';
                    Notification::make()->title("PO Classification Updated to {$typeLabel}")->success()->send();
                }),

            Action::make('approve_po')
                ->label('Approve PO')
                ->icon('heroicon-m-check-circle')
                ->color('success')
                ->tooltip(function (): string {
                    if (!$this->record->is_conforme_po && !$this->record->quotation_id) {
                        return 'Normal PO must be linked to an approved quotation first before approval.';
                    }
                    return 'Approve purchase order to authorize fulfillment and delivery';
                })
                ->visible(fn(): bool => !$this->record->trashed() && !$this->record->isApproved() && $this->record->status !== PurchaseOrder::STATUS_CANCELLED && $this->record->status !== PurchaseOrder::STATUS_REJECTED && ($this->record->is_conforme_po || (bool) $this->record->quotation_id) && !$this->record->hasLineItemDiscrepancies())
                ->disabled(fn(): bool => (!$this->record->is_conforme_po && !$this->record->quotation_id) || $this->record->hasLineItemDiscrepancies())
                ->requiresConfirmation(fn(): bool => $this->record->is_conforme_po || (bool) $this->record->quotation_id)
                ->action(function () {
                    if (!$this->record->is_conforme_po && !$this->record->quotation_id) {
                        Notification::make()
                            ->title('Quotation Link Required')
                            ->body("PO {$this->record->po_number} is a normal purchase order and must be linked to an approved quotation first.")
                            ->warning()
                            ->send();
                        return;
                    }
                    if ($this->record->hasLineItemDiscrepancies()) {
                        Notification::make()
                            ->title('Approval Restricted: Line Item Discrepancies')
                            ->body("PO {$this->record->po_number} has line item discrepancies with linked Quotation #{$this->record->quotation?->quotation_number}. Discrepancies must be resolved before approval.")
                            ->danger()
                            ->send();
                        return;
                    }
                    $this->record->update(['status' => PurchaseOrder::STATUS_APPROVED]);
                    if ($this->record->document) {
                        $this->record->document->update(['status' => \App\Models\Document::STATUS_VERIFIED]);
                    }
                    Notification::make()->title('Purchase Order Approved')->body("PO {$this->record->po_number} is now approved and verified for delivery.")->success()->send();
                }),

            Action::make('review')
                ->label('Review & Verify')
                ->icon('heroicon-m-clipboard-document-check')
                ->color('warning')
                ->visible(fn(): bool => !$this->record->trashed() && !$this->record->isReviewed() && !$this->record->isApproved() && $this->record->status !== PurchaseOrder::STATUS_CANCELLED && $this->record->status !== PurchaseOrder::STATUS_REJECTED && (bool) $this->record->document_id && ($this->record->is_conforme_po || (bool) $this->record->quotation_id))
                ->url(fn() => $this->record->document_id ? \App\Filament\Pages\ReviewQueuePage::getUrl(['document_id' => $this->record->document_id]) : null),

            Action::make('mark_delivered')
                ->label('Mark as Delivered')
                ->icon('heroicon-m-check-badge')
                ->color('success')
                ->tooltip('DR & SI are verified and attached. Mark this purchase order as delivered to deduct inventory and realize sales.')
                ->visible(fn(): bool => !$this->record->trashed() && $this->record->isApproved() && $this->record->hasBothDrAndSi() && $this->record->delivery_status !== PurchaseOrder::DELIVERY_DELIVERED)
                ->modalHeading(fn(): string => "Mark as Delivered: PO #{$this->record->po_number}")
                ->modalDescription('Both Delivery Receipt (DR) and Sales Invoice (SI) are verified and attached. Confirming delivery will finalize this order, deduct stock, and record sales.')
                ->form([
                    DatePicker::make('actual_delivery_date')
                        ->label('Actual Delivery Date')
                        ->default(now())
                        ->required(),
                    Toggle::make('has_warranty')
                        ->label('Include Warranty')
                        ->default(fn() => $this->record->has_warranty ?? true)
                        ->live(),
                    Select::make('warranty_period')
                        ->label('Warranty Period')
                        ->options(PurchaseOrder::getWarrantyPeriodOptions())
                        ->default(fn() => $this->record->warranty_period ?? PurchaseOrder::WARRANTY_1_YEAR)
                        ->visible(fn($get) => (bool) $get('has_warranty')),
                ])
                ->action(function (array $data) {
                    try {
                        app(OrderFulfillmentService::class)->completeDelivery($this->record, $data);

                        Notification::make()
                            ->title('PO Marked as Delivered')
                            ->body("PO {$this->record->po_number} marked as Delivered. Stocks deducted from catalog and sales realized.")
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Delivery Confirmation Failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('upload_dr_si')
                ->label('Upload DR & SI')
                ->icon('heroicon-m-arrow-up-tray')
                ->color('primary')
                ->tooltip('Upload physical Delivery Receipt (DR) and Sales Invoice (SI) hard copies (Images/PDF)')
                ->visible(fn(): bool => !$this->record->trashed() && $this->record->isApproved() && !$this->record->isCompleted())
                ->modalHeading(fn(): string => "Upload Hard Copies (DR & SI): PO #{$this->record->po_number}")
                ->modalDescription('Upload physical hard copies of both Delivery Receipt (DR) and Sales Invoice (SI) in PDF or Image format.')
                ->modalWidth('4xl')
                ->form([
                    Section::make('Delivery Receipt (DR) Details & Upload')
                        ->description('Attach physical/scanned delivery receipt (PDF, JPG, PNG, WEBP)')
                        ->icon('heroicon-o-truck')
                        ->schema([
                            Grid::make(2)->schema([
                                FileUpload::make('dr_file')
                                    ->label('Delivery Receipt File (PDF / Image)')
                                    ->required()
                                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(25600)
                                    ->disk('local')
                                    ->directory('documents/dr')
                                    ->preserveFilenames()
                                    ->helperText('Supported formats: PDF, JPG, PNG, WEBP (Max 25MB)')
                                    ->columnSpan(2),

                                TextInput::make('dr_number')
                                    ->label('DR Number')
                                    ->default(fn() => DeliveryReceipt::generateNumber())
                                    ->required(),

                                DatePicker::make('delivery_date')
                                    ->label('Delivery Date')
                                    ->default(fn() => $this->record->actual_delivery_date ?? now())
                                    ->required(),

                                TextInput::make('delivered_by')
                                    ->label('Delivered By')
                                    ->placeholder('Driver or logistics personnel'),

                                TextInput::make('received_by')
                                    ->label('Received By (Client / Site Receiver)')
                                    ->default(fn() => $this->record->customer_name)
                                    ->placeholder('Customer site receiver name')
                                    ->helperText('Name of the client or site personnel who received the delivery'),
                            ]),
                        ]),

                    Section::make('Sales Invoice (SI) Details & Upload')
                        ->description('Attach physical/scanned sales invoice (PDF, JPG, PNG, WEBP)')
                        ->icon('heroicon-o-receipt-percent')
                        ->schema([
                            Grid::make(2)->schema([
                                FileUpload::make('si_file')
                                    ->label('Sales Invoice File (PDF / Image)')
                                    ->required()
                                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(25600)
                                    ->disk('local')
                                    ->directory('documents/si')
                                    ->preserveFilenames()
                                    ->helperText('Supported formats: PDF, JPG, PNG, WEBP (Max 25MB)')
                                    ->columnSpan(2),

                                TextInput::make('si_number')
                                    ->label('SI Number')
                                    ->default(fn() => SalesInvoice::generateNumber())
                                    ->required(),

                                DatePicker::make('invoice_date')
                                    ->label('Invoice Date')
                                    ->default(now())
                                    ->required(),

                                Select::make('payment_status')
                                    ->label('Payment Status')
                                    ->options([
                                        SalesInvoice::STATUS_PAID => 'Paid',
                                        SalesInvoice::STATUS_UNPAID => 'Unpaid',
                                        SalesInvoice::STATUS_PARTIAL => 'Partial',
                                    ])
                                    ->default(SalesInvoice::STATUS_PAID)
                                    ->required(),

                                TextInput::make('total_amount')
                                    ->label('Invoice Total (₱)')
                                    ->numeric()
                                    ->prefix('₱')
                                    ->default(fn() => (float) $this->record->order_amount)
                                    ->required(),
                            ]),
                        ]),

                    Toggle::make('auto_mark_delivered')
                        ->label('Mark order as Delivered immediately upon upload')
                        ->helperText('If enabled, will immediately deduct stock and realize sales. If disabled, DR & SI will be attached and verified, unlocking the "Mark as Delivered" action button.')
                        ->default(true),
                ])
                ->action(function (array $data) {
                    try {
                        if (!empty($data['auto_mark_delivered'])) {
                            $result = app(OrderFulfillmentService::class)->fulfillOrder($this->record, $data);
                            $drNo = $result['delivery_receipt']->dr_number;
                            $siNo = $result['sales_invoice']->si_number;

                            Notification::make()
                                ->title('PO Marked Delivered & Completed')
                                ->body("PO {$this->record->po_number} marked as Delivered with DR #{$drNo} and SI #{$siNo}. Stocks deducted and sales realized.")
                                ->success()
                                ->send();
                        } else {
                            $result = app(OrderFulfillmentService::class)->attachFulfillmentDocuments($this->record, $data);
                            $drNo = $result['delivery_receipt']->dr_number;
                            $siNo = $result['sales_invoice']->si_number;

                            Notification::make()
                                ->title('DR & SI Uploaded and Verified')
                                ->body("DR #{$drNo} and SI #{$siNo} attached. 'Mark as Delivered' is now available to finalize the order.")
                                ->success()
                                ->send();
                        }
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Fulfillment Failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('delivery_tracker')
                ->label('Delivery Tracker')
                ->icon('heroicon-m-truck')
                ->color('info')
                ->tooltip('Open Delivery & Warranty Tracker for this purchase order')
                ->visible(fn(): bool => !$this->record->trashed() && $this->record->isApproved() && ($this->record->delivery_status === PurchaseOrder::DELIVERY_DELIVERED || $this->record->status === PurchaseOrder::STATUS_DELIVERED))
                ->url(fn() => DeliveryMonitoringPage::getUrl()),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Purchase Order Details')
                ->columns(3)
                ->schema([
                    TextEntry::make('po_number')->label('PO #')->weight('bold'),
                    TextEntry::make('customer_name')->label('Customer'),
                    TextEntry::make('salesAgent.name')->label('Sales Agent')->default('Unassigned'),
                    TextEntry::make('quotation.quotation_number')->label('Linked Quotation')->default('—'),
                    TextEntry::make('project.name')->label('Project')->default('—'),
                    TextEntry::make('order_date')->label('Order Date')->date('M j, Y'),
                    TextEntry::make('status')->label('Status')->badge()
                        ->color(fn(string $state) => match ($state) {
                            PurchaseOrder::STATUS_DELIVERED => 'success',
                            PurchaseOrder::STATUS_CANCELLED => 'danger',
                            default => 'warning',
                        }),
                    TextEntry::make('is_conforme_po')
                        ->label('PO Classification')
                        ->badge()
                        ->formatStateUsing(fn(bool $state) => $state ? 'Conforme PO' : 'Normal PO')
                        ->color(fn(bool $state) => $state ? 'info' : 'gray'),
                ]),

            Section::make('Financials')
                ->columns(3)
                ->schema([
                    TextEntry::make('order_amount')->label('Order Amount')->money('PHP'),
                    TextEntry::make('computed_vat')->label('Computed VAT (12%)')->money('PHP'),
                    TextEntry::make('realized_profit')->label('Realized Profit')->money('PHP')
                        ->color(fn($state) => $state > 0 ? 'success' : 'danger'),
                ]),

            Section::make('Delivery & Warranty')
                ->columns(3)
                ->schema([
                    TextEntry::make('delivery_status')->label('Delivery Status')->badge()
                        ->color(fn(string $state) => match ($state) {
                            PurchaseOrder::DELIVERY_DELIVERED => 'success',
                            PurchaseOrder::DELIVERY_OVERDUE => 'danger',
                            PurchaseOrder::DELIVERY_TRANSIT => 'info',
                            default => 'warning',
                        }),
                    TextEntry::make('actual_delivery_date')->label('Delivered On')->date('M j, Y')->placeholder('Not yet delivered'),
                    TextEntry::make('delivery_receipt_numbers_string')
                        ->label('Linked DR #s')
                        ->badge()
                        ->color('info')
                        ->placeholder('—'),
                    TextEntry::make('sales_invoice_numbers_string')
                        ->label('Linked SI #s')
                        ->badge()
                        ->color('success')
                        ->placeholder('—'),
                    TextEntry::make('total_invoiced_amount')
                        ->label('Total Invoiced')
                        ->money('PHP'),
                    TextEntry::make('warranty_status')->label('Warranty Status')->badge()
                        ->color(fn(string $state) => match ($state) {
                            PurchaseOrder::WARRANTY_ACTIVE => 'success',
                            PurchaseOrder::WARRANTY_EXPIRING => 'warning',
                            PurchaseOrder::WARRANTY_EXPIRED => 'danger',
                            default => 'gray',
                        }),
                    TextEntry::make('warranty_period')
                        ->label('Warranty Period')
                        ->formatStateUsing(fn(string $state): string => PurchaseOrder::getWarrantyPeriodOptions()[$state] ?? $state),
                    TextEntry::make('warranty_end_date')->label('Warranty Expires')->date('M j, Y')->placeholder('—'),
                ]),

            Section::make('Terms & Conditions')
                ->columns(3)
                ->schema([
                    TextEntry::make('payment_terms')->label('Payment Terms')->default('—'),
                    TextEntry::make('delivery_terms')->label('Delivery Terms')->default('—'),
                    TextEntry::make('terms_and_conditions')->label('Terms & Conditions')->columnSpanFull()->default('—'),
                ]),

            Section::make('Quotation Reconciliation & Line Item Comparison')
                ->description('Detailed line-by-line comparison and discrepancy verification against linked Quotation')
                ->icon('heroicon-m-scale')
                ->visible(fn(): bool => (bool) $this->record->quotation_id)
                ->schema([
                    \Filament\Infolists\Components\ViewEntry::make('reconciliation')
                        ->label('')
                        ->view('filament.infolists.po-quotation-reconciliation'),
                ]),

            Section::make('Line Items')
                ->schema([
                    RepeatableEntry::make('lineItems')
                        ->label('')
                        ->columns(6)
                        ->schema([
                            TextEntry::make('line_no')->label('#')->columnSpan(1),
                            TextEntry::make('item_code')->label('Item Code')->default('—')->columnSpan(1),
                            TextEntry::make('description')->label('Product / Description')->columnSpan(2),
                            TextEntry::make('qty')->label('Qty')->columnSpan(1),
                            TextEntry::make('unit')->label('Unit')->columnSpan(1),
                            TextEntry::make('unit_price')->label('Unit Price')->money('PHP')->columnSpan(1),
                            TextEntry::make('line_total')->label('Total')->money('PHP')->columnSpan(1),
                        ]),
                ]),
        ]);
    }
}
