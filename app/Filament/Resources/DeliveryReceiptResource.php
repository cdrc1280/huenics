<?php

namespace App\Filament\Resources;

use App\Enums\DeliveryReceiptStatus;
use App\Enums\UnitOfMeasure;
use App\Filament\Resources\DeliveryReceiptResource\Pages;
use App\Models\DeliveryReceipt;
use App\Models\Product;
use App\Models\PurchaseOrder;
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

class DeliveryReceiptResource extends Resource
{
    protected static ?string $model = DeliveryReceipt::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-truck';
    protected static UnitEnum|string|null $navigationGroup = 'Sales & Order Lifecycle';
    protected static ?string $navigationLabel = 'Delivery Receipts (DR)';
    protected static ?int $navigationSort = 3;

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
            ->with(['purchaseOrder.project', 'items']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Delivery Receipt Header (Official Pink Form)')
                ->description('Huenics Industrial Sales Inc. pre-printed official serial Delivery Receipt.')
                ->icon('heroicon-o-truck')
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('dr_number')
                            ->label('DR # (Serial No.)')
                            ->placeholder('e.g. 00451')
                            ->default(fn () => DeliveryReceipt::generateNumber())
                            ->required()
                            ->dehydrated(),

                        DatePicker::make('delivery_date')
                            ->label('Delivery Date')
                            ->required()
                            ->default(now()),

                        Select::make('delivery_type')
                            ->label('Delivery Type')
                            ->options([
                                'complete' => 'Complete Delivery',
                                'partial'  => 'Partial Delivery',
                            ])
                            ->default('complete')
                            ->required(),
                    ]),

                    Grid::make(2)->schema([
                        Select::make('purchase_order_id')
                            ->label('Purchase Order Reference')
                            ->relationship('purchaseOrder', 'po_number')
                            ->required()
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                if ($state) {
                                    $po = PurchaseOrder::with(['lineItems.product', 'project', 'salesInvoices'])->find($state);
                                    if ($po) {
                                        $set('customer_name', $po->customer_name);
                                        $set('delivery_address', $po->project?->location ?? null);
                                        $set('project_name', $po->project?->name ?? 'Palanza Tower');
                                        if ($po->salesInvoices->isNotEmpty()) {
                                            $set('sales_invoice_numbers', $po->salesInvoices->pluck('si_number')->implode(', '));
                                        }
                                        $items = $po->lineItems->map(fn ($line) => [
                                            'product_id'    => $line->product_id,
                                            'description'   => $line->description ?: ($line->product?->canonical_name ?? 'Line Item'),
                                            'qty_delivered' => (float) $line->qty,
                                            'unit'          => $line->unit ?: 'pcs',
                                            'remarks'       => $line->description,
                                        ])->toArray();
                                        $set('items', $items);
                                    }
                                }
                            }),

                        TextInput::make('customer_name')
                            ->label('Delivered To (Customer / Company)')
                            ->required(),

                        TextInput::make('customer_tin')
                            ->label('Customer TIN')
                            ->placeholder('e.g. 005-129-052-00000'),

                        TextInput::make('project_name')
                            ->label('Project / Site Location')
                            ->placeholder('e.g. Palanza Tower'),

                        TextInput::make('terms')
                            ->label('Terms')
                            ->placeholder('e.g. 30 Days / COD'),

                        Textarea::make('delivery_address')
                            ->label('Delivery Address')
                            ->placeholder('Full site or delivery address')
                            ->columnSpanFull(),

                        TextInput::make('sales_invoice_numbers')
                            ->label('Cross-Ref SI # (SI Numbers)')
                            ->placeholder('e.g. 0402, 0403')
                            ->helperText('Sales invoice numbers connected with this delivery'),

                        TextInput::make('rs_number')
                            ->label('Requisition Slip # (RS #)')
                            ->placeholder('e.g. RS-2026-01'),

                        Select::make('status')
                            ->label('Delivery Status')
                            ->options(DeliveryReceiptStatus::class)
                            ->required()
                            ->default(DeliveryReceiptStatus::Delivered),
                    ]),
                ]),

            Section::make('Articles / Delivered Line Items')
                ->description('Itemized goods dispatched in this delivery matching the physical DR.')
                ->icon('heroicon-o-cube')
                ->schema([
                    Repeater::make('items')
                        ->relationship('items')
                        ->schema([
                            Select::make('product_id')
                                ->label('Product')
                                ->options(Product::pluck('canonical_name', 'id'))
                                ->searchable()
                                ->columnSpan(4),

                            TextInput::make('qty_delivered')
                                ->label('Quantity')
                                ->numeric()
                                ->required()
                                ->columnSpan(2),

                            Select::make('unit')
                                ->label('Unit')
                                ->options(UnitOfMeasure::class)
                                ->default('pcs')
                                ->required()
                                ->columnSpan(2),

                            TextInput::make('description')
                                ->label('Articles / Description')
                                ->required()
                                ->columnSpan(6),

                            TextInput::make('remarks')
                                ->label('Item Remarks')
                                ->placeholder('Serial / Spec notes')
                                ->columnSpan(12),
                        ])
                        ->columns(12)
                        ->defaultItems(1)
                        ->addActionLabel('+ Add Delivered Item'),
                ]),

            Section::make('Signatures & Reception Acknowledgement')
                ->icon('heroicon-o-pencil-square')
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('delivered_by')
                            ->label('Delivered By (Driver / Logistics Staff)')
                            ->placeholder('Logistics or driver name'),

                        TextInput::make('prepared_by')
                            ->label('Prepared By')
                            ->placeholder('Warehouse / Logistics staff'),

                        TextInput::make('approved_by')
                            ->label('Approved By')
                            ->placeholder('Operations manager name'),
                    ]),

                    Grid::make(2)->schema([
                        TextInput::make('received_by')
                            ->label('Received By (Customer Signature over Printed Name)')
                            ->placeholder('Customer site receiver name')
                            ->helperText('Client or site personnel who signed acknowledgment')
                            ->required(),

                        DatePicker::make('received_date')
                            ->label('Received Date')
                            ->default(now()),

                        Textarea::make('remarks')
                            ->label('Delivery Remarks / Site Notes')
                            ->columnSpanFull(),
                    ]),
                ]),

            Section::make('Scanned Hard Copy (Physical Pink DR)')
                ->icon('heroicon-o-document-arrow-up')
                ->schema([
                    FileUpload::make('file_path')
                        ->label('Upload Scanned DR (PDF or Image)')
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                        ->maxSize(25600)
                        ->disk('local')
                        ->directory('documents/dr')
                        ->preserveFilenames()
                        ->helperText('Attach scanned official pink delivery receipt with customer stamp and signature.')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('dr_number')
                    ->label('DR #')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->tooltip('Click to copy Delivery Receipt #'),

                TextColumn::make('purchaseOrder.po_number')
                    ->label('PO #')
                    ->searchable()
                    ->sortable()
                    ->color('primary')
                    ->tooltip(fn (DeliveryReceipt $r): string => "Linked PO: " . ($r->purchaseOrder?->po_number ?? 'N/A')),

                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->default(fn (DeliveryReceipt $r) => $r->purchaseOrder?->customer_name ?? '—'),

                TextColumn::make('delivery_date')
                    ->label('Delivery Date')
                    ->date('M d, Y')
                    ->sortable(),

                TextColumn::make('delivery_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'partial' ? 'Partial Delivery' : 'Complete Delivery')
                    ->color(fn ($state) => $state === 'partial' ? 'warning' : 'success'),

                TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Items')
                    ->badge()
                    ->color('info'),

                TextColumn::make('received_by')
                    ->label('Received By')
                    ->searchable()
                    ->default('—'),

                TextColumn::make('sales_invoice_numbers')
                    ->label('Cross-Ref SI #')
                    ->default('—')
                    ->badge()
                    ->color('gray'),

                IconColumn::make('file_path')
                    ->label('Scanned Copy')
                    ->boolean()
                    ->trueIcon('heroicon-s-document-check')
                    ->falseIcon('heroicon-o-document')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->getStateUsing(fn (DeliveryReceipt $r): bool => !empty($r->file_path) || !empty($r->document_id)),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match ($state instanceof DeliveryReceiptStatus ? $state->value : (string) $state) {
                        'delivered' => 'success',
                        'in_transit', 'pending' => 'warning',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(DeliveryReceiptStatus::class),
                TrashedFilter::make(),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('export_pdf')
                        ->label('Export PDF')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('gray')
                        ->url(fn (DeliveryReceipt $record) => route('delivery-receipts.export-pdf', $record))
                        ->openUrlInNewTab(),

                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make()->requiresConfirmation(),
                    RestoreAction::make()->requiresConfirmation()->visible(fn (DeliveryReceipt $record): bool => $record->trashed()),
                    ForceDeleteAction::make()->requiresConfirmation()->visible(fn (DeliveryReceipt $record): bool => $record->trashed() && (auth()->user()?->canDeleteRecords() ?? false)),
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
            'index'  => Pages\ListDeliveryReceipts::route('/'),
            'create' => Pages\CreateDeliveryReceipt::route('/create'),
            'view'   => Pages\ViewDeliveryReceipt::route('/{record}'),
            'edit'   => Pages\EditDeliveryReceipt::route('/{record}/edit'),
        ];
    }
}
