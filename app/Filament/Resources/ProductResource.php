<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Services\InventoryService;
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
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static \UnitEnum|string|null $navigationGroup = 'Master Data & Registry';
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationLabel = 'Products Catalog';
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
        return auth()->user()?->canManageCatalog() ?? true;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->canManageCatalog() ?? true;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->canDeleteRecords() ?? true;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Canonical Product Details')
                    ->components([
                        FileUpload::make('image_path')
                            ->label('Product Image')
                            ->image()
                            ->imageEditor()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(5120)
                            ->maxFiles(1)
                            ->rules(['image', 'mimes:jpeg,png,webp', 'max:5120'])
                            ->directory('products/images')
                            ->disk('public')
                            ->visibility('public')
                            ->helperText('Upload product photo or diagram. Accepted formats: JPG, PNG, WEBP. Maximum file size: 5 MB.')
                            ->columnSpanFull(),

                        TextInput::make('canonical_name')
                            ->label('Canonical Product Name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('product_code')
                            ->label('Product Code / Model #')
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),

                        TextInput::make('sku')
                            ->label('SKU')
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),

                        TextInput::make('category')
                            ->label('Category')
                            ->placeholder('e.g. LED Lighting, Drivers, Architectural'),

                        Select::make('unit_default')
                            ->label('Default Unit')
                            ->options(\App\Enums\UnitOfMeasure::class)
                            ->default('pcs')
                            ->required(),

                        TextInput::make('base_cost_price')
                            ->label('Base Cost (₱)')
                            ->numeric()
                            ->prefix('₱')
                            ->default(0.00),

                        TextInput::make('selling_price')
                            ->label('Selling Price (₱)')
                            ->numeric()
                            ->prefix('₱')
                            ->default(0.00),

                        Toggle::make('is_huenics_owned')
                            ->label('Huenics Proprietary Product')
                            ->helperText('Only Huenics-owned products track inventory stock-on-hand.')
                            ->default(true),

                        Toggle::make('is_composite')
                            ->label('Composite Modular BOM Product')
                            ->helperText('Enable for modular products assembled from sub-components (e.g. LED Tracklight with COB/Driver).')
                            ->default(false)
                            ->live(),

                        Toggle::make('is_active')
                            ->label('Active in Catalog')
                            ->default(true),
                    ])->columnSpanFull(),

                Section::make('Modular Bill of Materials (BOM) & Parts')
                    ->description('Configure dynamic sub-components and modular parts for this product (e.g., LED COB, Driver, Housing, Optics).')
                    ->icon('heroicon-o-puzzle-piece')
                    ->visible(fn($get) => (bool) $get('is_composite'))
                    ->schema([
                        Repeater::make('components')
                            ->relationship('components')
                            ->label('Sub-Components & Parts')
                            ->schema([
                                TextInput::make('component_group')
                                    ->label('Part Group / Category')
                                    ->placeholder('e.g. LED COB, LED Driver, Optics, Housing')
                                    ->required()
                                    ->maxLength(100)
                                    ->columnSpan(['default' => 12, 'sm' => 3]),

                                TextInput::make('option_name')
                                    ->label('Part Name / Specification')
                                    ->placeholder('e.g. Citizen COB 3000K, Meanwell 700mA')
                                    ->required()
                                    ->maxLength(100)
                                    ->columnSpan(['default' => 12, 'sm' => 3]),

                                Select::make('component_product_id')
                                    ->label('Catalog Part (Optional)')
                                    ->options(fn() => Product::pluck('canonical_name', 'id'))
                                    ->searchable()
                                    ->nullable()
                                    ->columnSpan(['default' => 12, 'sm' => 3]),

                                TextInput::make('additional_cost')
                                    ->label('Additional Cost (₱)')
                                    ->numeric()
                                    ->prefix('₱')
                                    ->default(0.00)
                                    ->columnSpan(['default' => 12, 'sm' => 2]),

                                Toggle::make('is_default')
                                    ->label('Default')
                                    ->inline(false)
                                    ->default(false)
                                    ->columnSpan(['default' => 12, 'sm' => 1]),
                            ])
                            ->columns(12)
                            ->defaultItems(0)
                            ->addActionLabel('+ Add Part / Sub-Component')
                            ->reorderable()
                            ->cloneable(),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Image')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(url('/images/placeholder-product.png'))
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('product_code')
                    ->label('Code / SKU')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->default('—')
                    ->tooltip(fn(Product $record): string => "Product Code: " . ($record->product_code ?: 'N/A')),

                TextColumn::make('canonical_name')
                    ->label('Product Name')
                    ->searchable()
                    ->sortable()
                    ->tooltip(fn(Product $record): string => "Canonical Name: {$record->canonical_name}"),

                TextColumn::make('category')
                    ->label('Category')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->default('General')
                    ->tooltip(fn(Product $record): string => "Product Category: " . ($record->category ?: 'General')),

                IconColumn::make('is_huenics_owned')
                    ->label('Huenics Stock')
                    ->boolean()
                    ->trueIcon('heroicon-s-check-badge')
                    ->falseIcon('heroicon-o-cube-transparent')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip(fn(Product $record): string => $record->is_huenics_owned ? 'Huenics Proprietary Product: In-house inventory tracked' : 'Third-Party Product'),

                IconColumn::make('is_composite')
                    ->label('Modular BOM')
                    ->boolean()
                    ->trueIcon('heroicon-s-puzzle-piece')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('primary')
                    ->falseColor('gray')
                    ->tooltip(fn(Product $record): string => $record->is_composite ? 'Modular BOM: Assembled from configurable sub-components' : 'Standard Unit Product'),

                TextColumn::make('base_cost_price')
                    ->label('Cost (₱)')
                    ->money('PHP')
                    ->sortable()
                    ->tooltip(fn(Product $record): string => "Base acquisition/manufacturing cost: ₱" . number_format((float) $record->base_cost_price, 2)),

                TextColumn::make('selling_price')
                    ->label('Selling Price (₱)')
                    ->money('PHP')
                    ->sortable()
                    ->weight('bold')
                    ->color('success')
                    ->tooltip(fn(Product $record): string => "Standard catalogue selling price: ₱" . number_format((float) $record->selling_price, 2)),

                TextColumn::make('unit_default')
                    ->label('Unit')
                    ->tooltip('Default unit of measure (e.g. pcs, sets, meters)'),

                TextColumn::make('inventoryItem.quantity_on_hand')
                    ->label('Stock On Hand')
                    ->numeric(2)
                    ->sortable()
                    ->badge()
                    ->color(fn (?float $state, Product $record): string => match (true) {
                        ($state ?? 0) <= 0 => 'danger',
                        $record->inventoryItem?->reorder_point && ($state ?? 0) <= $record->inventoryItem->reorder_point => 'warning',
                        default => 'success',
                    })
                    ->formatStateUsing(fn (?float $state, Product $record): string => 
                        number_format((float) ($state ?? 0), 2) . ' ' . ($record->unit_default ?: 'pcs')
                    )
                    ->tooltip(fn(Product $record): string => "Current physical inventory on hand: " . number_format((float) ($record->inventoryItem?->quantity_on_hand ?? 0), 2) . " " . ($record->unit_default ?: 'pcs')),
            ])

            ->filters([
                TernaryFilter::make('is_huenics_owned')
                    ->label('Huenics Proprietary Only'),
                TernaryFilter::make('is_composite')
                    ->label('Modular BOM Only'),
                TrashedFilter::make(),
            ])
            ->actions([
                ActionGroup::make([
                    static::getAddStockAction(),
                    EditAction::make(),
                    DeleteAction::make()->requiresConfirmation(),
                    RestoreAction::make()->requiresConfirmation()->visible(fn(Product $record): bool => $record->trashed()),
                    ForceDeleteAction::make()->requiresConfirmation()->visible(fn(Product $record): bool => $record->trashed() && (auth()->user()?->canDeleteRecords() ?? false)),
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

    public static function getAddStockAction(): Action
    {
        return Action::make('add_stock')
            ->label('Add Stock')
            ->icon('heroicon-m-plus')
            ->color('success')
            ->visible(fn (Product $record): bool => ! $record->trashed())
            ->modalHeading(fn (Product $record): string => "Add Stock — {$record->canonical_name}")
            ->modalDescription(fn (Product $record): string => "Current inventory on hand: " . number_format((float) ($record->inventoryItem?->quantity_on_hand ?? 0), 2) . " " . ($record->unit_default ?: 'pcs') . ". Enter quantity to receive and add to inventory.")
            ->modalSubmitActionLabel('Confirm & Add Stock')
            ->form([
                TextInput::make('quantity')
                    ->label('Quantity to Add')
                    ->numeric()
                    ->minValue(0.0001)
                    ->step('any')
                    ->required()
                    ->autofocus()
                    ->placeholder('e.g. 50')
                    ->helperText(fn (Product $record): string => "Stock will be added in: " . ($record->unit_default ?: 'pcs')),

                Select::make('transaction_type')
                    ->label('Stock-In Type')
                    ->options([
                        'purchase_in' => 'Purchase In (Supplier Delivery)',
                        'initial_stock' => 'Initial Stock In',
                        'adjustment_up' => 'Inventory Adjustment (Found / Count)',
                        'returned_items' => 'Customer Return / RMA In',
                    ])
                    ->default('purchase_in')
                    ->required(),

                TextInput::make('reference')
                    ->label('Reference / DR # / PO #')
                    ->placeholder('e.g. Supplier DR #1240 or Inbound PO #8892')
                    ->maxLength(100),

                Textarea::make('notes')
                    ->label('Reason / Notes')
                    ->required()
                    ->placeholder('e.g. Received shipment of 50 units from supplier warehouse')
                    ->rows(3),
            ])
            ->action(function (Product $record, array $data) {
                $qty = (float) $data['quantity'];
                $type = $data['transaction_type'];
                $notes = (string) $data['notes'];
                $ref = !empty($data['reference']) ? (string) $data['reference'] : null;

                if ($ref) {
                    $notes = "[Ref: {$ref}] {$notes}";
                }

                $transaction = app(InventoryService::class)->addStock(
                    product: $record,
                    quantity: $qty,
                    type: $type,
                    notes: $notes,
                    reference: $ref,
                    user: auth()->user()
                );

                $newBalance = number_format((float) $record->fresh()->inventoryItem?->quantity_on_hand, 2);
                $unit = $record->unit_default ?: 'pcs';

                Notification::make()
                    ->title('Stock Added Successfully')
                    ->body("Added {$qty} {$unit} to {$record->canonical_name}. New stock balance: {$newBalance} {$unit}. Transaction recorded in Activity Log.")
                    ->success()
                    ->send();
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
