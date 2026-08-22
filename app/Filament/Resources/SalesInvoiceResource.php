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
                ->description('Specify billing customer, purchase order, delivery receipt link, and payment status.')
                ->icon('heroicon-o-receipt-percent')
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
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state, $set) {
                            if ($state) {
                                $po = PurchaseOrder::with('lineItems.product')->find($state);
                                if ($po) {
                                    $set('customer_name', $po->customer_name);
                                    $subtotal = 0;
                                    $items = $po->lineItems->map(function ($line) use (&$subtotal) {
                                        $qty = (float) $line->qty;
                                        $price = (float) ($line->discounted_price ?: $line->unit_price);
                                        $lineTotal = (float) ($line->line_total ?: round($qty * $price, 2));
                                        $subtotal += $lineTotal;
                                        return [
                                            'product_id' => $line->product_id,
                                            'description' => $line->description ?: ($line->product?->canonical_name ?? 'Line Item'),
                                            'qty' => $qty,
                                            'unit' => $line->unit ?: 'pcs',
                                            'unit_price' => $price,
                                            'line_total' => $lineTotal,
                                        ];
                                    })->toArray();
                                    $vat = round($subtotal * 0.12, 2);
                                    $set('items', $items);
                                    $set('subtotal', $subtotal);
                                    $set('vat_amount', $vat);
                                    $set('total_amount', $subtotal + $vat);
                                }
                            }
                        }),

                    Select::make('delivery_receipt_id')
                        ->label('Delivery Receipt (DR)')
                        ->relationship('deliveryReceipt', 'dr_number')
                        ->searchable()
                        ->nullable(),

                    TextInput::make('customer_name')
                        ->label('Customer Name')
                        ->required(),

                    DatePicker::make('invoice_date')
                        ->label('Invoice Date')
                        ->required()
                        ->default(now()),

                    DatePicker::make('due_date')
                        ->label('Payment Due Date')
                        ->nullable()
                        ->default(now()->addDays(30)),

                    Select::make('payment_status')
                        ->label('Payment Status')
                        ->options([
                            'unpaid' => 'Unpaid',
                            'partial' => 'Partial Payment',
                            'paid' => 'Fully Paid',
                        ])
                        ->default('unpaid')
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state, $set) {
                            if ($state === 'paid') {
                                $set('payment_date', now()->toDateString());
                            }
                        }),

                    DatePicker::make('payment_date')
                        ->label('Payment Date')
                        ->nullable(),

                    Textarea::make('billing_address')
                        ->label('Billing Address')
                        ->columnSpanFull(),

                    Textarea::make('notes')
                        ->label('Notes / Payment Terms')
                        ->columnSpanFull(),
                ])
                ->columns(3),

            Section::make('Invoiced Line Items')
                ->description('List of products and line items billed on this sales invoice.')
                ->icon('heroicon-o-shopping-bag')
                ->schema([
                    Repeater::make('items')
                        ->relationship('items')
                        ->schema([
                            Select::make('product_id')
                                ->label('Product')
                                ->options(Product::pluck('canonical_name', 'id'))
                                ->searchable()
                                ->required()
                                ->columnSpan(6),

                            TextInput::make('qty')
                                ->label('Qty')
                                ->numeric()
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(function ($state, $set, $get) {
                                    $total = round((float) $state * (float) $get('unit_price'), 2);
                                    $set('line_total', $total);
                                })
                                ->columnSpan(2),

                            Select::make('unit')
                                ->label('Unit')
                                ->options(\App\Enums\UnitOfMeasure::class)
                                ->default('pcs')
                                ->required()
                                ->columnSpan(1),

                            TextInput::make('unit_price')
                                ->label('Unit Price (₱)')
                                ->numeric()
                                ->prefix('₱')
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(function ($state, $set, $get) {
                                    $total = round((float) $get('qty') * (float) $state, 2);
                                    $set('line_total', $total);
                                })
                                ->columnSpan(1),

                            TextInput::make('line_total')
                                ->label('Line Total (₱)')
                                ->numeric()
                                ->prefix('₱')
                                ->disabled()
                                ->dehydrated()
                                ->columnSpan(2),
                        ])
                        ->columns(12)
                        ->defaultItems(1)
                        ->addActionLabel('+ Add Item'),

                    Grid::make(3)->schema([
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
                    ]),
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
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->tooltip('Click to copy Sales Invoice #'),

                TextColumn::make('purchaseOrder.po_number')
                    ->label('PO #')
                    ->searchable()
                    ->sortable()
                    ->color('primary')
                    ->tooltip(fn(SalesInvoice $r): string => "Linked PO: " . ($r->purchaseOrder?->po_number ?? 'N/A')),

                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->tooltip(fn(SalesInvoice $r): string => "Billed Customer: {$r->customer_name}"),

                TextColumn::make('invoice_date')
                    ->label('Date')
                    ->date('M d, Y')
                    ->sortable(),

                TextColumn::make('total_amount')
                    ->label('Total Amount')
                    ->money('PHP')
                    ->sortable()
                    ->weight('bold')
                    ->color('primary'),

                TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'unpaid' => 'Unpaid',
                        'partial' => 'Partial',
                        'paid' => 'Paid',
                        'cancelled' => 'Cancelled',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'unpaid', 'cancelled' => 'danger',
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
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'unpaid' => 'Unpaid',
                        'partial' => 'Partial',
                        'paid' => 'Paid',
                    ]),
                TrashedFilter::make(),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('mark_paid')
                        ->label('Record Full Payment')
                        ->icon('heroicon-m-banknotes')
                        ->color('success')
                        ->visible(fn(SalesInvoice $r): bool => !$r->trashed() && $r->payment_status !== 'paid')
                        ->requiresConfirmation()
                        ->action(function (SalesInvoice $record) {
                            $record->update([
                                'payment_status' => 'paid',
                                'payment_date' => now()->toDateString(),
                            ]);
                            Notification::make()->title('Payment Recorded')->body("Invoice {$record->si_number} marked as Paid.")->success()->send();
                        }),

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
                    ForceDeleteAction::make()->requiresConfirmation()->visible(fn(SalesInvoice $record): bool => $record->trashed() && (auth()->user()?->canDeleteRecords() ?? false)),
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
            'index' => Pages\ListSalesInvoices::route('/'),
            'create' => Pages\CreateSalesInvoice::route('/create'),
            'edit' => Pages\EditSalesInvoice::route('/{record}/edit'),
        ];
    }
}
