<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Services\InventoryService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
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
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/jfif', 'image/pjpeg', 'image/gif', 'image/svg+xml'])
                            ->maxSize(5120)
                            ->maxFiles(1)
                            ->rules(['mimes:jpeg,jpg,png,webp,jfif,pjpeg,gif,svg', 'max:5120'])
                            ->directory('products/images')
                            ->disk('public')
                            ->visibility('public')
                            ->helperText('Upload product photo or diagram. Accepted formats: JPG, PNG, WEBP, JFIF, GIF. Maximum file size: 5 MB.')
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
                            ->placeholder('e.g. SMD LED STRIP LIGHT INDOOR, COB LED STRIP LIGHT')
                            ->maxLength(255),

                        TextInput::make('wattage')
                            ->label('Wattage')
                            ->placeholder('e.g. 9.6W/M, 14.4W/M, 12W/M, 8W/M')
                            ->maxLength(100),

                        TextInput::make('voltage')
                            ->label('Voltage')
                            ->placeholder('e.g. DC12V, 220V')
                            ->maxLength(100),

                        TextInput::make('color_temperature')
                            ->label('Color / CCT')
                            ->placeholder('e.g. 3000K/6000K, 3000K/6000K/4000K, RGB')
                            ->maxLength(100),

                        Select::make('unit_default')
                            ->label('Default Unit')
                            ->options(\App\Enums\UnitOfMeasure::class)
                            ->default('pcs')
                            ->required(),

                        Textarea::make('description')
                            ->label('Description / Specifications')
                            ->placeholder('e.g. SMD LED STRIPS SIZE 2835, 120PCS LED/M, IP20 INDOOR')
                            ->rows(3)
                            ->columnSpanFull(),

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
                                Select::make('component_product_id')
                                    ->label('Link Catalog Product (Optional)')
                                    ->placeholder('Select catalog item to auto-populate specifications...')
                                    ->options(fn () => Product::orderBy('canonical_name')->pluck('canonical_name', 'id'))
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if (!$state) {
                                            return;
                                        }
                                        $catalogItem = Product::find($state);
                                        if ($catalogItem) {
                                            $set('component_name', $catalogItem->canonical_name);
                                            $set('product_code', $catalogItem->product_code);
                                            $set('category', $catalogItem->category);
                                            $set('wattage', $catalogItem->wattage);
                                            $set('voltage', $catalogItem->voltage);
                                            $set('color_temperature', $catalogItem->color_temperature);
                                            $set('unit', $catalogItem->unit_default ?: 'pcs');
                                            $set('cost_price', $catalogItem->base_cost_price > 0 ? $catalogItem->base_cost_price : $catalogItem->selling_price);
                                            $set('component_group', $catalogItem->category ?: 'General');
                                            $set('option_name', $catalogItem->canonical_name);
                                        }
                                    })
                                    ->columnSpan(['default' => 12, 'sm' => 4]),

                                TextInput::make('quantity')
                                    ->label('Qty / Parent Unit')
                                    ->numeric()
                                    ->default(1.0000)
                                    ->minValue(0.0001)
                                    ->step('any')
                                    ->required()
                                    ->columnSpan(['default' => 12, 'sm' => 2]),

                                TextInput::make('component_name')
                                    ->label('Component Name')
                                    ->placeholder('e.g. LED Driver 12V 5A')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(['default' => 12, 'sm' => 3]),

                                TextInput::make('product_code')
                                    ->label('Part Code / SKU')
                                    ->placeholder('e.g. DRV-12V-5A')
                                    ->maxLength(100)
                                    ->columnSpan(['default' => 12, 'sm' => 3]),

                                TextInput::make('category')
                                    ->label('Category')
                                    ->placeholder('e.g. Driver, Housing')
                                    ->maxLength(100)
                                    ->columnSpan(['default' => 12, 'sm' => 2]),

                                TextInput::make('wattage')
                                    ->label('Wattage')
                                    ->placeholder('e.g. 9W, 12W')
                                    ->maxLength(100)
                                    ->columnSpan(['default' => 12, 'sm' => 2]),

                                TextInput::make('voltage')
                                    ->label('Voltage')
                                    ->placeholder('e.g. DC12V, 220V')
                                    ->maxLength(100)
                                    ->columnSpan(['default' => 12, 'sm' => 2]),

                                TextInput::make('color_temperature')
                                    ->label('Color / CCT')
                                    ->placeholder('e.g. 3000K, 6000K')
                                    ->maxLength(100)
                                    ->columnSpan(['default' => 12, 'sm' => 2]),

                                Select::make('unit')
                                    ->label('Unit')
                                    ->options(\App\Enums\UnitOfMeasure::class)
                                    ->default('pcs')
                                    ->columnSpan(['default' => 12, 'sm' => 2]),

                                TextInput::make('cost_price')
                                    ->label('Unit Cost (₱)')
                                    ->numeric()
                                    ->prefix('₱')
                                    ->default(0.00)
                                    ->columnSpan(['default' => 12, 'sm' => 2]),
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
                    ->label('Picture')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(url('/images/placeholder-product.png'))
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('product_code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->default('—')
                    ->tooltip(fn(Product $record): string => "Product Code: " . ($record->product_code ?: 'N/A')),

                TextColumn::make('canonical_name')
                    ->label('Product Name')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->tooltip(fn(Product $record): string => "Canonical Name: {$record->canonical_name}"),

                TextColumn::make('category')
                    ->label('Category')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->default('General')
                    ->tooltip(fn(Product $record): string => "Product Category: " . ($record->category ?: 'General')),

                TextColumn::make('wattage')
                    ->label('Wattage')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->default('—')
                    ->toggleable(),

                TextColumn::make('voltage')
                    ->label('Voltage')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('warning')
                    ->default('—')
                    ->toggleable(),

                TextColumn::make('color_temperature')
                    ->label('Color')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary')
                    ->default('—')
                    ->toggleable(),

                TextColumn::make('unit_default')
                    ->label('Unit')
                    ->badge()
                    ->color('gray')
                    ->tooltip('Default unit of measure (e.g. ROLL, METER, SET, PC)'),

                TextColumn::make('selling_price')
                    ->label('Price (₱)')
                    ->money('PHP')
                    ->sortable()
                    ->weight('bold')
                    ->color('success')
                    ->tooltip(fn(Product $record): string => "Standard catalogue selling price: ₱" . number_format((float) $record->selling_price, 2)),

                TextColumn::make('inventoryItem.quantity_on_hand')
                    ->label('Stock On Hand')
                    ->numeric(2)
                    ->sortable()
                    ->badge()
                    ->color(fn(?float $state, Product $record): string => match (true) {
                        ($state ?? 0) <= 0 => 'danger',
                        $record->inventoryItem?->reorder_point && ($state ?? 0) <= $record->inventoryItem->reorder_point => 'warning',
                        default => 'success',
                    })
                    ->formatStateUsing(
                        fn(?float $state, Product $record): string =>
                        number_format((float) ($state ?? 0), 2) . ' ' . ($record->unit_default ?: 'pcs')
                    )
                    ->tooltip(fn(Product $record): string => "Current physical inventory on hand: " . number_format((float) ($record->inventoryItem?->quantity_on_hand ?? 0), 2) . " " . ($record->unit_default ?: 'pcs')),

                IconColumn::make('is_huenics_owned')
                    ->label('Huenics Stock')
                    ->boolean()
                    ->trueIcon('heroicon-s-check-badge')
                    ->falseIcon('heroicon-o-cube-transparent')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_composite')
                    ->label('Modular BOM')
                    ->boolean()
                    ->trueIcon('heroicon-s-puzzle-piece')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('primary')
                    ->falseColor('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('components_count')
                    ->counts('components')
                    ->label('Sub-Components')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'primary' : 'gray')
                    ->formatStateUsing(fn (int $state): string => $state > 0 ? "{$state} parts" : 'None')
                    ->tooltip(fn (Product $record): string => $record->components_count > 0 ? "Configured with {$record->components_count} sub-components (BOM parts)" : 'Single unit product without modular sub-components')
                    ->toggleable(),

                TextColumn::make('base_cost_price')
                    ->label('Cost (₱)')
                    ->money('PHP')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                    BulkAction::make('export_selected')
                        ->label('Export Selected to CSV')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $csv = app(\App\Services\ProductImportExportService::class)->exportCsv($records);
                            return response()->streamDownload(function () use ($csv) {
                                echo $csv;
                            }, 'huenics-products-selected-' . date('Ymd-His') . '.csv', [
                                'Content-Type' => 'text/csv; charset=UTF-8',
                            ]);
                        }),
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
            ->visible(fn(Product $record): bool => !$record->trashed())
            ->modalHeading(fn(Product $record): string => "Add Stock — {$record->canonical_name}")
            ->modalDescription(fn(Product $record): string => "Current inventory on hand: " . number_format((float) ($record->inventoryItem?->quantity_on_hand ?? 0), 2) . " " . ($record->unit_default ?: 'pcs') . ". Enter quantity to receive and add to inventory.")
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
                    ->helperText(fn(Product $record): string => "Stock will be added in: " . ($record->unit_default ?: 'pcs')),

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

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\ProductResource\RelationManagers\SubComponentsRelationManager::class,
        ];
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
