<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static \UnitEnum|string|null $navigationGroup = 'Catalog & Mapping';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-cube';

    protected static ?int $navigationSort = 1;

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
        return auth()->user()?->isAdmin() ?? true;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Canonical Product Details')
                    ->components([
                        Forms\Components\TextInput::make('canonical_name')
                            ->label('Canonical Product Name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('product_code')
                            ->label('Product Code / Model #')
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),

                        Forms\Components\TextInput::make('sku')
                            ->label('SKU')
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),

                        Forms\Components\TextInput::make('category')
                            ->label('Category')
                            ->placeholder('e.g. LED Lighting, Drivers, Architectural'),

                        Forms\Components\TextInput::make('unit_default')
                            ->label('Default Unit')
                            ->default('pcs')
                            ->required(),

                        Forms\Components\TextInput::make('base_cost_price')
                            ->label('Base Cost (₱)')
                            ->numeric()
                            ->prefix('₱')
                            ->default(0.00),

                        Forms\Components\TextInput::make('selling_price')
                            ->label('Selling Price (₱)')
                            ->numeric()
                            ->prefix('₱')
                            ->default(0.00),

                        Forms\Components\Toggle::make('is_huenics_owned')
                            ->label('Huenics Proprietary Product')
                            ->helperText('Only Huenics-owned products track inventory stock-on-hand.')
                            ->default(true),

                        Forms\Components\Toggle::make('is_composite')
                            ->label('Composite Modular BOM Product')
                            ->helperText('Enable for modular products assembled from sub-components (e.g. LED Tracklight with COB/Driver).')
                            ->default(false),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active in Catalog')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product_code')
                    ->label('Code / SKU')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->default('—')
                    ->tooltip(fn (Product $record): string => "Product Code: " . ($record->product_code ?: 'N/A')),

                Tables\Columns\TextColumn::make('canonical_name')
                    ->label('Product Name')
                    ->searchable()
                    ->sortable()
                    ->tooltip(fn (Product $record): string => "Canonical Name: {$record->canonical_name}"),

                Tables\Columns\TextColumn::make('category')
                    ->label('Category')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->default('General')
                    ->tooltip(fn (Product $record): string => "Product Category: " . ($record->category ?: 'General')),

                Tables\Columns\IconColumn::make('is_huenics_owned')
                    ->label('Huenics Stock')
                    ->boolean()
                    ->trueIcon('heroicon-s-check-badge')
                    ->falseIcon('heroicon-o-cube-transparent')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip(fn (Product $record): string => $record->is_huenics_owned ? 'Huenics Proprietary Product: In-house inventory tracked' : 'Third-Party Product'),

                Tables\Columns\IconColumn::make('is_composite')
                    ->label('Modular BOM')
                    ->boolean()
                    ->trueIcon('heroicon-s-puzzle-piece')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('primary')
                    ->falseColor('gray')
                    ->tooltip(fn (Product $record): string => $record->is_composite ? 'Modular BOM: Assembled from configurable sub-components' : 'Standard Unit Product'),

                Tables\Columns\TextColumn::make('base_cost_price')
                    ->label('Cost (₱)')
                    ->money('PHP')
                    ->sortable()
                    ->tooltip(fn (Product $record): string => "Base acquisition/manufacturing cost: ₱" . number_format((float) $record->base_cost_price, 2)),

                Tables\Columns\TextColumn::make('selling_price')
                    ->label('Selling Price (₱)')
                    ->money('PHP')
                    ->sortable()
                    ->weight('bold')
                    ->color('success')
                    ->tooltip(fn (Product $record): string => "Standard catalogue selling price: ₱" . number_format((float) $record->selling_price, 2)),

                Tables\Columns\TextColumn::make('unit_default')
                    ->label('Unit')
                    ->tooltip('Default unit of measure (e.g. pcs, sets, meters)'),
            ])

            ->filters([
                Tables\Filters\TernaryFilter::make('is_huenics_owned')
                    ->label('Huenics Proprietary Only'),
                Tables\Filters\TernaryFilter::make('is_composite')
                    ->label('Modular BOM Only'),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
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
