<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Document;
use App\Models\Project;
use App\Models\Transaction;
use App\Models\Vendor;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static \UnitEnum|string|null $navigationGroup = 'Sales & Order Lifecycle';
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-table-cells';
    protected static ?string $navigationLabel = 'Master Transactions Ledger';
    protected static ?int $navigationSort = 4;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->canEditTransactions() ?? true;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->canEditTransactions() ?? true;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->isAdmin() ?? true;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Transaction Record')
                    ->components([
                        Forms\Components\TextInput::make('transaction_code')
                            ->label('Transaction Reference Code')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Select::make('project_id')
                            ->label('Project / Customer Job')
                            ->options(Project::pluck('name', 'id'))
                            ->required()
                            ->searchable(),

                        Forms\Components\Select::make('vendor_id')
                            ->label('Vendor')
                            ->options(Vendor::pluck('name', 'id'))
                            ->required()
                            ->searchable(),

                        Forms\Components\TextInput::make('final_amount')
                            ->label('Authoritative Reconciled Amount (₱)')
                            ->numeric()
                            ->prefix('₱')
                            ->required(),

                        Forms\Components\DatePicker::make('order_date')
                            ->label('Order Date'),

                        Forms\Components\DatePicker::make('delivery_date')
                            ->label('Delivery Date'),

                        Forms\Components\Select::make('status')
                            ->options([
                                'pending_delivery' => 'Pending Delivery',
                                'delivered' => 'Delivered / Fulfilled',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required()
                            ->default('pending_delivery'),

                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Associated Verification Documents (3-Way Match)')
                    ->components([
                        Forms\Components\Select::make('quotation_document_id')
                            ->label('1. Vendors Agreement Form (Quotation)')
                            ->options(Document::where('document_type', Document::TYPE_VENDORS_AGREEMENT)->pluck('document_number', 'id'))
                            ->searchable()
                            ->placeholder('No quotation linked'),

                        Forms\Components\Select::make('purchase_order_document_id')
                            ->label('2. Purchase Order (Customer Order)')
                            ->options(Document::where('document_type', Document::TYPE_PURCHASE_ORDER)->pluck('document_number', 'id'))
                            ->searchable()
                            ->placeholder('No PO linked'),

                        Forms\Components\Select::make('order_slip_document_id')
                            ->label('3. Order Slip (Internal Sales Order)')
                            ->options(Document::where('document_type', Document::TYPE_ORDER_SLIP)->pluck('document_number', 'id'))
                            ->searchable()
                            ->placeholder('No Order Slip linked'),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction_code')
                    ->label('Transaction ID')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),

                Tables\Columns\TextColumn::make('project.name')
                    ->label('Project')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('vendor.name')
                    ->label('Vendor')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('final_amount')
                    ->label('Final Amount (₱)')
                    ->money('PHP')
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('order_date')
                    ->label('Order Date')
                    ->date('M j, Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending_delivery' => 'warning',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\IconColumn::make('3way_match')
                    ->label('3-Way Match')
                    ->state(fn(Transaction $record): bool => !empty($record->quotation_document_id) && !empty($record->purchase_order_document_id) && !empty($record->order_slip_document_id))
                    ->boolean()
                    ->trueIcon('heroicon-s-shield-check')
                    ->falseIcon('heroicon-o-link')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip(fn(Transaction $record): string => (!empty($record->quotation_document_id) && !empty($record->purchase_order_document_id) && !empty($record->order_slip_document_id)) ? 'Complete 3-Way Reconciled' : 'Partial Document Record'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Verified On')
                    ->dateTime('M j, Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make()->requiresConfirmation(),
                    RestoreAction::make()->requiresConfirmation()->visible(fn(Transaction $record): bool => $record->trashed()),
                    ForceDeleteAction::make()->requiresConfirmation()->visible(fn(Transaction $record): bool => $record->trashed() && (auth()->user()?->isAdmin() ?? false)),
                ]),
            ], position: RecordActionsPosition::BeforeColumns)
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->requiresConfirmation(),
                    RestoreBulkAction::make()->requiresConfirmation(),
                    ForceDeleteBulkAction::make()->requiresConfirmation()->visible(fn(): bool => auth()->user()?->isAdmin() ?? false),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
