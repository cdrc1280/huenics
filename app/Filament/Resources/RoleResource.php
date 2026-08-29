<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use App\Models\Permission;
use App\Models\Role;
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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static \UnitEnum|string|null $navigationGroup = 'System Administration';
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationLabel = 'Dynamic Roles & Permissions';
    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user && ($user->isAdmin() || $user->isCeo() || $user->canConfigureRoles());
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        return $user && ($user->isAdmin() || $user->isCeo() || $user->canConfigureRoles());
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Role Identity & Scope')
                    ->description('Define the role name, system key, and description.')
                    ->components([
                        Forms\Components\TextInput::make('name')
                            ->label('Role Title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, $state, Forms\Set $set, ?Role $record) {
                                if ($operation === 'create' || empty($record?->slug)) {
                                    $set('slug', Str::slug($state, '_'));
                                }
                            }),

                        Forms\Components\TextInput::make('slug')
                            ->label('System Role Slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->disabled(fn(?Role $record) => $record?->is_system ?? false)
                            ->dehydrated()
                            ->helperText('Unique system identifier (e.g. sales_executive, operations_manager).'),

                        Forms\Components\Textarea::make('description')
                            ->label('Operational Description')
                            ->rows(2)
                            ->columnSpanFull()
                            ->helperText('Brief overview of responsibilities and scope of this role.'),

                        Forms\Components\Toggle::make('is_system')
                            ->label('Core System Role (Protected)')
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Core system roles cannot be deleted to maintain system integrity.'),
                    ])->columns(2),

                Section::make('Module Permissions Matrix')
                    ->description('Select granular permissions granted to users with this role across all business workflows.')
                    ->components([
                        Forms\Components\CheckboxList::make('permissions')
                            ->relationship('permissions', 'name')
                            ->descriptions(
                                fn() => Permission::pluck('description', 'id')->toArray()
                            )
                            ->columns(2)
                            ->searchable()
                            ->bulkToggleable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Role Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('slug')
                    ->label('System Slug')
                    ->badge()
                    ->color('gray')
                    ->fontFamily('mono')
                    ->searchable(),

                Tables\Columns\TextColumn::make('permissions_count')
                    ->counts('permissions')
                    ->label('Assigned Permissions')
                    ->badge()
                    ->color(fn(int $state): string => match (true) {
                        $state >= 15 => 'success',
                        $state >= 8 => 'warning',
                        $state >= 1 => 'info',
                        default => 'danger',
                    }),

                Tables\Columns\TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Active Staff')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\IconColumn::make('is_system')
                    ->label('Core Role')
                    ->boolean(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Modified')
                    ->dateTime('M j, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make()
                        ->requiresConfirmation()
                        ->hidden(fn(Role $record): bool => $record->is_system),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $records->filter(fn(Role $r) => !$r->is_system)->each->delete();
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
