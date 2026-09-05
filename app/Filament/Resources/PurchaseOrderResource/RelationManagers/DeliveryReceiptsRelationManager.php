<?php

namespace App\Filament\Resources\PurchaseOrderResource\RelationManagers;

use App\Enums\DeliveryReceiptStatus;
use App\Enums\UnitOfMeasure;
use App\Models\DeliveryReceipt;
use App\Models\Product;
use App\Models\PurchaseOrder;
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

class DeliveryReceiptsRelationManager extends RelationManager
{
    protected static string $relationship = 'deliveryReceipts';
    protected static ?string $title = 'Delivery Receipts (DR)';
    protected static \BackedEnum|string|null $icon = 'heroicon-o-truck';

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        /** @var PurchaseOrder $ownerRecord */
        $count = $ownerRecord->deliveryReceipts()->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getBadgeColor(Model $ownerRecord, string $pageClass): ?string
    {
        return 'success';
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Delivery Receipt Header (Official Form)')
                ->description('Pink pre-printed serial Delivery Receipt details.')
                ->icon('heroicon-o-truck')
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('dr_number')
                            ->label('DR # (Serial No.)')
                            ->placeholder('e.g. 00451')
                            ->default(fn () => DeliveryReceipt::generateNumber())
                            ->required(),

                        DatePicker::make('delivery_date')
                            ->label('Delivery Date')
                            ->default(now())
                            ->required(),

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
                        TextInput::make('customer_name')
                            ->label('Delivered To')
                            ->default(fn () => $this->getOwnerRecord()->customer_name)
                            ->required(),

                        TextInput::make('customer_tin')
                            ->label('Customer TIN')
                            ->placeholder('e.g. 005-129-052-00000'),

                        TextInput::make('project_name')
                            ->label('Project / Site Location')
                            ->default(fn () => $this->getOwnerRecord()->project?->name ?? 'Palanza Tower'),

                        TextInput::make('terms')
                            ->label('Terms')
                            ->placeholder('e.g. 30 Days / COD'),

                        Textarea::make('delivery_address')
                            ->label('Delivery Address')
                            ->default(fn () => $this->getOwnerRecord()->project?->location ?? null)
                            ->columnSpanFull(),

                        TextInput::make('sales_invoice_numbers')
                            ->label('Cross-Ref SI # (SI Numbers)')
                            ->placeholder('e.g. 0402, 0403')
                            ->helperText('Connected Sales Invoice numbers for this delivery'),

                        TextInput::make('rs_number')
                            ->label('Requisition Slip # (RS #)')
                            ->placeholder('e.g. RS-2026-01'),

                        Select::make('status')
                            ->label('Status')
                            ->options(DeliveryReceiptStatus::class)
                            ->default(DeliveryReceiptStatus::Delivered)
                            ->required(),
                    ]),
                ]),

            Section::make('Articles / Delivered Line Items')
                ->description('Line items dispatched in this delivery batch.')
                ->icon('heroicon-o-cube')
                ->schema([
                    Repeater::make('items')
                        ->relationship('items')
                        ->schema([
                            Select::make('product_id')
                                ->label('Product')
                                ->options(Product::pluck('canonical_name', 'id'))
                                ->searchable()
                                ->columnSpan(5),

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
                                ->columnSpan(5),

                            TextInput::make('remarks')
                                ->label('Item Remarks')
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
                            ->label('Delivered By (Driver / Logistics)')
                            ->placeholder('Logistics driver name'),

                        TextInput::make('prepared_by')
                            ->label('Prepared By')
                            ->placeholder('Huenics inventory staff'),

                        TextInput::make('approved_by')
                            ->label('Approved By')
                            ->placeholder('Operations manager name'),
                    ]),

                    Grid::make(2)->schema([
                        TextInput::make('received_by')
                            ->label('Received By (Client Signature over Printed Name)')
                            ->default(fn () => $this->getOwnerRecord()->customer_name)
                            ->placeholder('Customer site receiver name')
                            ->helperText('Client or site personnel who acknowledged receipt of goods')
                            ->required(),

                        DatePicker::make('received_date')
                            ->label('Received Date')
                            ->default(now()),

                        Textarea::make('remarks')
                            ->label('Site / Delivery Notes')
                            ->columnSpanFull(),
                    ]),
                ]),

            Section::make('Scanned Hard Copy (Physical Pink DR)')
                ->icon('heroicon-o-document-arrow-up')
                ->schema([
                    FileUpload::make('file_path')
                        ->label('Upload Scanned DR (PDF, JPG, PNG)')
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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('dr_number')
            ->columns([
                TextColumn::make('dr_number')
                    ->label('DR #')
                    ->weight('bold')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

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
                    ->label('Items Count')
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
            ->headerActions([
                CreateAction::make()
                    ->label('+ Add Delivery Receipt (DR)')
                    ->icon('heroicon-m-plus')
                    ->mutateFormDataUsing(function (array $data): array {
                        /** @var PurchaseOrder $po */
                        $po = $this->getOwnerRecord();
                        $data['purchase_order_id'] = $po->id;
                        if (empty($data['customer_name'])) {
                            $data['customer_name'] = $po->customer_name;
                        }
                        if (empty($data['delivery_address'])) {
                            $data['delivery_address'] = $po->project?->location;
                        }
                        if (empty($data['project_name'])) {
                            $data['project_name'] = $po->project?->name;
                        }
                        return $data;
                    })
                    ->after(function (DeliveryReceipt $record) {
                        /** @var PurchaseOrder $po */
                        $po = $this->getOwnerRecord();
                        // Append to comma-separated list of DRs on PO
                        $allDrNumbers = $po->deliveryReceipts()->pluck('dr_number')->filter()->unique()->implode(', ');
                        $po->update([
                            'delivery_receipt_no'  => $allDrNumbers,
                            'actual_delivery_date' => $record->delivery_date ?? $po->actual_delivery_date ?? now(),
                        ]);
                    }),
            ])
            ->actions([
                Action::make('export_pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->url(fn (DeliveryReceipt $record) => route('delivery-receipts.export-pdf', $record))
                    ->openUrlInNewTab(),

                ViewAction::make(),
                EditAction::make()
                    ->after(function (DeliveryReceipt $record) {
                        /** @var PurchaseOrder $po */
                        $po = $this->getOwnerRecord();
                        $allDrNumbers = $po->deliveryReceipts()->pluck('dr_number')->filter()->unique()->implode(', ');
                        $po->update(['delivery_receipt_no' => $allDrNumbers]);
                    }),
                DeleteAction::make()
                    ->after(function () {
                        /** @var PurchaseOrder $po */
                        $po = $this->getOwnerRecord();
                        $allDrNumbers = $po->deliveryReceipts()->pluck('dr_number')->filter()->unique()->implode(', ');
                        $po->update(['delivery_receipt_no' => $allDrNumbers ?: null]);
                    }),
            ]);
    }
}
