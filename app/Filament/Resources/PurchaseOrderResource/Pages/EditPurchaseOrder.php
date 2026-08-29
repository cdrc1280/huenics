<?php

namespace App\Filament\Resources\PurchaseOrderResource\Pages;

use App\Filament\Pages\DeliveryMonitoringPage;
use App\Filament\Resources\PurchaseOrderResource;
use App\Models\DeliveryReceipt;
use App\Models\PurchaseOrder;
use App\Models\SalesInvoice;
use App\Services\OrderFulfillmentService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

class EditPurchaseOrder extends EditRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            PurchaseOrderResource::getLinkToQuotationAction(),
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
                ->visible(fn(): bool => !$this->record->trashed() && !$this->record->isApproved() && $this->record->status !== PurchaseOrder::STATUS_CANCELLED && $this->record->status !== PurchaseOrder::STATUS_REJECTED)
                ->disabled(fn(): bool => !$this->record->is_conforme_po && !$this->record->quotation_id)
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
                    $this->record->update(['status' => PurchaseOrder::STATUS_APPROVED]);
                    if ($this->record->document) {
                        $this->record->document->update(['status' => \App\Models\Document::STATUS_VERIFIED]);
                    }
                    $this->refreshFormData(['status']);
                    Notification::make()->title('Purchase Order Approved')->body("PO {$this->record->po_number} is now approved and verified for delivery.")->success()->send();
                }),

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
                        $this->refreshFormData(['delivery_status', 'status', 'is_completed', 'completed_at', 'delivery_receipt_no', 'actual_delivery_date', 'has_warranty', 'warranty_period', 'warranty_status']);

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

                            $this->refreshFormData(['delivery_status', 'status', 'is_completed', 'completed_at', 'delivery_receipt_no', 'actual_delivery_date']);

                            Notification::make()
                                ->title('PO Marked Delivered & Completed')
                                ->body("PO {$this->record->po_number} marked as Delivered with DR #{$drNo} and SI #{$siNo}. Stocks deducted and sales realized.")
                                ->success()
                                ->send();
                        } else {
                            $result = app(OrderFulfillmentService::class)->attachFulfillmentDocuments($this->record, $data);
                            $drNo = $result['delivery_receipt']->dr_number;
                            $siNo = $result['sales_invoice']->si_number;

                            $this->refreshFormData(['delivery_receipt_no', 'actual_delivery_date']);

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

            DeleteAction::make()->requiresConfirmation(),
            RestoreAction::make()->requiresConfirmation()->visible(fn(): bool => $this->record->trashed()),
            ForceDeleteAction::make()->requiresConfirmation()->visible(fn(): bool => $this->record->trashed() && (auth()->user()?->canDeleteRecords() ?? false)),
        ];
    }
}
