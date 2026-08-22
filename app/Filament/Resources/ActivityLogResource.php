<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use App\Models\AuditLog;
use App\Models\DeliveryReceipt;
use App\Models\Document;
use App\Models\Product;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\SalesInvoice;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Vendor;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class ActivityLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static \UnitEnum|string|null $navigationGroup = 'System Administration';
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-finger-print';
    protected static ?string $navigationLabel = 'Activity Logs & Audit Trail';
    protected static ?int $navigationSort = 2;
    protected static ?string $slug = 'activity-logs';

    public static function canViewAny(): bool
    {
        return auth()->user()?->canViewActivityLogs() ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->canViewActivityLogs() ?? false;
    }

    public static function canView(Model $record): bool
    {
        return auth()->user()?->canViewActivityLogs() ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->canDeleteRecords() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->poll('30s')
            ->columns([
                TextColumn::make('event')
                    ->label('Activity')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match (strtolower($state)) {
                        'created' => 'Created',
                        'updated', 'line_item_adjusted' => 'Updated',
                        'deleted' => 'Deleted',
                        'force_deleted' => 'Purged',
                        'restored' => 'Restored',
                        'login' => 'Sign In',
                        'logout' => 'Sign Out',
                        'verified' => 'Verified',
                        'converted' => 'Converted',
                        default => ucwords(str_replace('_', ' ', $state)),
                    })
                    ->colors([
                        'success' => fn($state) => in_array($state, ['created', 'verified', 'converted', 'restored']),
                        'info' => fn($state) => in_array($state, ['updated', 'line_item_adjusted']),
                        'danger' => fn($state) => in_array($state, ['deleted', 'force_deleted', 'document_rejected']),
                        'primary' => fn($state) => in_array($state, ['login']),
                        'gray' => fn($state) => in_array($state, ['logout', 'custom']),
                        'warning' => fn($state) => in_array($state, ['transaction_updated', 'status_changed']),
                    ])
                    ->sortable()
                    ->searchable(),

                TextColumn::make('subject_name')
                    ->label('Subject / Entity')
                    ->weight('semibold')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where('auditable_type', 'like', "%{$search}%")
                            ->orWhere('auditable_id', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%");
                    }),

                TextColumn::make('description')
                    ->label('Action Summary')
                    ->wrap()
                    ->searchable()
                    ->color('gray')
                    ->tooltip(fn(AuditLog $record): string => $record->description ?: $record->action),

                TextColumn::make('user.name')
                    ->label('Actor')
                    ->description(fn(AuditLog $record): string => $record->user ? ucwords(str_replace('_', ' ', $record->user->role)) : 'Automated Trigger')
                    ->searchable()
                    ->sortable()
                    ->default('System'),

                TextColumn::make('created_at')
                    ->label('Time')
                    ->since()
                    ->tooltip(fn(AuditLog $record): string => $record->created_at?->format('F d, Y h:i:s A') ?? '')
                    ->sortable(),

                TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->fontFamily('mono')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('event')
                    ->label('Filter by Activity')
                    ->options([
                        AuditLog::EVENT_CREATED => 'Created',
                        AuditLog::EVENT_UPDATED => 'Updated',
                        AuditLog::EVENT_DELETED => 'Deleted',
                        AuditLog::EVENT_RESTORED => 'Restored',
                        AuditLog::EVENT_FORCE_DELETED => 'Permanently Purged',
                        AuditLog::EVENT_LOGIN => 'User Sign-In',
                        AuditLog::EVENT_LOGOUT => 'User Sign-Out',
                        AuditLog::EVENT_VERIFIED => 'Document Verified',
                        AuditLog::EVENT_CONVERTED => 'Quotation Converted',
                    ]),

                SelectFilter::make('auditable_type')
                    ->label('Filter by Module')
                    ->options([
                        User::class => 'User Accounts',
                        Quotation::class => 'Quotations',
                        PurchaseOrder::class => 'Purchase Orders',
                        Product::class => 'Products Catalog',
                        Document::class => 'Ingested Documents',
                        DeliveryReceipt::class => 'Delivery Receipts',
                        SalesInvoice::class => 'Sales Invoices',
                        Transaction::class => 'Transactions Ledger',
                        Project::class => 'Projects',
                        Vendor::class => 'Vendors',
                    ]),

                SelectFilter::make('user_id')
                    ->label('Filter by Actor')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Action::make('view_diff')
                    ->label('Inspect')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalHeading(fn(AuditLog $record): string => "Activity Audit Inspection: " . strtoupper(str_replace('_', ' ', $record->event)))
                    ->modalDescription(fn(AuditLog $record): string => $record->subject_name)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalWidth('4xl')
                    ->modalContent(fn(AuditLog $record): HtmlString => new HtmlString(
                        view('filament.modals.activity-log-diff', ['record' => $record])->render()
                    )),
            ], position: RecordActionsPosition::BeforeColumns)
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->visible(fn(): bool => auth()->user()?->canDeleteRecords() ?? false),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
        ];
    }
}
