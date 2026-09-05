<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TestimonialResource\Pages;
use App\Models\Testimonial;
use Filament\Actions;
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

class TestimonialResource extends Resource
{
    protected static ?string $model = Testimonial::class;

    protected static \UnitEnum|string|null $navigationGroup = 'Sales & Marketing';
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';
    protected static ?string $navigationLabel = 'Client Testimonials';
    protected static ?int $navigationSort = 5;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Testimonial Details')
                    ->description('Verified client and commercial contractor reviews for the customer portal.')
                    ->components([
                        Forms\Components\TextInput::make('client_name')
                            ->label('Client / Contact Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g. Engr. Juan Dela Cruz'),

                        Forms\Components\TextInput::make('company_name')
                            ->label('Company / Firm Name')
                            ->maxLength(255)
                            ->placeholder('e.g. Megawide Construction Corp.'),

                        Forms\Components\TextInput::make('role_title')
                            ->label('Job / Role Title')
                            ->maxLength(255)
                            ->placeholder('e.g. Senior MEPF Project Director'),

                        Forms\Components\TextInput::make('project_name')
                            ->label('Project / Site Reference')
                            ->maxLength(255)
                            ->placeholder('e.g. Palanza Tower Fit-Out'),

                        Forms\Components\Textarea::make('quote')
                            ->label('Review / Testimonial Quote')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull()
                            ->placeholder('Enter the verified quote from the client...'),

                        Forms\Components\TextInput::make('rating')
                            ->label('Star Rating (1 - 5)')
                            ->numeric()
                            ->default(5)
                            ->minValue(1)
                            ->maxValue(5)
                            ->required(),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Display Sort Order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Lower numbers appear first on the landing page.'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Publish to Public Website')
                            ->default(true)
                            ->helperText('When enabled, this review displays on the customer landing page.'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('client_name')
                    ->label('Client Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('company_name')
                    ->label('Company')
                    ->searchable()
                    ->sortable()
                    ->default('—'),

                Tables\Columns\TextColumn::make('role_title')
                    ->label('Role')
                    ->searchable()
                    ->default('—'),

                Tables\Columns\TextColumn::make('project_name')
                    ->label('Project')
                    ->searchable()
                    ->default('—'),

                Tables\Columns\TextColumn::make('rating')
                    ->label('Rating')
                    ->formatStateUsing(fn ($state) => str_repeat('★', (int) $state) . str_repeat('☆', 5 - (int) $state))
                    ->color('warning')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Published')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Added On')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Published Status')
                    ->boolean(),
                TrashedFilter::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ], position: RecordActionsPosition::BeforeColumns)
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTestimonials::route('/'),
            'create' => Pages\CreateTestimonial::route('/create'),
            'edit' => Pages\EditTestimonial::route('/{record}/edit'),
        ];
    }
}
