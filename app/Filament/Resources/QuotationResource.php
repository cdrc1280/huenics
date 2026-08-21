<?php

namespace App\Filament\Resources;

use App\Filament\Pages\ReviewQueuePage;
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
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
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
        $query = parent::getEloquentQuery()->with(['salesAgent', 'project', 'lineItems']);
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
                                ->columnSpan(1),

                            TextInput::make('item_code')
                                ->label('Item Code')
                                ->nullable()
                                ->columnSpan(2),

                            Select::make('product_id')
                                ->label('Product')
                                ->options(Product::active()->pluck('canonical_name', 'id'))
                                ->searchable()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, $set) {
                                    if ($state) {
                                        $product = Product::find($state);
                                        if ($product) {
                                            $set('description', $product->canonical_name);
                                            $set('item_code', $product->sku ?? null);
                                            $set('unit_price', $product->selling_price ?? $product->default_price ?? 0);
                                            $set('base_cost', $product->base_cost_price ?? 0);
                                            $set('unit', $product->unit_default ?? 'pcs');
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

                            TextInput::make('unit')
                                ->label('Unit')
                                ->default('pcs')
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
                        ->reorderable('line_no')
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
            ->columns([
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

                TextColumn::make('workflow_status')
                    ->label('Review & Approval')
                    ->badge()
                    ->state(function (Quotation $record): string {
                        if ($record->isRejected()) {
                            return 'Rejected';
                        }
                        if ($record->is_official_po && !empty($record->customer_signature_name)) {
                            return 'Official PO (Signed)';
                        }
                        if ($record->isReadyForConversion()) {
                            return 'Approved & Reviewed';
                        }
                        if ($record->isApproved()) {
                            return 'Approved (Pending Review)';
                        }
                        if ($record->isReviewed()) {
                            return 'Reviewed (Pending Approval)';
                        }
                        return 'Draft / Pending';
                    })
                    ->color(function (Quotation $record): string {
                        if ($record->isRejected()) {
                            return 'danger';
                        }
                        if ($record->is_official_po || $record->isReadyForConversion()) {
                            return 'success';
                        }
                        if ($record->isApproved() || $record->isReviewed()) {
                            return 'info';
                        }
                        return 'warning';
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        Quotation::STATUS_PENDING => 'Pending',
                        Quotation::STATUS_APPROVED => 'Approved',
                        Quotation::STATUS_REJECTED => 'Rejected / Lost',
                        Quotation::STATUS_CONVERTED => 'Converted to PO',
                        default => ucfirst($state),
                    })
                    ->color(fn(string $state): string => match ($state) {
                        Quotation::STATUS_PENDING => 'warning',
                        Quotation::STATUS_APPROVED => 'info',
                        Quotation::STATUS_REJECTED => 'danger',
                        Quotation::STATUS_CONVERTED => 'success',
                        default => 'gray',
                    })
                    ->tooltip(fn(Quotation $record): string => "Lifecycle status: " . ucfirst($record->status)),

                TextColumn::make('quotation_date')
                    ->label('Date')
                    ->date('M j, Y')
                    ->sortable(),

                TextColumn::make('valid_until')
                    ->label('Valid Until')
                    ->date('M j, Y')
                    ->sortable()
                    ->color(fn($record) => $record->valid_until && $record->valid_until->isPast() ? 'danger' : null)
                    ->tooltip(fn($record) => $record->valid_until && $record->valid_until->isPast() ? 'Quotation estimate has expired' : 'Quotation validity period'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        Quotation::STATUS_PENDING => 'Pending',
                        Quotation::STATUS_APPROVED => 'Approved',
                        Quotation::STATUS_REJECTED => 'Rejected / Lost',
                        Quotation::STATUS_CONVERTED => 'Converted to PO',
                    ]),

                SelectFilter::make('sales_agent_id')
                    ->label('Sales Agent')
                    ->options(User::whereIn('role', [
                        User::ROLE_SALES_EXECUTIVE,
                        User::ROLE_ADMIN,
                        User::ROLE_OPERATIONS_MANAGER,
                    ])->pluck('name', 'id')),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('review')
                        ->label('Review & Verify')
                        ->icon('heroicon-m-clipboard-document-check')
                        ->color('warning')
                        ->tooltip('Review, verify math and reconcile quotation line items')
                        ->visible(fn(Quotation $r): bool => !$r->isReviewed() && $r->status !== Quotation::STATUS_CONVERTED && $r->status !== Quotation::STATUS_REJECTED)
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
                        ->visible(fn(Quotation $r): bool => $r->status === Quotation::STATUS_PENDING)
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
                        ->visible(fn(Quotation $r): bool => $r->status === Quotation::STATUS_PENDING)
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
                        ->tooltip('Convert this approved & reviewed quotation into an active Purchase Order')
                        ->visible(fn(Quotation $r): bool => $r->status !== Quotation::STATUS_CONVERTED && $r->status !== Quotation::STATUS_REJECTED && ($r->canServeAsOfficialPO() || $r->isReadyForConversion()))
                        ->form([
                            DatePicker::make('order_date')
                                ->label('Order Date')
                                ->required()
                                ->default(now()),

                            DatePicker::make('expected_delivery_date')
                                ->label('Expected Delivery Date')
                                ->nullable(),

                            Toggle::make('has_warranty')
                                ->label('Include Warranty')
                                ->default(true)
                                ->live(),

                            Select::make('warranty_period')
                                ->label('Warranty Period')
                                ->options(PurchaseOrder::getWarrantyPeriodOptions())
                                ->default(PurchaseOrder::WARRANTY_1_YEAR)
                                ->visible(fn($get) => $get('has_warranty')),

                            Textarea::make('notes')
                                ->label('Notes')
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
                            } catch (\Throwable $e) {
                                Notification::make()->title('Conversion Failed')->body($e->getMessage())->danger()->send();
                            }
                        }),

                    Action::make('export_pdf')
                        ->label('Export PDF')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('gray')
                        ->tooltip('Download Quotation PDF with e-signatures')
                        ->url(fn(Quotation $r) => route('quotations.export-pdf', $r))
                        ->openUrlInNewTab(),

                    Action::make('preview_pdf')
                        ->label('Preview PDF')
                        ->icon('heroicon-o-eye')
                        ->color('gray')
                        ->tooltip('Preview Quotation PDF in browser')
                        ->url(fn(Quotation $r) => route('quotations.preview-pdf', $r))
                        ->openUrlInNewTab(),

                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ], position: RecordActionsPosition::BeforeColumns)
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
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
