<?php

namespace App\Filament\Resources;

use App\Filament\Pages\ReviewQueuePage;
use App\Filament\Resources\PurchaseOrderResource;
use App\Filament\Resources\QuotationResource\Pages;
use App\Models\Product;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\User;
use App\Services\QuotationService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class QuotationResource extends Resource
{
    protected static ?string $model = Quotation::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-document-currency-dollar';
    protected static UnitEnum|string|null $navigationGroup = 'Sales & Order Lifecycle';
    protected static ?string $navigationLabel = 'Quotations';
    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return auth()->user()?->canManageQuotations() ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->with(['salesAgent', 'project', 'lineItems']);
        $user = auth()->user();

        if ($user && $user->isSalesExecutive()) {
            $query->where('sales_agent_id', $user->id);
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Quotation Details')
                ->icon('heroicon-o-document-text')
                ->schema([
                    TextInput::make('quotation_number')
                        ->label('Quotation #')
                        ->default(fn() => Quotation::generateNumber())
                        // ->disabled()
                        ->dehydrated()
                        ->required(),

                    Select::make('sales_agent_id')
                        ->label('Sales Agent')
                        ->options(User::whereIn('role', [
                            User::ROLE_SALES_EXECUTIVE,
                            User::ROLE_ADMIN,
                            User::ROLE_OPERATIONS_MANAGER,
                        ])->pluck('name', 'id'))
                        ->default(fn() => auth()->id())
                        ->required()
                        ->searchable(),

                    TextInput::make('customer_name')
                        ->label('Customer Name')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('customer_company')
                        ->label('Company')
                        ->maxLength(255),

                    TextInput::make('project_name')
                        ->label('Project Name')
                        ->maxLength(255),

                    TextInput::make('project_location')
                        ->label('Project Location')
                        ->maxLength(255),

                    TextInput::make('phone_no')
                        ->label('Phone No.')
                        ->maxLength(100),

                    Select::make('project_id')
                        ->label('Linked System Project')
                        ->options(Project::pluck('name', 'id'))
                        ->searchable()
                        ->nullable(),

                    DatePicker::make('quotation_date')
                        ->label('Quotation Date')
                        ->required()
                        ->default(now()),

                    DatePicker::make('valid_until')
                        ->label('Valid Until')
                        ->nullable()
                        ->minDate(now()),

                    Select::make('status')
                        ->label('Status')
                        ->options([
                            Quotation::STATUS_PENDING => 'Pending',
                            Quotation::STATUS_APPROVED => 'Approved',
                            Quotation::STATUS_REJECTED => 'Rejected / Lost',
                        ])
                        ->default(Quotation::STATUS_PENDING)
                        ->required()
                        ->live(),

                    Textarea::make('rejection_reason')
                        ->label('Rejection Reason')
                        ->visible(fn($get) => $get('status') === Quotation::STATUS_REJECTED)
                        ->nullable()
                        ->columnSpanFull(),

                    Textarea::make('notes')
                        ->label('Notes / Remarks')
                        ->nullable()
                        ->columnSpanFull(),
                ])
                ->columnSpanFull(),

            Section::make('Official PO & Customer Signature')
                ->icon('heroicon-o-check-badge')
                ->schema([
                    Toggle::make('is_official_po')
                        ->label('Serve as Official Purchase Order (PO)')
                        ->helperText('When checked with Customer Signature Name, this Quotation is recognized as an Official Purchase Order.')
                        ->live(),

                    TextInput::make('customer_signature_name')
                        ->label("Customer's Name Over Signature")
                        ->placeholder('e.g. Engr. Juan Dela Cruz')
                        ->visible(fn($get) => (bool) $get('is_official_po'))
                        ->required(fn($get) => (bool) $get('is_official_po')),

                    DatePicker::make('customer_signed_at')
                        ->label('Date Signed')
                        ->default(now())
                        ->visible(fn($get) => (bool) $get('is_official_po')),
                ])
                ->columnSpanFull(),

            Section::make('Terms, Payment & Delivery')
                ->icon('heroicon-o-document-text')
                ->schema([
                    Textarea::make('terms_and_conditions')
                        ->label('Terms and Conditions')
                        ->rows(4)
                        ->columnSpanFull(),
                    TextInput::make('payment_terms')
                        ->label('Payment Terms'),
                    TextInput::make('delivery_terms')
                        ->label('Delivery Terms'),
                ])
                ->columnSpanFull(),

            Section::make('Line Items')
                ->icon('heroicon-o-list-bullet')
                ->schema([
                    Repeater::make('lineItems')
                        ->relationship('lineItems')
                        ->label('')
                        ->schema([
                            TextInput::make('line_no')
                                ->label('#')
                                ->numeric()
                                ->default(1)
                                ->disabled()
                                ->dehydrated()
                                ->columnSpan(1),

                            Select::make('item_code')
                                ->label('Item Code')
                                ->options(Product::getSkuOptions())
                                ->searchable()
                                ->nullable()
                                ->live()
                                ->afterStateUpdated(function ($state, $set, $get) {
                                    if ($state) {
                                        $product = Product::where('sku', $state)->first();
                                        if ($product) {
                                            $set('product_id', $product->id);
                                            $set('description', $product->canonical_name);
                                            $set('unit_price', $product->selling_price ?? $product->default_price ?? 0);
                                            $set('base_cost', $product->base_cost_price ?? 0);
                                            $set('unit', $product->unit_default ?? 'pcs');
                                            $qty = (float) ($get('qty') ?: 1);
                                            $disc = (float) ($get('discounted_price') ?: 0);
                                            $set('line_total', round($qty * ($disc > 0 ? $disc : (float) $get('unit_price')), 2));
                                        }
                                    }
                                })
                                ->columnSpan(2),

                            Select::make('product_id')
                                ->label('Product')
                                ->options(Product::active()->pluck('canonical_name', 'id'))
                                ->searchable()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, $set, $get) {
                                    if ($state) {
                                        $product = Product::find($state);
                                        if ($product) {
                                            $set('description', $product->canonical_name);
                                            if ($product->sku) {
                                                $set('item_code', $product->sku);
                                            }
                                            $set('unit_price', $product->selling_price ?? $product->default_price ?? 0);
                                            $set('base_cost', $product->base_cost_price ?? 0);
                                            $set('unit', $product->unit_default ?? 'pcs');
                                            $qty = (float) ($get('qty') ?: 1);
                                            $disc = (float) ($get('discounted_price') ?: 0);
                                            $set('line_total', round($qty * ($disc > 0 ? $disc : (float) $get('unit_price')), 2));
                                        }
                                    }
                                })
                                ->columnSpan(6),

                            TextInput::make('qty')
                                ->label('Qty')
                                ->numeric()
                                ->default(1)
                                ->minValue(0.0001)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn($state, $set, $get) => $set('line_total', round((float) $state * ((float) $get('discounted_price') > 0 ? (float) $get('discounted_price') : (float) $get('unit_price')), 2)))
                                ->columnSpan(1),

                            Select::make('unit')
                                ->label('Unit')
                                ->options(\App\Enums\UnitOfMeasure::class)
                                ->default('pcs')
                                ->required()
                                ->columnSpan(1),

                            TextInput::make('unit_price')
                                ->label('Unit Price (₱)')
                                ->numeric()
                                ->prefix('₱')
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn($state, $set, $get) => $set('line_total', round((float) $get('qty') * ((float) $get('discounted_price') > 0 ? (float) $get('discounted_price') : (float) $state), 2)))
                                ->columnSpan(2),

                            TextInput::make('discounted_price')
                                ->label('Discounted Price (₱)')
                                ->numeric()
                                ->prefix('₱')
                                ->nullable()
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn($state, $set, $get) => $set('line_total', round((float) $get('qty') * ((float) $state > 0 ? (float) $state : (float) $get('unit_price')), 2)))
                                ->columnSpan(2),

                            TextInput::make('line_total')
                                ->label('Total (₱)')
                                ->numeric()
                                ->prefix('₱')
                                ->disabled()
                                ->dehydrated()
                                ->columnSpan(3),
                        ])
                        ->columns(9)
                        ->orderColumn('line_no')
                        ->reorderable()
                        ->addActionLabel('+ Add Line Item')
                        ->defaultItems(1)
                        ->cloneable(),
                ])
                ->columnSpanFull(),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(static::getTableColumns())
            ->defaultSort('created_at', 'desc')
            ->filters(static::getTableFilters())
            ->actions(static::getTableActions(), position: RecordActionsPosition::BeforeColumns)
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->requiresConfirmation(),
                    RestoreBulkAction::make()->requiresConfirmation(),
                    ForceDeleteBulkAction::make()->requiresConfirmation()->visible(fn(): bool => auth()->user()?->canDeleteRecords() ?? false),
                ]),
            ]);
    }

    protected static function getStatusBadgeLabel(string $state, Quotation $record): string
    {
        if ($record->isRejected()) {
            return 'Rejected';
        }
        if ($record->is_official_po && !empty($record->customer_signature_name)) {
            return 'Official PO (Signed)';
        }
        if ($state === Quotation::STATUS_CONVERTED) {
            return 'Converted to PO';
        }
        if ($record->isApproved() && $record->isReviewed()) {
            return 'Approved & Reviewed';
        }
        if ($record->isApproved()) {
            return 'Approved';
        }
        if ($record->isReviewed()) {
            return 'Reviewed';
        }
        return match ($state) {
            Quotation::STATUS_PENDING => 'Pending',
            Quotation::STATUS_APPROVED => 'Approved',
            Quotation::STATUS_REJECTED => 'Rejected',
            Quotation::STATUS_CONVERTED => 'Converted to PO',
            default => ucfirst($state),
        };
    }

    protected static function getStatusBadgeColor(string $state, Quotation $record): string
    {
        if ($record->isRejected()) {
            return 'danger';
        }
        if ($record->is_official_po || $state === Quotation::STATUS_CONVERTED || $record->isApproved()) {
            return 'success';
        }
        if ($record->isReviewed()) {
            return 'info';
        }
        return match ($state) {
            Quotation::STATUS_PENDING => 'warning',
            Quotation::STATUS_REVIEWED => 'info',
            Quotation::STATUS_APPROVED => 'success',
            Quotation::STATUS_REJECTED => 'danger',
            Quotation::STATUS_CONVERTED => 'success',
            default => 'gray',
        };
    }

    protected static function getTableColumns(): array
    {
        return [
            TextColumn::make('quotation_number')
                ->label('Quotation #')
                ->searchable()
                ->sortable()
                ->weight('bold')
                ->copyable()
                ->tooltip('Click to copy Quotation #'),

            TextColumn::make('customer_name')
                ->label('Customer Name')
                ->searchable()
                ->sortable()
                ->description(fn(Quotation $record): string => $record->customer_company ?: '')
                ->tooltip(fn(Quotation $record): string => "Customer: {$record->customer_name}" . ($record->customer_company ? " ({$record->customer_company})" : '')),

            TextColumn::make('project_name')
                ->label('Project Name')
                ->searchable()
                ->sortable()
                ->default(fn(Quotation $record) => $record->project?->name ?? 'Palanza Tower')
                ->description(fn(Quotation $record): string => $record->project_location ?: '')
                ->tooltip(fn(Quotation $record): string => "Project: " . ($record->project_name ?? $record->project?->name ?? 'Palanza Tower')),

            TextColumn::make('phone_no')
                ->label('Phone No.')
                ->searchable()
                ->default('—')
                ->toggleable(isToggledHiddenByDefault: false),

            TextColumn::make('total_amount')
                ->label('Total Amount')
                ->money('PHP')
                ->sortable()
                ->tooltip(fn(Quotation $record): string => "Quotation total sum: ₱" . number_format((float) $record->total_amount, 2)),

            TextColumn::make('estimated_profit')
                ->label('Est. Profit')
                ->money('PHP')
                ->sortable()
                ->color(fn($state) => $state > 0 ? 'success' : 'danger')
                ->tooltip(fn(Quotation $record): string => "Estimated gross profit (Total ₱" . number_format((float) $record->total_amount, 2) . " minus Cost ₱" . number_format((float) $record->total_cost, 2) . ")"),

            TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->formatStateUsing(fn(string $state, Quotation $record): string => static::getStatusBadgeLabel($state, $record))
                ->color(fn(string $state, Quotation $record): string => static::getStatusBadgeColor($state, $record))
                ->tooltip(fn(Quotation $record): string => "Status: " . ucfirst($record->status)),

            TextColumn::make('quotation_date')
                ->label('Date')
                ->date('M j, Y')
                ->sortable(),

            TextColumn::make('valid_until')
                ->label('Valid Until')
                ->date('M j, Y')
                ->sortable()
                ->color(fn(Quotation $record): ?string => $record->valid_until && \Carbon\Carbon::parse($record->valid_until)->isPast() ? 'danger' : null)
                ->tooltip(fn(Quotation $record): string => $record->valid_until && \Carbon\Carbon::parse($record->valid_until)->isPast() ? 'Quotation estimate has expired' : 'Quotation validity period'),
        ];
    }

    protected static function getTableFilters(): array
    {
        return [
            SelectFilter::make('status_scope')
                ->label('Status Filter')
                ->options([
                    'pending_review' => 'Pending & For Review',
                    'approved'       => 'Approved',
                    'converted'      => 'Converted to PO',
                    'rejected'       => 'Rejected / Lost',
                ])
                ->query(function (Builder $query, array $data) {
                    $scope = $data['value'] ?? null;
                    if (empty($scope)) {
                        return $query;
                    }

                    return match ($scope) {
                        'pending_review' => $query->whereIn('status', [Quotation::STATUS_PENDING, Quotation::STATUS_REVIEWED, 'pending', 'reviewed', 'for_review'])->where('is_official_po', false),
                        'approved'       => $query->where(fn(Builder $q) => $q->where('status', Quotation::STATUS_APPROVED)->orWhere('is_official_po', true)),
                        'converted'      => $query->where('status', Quotation::STATUS_CONVERTED),
                        'rejected'       => $query->where('status', Quotation::STATUS_REJECTED),
                        default          => $query,
                    };
                }),

            SelectFilter::make('sales_agent_id')
                ->label('Sales Agent')
                ->options(User::whereIn('role', [
                    User::ROLE_SALES_EXECUTIVE,
                    User::ROLE_ADMIN,
                    User::ROLE_OPERATIONS_MANAGER,
                ])->pluck('name', 'id')),

            TrashedFilter::make(),
        ];
    }

    protected static function getTableActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('review')
                    ->label('Review & Verify')
                    ->icon('heroicon-m-clipboard-document-check')
                    ->color('warning')
                    ->tooltip('Review, verify math and reconcile quotation line items')
                    ->visible(fn(Quotation $r): bool => !$r->trashed() && !$r->isReviewed() && !$r->isApproved() && !$r->isConverted() && !$r->isRejected())
                    ->url(function (Quotation $record) {
                        if ($record->document_id) {
                            return ReviewQueuePage::getUrl(['document_id' => $record->document_id]);
                        }
                        return null;
                    })
                    ->action(function (Quotation $record) {
                        if ($record->document_id) {
                            return redirect(ReviewQueuePage::getUrl(['document_id' => $record->document_id]));
                        }
                        app(QuotationService::class)->review($record);
                        Notification::make()->title('Quotation Marked as Reviewed')->success()->send();
                    }),

                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->tooltip('Approve quotation estimate')
                    ->visible(fn(Quotation $r): bool => !$r->trashed() && !$r->isApproved() && !$r->isConverted() && !$r->isRejected())
                    ->requiresConfirmation()
                    ->action(function (Quotation $record) {
                        app(QuotationService::class)->approve($record);
                        Notification::make()->title('Quotation Approved')->success()->send();
                    }),

                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->tooltip('Mark quotation as rejected / lost with reason notes')
                    ->visible(fn(Quotation $r): bool => !$r->trashed() && !$r->isApproved() && !$r->isConverted() && !$r->isRejected())
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('Reason for Rejection')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (Quotation $record, array $data) {
                        app(QuotationService::class)->reject($record, $data['rejection_reason']);
                        Notification::make()->title('Quotation Rejected')->warning()->send();
                    }),

                Action::make('convert_to_po')
                    ->label('Convert to PO')
                    ->icon('heroicon-m-shopping-cart')
                    ->color('primary')
                    ->tooltip('Convert this approved quotation into an active Purchase Order')
                    ->visible(fn(Quotation $r): bool => !$r->trashed() && $r->isReadyForConversion() && !$r->isConverted())
                    ->modalHeading('Convert Quotation to Purchase Order')
                    ->modalDescription('Are you sure you want to convert this quotation into an active Purchase Order? All line items, pricing, and project details will be transferred.')
                    ->modalSubmitActionLabel('Convert to PO')
                    ->form([
                        Textarea::make('notes')
                            ->label('PO Notes / Instructions (Optional)')
                            ->placeholder('Enter any instructions, remarks, or reference notes...')
                            ->rows(3)
                            ->nullable(),
                    ])
                    ->action(function (Quotation $record, array $data) {
                        try {
                            $po = app(QuotationService::class)->convertToPO($record, $data);
                            Notification::make()
                                ->title('Converted to Purchase Order')
                                ->body("PO {$po->po_number} created successfully.")
                                ->success()
                                ->send();

                            return redirect(PurchaseOrderResource::getUrl('index'));
                        } catch (\Throwable $e) {
                            Notification::make()->title('Conversion Failed')->body($e->getMessage())->danger()->send();
                        }
                    }),

                Action::make('export_pdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->tooltip('Download Quotation PDF with e-signatures')
                    ->visible(fn(Quotation $r): bool => !$r->trashed())
                    ->url(fn(Quotation $r) => route('quotations.export-pdf', $r))
                    ->openUrlInNewTab(),

                Action::make('preview_pdf')
                    ->label('Preview PDF')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->tooltip('Preview Quotation PDF in browser')
                    ->visible(fn(Quotation $r): bool => !$r->trashed())
                    ->url(fn(Quotation $r) => route('quotations.preview-pdf', $r))
                    ->openUrlInNewTab(),

                EditAction::make(),
                DeleteAction::make()->requiresConfirmation(),
                RestoreAction::make()->requiresConfirmation()->visible(fn(Quotation $record): bool => $record->trashed()),
                ForceDeleteAction::make()->requiresConfirmation()->visible(fn(Quotation $record): bool => $record->trashed() && (auth()->user()?->canDeleteRecords() ?? false)),
            ]),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuotations::route('/'),
            'create' => Pages\CreateQuotation::route('/create'),
            'edit' => Pages\EditQuotation::route('/{record}/edit'),
        ];
    }
}
