<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Models\Vendor;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;

class AliasesRelationManager extends RelationManager
{
    protected static string $relationship = 'aliases';

    protected static ?string $title = 'Product Aliases & OCR Match Patterns';

    protected static \BackedEnum|string|null $icon = 'heroicon-o-arrows-right-left';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Alias & Pattern Mapping')
                ->description('Associate messy, abbreviated, or vendor-specific line descriptions from PDF orders to this canonical catalog product.')
                ->icon('heroicon-o-arrows-right-left')
                ->schema([
                    TextInput::make('alias_text')
                        ->label('Raw PDF Description / Alias')
                        ->required()
                        ->maxLength(500)
                        ->placeholder('e.g. 1-1/4" PVC Pipe Sch 40 or G.I. Nipple 1/2 x 2"')
                        ->helperText('Exact or variant description as printed on vendor or client document line items.'),

                    Select::make('vendor_id')
                        ->label('Specific Vendor (Optional)')
                        ->options(fn () => Vendor::orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->placeholder('Universal (Matches across all vendors)')
                        ->helperText('Leave empty to match across all vendors, or select a specific supplier.'),
                ])->columns(2),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('alias_text')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('alias_text')
                    ->label('PDF Line Description')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->wrap(),

                TextColumn::make('vendor.name')
                    ->label('Vendor Scope')
                    ->badge()
                    ->color(fn ($state) => $state ? 'info' : 'gray')
                    ->default('Universal (All Vendors)')
                    ->searchable(),

                TextColumn::make('normalized_alias')
                    ->label('Normalized Pattern')
                    ->fontFamily('mono')
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Added On')
                    ->date('M j, Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Add Alias')
                    ->icon('heroicon-o-plus'),
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make()->requiresConfirmation(),
                ]),
            ], position: RecordActionsPosition::BeforeColumns)
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->requiresConfirmation(),
                ]),
            ]);
    }
}
