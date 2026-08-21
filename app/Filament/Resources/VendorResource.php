<?php

namespace App\Filament\Resources;

use App\Filament\Pages\VendorLayoutEditorPage;
use App\Filament\Resources\VendorResource\Pages;
use App\Models\Vendor;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class VendorResource extends Resource
{
    protected static ?string $model = Vendor::class;

    protected static \UnitEnum|string|null $navigationGroup = 'Master Data & Registry';
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationLabel = 'Vendors & Suppliers';
    protected static ?int $navigationSort = 4;

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
                Section::make('Vendor Profile')
                    ->components([
                        Forms\Components\TextInput::make('name')
                            ->label('Vendor / Company Name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('tin')
                            ->label('Tax Identification Number (TIN)')
                            ->maxLength(50),

                        Forms\Components\TextInput::make('contact_person')
                            ->label('Contact Person')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('phone')
                            ->label('Phone Number')
                            ->tel(),

                        Forms\Components\TextInput::make('email')
                            ->label('Email Address')
                            ->email(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active Status')
                            ->default(true),

                        Forms\Components\Textarea::make('address')
                            ->label('Business Address')
                            ->rows(2)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notes & Extraction Remarks')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Vendor Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('tin')
                    ->label('TIN')
                    ->searchable()
                    ->default('—'),

                Tables\Columns\TextColumn::make('contact_person')
                    ->label('Contact')
                    ->searchable()
                    ->default('—'),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone')
                    ->default('—'),

                Tables\Columns\TextColumn::make('documents_count')
                    ->counts('documents')
                    ->label('Documents Ingested')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('transactions_count')
                    ->counts('transactions')
                    ->label('Transactions')
                    ->badge()
                    ->color('success'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->actions([
                Action::make('configure_layout')
                    ->label('Layout Config')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->color('info')
                    ->url(fn(Vendor $record): string => VendorLayoutEditorPage::getUrl()),
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
            'index' => Pages\ListVendors::route('/'),
            'create' => Pages\CreateVendor::route('/create'),
            'edit' => Pages\EditVendor::route('/{record}/edit'),
        ];
    }
}
