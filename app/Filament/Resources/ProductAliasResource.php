<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductAliasResource\Pages;
use App\Models\Product;
use App\Models\ProductAlias;
use App\Models\Vendor;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ProductAliasResource extends Resource
{
    protected static ?string $model = ProductAlias::class;

    protected static \UnitEnum|string|null $navigationGroup = 'Master Data & Registry';
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?string $navigationLabel = 'Product Aliases & Matching';
    protected static ?int $navigationSort = 2;

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
                Section::make('Product Alias Mapping')
                    ->description('Maps messy or variant PDF line item descriptions to a canonical product.')
                    ->components([
                        Forms\Components\Select::make('product_id')
                            ->label('Canonical Product')
                            ->options(Product::pluck('canonical_name', 'id'))
                            ->searchable()
                            ->required(),

                        Forms\Components\TextInput::make('alias_text')
                            ->label('Raw PDF Description / Alias')
                            ->required()
                            ->maxLength(500)
                            ->helperText('e.g. 1-1/4" PVC Pipe Sch 40'),

                        Forms\Components\Select::make('vendor_id')
                            ->label('Vendor (Optional)')
                            ->options(Vendor::pluck('name', 'id'))
                            ->searchable()
                            ->placeholder('Applies to all vendors'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('alias_text')
                    ->label('PDF Line Description')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('product.canonical_name')
                    ->label('Maps To Canonical Product')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('vendor.name')
                    ->label('Vendor Specific')
                    ->searchable()
                    ->default('All Vendors'),

                Tables\Columns\TextColumn::make('normalized_alias')
                    ->label('Normalized Search Key')
                    ->fontFamily('mono')
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make(),
                ])
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
            'index' => Pages\ListProductAliases::route('/'),
            'create' => Pages\CreateProductAlias::route('/create'),
            'edit' => Pages\EditProductAlias::route('/{record}/edit'),
        ];
    }
}
