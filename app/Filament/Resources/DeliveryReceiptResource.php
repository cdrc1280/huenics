<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeliveryReceiptResource\Pages;
use App\Models\DeliveryReceipt;
use App\Models\Product;
use App\Models\PurchaseOrder;
use BackedEnum;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;
use UnitEnum;

class DeliveryReceiptResource extends Resource
{
    protected static ?string $model = DeliveryReceipt::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-truck';
    protected static UnitEnum|string|null $navigationGroup = 'Sales & Order Lifecycle';
    protected static ?string $navigationLabel = 'Delivery Receipts (DR)';
    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Delivery Details')
                ->schema([
                    TextInput::make('dr_number')
                        ->label('DR #')
                        ->required()
                        ->default(fn() => DeliveryReceipt::generateNumber())
                        ->dehydrated(),

                    Select::make('purchase_order_id')
                        ->label('Purchase Order')
                        ->relationship('purchaseOrder', 'po_number')
                        ->required()
                        ->searchable(),

                    DatePicker::make('delivery_date')
                        ->label('Delivery Date')
                        ->required()
                        ->default(now()),

                    TextInput::make('delivered_by')
                        ->label('Delivered By'),

                    TextInput::make('received_by')
                        ->label('Received By'),

                    Select::make('status')
                        ->options(\App\Enums\DeliveryReceiptStatus::class)
                        ->required()
                        ->default(\App\Enums\DeliveryReceiptStatus::Draft),
                ])
                ->columns(2),

            Section::make('Delivered Items')
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

                            TextInput::make('qty_delivered')
                                ->label('Qty Delivered')
                                ->numeric()
                                ->required()
                                ->columnSpan(2),

                            Select::make('unit')
                                ->label('Unit')
                                ->options(\App\Enums\UnitOfMeasure::class)
                                ->default('pcs')
                                ->required()
                                ->columnSpan(1),

                            TextInput::make('remarks')
                                ->label('Remarks')
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
                TextColumn::make('dr_number')
                    ->label('DR #')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('purchaseOrder.po_number')
                    ->label('PO Number')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('delivery_date')
                    ->date('M d, Y')
                    ->sortable(),

                TextColumn::make('delivered_by')
                    ->searchable(),

                TextColumn::make('received_by')
                    ->searchable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'draft' => 'gray',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('export_pdf')
                        ->label('Export PDF')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('gray')
                        ->url(fn(DeliveryReceipt $record) => route('delivery-receipts.export-pdf', $record))
                        ->openUrlInNewTab(),

                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ], position: RecordActionsPosition::BeforeColumns)
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDeliveryReceipts::route('/'),
            'create' => Pages\CreateDeliveryReceipt::route('/create'),
            'edit' => Pages\EditDeliveryReceipt::route('/{record}/edit'),
        ];
    }
}
