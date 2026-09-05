<?php

namespace App\Filament\Resources;

use App\Enums\DocumentType;
use App\Filament\Resources\TransactionResource\Pages;
use App\Models\DeliveryReceipt;
use App\Models\Document;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\SalesInvoice;
use App\Models\Transaction;
use App\Models\Vendor;
use App\Services\OrderFulfillmentService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationLabel = 'Master Transactions Ledger';

    protected static \UnitEnum|string|null $navigationGroup = 'Financial Operations';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->canEditTransactions() ?? true;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->canEditTransactions() ?? true;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->canDeleteRecords() ?? true;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Transaction Record')
                    ->components([
                        Forms\Components\TextInput::make('transaction_code')
                            ->label('Transaction Reference Code')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Select::make('project_id')
                            ->label('Project / Customer Job')
                            ->options(Project::pluck('name', 'id'))
                            ->required()
                            ->searchable(),

                        Forms\Components\Select::make('vendor_id')
                            ->label('Vendor')
                            ->options(Vendor::pluck('name', 'id'))
                            ->required()
                            ->searchable(),

                        Forms\Components\Select::make('purchase_order_id')
                            ->label('Purchase Order Reference')
                            ->options(PurchaseOrder::pluck('po_number', 'id'))
                            ->searchable()
                            ->nullable(),

                        Forms\Components\TextInput::make('final_amount')
                            ->label('Authoritative Reconciled Amount (₱)')
                            ->numeric()
                            ->prefix('₱')
                            ->required(),

                        Forms\Components\DatePicker::make('order_date')
                            ->label('Order Date'),

                        Forms\Components\DatePicker::make('delivery_date')
                            ->label('Delivery Date'),

                        Forms\Components\Select::make('status')
                            ->options([
                                'pending_delivery' => 'Pending Delivery',
                                'delivered' => 'Delivered / Fulfilled',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required()
                            ->default('pending_delivery'),

                        Forms\Components\Toggle::make('is_completed')
                            ->label('Completed & Realized')
                            ->helperText('Indicates both DR and SI have been fulfilled and stock/sales are realized.')
                            ->default(false),

                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Associated Lifecycle Documents')
                    ->description('Links to Quotation, Purchase Order, Delivery Receipt, and Sales Invoice documents.')
                    ->components([
                        Forms\Components\Select::make('quotation_document_id')
                            ->label('1. Quotation Document')
                            ->options(Document::where('document_type', Document::TYPE_VENDORS_AGREEMENT)->pluck('document_number', 'id'))
                            ->searchable()
                            ->placeholder('No quotation linked'),

                        Forms\Components\Select::make('purchase_order_document_id')
                            ->label('2. Purchase Order Document')
                            ->options(Document::where('document_type', Document::TYPE_PURCHASE_ORDER)->pluck('document_number', 'id'))
                            ->searchable()
                            ->placeholder('No PO linked'),

                        Forms\Components\Select::make('delivery_receipt_document_id')
                            ->label('3. Delivery Receipt Document')
                            ->options(Document::where('document_type', DocumentType::DeliveryReceipt->value)->pluck('document_number', 'id'))
                            ->searchable()
                            ->placeholder('No DR linked'),

                        Forms\Components\Select::make('sales_invoice_document_id')
                            ->label('4. Sales Invoice Document')
                            ->options(Document::where('document_type', DocumentType::SalesInvoice->value)->pluck('document_number', 'id'))
                            ->searchable()
                            ->placeholder('No SI linked'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('transaction_code')
                    ->label('Transaction ID')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),

                Tables\Columns\TextColumn::make('project.name')
                    ->label('Project')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('vendor.name')
                    ->label('Vendor')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('final_amount')
                    ->label('Final Amount (₱)')
                    ->money('PHP')
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('order_date')
                    ->label('Order Date')
                    ->date('M j, Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending_delivery' => 'warning',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('fulfillment_status')
                    ->label('Fulfillment')
                    ->badge()
                    ->state(fn(Transaction $record): string => match (true) {
                        $record->is_completed || $record->hasFulfillmentDocuments() => 'Completed & Realized',
                        $record->status === 'delivered' => 'Delivered (Awaiting DR & SI)',
                        default => 'Pending Delivery',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'Completed & Realized' => 'success',
                        'Delivered (Awaiting DR & SI)' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\IconColumn::make('doc_match')
                    ->label('Quotation & PO Match')
                    ->state(fn(Transaction $record): bool => !empty($record->quotation_document_id) && !empty($record->purchase_order_document_id))
                    ->boolean()
                    ->trueIcon('heroicon-s-shield-check')
                    ->falseIcon('heroicon-o-link')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip(fn(Transaction $record): string => (!empty($record->quotation_document_id) && !empty($record->purchase_order_document_id)) ? 'Quotation & Purchase Order Linked' : 'Partial Document Record'),

                Tables\Columns\IconColumn::make('dr_si_match')
                    ->label('DR & SI Uploaded')
                    ->state(fn(Transaction $record): bool => $record->hasFulfillmentDocuments() || $record->is_completed)
                    ->boolean()
                    ->trueIcon('heroicon-s-check-badge')
                    ->falseIcon('heroicon-o-arrow-up-tray')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->tooltip(fn(Transaction $record): string => ($record->hasFulfillmentDocuments() || $record->is_completed) ? 'Delivery Receipt & Sales Invoice Uploaded' : 'Awaiting DR & SI Upload'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Verified On')
                    ->dateTime('M j, Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),

                    Action::make('upload_dr_si')
                        ->label('Upload DR & SI (Complete)')
                        ->icon('heroicon-o-arrow-up-tray')
                        ->color('primary')
                        ->visible(fn(Transaction $record): bool => !$record->trashed() && !$record->is_completed && ($record->purchaseOrder !== null || $record->purchase_order_document_id !== null))
                        ->modalHeading(fn(Transaction $record): string => "Complete Transaction: Upload DR & SI for {$record->transaction_code}")
                        ->modalDescription('Upload both the Delivery Receipt and Sales Invoice files (Images or PDF) to finalize this transaction, deduct stocks from inventory, and realize sales analytics.')
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
                                            ->default(fn(Transaction $record) => $record->delivery_date ?? now())
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
                                            ->default(fn(Transaction $record) => (float) $record->final_amount)
                                            ->required(),
                                    ]),
                                ]),
                        ])
                        ->action(function (Transaction $record, array $data) {
                            try {
                                $po = $record->purchaseOrder
                                    ?: ($record->purchase_order_document_id ? PurchaseOrder::where('document_id', $record->purchase_order_document_id)->first() : null);

                                if (!$po) {
                                    Notification::make()
                                        ->title('Cannot Complete')
                                        ->body('No linked Purchase Order was found for this transaction.')
                                        ->danger()
                                        ->send();
                                    return;
                                }

                                $result = app(OrderFulfillmentService::class)->fulfillOrder($po, $data);
                                $drNo = $result['delivery_receipt']->dr_number;
                                $siNo = $result['sales_invoice']->si_number;

                                Notification::make()
                                    ->title('Transaction Completed & Realized')
                                    ->body("Transaction {$record->transaction_code} fulfilled with DR #{$drNo} and SI #{$siNo}. Stocks deducted from catalog and sales reflected.")
                                    ->success()
                                    ->send();
                            } catch (\Throwable $e) {
                                Notification::make()
                                    ->title('Fulfillment Failed')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    DeleteAction::make()->requiresConfirmation(),
                    RestoreAction::make()->requiresConfirmation()->visible(fn(Transaction $record): bool => $record->trashed()),
                    ForceDeleteAction::make()->requiresConfirmation()->visible(fn(Transaction $record): bool => $record->trashed() && (auth()->user()?->canDeleteRecords() ?? false)),
                ]),
            ], position: RecordActionsPosition::BeforeColumns)
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->requiresConfirmation(),
                    RestoreBulkAction::make()->requiresConfirmation(),
                    ForceDeleteBulkAction::make()->requiresConfirmation()->visible(fn(): bool => auth()->user()?->canDeleteRecords() ?? false),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
