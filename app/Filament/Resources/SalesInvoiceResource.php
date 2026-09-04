<?php

namespace App\Filament\Resources;

use App\Enums\SalesInvoiceStatus;
use App\Enums\UnitOfMeasure;
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
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\TrashedFilter;
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
    protected static ?int $navigationSort = 4;

    public static function canAccess(): bool
    {
        return auth()->user()?->canManageQuotations() ?? true;
    }

    public static function canCreate(): bool
    {
        return true;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->with(['purchaseOrder.project', 'deliveryReceipt', 'items']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Sales Invoice Header (Official BIR Green Form)')
                ->description('Huenics Industrial Sales Inc. pre-printed official serial Sales Invoice details.')
                ->icon('heroicon-o-receipt-percent')
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('si_number')
                            ->label('SI # (Serial No.)')
                            ->placeholder('e.g. 0402, 0403, 0424')
                            ->default(fn () => SalesInvoice::generateNumber())
                            ->required()
                            ->dehydrated(),

                        DatePicker::make('invoice_date')
                            ->label('Invoice Date')
                            ->required()
                            ->default(now()),

                        Select::make('payment_status')
                            ->label('Payment Status')
                            ->options(SalesInvoiceStatus::class)
                            ->required()
                            ->default(SalesInvoiceStatus::Paid),
                    ]),

                    Grid::make(2)->schema([
                        Select::make('purchase_order_id')
                            ->label('Purchase Order Reference')
                            ->relationship('purchaseOrder', 'po_number')
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                if ($state) {
                                    $po = PurchaseOrder::with(['lineItems.product', 'project', 'deliveryReceipts'])->find($state);
                                    if ($po) {
                                        $set('customer_name', $po->customer_name);
                                        $set('business_style', $po->customer_name);
                                        $set('billing_address', $po->project?->location ?? null);
                                        if ($po->deliveryReceipts->isNotEmpty()) {
                                            $set('delivery_receipt_numbers', $po->deliveryReceipts->pluck('dr_number')->implode(', '));
                                        }

                                        $subtotal = 0;
                                        $items = $po->lineItems->map(function ($line) use (&$subtotal) {
                                            $qty = (float) $line->qty;
                                            $price = (float) ($line->discounted_price ?: $line->unit_price);
                                            $lineTotal = (float) ($line->line_total ?: round($qty * $price, 2));
                                            $subtotal += $lineTotal;
                                            return [
                                                'product_id'  => $line->product_id,
                                                'description' => $line->description ?: ($line->product?->canonical_name ?? 'Line Item'),
                                                'qty'         => $qty,
                                                'unit'        => $line->unit ?: 'pcs',
                                                'unit_price'  => $price,
                                                'line_total'  => $lineTotal,
                                            ];
                                        })->toArray();

                                        $netOfVat = round($subtotal / 1.12, 2);
                                        $vat = round($netOfVat * 0.12, 2);

                                        $set('items', $items);
                                        $set('subtotal', $subtotal);
                                        $set('discount_amount', 0);
                                        $set('net_of_vat', $netOfVat);
                                        $set('vatable_sales', $netOfVat);
                                        $set('vat_amount', $vat);
                                        $set('total_amount', round($netOfVat + $vat, 2));
                                    }
                                }
                            }),

                        TextInput::make('customer_name')
                            ->label('Sold To (Customer Name)')
                            ->required(),

                        TextInput::make('customer_tin')
                            ->label('Customer TIN')
                            ->placeholder('e.g. 005-129-052-00000'),

                        TextInput::make('business_style')
                            ->label('Business Style')
                            ->placeholder('e.g. MGS CONSTRUCTION, INC.'),

                        TextInput::make('terms')
                            ->label('Terms')
                            ->placeholder('e.g. 30 Days / COD'),

                        Textarea::make('billing_address')
                            ->label('Billing Address')
                            ->placeholder('Customer registered billing address')
                            ->columnSpanFull(),

                        TextInput::make('delivery_receipt_numbers')
                            ->label('Cross-Ref DR # (DR Numbers)')
                            ->placeholder('e.g. 00426, 00423')
                            ->helperText('Connected Delivery Receipt numbers for this billing'),

                        TextInput::make('collection_receipt_numbers')
                            ->label('Collection Receipt # (RC #)')
                            ->placeholder('e.g. RC# 1410708, 1410709, 1410710')
                            ->helperText('Official Collection Receipts issued for this billing'),

                        TextInput::make('rs_number')
                            ->label('Requisition Slip # (RS #)')
                            ->placeholder('e.g. RS-042'),

                        TextInput::make('osca_pwd_id')
                            ->label('OSCA / PWD ID No.')
                            ->placeholder('Senior Citizen / PWD ID if applicable'),

                        DatePicker::make('due_date')
                            ->label('Due Date')
                            ->default(now()->addDays(30)),

                        DatePicker::make('payment_date')
                            ->label('Payment Date')
                            ->nullable(),
                    ]),
                ]),

            Section::make('Articles / Billed Line Items')
                ->description('Itemized billable goods matching the Purchase Order.')
                ->icon('heroicon-o-list-bullet')
                ->schema([
                    Repeater::make('items')
                        ->relationship('items')
                        ->schema([
                            Select::make('product_id')
                                ->label('Product')
                                ->options(Product::pluck('canonical_name', 'id'))
                                ->searchable()
                                ->columnSpan(4),

                            TextInput::make('qty')
                                ->label('QTY.')
                                ->numeric()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, $get, $set) {
                                    $qty = (float) $state;
                                    $price = (float) $get('unit_price');
                                    $set('line_total', round($qty * $price, 2));
                                })
                                ->columnSpan(2),

                            Select::make('unit')
                                ->label('UNIT')
                                ->options(UnitOfMeasure::class)
                                ->default('pcs')
                                ->required()
                                ->columnSpan(1),

                            TextInput::make('description')
                                ->label('ARTICLES / Description')
                                ->required()
                                ->columnSpan(5),

                            TextInput::make('unit_price')
                                ->label('UNIT PRICE (₱)')
                                ->numeric()
                                ->prefix('₱')
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, $get, $set) {
                                    $price = (float) $state;
                                    $qty = (float) $get('qty');
                                    $set('line_total', round($qty * $price, 2));
                                })
                                ->columnSpan(3),

                            TextInput::make('line_total')
                                ->label('AMOUNT (₱)')
                                ->numeric()
                                ->prefix('₱')
                                ->required()
                                ->columnSpan(3),
                        ])
                        ->columns(18)
                        ->defaultItems(1)
                        ->addActionLabel('+ Add Invoice Item'),
                ]),

            Section::make('Financial Summary & BIR VAT Breakdown')
                ->description('Official 12% Value Added Tax breakdown matching the physical green invoice form.')
                ->icon('heroicon-o-calculator')
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('subtotal')
                            ->label('Total Sales (VAT Inclusive) ₱')
                            ->numeric()
                            ->prefix('₱')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, $get, $set) {
                                $total = (float) $state;
                                $discount = (float) ($get('discount_amount') ?? 0);
                                $netOfVat = round(($total - $discount) / 1.12, 2);
                                $vat = round($netOfVat * 0.12, 2);
                                $set('net_of_vat', $netOfVat);
                                $set('vatable_sales', $netOfVat);
                                $set('vat_amount', $vat);
                                $set('total_amount', round($netOfVat + $vat, 2));
                            }),

                        TextInput::make('discount_amount')
                            ->label('Less: Discount ₱')
                            ->numeric()
                            ->prefix('₱')
                            ->default(0)
                            ->live()
                            ->afterStateUpdated(function ($state, $get, $set) {
                                $discount = (float) $state;
                                $total = (float) ($get('subtotal') ?? 0);
                                $netOfVat = round(($total - $discount) / 1.12, 2);
                                $vat = round($netOfVat * 0.12, 2);
                                $set('net_of_vat', $netOfVat);
                                $set('vatable_sales', $netOfVat);
                                $set('vat_amount', $vat);
                                $set('total_amount', round($netOfVat + $vat, 2));
                            }),

                        TextInput::make('net_of_vat')
                            ->label('Amount Net of VAT ₱')
                            ->numeric()
                            ->prefix('₱')
                            ->required(),

                        TextInput::make('vatable_sales')
                            ->label('VATable Sales ₱')
                            ->numeric()
                            ->prefix('₱')
                            ->required(),

                        TextInput::make('vat_exempt_sales')
                            ->label('VAT-Exempt Sales ₱')
                            ->numeric()
                            ->prefix('₱')
                            ->default(0),

                        TextInput::make('zero_rated_sales')
                            ->label('Zero Rated Sales ₱')
                            ->numeric()
                            ->prefix('₱')
                            ->default(0),

                        TextInput::make('vat_amount')
                            ->label('VAT-Amount (12%) ₱')
                            ->numeric()
                            ->prefix('₱')
                            ->required(),

                        TextInput::make('withholding_tax')
                            ->label('Less: Withholding Tax ₱')
                            ->numeric()
                            ->prefix('₱')
                            ->default(0),

                        TextInput::make('total_amount')
                            ->label('TOTAL AMOUNT DUE ₱')
                            ->numeric()
                            ->prefix('₱')
                            ->required(),
                    ]),
                ]),

            Section::make('Signatures & Acknowledgements')
                ->icon('heroicon-o-pencil-square')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('cashier_representative')
                            ->label('Cashier / Authorized Representative')
                            ->placeholder('Name of authorized representative'),

                        DatePicker::make('cashier_signature_date')
                            ->label('Cashier Signature Date')
                            ->default(now()),

                        Textarea::make('notes')
                            ->label('Invoicing / Payment Notes')
                            ->columnSpanFull(),
                    ]),
                ]),

            Section::make('Scanned Hard Copy (Physical Green SI)')
                ->icon('heroicon-o-document-arrow-up')
                ->schema([
                    FileUpload::make('file_path')
                        ->label('Upload Scanned SI (PDF or Image)')
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                        ->maxSize(25600)
                        ->disk('local')
                        ->directory('documents/si')
                        ->preserveFilenames()
                        ->helperText('Attach scanned official green sales invoice with cashier stamp and signature.')
                        ->columnSpanFull(),
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
                    ->tooltip(fn (SalesInvoice $r): string => "Linked PO: " . ($r->purchaseOrder?->po_number ?? 'N/A')),

                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('invoice_date')
                    ->label('Invoice Date')
                    ->date('M d, Y')
                    ->sortable(),

                TextColumn::make('delivery_receipt_numbers')
                    ->label('DR #s')
                    ->default('—')
                    ->badge()
                    ->color('info'),

                TextColumn::make('collection_receipt_numbers')
                    ->label('RC #s')
                    ->default('—')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('subtotal')
                    ->label('Sales (VAT Inc.)')
                    ->money('PHP')
                    ->sortable(),

                TextColumn::make('vat_amount')
                    ->label('12% VAT')
                    ->money('PHP')
                    ->sortable(),

                TextColumn::make('total_amount')
                    ->label('Total Due')
                    ->money('PHP')
                    ->weight('bold')
                    ->color('success')
                    ->sortable(),

                TextColumn::make('payment_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match ($state instanceof SalesInvoiceStatus ? $state->value : (string) $state) {
                        'paid' => 'success',
                        'partial' => 'warning',
                        'unpaid' => 'danger',
                        default => 'gray',
                    }),

                IconColumn::make('file_path')
                    ->label('Scanned Copy')
                    ->boolean()
                    ->trueIcon('heroicon-s-document-check')
                    ->falseIcon('heroicon-o-document')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->getStateUsing(fn (SalesInvoice $r): bool => !empty($r->file_path) || !empty($r->document_id)),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options(SalesInvoiceStatus::class),
                TrashedFilter::make(),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('export_pdf')
                        ->label('Export PDF')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('gray')
                        ->url(fn (SalesInvoice $record) => route('sales-invoices.export-pdf', $record))
                        ->openUrlInNewTab(),

                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make()->requiresConfirmation(),
                    RestoreAction::make()->requiresConfirmation()->visible(fn (SalesInvoice $record): bool => $record->trashed()),
                    ForceDeleteAction::make()->requiresConfirmation()->visible(fn (SalesInvoice $record): bool => $record->trashed() && (auth()->user()?->canDeleteRecords() ?? false)),
                ]),
            ], position: RecordActionsPosition::BeforeColumns)
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->requiresConfirmation(),
                    RestoreBulkAction::make()->requiresConfirmation(),
                    ForceDeleteBulkAction::make()->requiresConfirmation()->visible(fn (): bool => auth()->user()?->canDeleteRecords() ?? false),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSalesInvoices::route('/'),
            'create' => Pages\CreateSalesInvoice::route('/create'),
            'view'   => Pages\ViewSalesInvoice::route('/{record}'),
            'edit'   => Pages\EditSalesInvoice::route('/{record}/edit'),
        ];
    }
}
