<?php

namespace App\Filament\Resources;

use App\Enums\UnitOfMeasure;
use App\Filament\Resources\SubComponentResource\Pages;
use App\Models\Product;
use App\Models\ProductComponent;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SubComponentResource extends Resource
{
    protected static ?string $model = ProductComponent::class;

    protected static ?string $modelLabel = 'Sub component';

    protected static ?string $pluralModelLabel = 'Sub component';

    protected static \UnitEnum|string|null $navigationGroup = 'Master Data & Registry';

    protected static ?string $navigationParentItem = 'Products Catalog';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-puzzle-piece';

    protected static ?string $navigationLabel = 'Sub component';

    protected static ?int $navigationSort = 3;

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

    public static function getEloquentQuery(): Builder
    {
        // Serve standalone sub-component master records only (parent-child linkage belongs in Product Catalog)
        return parent::getEloquentQuery()
            ->whereNull('parent_product_id')
            ->with(['componentProduct.inventoryItem']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Sub Component Identification & Category')
                ->description('Define standalone sub component part registry specifications.')
                ->icon('heroicon-o-puzzle-piece')
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('component_name')
                            ->label('Part Name / Description')
                            ->placeholder('e.g. Meanwell LED Driver 12V 5A, Citizen COB Chip, E27 Aluminum Base')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(['default' => 3, 'lg' => 2]),

                        TextInput::make('product_code')
                            ->label('Part Code / SKU / Model #')
                            ->placeholder('e.g. DRV-12V-5A, CHIP-COB-3500K')
                            ->maxLength(100)
                            ->columnSpan(['default' => 3, 'lg' => 1]),
                    ]),

                    Grid::make(3)->schema([
                        TextInput::make('category')
                            ->label('Category / Part Group')
                            ->placeholder('e.g. Driver, Chip, Housing, Optics, Socket, Heatsink, Wire')
                            ->maxLength(100)
                            ->columnSpan(['default' => 3, 'lg' => 1]),

                        Select::make('unit')
                            ->label('Unit of Measure')
                            ->options(UnitOfMeasure::class)
                            ->default('pcs')
                            ->required()
                            ->columnSpan(['default' => 3, 'lg' => 1]),

                        TextInput::make('cost_price')
                            ->label('Unit Cost (₱)')
                            ->numeric()
                            ->prefix('₱')
                            ->default(0.00)
                            ->helperText('Standard unit cost.')
                            ->columnSpan(['default' => 3, 'lg' => 1]),
                    ]),
                ]),

            Section::make('Technical Specifications')
                ->description('Electrical, optical, and physical engineering parameters.')
                ->icon('heroicon-o-cpu-chip')
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('wattage')
                            ->label('Wattage')
                            ->placeholder('e.g. 7W, 12W, 50W')
                            ->maxLength(100),

                        TextInput::make('voltage')
                            ->label('Voltage')
                            ->placeholder('e.g. 12V DC, 220V AC, 36V DC')
                            ->maxLength(100),

                        TextInput::make('color_temperature')
                            ->label('Color Temperature / CCT')
                            ->placeholder('e.g. 3000K Warm White, 4000K Cool White, 6500K')
                            ->maxLength(100),
                    ]),
                ]),

            Section::make('Warehouse Stock Linkage & Inventory Tracking (Optional)')
                ->description('Optionally link with an existing product in the warehouse catalog to monitor inventory stock levels.')
                ->icon('heroicon-o-building-storefront')
                ->collapsed()
                ->schema([
                    Select::make('component_product_id')
                        ->label('Linked Warehouse Catalog Product')
                        ->placeholder('Select product from warehouse catalog...')
                        ->options(fn () => Product::orderBy('canonical_name')->pluck('canonical_name', 'id'))
                        ->searchable()
                        ->nullable()
                        ->helperText('If linked, stock on hand will reflect this warehouse catalog product.'),
                ]),

            Section::make('Technical Notes & Documentation')
                ->icon('heroicon-o-document-text')
                ->collapsed()
                ->schema([
                    Textarea::make('notes')
                        ->label('Technical & Manufacturing Notes')
                        ->placeholder('e.g. Requires thermal paste application during assembly. UL listed.')
                        ->rows(3)
                        ->columnSpanFull(),

                    FileUpload::make('image_path')
                        ->label('Part Drawing / Photo')
                        ->image()
                        ->directory('components')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('effective_code')
                    ->label('Part Code')
                    ->weight('semibold')
                    ->searchable(query: fn (Builder $query, string $search) => $query->where('product_code', 'like', "%{$search}%"))
                    ->sortable(query: fn (Builder $query, string $direction) => $query->orderBy('product_code', $direction))
                    ->copyable()
                    ->default('—'),

                TextColumn::make('effective_name')
                    ->label('Component Name')
                    ->weight('bold')
                    ->wrap()
                    ->searchable(query: fn (Builder $query, string $search) => $query->where('component_name', 'like', "%{$search}%"))
                    ->sortable(query: fn (Builder $query, string $direction) => $query->orderBy('component_name', $direction))
                    ->description(fn (ProductComponent $r) => $r->componentProduct ? 'Linked Inventory: '.$r->componentProduct->canonical_name : null),

                TextColumn::make('effective_category')
                    ->label('Category')
                    ->badge()
                    ->color('info')
                    ->default('General')
                    ->sortable(query: fn (Builder $query, string $direction) => $query->orderBy('category', $direction)),

                TextColumn::make('wattage')
                    ->label('Wattage')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('voltage')
                    ->label('Voltage')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('color_temperature')
                    ->label('Color CCT')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('effective_unit')
                    ->label('Unit')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('effective_cost')
                    ->label('Unit Cost (₱)')
                    ->money('PHP')
                    ->color('success')
                    ->weight('semibold')
                    ->sortable(query: fn (Builder $query, string $direction) => $query->orderBy('cost_price', $direction)),

                TextColumn::make('stock_on_hand')
                    ->label('Stock On Hand')
                    ->badge()
                    ->color(fn ($state) => $state === null ? 'gray' : ((float) $state <= 0 ? 'danger' : 'success'))
                    ->formatStateUsing(fn ($state, ProductComponent $r) => $state !== null ? number_format((float) $state, 2).' '.$r->effective_unit : 'Custom')
                    ->tooltip(fn (ProductComponent $r) => $r->componentProduct ? "Current inventory stock for {$r->componentProduct->canonical_name}" : 'Not tracked in warehouse catalog'),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->date('M d, Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Filter by Category')
                    ->options(fn () => ProductComponent::whereNull('parent_product_id')->distinct()->whereNotNull('category')->pluck('category', 'category')),
            ])
            ->actions([
                EditAction::make()
                    ->modalHeading('Edit Sub Component')
                    ->modalWidth('3xl')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['component_group'] = $data['category'] ?: 'General';
                        $data['option_name'] = $data['component_name'] ?: 'Part';
                        $data['additional_cost'] = $data['cost_price'] ?? 0.00;

                        return $data;
                    }),
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
            'index' => Pages\ListSubComponents::route('/'),
            'create' => Pages\CreateSubComponent::route('/create'),
            'edit' => Pages\EditSubComponent::route('/{record}/edit'),
        ];
    }
}
