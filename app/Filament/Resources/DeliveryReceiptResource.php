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
    protected static bool $shouldRegisterNavigation = false;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function canCreate(): bool
    {
        // DRs are strictly uploaded from physical hard copies via the unified Upload DR & SI workflow
        return false;
    }

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
                ->description('Attached physical delivery receipt reference, personnel, and schedule.')
                ->icon('heroicon-o-truck')
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
                        ->searchable()
                        ->live()
                        ->afterStateUpdated(function ($state, $set) {
                            if ($state) {
                                $po = PurchaseOrder::with('lineItems.product')->find($state);
                                if ($po) {
                                    $items = $po->lineItems->map(fn($line) => [
                                        'product_id' => $line->product_id,
                                        'description' => $line->description ?: ($line->product?->canonical_name ?? 'Line Item'),
                                        'qty_delivered' => (float) $line->qty,
                                        'unit' => $line->unit ?: 'pcs',
                                        'remarks' => $line->description,
                                    ])->toArray();
                                    $set('items', $items);
                                }
                            }
                        }),

                    DatePicker::make('delivery_date')
                        ->label('Delivery Date')
                        ->required()
                        ->default(now()),

                    TextInput::make('delivered_by')
                        ->label('Delivered By')
                        ->placeholder('Driver or Delivery Staff Name'),

                    TextInput::make('received_by')
                        ->label('Received By')
                        ->placeholder('Client Authorized Representative'),

                    Select::make('status')
                        ->options(\App\Enums\DeliveryReceiptStatus::class)
                        ->required()
                        ->default(\App\Enums\DeliveryReceiptStatus::Delivered),

                    Textarea::make('remarks')
                        ->label('Delivery Remarks / Site Notes')
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('Delivered Items')
                ->description('List of products and quantities dispatched for this delivery receipt.')
                ->icon('heroicon-o-cube')
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
                                ->label('Item Remarks')
                                ->placeholder('Serial / Spec notes')
                                ->columnSpan(3),
                        ])
                        ->columns(12)
                        ->defaultItems(1)
                        ->addActionLabel('+ Add Item'),
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
                    ->tooltip(fn(DeliveryReceipt $r): string => "Linked PO: " . ($r->purchaseOrder?->po_number ?? 'N/A')),

                TextColumn::make('purchaseOrder.customer_name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->tooltip(fn(DeliveryReceipt $r): string => "Customer: " . ($r->purchaseOrder?->customer_name ?? 'N/A')),

                IconColumn::make('has_document')
                    ->label('Attached Copy')
                    ->state(fn(DeliveryReceipt $r): bool => !empty($r->document_id))
                    ->boolean()
                    ->trueIcon('heroicon-s-document-check')
                    ->falseIcon('heroicon-o-document')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip(fn(DeliveryReceipt $r): string => $r->document ? "Hard copy attached: {$r->document->original_filename}" : 'Physical copy linked'),

                TextColumn::make('delivery_date')
                    ->label('Delivery Date')
                    ->date('M d, Y')
                    ->sortable(),

                TextColumn::make('delivered_by')
                    ->label('Delivered By')
                    ->searchable()
                    ->default('—'),

                TextColumn::make('received_by')
                    ->label('Received By')
                    ->searchable()
                    ->default('—'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'in_transit', 'pending' => 'In Transit',
                        'delivered' => 'Delivered',
                        'cancelled', 'rejected' => 'Cancelled',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'draft' => 'gray',
                        'in_transit', 'pending' => 'warning',
                        'delivered' => 'success',
                        'cancelled', 'rejected' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(\App\Enums\DeliveryReceiptStatus::class),
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
                    ForceDeleteAction::make()->requiresConfirmation()->visible(fn(DeliveryReceipt $record): bool => $record->trashed() && (auth()->user()?->canDeleteRecords() ?? false)),
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
            'index' => Pages\ListDeliveryReceipts::route('/'),
            'edit' => Pages\EditDeliveryReceipt::route('/{record}/edit'),
        ];
    }
}
