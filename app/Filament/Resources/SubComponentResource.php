<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubComponentResource\Pages;
use App\Models\Product;
use App\Models\ProductComponent;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SubComponentResource extends Resource
{
    protected static ?string $model = ProductComponent::class;

    protected static \UnitEnum|string|null $navigationGroup = 'Master Data & Registry';
    protected static ?string $navigationParentItem = 'Products Catalog';
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-puzzle-piece';
    protected static ?string $navigationLabel = 'Sub-Components & BOM';
    protected static ?int $navigationSort = 3;

    public static function canCreate(): bool
    {
        return auth()->user()?->canManageCatalog() ?? true;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->canManageCatalog() ?? true;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->canDeleteRecords() ?? true;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['parentProduct', 'componentProduct.inventoryItem']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Parent Product & BOM Assembly')
                ->description('Specify the finished good product that requires this sub-component part.')
                ->schema([
                    Select::make('parent_product_id')
                        ->label('Parent Finished Product (Assembled Good)')
                        ->options(fn() => Product::orderBy('canonical_name')->pluck('canonical_name', 'id'))
                        ->searchable()
                        ->required()
                        ->helperText('The catalog product that will consume this sub-component.')
                        ->columnSpanFull(),
                ]),

            Section::make('Sub-Component Specification & Linkage')
                ->description('Link an existing catalogue item or specify bespoke part details.')
                ->schema([
                    Grid::make(3)->schema([
                        Select::make('component_product_id')
                            ->label('Link Catalogue Product (Dropdown Selection)')
                            ->placeholder('Select catalog item to auto-populate specifications...')
                            ->options(fn() => Product::orderBy('canonical_name')->pluck('canonical_name', 'id'))
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if (!$state) return;
                                $item = Product::find($state);
                                if ($item) {
                                    $set('component_name', $item->canonical_name);
                                    $set('product_code', $item->product_code ?: $item->sku);
                                    $set('category', $item->category);
                                    $set('wattage', $item->wattage);
                                    $set('voltage', $item->voltage);
                                    $set('color_temperature', $item->color_temperature);
                                    $set('unit', $item->unit_default ?: 'pcs');
                                    $set('cost_price', $item->base_cost_price > 0 ? $item->base_cost_price : $item->selling_price);
                                    $set('image_path', $item->image_path);
                                    $set('component_group', $item->category ?: 'General');
                                    $set('option_name', $item->canonical_name);
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
                            ->helperText('Units consumed to build 1 finished unit.')
                            ->columnSpan(['default' => 3, 'lg' => 1]),
                    ]),

                    Grid::make(3)->schema([
                        TextInput::make('component_name')
                            ->label('Part Name / Description')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(['default' => 3, 'lg' => 2]),

                        TextInput::make('product_code')
                            ->label('Part Code / SKU')
                            ->maxLength(100)
                            ->columnSpan(['default' => 3, 'lg' => 1]),
                    ]),

                    Grid::make(4)->schema([
                        TextInput::make('category')
                            ->label('Category')
                            ->placeholder('e.g. Driver, Housing, Chip, Optics'),

                        TextInput::make('wattage')
                            ->label('Wattage')
                            ->placeholder('e.g. 7W, 12W/M, 50W'),

                        TextInput::make('voltage')
                            ->label('Voltage')
                            ->placeholder('e.g. 12V DC, 220V AC'),

                        TextInput::make('color_temperature')
                            ->label('Color CCT')
                            ->placeholder('e.g. 3000K, 4000K, 5000K'),
                    ]),

                    Grid::make(2)->schema([
                        TextInput::make('unit')
                            ->label('Unit of Measure')
                            ->default('pcs'),

                        TextInput::make('cost_price')
                            ->label('Unit Cost (₱)')
                            ->numeric()
                            ->prefix('₱')
                            ->default(0.00),
                    ]),

                    Textarea::make('notes')
                        ->label('Technical Notes')
                        ->rows(2)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('parentProduct.canonical_name')
                    ->label('Parent Product')
                    ->weight('bold')
                    ->wrap()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('effective_code')
                    ->label('Part Code')
                    ->weight('semibold')
                    ->searchable()
                    ->sortable()
                    ->default('—'),

                TextColumn::make('effective_name')
                    ->label('Component Name')
                    ->wrap()
                    ->searchable()
                    ->description(fn(ProductComponent $r) => $r->componentProduct ? 'Linked Catalog: ' . $r->componentProduct->canonical_name : 'Custom Part'),

                TextColumn::make('effective_category')
                    ->label('Category')
                    ->badge()
                    ->color('info')
                    ->default('General'),

                TextColumn::make('quantity')
                    ->label('Qty / Unit')
                    ->badge()
                    ->color('success')
                    ->formatStateUsing(fn($state, ProductComponent $r) => number_format((float) ($state ?: 1), 2) . ' ' . $r->effective_unit),

                TextColumn::make('stock_on_hand')
                    ->label('Stock On Hand')
                    ->badge()
                    ->color(fn($state) => $state === null ? 'gray' : ((float) $state <= 0 ? 'danger' : 'success'))
                    ->formatStateUsing(fn($state, ProductComponent $r) => $state !== null ? number_format((float) $state, 2) . ' ' . $r->effective_unit : 'Custom')
                    ->tooltip(fn(ProductComponent $r) => $r->componentProduct ? "Current inventory stock balance for {$r->componentProduct->canonical_name}" : 'Custom part not tracked separately'),

                TextColumn::make('assembleable_units')
                    ->label('Max Build Units')
                    ->badge()
                    ->state(function (ProductComponent $r): string {
                        if ($r->stock_on_hand === null) return 'Custom';
                        $qty = (float) ($r->quantity ?: 1);
                        return $qty > 0 ? number_format(floor($r->stock_on_hand / $qty), 0) . ' units' : '0 units';
                    })
                    ->color(function (ProductComponent $r): string {
                        if ($r->stock_on_hand === null) return 'gray';
                        $qty = (float) ($r->quantity ?: 1);
                        $avail = $qty > 0 ? floor($r->stock_on_hand / $qty) : 0;
                        return $avail <= 0 ? 'danger' : ($avail < 10 ? 'warning' : 'success');
                    })
                    ->tooltip('Maximum parent units that can be built from current stock of this component'),

                TextColumn::make('effective_cost')
                    ->label('Unit Cost (₱)')
                    ->money('PHP')
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('total_cost')
                    ->label('Total Cost (₱)')
                    ->money('PHP')
                    ->weight('bold')
                    ->color('success'),
            ])
            ->filters([
                SelectFilter::make('parent_product_id')
                    ->label('Filter by Parent Product')
                    ->options(fn() => Product::orderBy('canonical_name')->pluck('canonical_name', 'id'))
                    ->searchable(),

                SelectFilter::make('category')
                    ->label('Filter by Category')
                    ->options(fn() => ProductComponent::distinct()->whereNotNull('category')->pluck('category', 'category')),
            ])
            ->headerActions([
                Action::make('bulk_add_subcomponents')
                    ->label('Bulk Add Sub-Components')
                    ->icon('heroicon-o-squares-plus')
                    ->color('primary')
                    ->modalHeading('Bulk Add Sub-Components (Select Parent & Multiple Catalog Components)')
                    ->modalDescription('Select a parent finished good and multiple catalogue components to attach in bulk.')
                    ->modalWidth('2xl')
                    ->form([
                        Select::make('parent_product_id')
                            ->label('Select Parent Finished Product')
                            ->options(fn() => Product::orderBy('canonical_name')->pluck('canonical_name', 'id'))
                            ->searchable()
                            ->required(),

                        Select::make('component_product_ids')
                            ->label('Select Catalogue Components (Multiple Dropdown)')
                            ->multiple()
                            ->options(fn() => Product::orderBy('canonical_name')->pluck('canonical_name', 'id'))
                            ->searchable()
                            ->required()
                            ->placeholder('Search and select parts...'),

                        TextInput::make('default_quantity')
                            ->label('Default Quantity per Parent Unit')
                            ->numeric()
                            ->default(1.0)
                            ->minValue(0.0001)
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        $parent = Product::find($data['parent_product_id']);
                        if (!$parent) return;

                        $ids = $data['component_product_ids'] ?? [];
                        $qty = (float) ($data['default_quantity'] ?? 1.0);
                        $count = 0;

                        foreach ($ids as $cid) {
                            $part = Product::find($cid);
                            if (!$part) continue;

                            ProductComponent::create([
                                'parent_product_id'    => $parent->id,
                                'component_product_id' => $part->id,
                                'component_name'       => $part->canonical_name,
                                'product_code'         => $part->product_code ?: $part->sku,
                                'category'             => $part->category ?: 'General',
                                'wattage'              => $part->wattage,
                                'voltage'              => $part->voltage,
                                'color_temperature'    => $part->color_temperature,
                                'unit'                 => $part->unit_default ?: 'pcs',
                                'cost_price'           => $part->base_cost_price > 0 ? $part->base_cost_price : $part->selling_price,
                                'quantity'             => $qty,
                                'image_path'           => $part->image_path,
                                'component_group'      => $part->category ?: 'General',
                                'option_name'          => $part->canonical_name,
                                'additional_cost'      => $part->base_cost_price > 0 ? $part->base_cost_price : $part->selling_price,
                            ]);
                            $count++;
                        }

                        Notification::make()
                            ->title("{$count} Sub-Components Attached")
                            ->body("Successfully attached {$count} parts to {$parent->canonical_name}. Stocks and BOM metrics updated.")
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make()->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->requiresConfirmation(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSubComponents::route('/'),
            'create' => Pages\CreateSubComponent::route('/create'),
            'edit'   => Pages\EditSubComponent::route('/{record}/edit'),
        ];
    }
}
