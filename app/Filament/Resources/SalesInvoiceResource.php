<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalesInvoiceResource\Pages;
use App\Models\DeliveryReceipt;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\SalesInvoice;
use BackedEnum;
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
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class SalesInvoiceResource extends Resource
{
    protected static ?string $model = SalesInvoice::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-receipt-percent';
    protected static UnitEnum|string|null $navigationGroup = 'Sales & Order Lifecycle';
    protected static ?string $navigationLabel = 'Sales Invoices (SI)';
    protected static ?int $navigationSort = 6;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Invoice Header Details')
                ->schema([
                    TextInput::make('si_number')
                        ->label('SI #')
                        ->required()
                        ->default(fn () => SalesInvoice::generateNumber())
                        ->dehydrated(),

                    Select::make('purchase_order_id')
                        ->label('Purchase Order')
                        ->relationship('purchaseOrder', 'po_number')
                        ->searchable()
                        ->required(),

                    Select::make('delivery_receipt_id')
                        ->label('Delivery Receipt')
                        ->relationship('deliveryReceipt', 'dr_number')
                        ->searchable()
                        ->nullable(),

                    TextInput::make('customer_name')
                        ->label('Customer Name')
                        ->required(),

                    Textarea::make('billing_address')
                        ->label('Billing Address')
                        ->columnSpan(2),

                    DatePicker::make('invoice_date')
                        ->label('Invoice Date')
                        ->required()
                        ->default(now()),

                    DatePicker::make('due_date')
                        ->label('Due Date')
                        ->nullable(),

                    Select::make('payment_status')
                        ->label('Payment Status')
                        ->options([
                            'unpaid' => 'Unpaid',
                            'partial' => 'Partial',
                            'paid' => 'Paid',
                        ])
                        ->default('unpaid')
                        ->required(),

                    DatePicker::make('payment_date')
                        ->label('Payment Date')
                        ->nullable(),

                    TextInput::make('subtotal')
                        ->label('Subtotal (₱)')
                        ->numeric()
                        ->prefix('₱')
                        ->disabled()
                        ->dehydrated()
                        ->default(0),

                    TextInput::make('vat_amount')
                        ->label('12% VAT (₱)')
                        ->numeric()
                        ->prefix('₱')
                        ->disabled()
                        ->dehydrated()
                        ->default(0),

                    TextInput::make('total_amount')
                        ->label('Total Amount (₱)')
                        ->numeric()
                        ->prefix('₱')
                        ->disabled()
                        ->dehydrated()
                        ->default(0),

                    Textarea::make('notes')
                        ->label('Notes / Remarks')
                        ->columnSpanFull(),
                ])
                ->columns(3),

            Section::make('Invoiced Line Items')
                ->schema([
                    Repeater::make('items')
                        ->relationship('items')
                        ->schema([
                            Select::make('product_id')
                                ->label('Product')
                                ->options(Product::pluck('canonical_name', 'id'))
                                ->searchable()
                                ->required()
                                ->columnSpan(7),

                            TextInput::make('qty')
                                ->label('Qty')
                                ->numeric()
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn($state, $set, $get) => $set('line_total', round((float) $state * (float) $get('unit_price'), 2)))
                                ->columnSpan(1),

                            Select::make('unit')
                                ->label('Unit')
                                ->options(\App\Enums\UnitOfMeasure::class)
                                ->default('pcs')
                                ->required()
                                ->columnSpan(1),

                            TextInput::make('unit_price')
                                ->label('Unit Price')
                                ->numeric()
                                ->prefix('₱')
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn($state, $set, $get) => $set('line_total', round((float) $get('qty') * (float) $state, 2)))
                                ->columnSpan(1),

                            TextInput::make('line_total')
                                ->label('Total')
                                ->numeric()
                                ->prefix('₱')
                                ->disabled()
                                ->dehydrated()
                                ->columnSpan(2),
                        ])
                        ->columns(12)
                        ->defaultItems(1),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('si_number')
                    ->label('SI #')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('purchaseOrder.po_number')
                    ->label('PO #')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('invoice_date')
                    ->label('Date')
                    ->date('M d, Y')
                    ->sortable(),

                TextColumn::make('total_amount')
                    ->label('Total Amount')
                    ->money('PHP')
                    ->sortable(),

                TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'unpaid' => 'danger',
                        'partial' => 'warning',
                        'paid' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('payment_date')
                    ->label('Paid On')
                    ->date('M d, Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('export_pdf')
                        ->label('Export PDF')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('gray')
                        ->visible(fn(SalesInvoice $record): bool => !$record->trashed())
                        ->url(fn (SalesInvoice $record) => route('sales-invoices.export-pdf', $record))
                        ->openUrlInNewTab(),

                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make()->requiresConfirmation(),
                    RestoreAction::make()->requiresConfirmation()->visible(fn(SalesInvoice $record): bool => $record->trashed()),
                    ForceDeleteAction::make()->requiresConfirmation()->visible(fn(SalesInvoice $record): bool => $record->trashed() && (auth()->user()?->isAdmin() ?? false)),
                ]),
            ], position: RecordActionsPosition::BeforeColumns)
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->requiresConfirmation(),
                    RestoreBulkAction::make()->requiresConfirmation(),
                    ForceDeleteBulkAction::make()->requiresConfirmation()->visible(fn(): bool => auth()->user()?->isAdmin() ?? false),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSalesInvoices::route('/'),
            'create' => Pages\CreateSalesInvoice::route('/create'),
            'edit' => Pages\EditSalesInvoice::route('/{record}/edit'),
        ];
    }
}
