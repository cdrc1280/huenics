<?php

namespace App\Filament\Resources\PurchaseOrderResource\RelationManagers;

use App\Enums\SalesInvoiceStatus;
use App\Enums\UnitOfMeasure;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\SalesInvoice;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class SalesInvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'salesInvoices';
    protected static ?string $title = 'Sales Invoices (SI)';
    protected static \BackedEnum|string|null $icon = 'heroicon-o-receipt-percent';

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        /** @var PurchaseOrder $ownerRecord */
        $count = $ownerRecord->salesInvoices()->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getBadgeColor(Model $ownerRecord, string $pageClass): ?string
    {
        return 'info';
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Sales Invoice Header (Official BIR Green Form)')
                ->description('Green pre-printed official serial Sales Invoice details.')
                ->icon('heroicon-o-receipt-percent')
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('si_number')
                            ->label('SI # (Serial No.)')
                            ->placeholder('e.g. 0402, 0403, 0424')
                            ->default(fn () => SalesInvoice::generateNumber())
                            ->required(),

                        DatePicker::make('invoice_date')
                            ->label('Invoice Date')
                            ->default(now())
                            ->required(),

                        Select::make('payment_status')
                            ->label('Payment Status')
                            ->options(SalesInvoiceStatus::class)
                            ->default(SalesInvoiceStatus::Paid)
                            ->required(),
                    ]),

                    Grid::make(2)->schema([
                        TextInput::make('customer_name')
                            ->label('Sold To (Customer Name)')
                            ->default(fn () => $this->getOwnerRecord()->customer_name)
                            ->required(),

                        TextInput::make('customer_tin')
                            ->label('Customer TIN')
                            ->placeholder('e.g. 005-129-052-00000'),

                        TextInput::make('business_style')
                            ->label('Business Style')
                            ->default(fn () => $this->getOwnerRecord()->customer_name)
                            ->placeholder('e.g. MGS CONSTRUCTION, INC.'),

                        TextInput::make('terms')
                            ->label('Terms')
                            ->placeholder('e.g. 30 Days / COD'),

                        Textarea::make('billing_address')
                            ->label('Address')
                            ->default(fn () => $this->getOwnerRecord()->project?->location ?? null)
                            ->columnSpanFull(),

                        TextInput::make('delivery_receipt_numbers')
                            ->label('Cross-Ref DR # (DR Numbers)')
                            ->placeholder('e.g. 00426, 00423')
                            ->helperText('Delivery Receipt numbers connected to this billing invoice'),

                        TextInput::make('collection_receipt_numbers')
                            ->label('Collection Receipt # (RC #)')
                            ->placeholder('e.g. RC# 1410708, 1410709, 1410710')
                            ->helperText('Official collection receipts issued for this billing'),

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
                ->description('Itemized billable items matching the Purchase Order line items.')
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
                        ->addActionLabel('+ Add Billable Line Item'),
                ]),

            Section::make('Financial Summary & BIR VAT Breakdown')
                ->description('Official 12% Value Added Tax calculation matching the green BIR invoice form.')
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
                            ->placeholder('Name of cashier or billing executive'),

                        DatePicker::make('cashier_signature_date')
                            ->label('Cashier Signature Date')
                            ->default(now()),

                        Textarea::make('notes')
                            ->label('Invoicing Notes')
                            ->columnSpanFull(),
                    ]),
                ]),

            Section::make('Scanned Hard Copy (Physical Green SI)')
                ->icon('heroicon-o-document-arrow-up')
                ->schema([
                    FileUpload::make('file_path')
                        ->label('Upload Scanned SI (PDF, JPG, PNG)')
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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('si_number')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('si_number')
                    ->label('SI #')
                    ->weight('bold')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

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
                    ->label('Total Sales (VAT Inc.)')
                    ->money('PHP')
                    ->sortable(),

                TextColumn::make('vat_amount')
                    ->label('12% VAT')
                    ->money('PHP')
                    ->sortable(),

                TextColumn::make('total_amount')
                    ->label('Total Amount Due')
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
            ->headerActions([
                CreateAction::make()
                    ->label('+ Add Sales Invoice (SI)')
                    ->icon('heroicon-m-plus')
                    ->mutateFormDataUsing(function (array $data): array {
                        /** @var PurchaseOrder $po */
                        $po = $this->getOwnerRecord();
                        $data['purchase_order_id'] = $po->id;
                        if (empty($data['customer_name'])) {
                            $data['customer_name'] = $po->customer_name;
                        }
                        if (empty($data['billing_address'])) {
                            $data['billing_address'] = $po->project?->location;
                        }
                        if (empty($data['business_style'])) {
                            $data['business_style'] = $po->customer_name;
                        }
                        if (empty($data['delivery_receipt_numbers'])) {
                            $data['delivery_receipt_numbers'] = $po->deliveryReceipts()->pluck('dr_number')->implode(', ');
                        }
                        return $data;
                    })
                    ->after(function (SalesInvoice $record) {
                        /** @var PurchaseOrder $po */
                        $po = $this->getOwnerRecord();
                        // Append to comma-separated list of SIs on PO
                        $allSiNumbers = $po->salesInvoices()->pluck('si_number')->filter()->unique()->implode(', ');
                        $po->update([
                            'sales_invoice_no' => $allSiNumbers,
                        ]);
                    }),
            ])
            ->actions([
                Action::make('export_pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->url(fn (SalesInvoice $record) => route('sales-invoices.export-pdf', $record))
                    ->openUrlInNewTab(),

                ViewAction::make(),
                EditAction::make()
                    ->after(function (SalesInvoice $record) {
                        /** @var PurchaseOrder $po */
                        $po = $this->getOwnerRecord();
                        $allSiNumbers = $po->salesInvoices()->pluck('si_number')->filter()->unique()->implode(', ');
                        $po->update(['sales_invoice_no' => $allSiNumbers]);
                    }),
                DeleteAction::make()
                    ->after(function () {
                        /** @var PurchaseOrder $po */
                        $po = $this->getOwnerRecord();
                        $allSiNumbers = $po->salesInvoices()->pluck('si_number')->filter()->unique()->implode(', ');
                        $po->update(['sales_invoice_no' => $allSiNumbers ?: null]);
                    }),
            ]);
    }
}
