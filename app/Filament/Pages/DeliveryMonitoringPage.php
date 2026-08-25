<?php

namespace App\Filament\Pages;

use App\Models\DeliveryReceipt;
use App\Models\PurchaseOrder;
use App\Models\SalesInvoice;
use App\Services\OrderFulfillmentService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class DeliveryMonitoringPage extends Page implements HasTable, HasForms
{
    use InteractsWithTable, InteractsWithForms;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'Delivery & Warranty Tracker';
    protected static UnitEnum|string|null $navigationGroup = 'Sales & Order Lifecycle';
    protected string $view = 'filament.pages.delivery-monitoring-page';
    protected static ?int $navigationSort = 7;

    public function table(Table $table): Table
    {
        return $table
            ->query(PurchaseOrder::query()->orderBy('expected_delivery_date', 'asc'))
            ->columns([
                TextColumn::make('po_number')
                    ->label('PO #')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer_name')
                    ->searchable(),
                TextColumn::make('project.name')
                    ->label('Project'),
                TextColumn::make('order_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('expected_delivery_date')
                    ->date()
                    ->sortable()
                    ->color(fn($record) => $record->is_overdue ? 'danger' : (now()->diffInDays($record->expected_delivery_date, false) < 3 && $record->delivery_status !== 'delivered' ? 'warning' : null)),
                TextColumn::make('delivery_status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        PurchaseOrder::DELIVERY_PENDING, 'pending' => 'warning',
                        PurchaseOrder::DELIVERY_TRANSIT, 'in_transit' => 'info',
                        PurchaseOrder::DELIVERY_DELIVERED, 'delivered' => 'success',
                        PurchaseOrder::DELIVERY_OVERDUE, 'overdue' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('fulfillment_status')
                    ->label('Fulfillment')
                    ->badge()
                    ->state(fn(PurchaseOrder $record): string => match (true) {
                        $record->isCompleted() => 'Completed & Realized',
                        $record->delivery_status === PurchaseOrder::DELIVERY_DELIVERED => 'Delivered (Awaiting DR & SI)',
                        $record->isApproved() => 'Approved (Pending Delivery)',
                        default => 'Pending Review',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'Completed & Realized' => 'success',
                        'Delivered (Awaiting DR & SI)' => 'warning',
                        'Approved (Pending Delivery)' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('delivery_receipt_no')
                    ->label('DR #'),
                TextColumn::make('warranty_status')
                    ->label('Warranty')
                    ->badge()
                    ->formatStateUsing(fn(?string $state, $record): string => match ($state) {
                        PurchaseOrder::WARRANTY_ACTIVE => 'Active (' . ($record->warranty_period === PurchaseOrder::WARRANTY_2_YEARS_6_MONTHS || $record->warranty_period === '2_years' ? '2.5 yrs' : '1 yr') . ')',
                        PurchaseOrder::WARRANTY_EXPIRING => 'Expiring Soon',
                        PurchaseOrder::WARRANTY_EXPIRED => 'Expired',
                        default => 'No Warranty',
                    })
                    ->color(fn(?string $state): string => match ($state) {
                        PurchaseOrder::WARRANTY_ACTIVE => 'success',
                        PurchaseOrder::WARRANTY_EXPIRING => 'warning',
                        PurchaseOrder::WARRANTY_EXPIRED => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('realized_profit')
                    ->money('PHP')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('approve_po')
                        ->label('Approve PO')
                        ->icon('heroicon-o-check')
                        ->color('info')
                        ->visible(fn($record) => !$record->isApproved())
                        ->requiresConfirmation()
                        ->action(function (PurchaseOrder $record) {
                            $record->update(['status' => PurchaseOrder::STATUS_APPROVED]);
                            if ($record->document) {
                                $record->document->update(['status' => \App\Models\Document::STATUS_VERIFIED]);
                            }
                            Notification::make()->title('Purchase Order Approved')->body("PO {$record->po_number} is now approved and verified for delivery.")->success()->send();
                        }),

                    Action::make('mark_delivered')
                        ->label('Mark as Delivered')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->tooltip('DR & SI are verified and attached. Mark this purchase order as delivered to deduct inventory and realize sales.')
                        ->visible(fn(PurchaseOrder $r): bool => $r->isApproved() && $r->hasBothDrAndSi() && $r->delivery_status !== PurchaseOrder::DELIVERY_DELIVERED)
                        ->modalHeading(fn(PurchaseOrder $record): string => "Mark as Delivered: PO #{$record->po_number}")
                        ->modalDescription('Both Delivery Receipt (DR) and Sales Invoice (SI) are verified and attached. Confirming delivery will finalize this order, deduct stock from the product catalog/BOM, and record sales in the dashboard.')
                        ->form([
                            DatePicker::make('actual_delivery_date')
                                ->label('Actual Delivery Date')
                                ->default(now())
                                ->required(),
                            Toggle::make('has_warranty')
                                ->label('Include Warranty')
                                ->default(fn($record) => $record->has_warranty ?? true)
                                ->live(),
                            Select::make('warranty_period')
                                ->label('Warranty Period')
                                ->options(PurchaseOrder::getWarrantyPeriodOptions())
                                ->default(fn($record) => $record->warranty_period ?? PurchaseOrder::WARRANTY_1_YEAR)
                                ->visible(fn($get) => (bool) $get('has_warranty')),
                        ])
                        ->action(function (PurchaseOrder $record, array $data) {
                            try {
                                app(OrderFulfillmentService::class)->completeDelivery($record, $data);
                                Notification::make()
                                    ->title('PO Marked as Delivered')
                                    ->body("PO {$record->po_number} marked as Delivered. Stocks deducted from catalog and sales realized.")
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
                        ->icon('heroicon-o-arrow-up-tray')
                        ->color('primary')
                        ->tooltip('Upload physical Delivery Receipt (DR) and Sales Invoice (SI) hard copies (Images/PDF)')
                        ->visible(fn(PurchaseOrder $r): bool => $r->isApproved() && !$r->isCompleted())
                        ->modalHeading(fn(PurchaseOrder $record): string => "Upload Hard Copies (DR & SI): PO #{$record->po_number}")
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
                                            ->default(fn(PurchaseOrder $record) => $record->actual_delivery_date ?? now())
                                            ->required(),

                                        TextInput::make('delivered_by')
                                            ->label('Delivered By')
                                            ->placeholder('Driver or logistics personnel'),

                                        TextInput::make('received_by')
                                            ->label('Received By')
                                            ->placeholder('Customer site receiver name'),
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
                                            ->default(fn(PurchaseOrder $record) => (float) $record->order_amount)
                                            ->required(),
                                    ]),
                                ]),

                            Toggle::make('auto_mark_delivered')
                                ->label('Mark order as Delivered immediately upon upload')
                                ->helperText('If enabled, will immediately deduct stock and realize sales. If disabled, DR & SI will be attached and verified, unlocking the "Mark as Delivered" action button.')
                                ->default(true),
                        ])
                        ->action(function (PurchaseOrder $record, array $data) {
                            try {
                                if (!empty($data['auto_mark_delivered'])) {
                                    $result = app(OrderFulfillmentService::class)->fulfillOrder($record, $data);
                                    $drNo = $result['delivery_receipt']->dr_number;
                                    $siNo = $result['sales_invoice']->si_number;

                                    Notification::make()
                                        ->title('Order Delivered & Completed')
                                        ->body("PO {$record->po_number} fulfilled with DR #{$drNo} and SI #{$siNo}. Stocks deducted from catalog and sales reflected across dashboards.")
                                        ->success()
                                        ->send();
                                } else {
                                    $result = app(OrderFulfillmentService::class)->attachFulfillmentDocuments($record, $data);
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
                ]),
            ], position: RecordActionsPosition::BeforeColumns);
    }
}
