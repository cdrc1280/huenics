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
use Filament\Forms\Components\Textarea;
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
                ->visible(fn(): bool => !$this->record->trashed() && $this->record->isApproved() && !$this->record->isCompleted() && !$this->record->isDelivered())
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
                        ->helperText('If enabled, will immediately deduct stock and realize sales. If disabled, DR & SI will be attached and verified, unlocking the "Mark as Delivered" action button.'),
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

            Action::make('add_payment_terms')
                ->label(fn(): string => $this->record->payment_term_type ? 'Update Payment Terms' : 'Add Payment Terms')
                ->icon('heroicon-m-credit-card')
                ->color('success')
                ->visible(fn(): bool => !$this->record->trashed() && ($this->record->isDelivered() || $this->record->delivery_status === PurchaseOrder::DELIVERY_DELIVERED || $this->record->status === PurchaseOrder::STATUS_DELIVERED))
                ->modalHeading(fn(): string => "Set Payment Terms: PO #{$this->record->po_number}")
                ->modalDescription('Specify credit payment terms for this delivered purchase order (Strict limit: Max 30 days from delivery).')
                ->modalWidth('2xl')
                ->form([
                    Select::make('payment_term_type')
                        ->label('Payment Terms (Max 30 Days)')
                        ->options(PurchaseOrder::getPaymentTermOptions())
                        ->required()
                        ->live()
                        ->default(fn() => $this->record->payment_term_type ?? PurchaseOrder::PAYMENT_TERM_COD)
                        ->afterStateUpdated(function ($state, callable $set) {
                            $baseDate = $this->record->actual_delivery_date ? \Carbon\Carbon::parse($this->record->actual_delivery_date) : now();
                            $dueDate = match ($state) {
                                PurchaseOrder::PAYMENT_TERM_COD => $baseDate->copy(),
                                PurchaseOrder::PAYMENT_TERM_PDC_7 => $baseDate->copy()->addDays(7),
                                PurchaseOrder::PAYMENT_TERM_PDC_15 => $baseDate->copy()->addDays(15),
                                PurchaseOrder::PAYMENT_TERM_PDC_30, PurchaseOrder::PAYMENT_TERM_CREDIT_30 => $baseDate->copy()->addDays(30),
                                default => $baseDate->copy()->addDays(30),
                            };
                            $set('payment_due_date', $dueDate->format('Y-m-d'));
                        }),

                    DatePicker::make('payment_due_date')
                        ->label('Payment Due Date')
                        ->required()
                        ->default(function () {
                            if ($this->record->payment_due_date) {
                                return $this->record->payment_due_date->format('Y-m-d');
                            }
                            $baseDate = $this->record->actual_delivery_date ? \Carbon\Carbon::parse($this->record->actual_delivery_date) : now();
                            return $baseDate->copy()->addDays(30)->format('Y-m-d');
                        })
                        ->maxDate(fn() => ($this->record->actual_delivery_date ? \Carbon\Carbon::parse($this->record->actual_delivery_date) : now())->addDays(30))
                        ->helperText('Strict ERP rule: Payment terms cannot exceed 30 days from delivery date.'),

                    TextInput::make('pdc_check_number')
                        ->label('PDC Check Number')
                        ->visible(fn($get) => in_array($get('payment_term_type'), [PurchaseOrder::PAYMENT_TERM_PDC_7, PurchaseOrder::PAYMENT_TERM_PDC_15, PurchaseOrder::PAYMENT_TERM_PDC_30]))
                        ->required(fn($get) => in_array($get('payment_term_type'), [PurchaseOrder::PAYMENT_TERM_PDC_7, PurchaseOrder::PAYMENT_TERM_PDC_15, PurchaseOrder::PAYMENT_TERM_PDC_30]))
                        ->default(fn() => $this->record->pdc_check_number)
                        ->placeholder('e.g. CHK-9842103'),

                    TextInput::make('pdc_bank')
                        ->label('Bank Name / Branch')
                        ->visible(fn($get) => in_array($get('payment_term_type'), [PurchaseOrder::PAYMENT_TERM_PDC_7, PurchaseOrder::PAYMENT_TERM_PDC_15, PurchaseOrder::PAYMENT_TERM_PDC_30]))
                        ->required(fn($get) => in_array($get('payment_term_type'), [PurchaseOrder::PAYMENT_TERM_PDC_7, PurchaseOrder::PAYMENT_TERM_PDC_15, PurchaseOrder::PAYMENT_TERM_PDC_30]))
                        ->default(fn() => $this->record->pdc_bank)
                        ->placeholder('e.g. BDO Unibank - Ortigas Center'),

                    TextInput::make('payment_account')
                        ->label('Account Reference / Counter Tag')
                        ->default(fn() => $this->record->payment_account)
                        ->placeholder('e.g. ACCT-MGS-01 / Counter Ticket #884'),

                    Textarea::make('payment_notes')
                        ->label('Payment Notes / Counter Details')
                        ->default(fn() => $this->record->payment_notes)
                        ->placeholder('Enter special instructions, counter schedule, or check release details...')
                        ->rows(2),
                ])
                ->action(function (array $data): void {
                    $termType = $data['payment_term_type'];
                    $dueDate = $data['payment_due_date'];
                    $isPaid = in_array($termType, [
                        PurchaseOrder::PAYMENT_TERM_COD,
                        PurchaseOrder::PAYMENT_TERM_PDC_7,
                        PurchaseOrder::PAYMENT_TERM_PDC_15,
                        PurchaseOrder::PAYMENT_TERM_PDC_30,
                    ]);

                    $this->record->update([
                        'payment_term_type' => $termType,
                        'payment_terms'     => PurchaseOrder::getPaymentTermOptions()[$termType] ?? $termType,
                        'payment_due_date'  => $dueDate,
                        'payment_status'    => $isPaid ? PurchaseOrder::PAYMENT_STATUS_PAID : PurchaseOrder::PAYMENT_STATUS_UNPAID,
                        'paid_at'           => $isPaid ? now() : null,
                        'is_completed'      => $isPaid ? true : $this->record->is_completed,
                        'completed_at'      => $isPaid ? ($this->record->completed_at ?? now()) : $this->record->completed_at,
                        'pdc_check_number'  => $data['pdc_check_number'] ?? null,
                        'pdc_bank'          => $data['pdc_bank'] ?? null,
                        'payment_account'   => $data['payment_account'] ?? null,
                        'payment_notes'     => $data['payment_notes'] ?? null,
                    ]);

                    Notification::make()
                        ->title('Payment Terms Configured')
                        ->body("Payment terms set to " . (PurchaseOrder::getPaymentTermOptions()[$termType] ?? $termType) . ". Status: " . ($isPaid ? 'PAID' : 'UNPAID (Pending Counter)'))
                        ->success()
                        ->send();
                }),

            Action::make('mark_payment_received')
                ->label('Mark Payment Received')
                ->icon('heroicon-m-banknotes')
                ->color('success')
                ->visible(fn(): bool => !$this->record->trashed() && ($this->record->isDelivered() || $this->record->delivery_status === PurchaseOrder::DELIVERY_DELIVERED) && !$this->record->isPaid())
                ->requiresConfirmation()
                ->modalHeading(fn(): string => "Confirm Payment Received: PO #{$this->record->po_number}")
                ->modalDescription('Are you sure you want to mark this 30-day counter credit order as PAID in full?')
                ->action(function (): void {
                    $this->record->update([
                        'payment_status' => PurchaseOrder::PAYMENT_STATUS_PAID,
                        'paid_at'        => now(),
                        'is_completed'   => true,
                        'completed_at'   => $this->record->completed_at ?? now(),
                    ]);

                    Notification::make()
                        ->title('Payment Recorded')
                        ->body("PO #{$this->record->po_number} marked as fully PAID. Order is now completed.")
                        ->success()
                        ->send();
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
