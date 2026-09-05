<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Enums\UnitOfMeasure;
use App\Models\Product;
use App\Models\ProductComponent;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubComponentsRelationManager extends RelationManager
{
    protected static string $relationship = 'components';
    protected static ?string $title = 'Sub-Components / Bill of Materials (BOM)';
    protected static \BackedEnum|string|null $icon = 'heroicon-o-puzzle-piece';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Sub-Component Configuration')
                ->description('Define part specifications matching product catalog attributes. You can link an existing catalog product or specify custom part details.')
                ->icon('heroicon-o-puzzle-piece')
                ->schema([
                    Grid::make(3)->schema([
                        Select::make('component_product_id')
                            ->label('Link Existing Catalog Product (Optional)')
                            ->placeholder('Select catalog item to auto-populate specifications...')
                            ->options(fn() => Product::orderBy('canonical_name')->pluck('canonical_name', 'id'))
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
                                    $set('image_path', $catalogItem->image_path);
                                    $set('component_group', $catalogItem->category ?: 'General');
                                    $set('option_name', $catalogItem->canonical_name);
                                }
                            })
                            ->columnSpan(['default' => 3, 'lg' => 2]),

                        TextInput::make('quantity')
                            ->label('Quantity per Parent Unit')
                            ->numeric()
                            ->default(1.0000)
                            ->minValue(0.0001)
                            ->step('any')
                            ->required()
                            ->helperText('Units required to assemble 1 unit of the parent product (e.g. 1 driver, 2 screws).')
                            ->columnSpan(['default' => 3, 'lg' => 1]),
                    ]),

                    Grid::make(3)->schema([
                        TextInput::make('component_name')
                            ->label('Component Name / Description')
                            ->placeholder('e.g. LED Driver 12V 5A, E27 Aluminum Base, Citizen COB Chip')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(['default' => 3, 'lg' => 2]),

                        TextInput::make('product_code')
                            ->label('Component Code / Model #')
                            ->placeholder('e.g. DRV-12V-5A, BASE-E27-AL')
                            ->maxLength(100)
                            ->columnSpan(['default' => 3, 'lg' => 1]),
                    ]),

                    Grid::make(4)->schema([
                        TextInput::make('category')
                            ->label('Category / Part Group')
                            ->placeholder('e.g. Driver, Housing, Optics, Socket')
                            ->maxLength(100),

                        TextInput::make('wattage')
                            ->label('Wattage')
                            ->placeholder('e.g. 9W, 12W/M, 50W')
                            ->maxLength(100),

                        TextInput::make('voltage')
                            ->label('Voltage')
                            ->placeholder('e.g. DC12V, 220V')
                            ->maxLength(100),

                        TextInput::make('color_temperature')
                            ->label('Color / CCT')
                            ->placeholder('e.g. 3000K, 6000K, RGB')
                            ->maxLength(100),
                    ]),

                    Grid::make(3)->schema([
                        Select::make('unit')
                            ->label('Unit of Measure')
                            ->options(UnitOfMeasure::class)
                            ->default('pcs')
                            ->required(),

                        TextInput::make('cost_price')
                            ->label('Unit Cost (₱)')
                            ->numeric()
                            ->prefix('₱')
                            ->default(0.00)
                            ->helperText('Cost of 1 unit of this component.'),

                        Toggle::make('is_default')
                            ->label('Default Component')
                            ->helperText('Active standard assembly part for this product.')
                            ->default(true)
                            ->inline(false),
                    ]),

                    FileUpload::make('image_path')
                        ->label('Component Picture')
                        ->image()
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/jfif', 'image/pjpeg', 'image/gif', 'image/svg+xml'])
                        ->directory('products/components')
                        ->disk('public')
                        ->visibility('public')
                        ->helperText('Upload component photo or diagram. Formats: JPG, PNG, WEBP, JFIF.')
                        ->columnSpanFull(),

                    Textarea::make('notes')
                        ->label('Assembly Instructions / Notes')
                        ->placeholder('e.g. Soldered to terminal 1 and 2 with thermal paste')
                        ->rows(2)
                        ->columnSpanFull(),
                ])->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('component_name')
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('effective_image')
                    ->label('Picture')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(url('/images/placeholder-product.png')),

                TextColumn::make('effective_code')
                    ->label('Code')
                    ->weight('bold')
                    ->searchable(query: function ($query, string $search) {
                        $query->where('product_code', 'like', "%{$search}%")
                            ->orWhereHas('componentProduct', fn($q) => $q->where('product_code', 'like', "%{$search}%"));
                    })
                    ->sortable()
                    ->default('—'),

                TextColumn::make('effective_name')
                    ->label('Component Name')
                    ->weight('medium')
                    ->wrap()
                    ->searchable(query: function ($query, string $search) {
                        $query->where('component_name', 'like', "%{$search}%")
                            ->orWhere('option_name', 'like', "%{$search}%")
                            ->orWhereHas('componentProduct', fn($q) => $q->where('canonical_name', 'like', "%{$search}%"));
                    })
                    ->description(fn(ProductComponent $record): ?string => $record->componentProduct ? 'Linked Catalog Part: ' . $record->componentProduct->canonical_name : null),

                TextColumn::make('effective_category')
                    ->label('Category')
                    ->badge()
                    ->color('info')
                    ->default('General'),

                TextColumn::make('effective_wattage')
                    ->label('Wattage')
                    ->badge()
                    ->color('gray')
                    ->default('—'),

                TextColumn::make('effective_voltage')
                    ->label('Voltage')
                    ->badge()
                    ->color('warning')
                    ->default('—'),

                TextColumn::make('effective_color')
                    ->label('Color')
                    ->badge()
                    ->color('primary')
                    ->default('—'),

                TextColumn::make('effective_unit')
                    ->label('Unit')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('effective_cost')
                    ->label('Unit Cost (₱)')
                    ->money('PHP')
                    ->weight('semibold')
                    ->color('gray'),

                TextColumn::make('quantity')
                    ->label('Qty / Unit')
                    ->badge()
                    ->color('success')
                    ->formatStateUsing(
                        fn($state, ProductComponent $record): string =>
                        number_format((float) ($state ?: 1), 2) . ' ' . $record->effective_unit
                    )
                    ->tooltip('Quantity needed to manufacture/assemble 1 unit of the parent product'),

                TextColumn::make('total_cost')
                    ->label('Total Cost (₱)')
                    ->money('PHP')
                    ->weight('bold')
                    ->color('success')
                    ->tooltip(
                        fn(ProductComponent $record): string =>
                        number_format((float) ($record->quantity ?: 1), 2) . ' × ₱' . number_format($record->effective_cost, 2)
                    ),

                TextColumn::make('stock_on_hand')
                    ->label('Stock On Hand')
                    ->badge()
                    ->color(fn($state) => $state === null ? 'gray' : ((float) $state <= 0 ? 'danger' : 'success'))
                    ->formatStateUsing(
                        fn($state, ProductComponent $record): string =>
                        $state !== null
                        ? number_format((float) $state, 2) . ' ' . $record->effective_unit
                        : 'Custom Part'
                    )
                    ->tooltip(
                        fn(ProductComponent $record): string =>
                        $record->componentProduct
                        ? "Warehouse stock for catalog item {$record->componentProduct->canonical_name}"
                        : 'Custom bespoke component (not tracked separately in warehouse catalog)'
                    ),

                TextColumn::make('assembleable_units')
                    ->label('Max Build Units')
                    ->badge()
                    ->state(function (ProductComponent $record): string {
                        if ($record->stock_on_hand === null) {
                            return 'Custom Part';
                        }
                        $qty = (float) ($record->quantity ?: 1);
                        $avail = $qty > 0 ? floor($record->stock_on_hand / $qty) : 0;
                        return number_format($avail, 0) . ' units';
                    })
                    ->color(function (ProductComponent $record): string {
                        if ($record->stock_on_hand === null) return 'gray';
                        $qty = (float) ($record->quantity ?: 1);
                        $avail = $qty > 0 ? floor($record->stock_on_hand / $qty) : 0;
                        return $avail <= 0 ? 'danger' : ($avail < 10 ? 'warning' : 'success');
                    })
                    ->tooltip('Maximum parent units that can be built from current stock of this component'),
            ])
            ->headerActions([
                Action::make('bulk_add_subcomponents')
                    ->label('Bulk Add Sub-Components')
                    ->icon('heroicon-o-squares-plus')
                    ->color('primary')
                    ->modalHeading('Bulk Add Sub-Components (Select Multiple Catalog Products)')
                    ->modalDescription('Select multiple products from the catalogue dropdown to automatically attach them as sub-components / BOM parts.')
                    ->modalWidth('2xl')
                    ->form([
                        Select::make('component_product_ids')
                            ->label('Select Catalogue Products (Multiple Dropdown)')
                            ->multiple()
                            ->options(fn() => Product::orderBy('canonical_name')->pluck('canonical_name', 'id'))
                            ->searchable()
                            ->required()
                            ->placeholder('Search and select products...')
                            ->helperText('Selected products will be bulk-created as BOM sub-components.'),

                        TextInput::make('default_quantity')
                            ->label('Quantity per Parent Unit')
                            ->numeric()
                            ->default(1.0)
                            ->minValue(0.0001)
                            ->required()
                            ->helperText('Number of units required per 1 unit of the parent product.'),
                    ])
                    ->action(function (array $data): void {
                        $parentProduct = $this->getOwnerRecord();
                        $productIds = $data['component_product_ids'] ?? [];
                        $qty = (float) ($data['default_quantity'] ?? 1.0);
                        $count = 0;

                        foreach ($productIds as $prodId) {
                            $catalogItem = Product::find($prodId);
                            if (!$catalogItem) continue;

                            ProductComponent::create([
                                'parent_product_id'    => $parentProduct->id,
                                'component_product_id' => $catalogItem->id,
                                'component_name'       => $catalogItem->canonical_name,
                                'product_code'         => $catalogItem->product_code ?: $catalogItem->sku,
                                'category'             => $catalogItem->category ?: 'General',
                                'wattage'              => $catalogItem->wattage,
                                'voltage'              => $catalogItem->voltage,
                                'color_temperature'    => $catalogItem->color_temperature,
                                'unit'                 => $catalogItem->unit_default ?: 'pcs',
                                'cost_price'           => $catalogItem->base_cost_price > 0 ? $catalogItem->base_cost_price : $catalogItem->selling_price,
                                'quantity'             => $qty,
                                'image_path'           => $catalogItem->image_path,
                                'component_group'      => $catalogItem->category ?: 'General',
                                'option_name'          => $catalogItem->canonical_name,
                                'additional_cost'      => $catalogItem->base_cost_price > 0 ? $catalogItem->base_cost_price : $catalogItem->selling_price,
                            ]);
                            $count++;
                        }

                        Notification::make()
                            ->title("{$count} Sub-Components Created")
                            ->body("Successfully attached {$count} parts to {$parentProduct->canonical_name}. Stocks and BOM capacity updated.")
                            ->success()
                            ->send();
                    }),

                CreateAction::make()
                    ->label('Add Single Sub-Component')
                    ->icon('heroicon-o-plus')
                    ->modalHeading('Add Product Sub-Component (BOM Part)')
                    ->mutateFormDataUsing(function (array $data): array {
                        // Ensure legacy columns are also populated for backwards compatibility
                        $data['component_group'] = $data['category'] ?: ($data['component_group'] ?? 'General');
                        $data['option_name'] = $data['component_name'] ?: ($data['option_name'] ?? 'Part');
                        $data['additional_cost'] = $data['cost_price'] ?? 0.00;
                        return $data;
                    }),
            ])
            ->actions([
                EditAction::make()
                    ->modalHeading('Edit Sub-Component (BOM Part)')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['component_group'] = $data['category'] ?: ($data['component_group'] ?? 'General');
                        $data['option_name'] = $data['component_name'] ?: ($data['option_name'] ?? 'Part');
                        $data['additional_cost'] = $data['cost_price'] ?? 0.00;
                        return $data;
                    }),
                DeleteAction::make()->requiresConfirmation(),
            ]);
    }
}
