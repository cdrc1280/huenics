<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Models\Project;
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
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static \UnitEnum|string|null $navigationGroup = 'Master Data & Registry';
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationLabel = 'Projects & Job Sites';
    protected static ?int $navigationSort = 3;

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

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->canManageCatalog() ?? true;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->canDeleteRecords() ?? true;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Project / Job Site')
                    ->components([
                        Forms\Components\TextInput::make('name')
                            ->label('Project / Site Name')
                            ->placeholder('e.g. Palanza Tower')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('code')
                            ->label('Project Code')
                            ->placeholder('e.g. PRJ-PALANZA')
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),

                        Forms\Components\TextInput::make('customer_name')
                            ->label('Customer / General Contractor')
                            ->placeholder('e.g. MGS Corporation')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('location')
                            ->label('Project Location / Address')
                            ->maxLength(255),

                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'completed' => 'Completed',
                                'on_hold' => 'On Hold',
                            ])
                            ->default('active')
                            ->required(),

                        Forms\Components\Textarea::make('description')
                            ->label('Description & Scope')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Project Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Customer / GC')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('location')
                    ->label('Location')
                    ->searchable()
                    ->default('—'),

                Tables\Columns\TextColumn::make('documents_count')
                    ->counts('documents')
                    ->label('Documents')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('transactions_count')
                    ->counts('transactions')
                    ->label('Transactions')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'completed' => 'info',
                        'on_hold' => 'warning',
                        default => 'gray',
                    }),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make()->requiresConfirmation(),
                    RestoreAction::make()->requiresConfirmation()->visible(fn(Project $record): bool => $record->trashed()),
                    ForceDeleteAction::make()->requiresConfirmation()->visible(fn(Project $record): bool => $record->trashed() && (auth()->user()?->canDeleteRecords() ?? false)),
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
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
