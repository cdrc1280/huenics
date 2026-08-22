<?php

namespace App\Filament\Resources;

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
    protected static ?int $navigationSort = 5;

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
                        'in_transit', 'pending' => 'warning',
                        'delivered' => 'success',
                        'cancelled', 'rejected' => 'danger',
                        default => 'gray',
                    }),
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
                        ->url(fn(DeliveryReceipt $record) => route('delivery-receipts.export-pdf', $record))
                        ->openUrlInNewTab(),

                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make()->requiresConfirmation(),
                    RestoreAction::make()->requiresConfirmation()->visible(fn(DeliveryReceipt $record): bool => $record->trashed()),
                    ForceDeleteAction::make()->requiresConfirmation()->visible(fn(DeliveryReceipt $record): bool => $record->trashed() && (auth()->user()?->isAdmin() ?? false)),
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
            'index' => Pages\ListDeliveryReceipts::route('/'),
            'create' => Pages\CreateDeliveryReceipt::route('/create'),
            'edit' => Pages\EditDeliveryReceipt::route('/{record}/edit'),
        ];
    }
}
