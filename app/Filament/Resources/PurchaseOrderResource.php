<?php

namespace App\Filament\Resources;

use App\Filament\Pages\DeliveryMonitoringPage;
use App\Filament\Pages\ReviewQueuePage;
use App\Filament\Resources\PurchaseOrderResource\Pages;
use App\Models\DeliveryReceipt;
use App\Models\Product;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\SalesInvoice;
use App\Models\User;
use App\Services\OrderFulfillmentService;
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
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-shopping-cart';
    protected static \UnitEnum|string|null $navigationGroup = 'Sales & Order Lifecycle';
    protected static ?string $navigationLabel = 'Purchase Orders';
    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return auth()->user()?->canManageQuotations() ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->with(['salesAgent', 'project', 'quotation']);
        $user = auth()->user();

        if ($user && $user->isSalesExecutive()) {
            $query->where('sales_agent_id', $user->id);
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Purchase Order Details')
                ->icon('heroicon-o-shopping-cart')
                ->schema([
                    TextInput::make('po_number')
                        ->label('PO #')
                        ->default(fn() => PurchaseOrder::generateNumber())
                        ->disabled()
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

                    Select::make('quotation_id')
                        ->label('Linked Approved Quotation (Optional)')
                        ->options(function (?PurchaseOrder $record) {
                            $query = Quotation::query();
                            if ($record && $record->quotation_id) {
                                $query->where(function ($q) use ($record) {
                                    $q->whereDoesntHave('purchaseOrders')
                                        ->where('status', Quotation::STATUS_APPROVED)
                                        ->orWhere('id', $record->quotation_id);
                                });
                            } else {
                                $query->whereDoesntHave('purchaseOrders')
                                    ->where('status', Quotation::STATUS_APPROVED);
                            }

                            return $query->get()->mapWithKeys(fn(Quotation $q) => [
                                $q->id => "{$q->quotation_number} - {$q->customer_name} (" . ($q->project?->name ?? $q->project_name ?? 'No Project') . ") - ₱" . number_format((float) $q->total_amount, 2)
                            ]);
                        })
                        ->searchable()
                        ->nullable()
                        ->placeholder('Select an approved quotation to link, or leave blank')
                        ->helperText('Only approved quotations without an existing Purchase Order are selectable.')
                        ->live()
                        ->afterStateUpdated(function ($state, $set, $get) {
                            if ($state && $quotation = Quotation::find($state)) {
                                if (!$get('customer_name') || $get('customer_name') === 'Valued Customer') {
                                    $set('customer_name', $quotation->customer_name);
                                }
                                if (!$get('project_id') && $quotation->project_id) {
                                    $set('project_id', $quotation->project_id);
                                }
                                if ($quotation->sales_agent_id) {
                                    $set('sales_agent_id', $quotation->sales_agent_id);
                                }
                            }
                        }),

                    Toggle::make('is_conforme_po')
                        ->label('Conforme PO (No Quotation Required)')
                        ->helperText('Check if this is a conforme purchase order that does not require a matching quotation')
                        ->visible(fn() => auth()->user()?->is_owner === true)
                        ->default(false),

                    TextInput::make('customer_name')
                        ->label('Customer / Client')
                        ->required(),

                    Select::make('project_id')
                        ->label('Project / Site')
                        ->options(Project::pluck('name', 'id'))
                        ->searchable()
                        ->nullable(),

                    DatePicker::make('order_date')
                        ->label('Order Date')
                        ->required()
                        ->default(now()),

                    DatePicker::make('expected_delivery_date')
                        ->label('Expected Delivery Date')
                        ->nullable(),

                    Select::make('delivery_status')
                        ->label('Delivery Status')
                        ->options(function (?PurchaseOrder $record): array {
                            $opts = [
                                PurchaseOrder::DELIVERY_PENDING => 'Pending',
                                PurchaseOrder::DELIVERY_TRANSIT => 'In Transit',
                                PurchaseOrder::DELIVERY_OVERDUE => 'Overdue',
                            ];
                            if ($record && ($record->is_completed || $record->hasBothDrAndSi())) {
                                $opts[PurchaseOrder::DELIVERY_DELIVERED] = 'Delivered (DR & SI Attached)';
                            }
                            return $opts;
                        })
                        ->default(PurchaseOrder::DELIVERY_PENDING)
                        ->required()
                        ->helperText('PO is automatically marked Delivered upon attaching DR & SI hard copies.'),

                    Select::make('status')
                        ->label('PO Status')
                        ->options(function (?PurchaseOrder $record): array {
                            $opts = [
                                PurchaseOrder::STATUS_PENDING => 'Pending Approval',
                                PurchaseOrder::STATUS_APPROVED => 'Approved (Ready to Deliver)',
                                PurchaseOrder::STATUS_PENDING_DELIVERY => 'Pending Delivery',
                                PurchaseOrder::STATUS_CANCELLED => 'Cancelled',
                                PurchaseOrder::STATUS_REJECTED => 'Rejected',
                            ];
                            if ($record && ($record->is_completed || $record->hasBothDrAndSi())) {
                                $opts[PurchaseOrder::STATUS_DELIVERED] = 'Delivered (DR & SI Attached)';
                            }
                            return $opts;
                        })
                        ->default(PurchaseOrder::STATUS_APPROVED)
                        ->required()
                        ->helperText('PO transitions to Delivered only after DR & SI hard copies are attached.'),

                    Textarea::make('notes')
                        ->label('Notes')
                        ->nullable()
                        ->columnSpanFull(),
                ]),


            Section::make([
                Section::make('Delivery & Warranty')
                    ->icon('heroicon-o-truck')
                    ->schema([
                        DatePicker::make('actual_delivery_date')
                            ->label('Actual Delivery Date')
                            ->nullable()
                            ->helperText('Set automatically upon DR & SI fulfillment.'),

                        TextInput::make('delivery_receipt_no')
                            ->label('Delivery Receipt # (DR#)')
                            ->nullable(),

                        Toggle::make('has_warranty')
                            ->label('Has Warranty')
                            ->default(true)
                            ->live(),

                        Select::make('warranty_period')
                            ->label('Warranty Period')
                            ->options(PurchaseOrder::getWarrantyPeriodOptions())
                            ->default(PurchaseOrder::WARRANTY_1_YEAR)
                            ->required()
                            ->visible(fn($get) => $get('has_warranty')),
                    ]),

                Section::make('Financials')
                    ->icon('heroicon-o-calculator')
                    ->schema([
                        TextInput::make('order_amount')
                            ->label('Order Amount (₱)')
                            ->numeric()
                            ->prefix('₱')
                            ->default(0),

                        TextInput::make('computed_vat')
                            ->label('Computed 12% VAT (₱)')
                            ->numeric()
                            ->prefix('₱')
                            ->disabled()
                            ->dehydrated(),

                        TextInput::make('realized_profit')
                            ->label('Realized Profit (₱)')
                            ->numeric()
                            ->prefix('₱')
                            ->disabled()
                            ->dehydrated(),
                    ]),
            ]),



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
                                ->columnSpan(5),

                            Placeholder::make('product_image_preview')
                                ->label('Photo')
                                ->content(function ($get) {
                                    $pId = $get('product_id');
                                    if (!$pId) {
                                        return new \Illuminate\Support\HtmlString('<div class="w-8 h-8 rounded border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-white/5 flex items-center justify-center text-gray-400 text-[10px]">—</div>');
                                    }
                                    $product = Product::find($pId);
                                    $url = $product?->image_url;
                                    if (!$url) {
                                        return new \Illuminate\Support\HtmlString('<div class="w-8 h-8 rounded border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-white/5 flex items-center justify-center text-gray-400 text-[10px]">—</div>');
                                    }
                                    return new \Illuminate\Support\HtmlString('<img src="' . e($url) . '" alt="Product" class="w-8 h-8 object-contain rounded border border-gray-200 dark:border-gray-700 bg-white p-0.5" />');
                                })
                                ->columnSpan(1),

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
            ->bulkActions(static::getTableBulkActions());
    }

    protected static function getTableColumns(): array
    {
        return [
            TextColumn::make('connected_quotation')
                ->label('Connected Quotation')
                ->state(function (PurchaseOrder $record): string {
                    if ($record->quotation) {
                        $reconciliation = $record->getReconciliationReport();
                        if ($reconciliation['has_discrepancies']) {
                            return "{$record->quotation->quotation_number} ({$reconciliation['discrepancy_count']} Discrepancies)";
                        }
                        return "{$record->quotation->quotation_number} (Matched)";
                    }
                    if ($record->is_conforme_po) {
                        return 'Conforme PO (No Quotation)';
                    }
                    return 'Not Linked';
                })
                ->badge()
                ->icon(fn(PurchaseOrder $record) => match (true) {
                    (bool) $record->quotation && $record->hasLineItemDiscrepancies() => 'heroicon-m-exclamation-triangle',
                    (bool) $record->quotation => 'heroicon-m-check-badge',
                    $record->is_conforme_po => 'heroicon-m-document-check',
                    default => 'heroicon-m-exclamation-triangle',
                })
                ->color(fn(PurchaseOrder $record) => match (true) {
                    (bool) $record->quotation && $record->hasLineItemDiscrepancies() => 'warning',
                    (bool) $record->quotation => 'success',
                    $record->is_conforme_po => 'gray',
                    default => 'danger',
                })
                ->url(fn(PurchaseOrder $record) => $record->quotation ? QuotationResource::getUrl('view', ['record' => $record->quotation]) : null)
                ->tooltip(function (PurchaseOrder $record): string {
                    if ($record->quotation) {
                        $reconciliation = $record->getReconciliationReport();
                        if ($reconciliation['has_discrepancies']) {
                            return "Connected to Quotation {$record->quotation->quotation_number} with {$reconciliation['discrepancy_count']} discrepancies (Qty: {$reconciliation['qty_mismatches_count']}, Price: {$reconciliation['price_mismatches_count']}, Unquoted: {$reconciliation['missing_in_quotation_count']}). Click 'Line Item Discrepancies' to inspect.";
                        }
                        return "Connected to Quotation {$record->quotation->quotation_number} (₱" . number_format((float) $record->quotation->total_amount, 2) . ") — 100% line items and pricing match perfectly.";
                    }
                    if ($record->is_conforme_po) {
                        return "Conforme Purchase Order — self-contained order, quotation link not required";
                    }
                    return "Unlinked Normal PO — must be linked to an approved quotation before Review and Approval";
                })
                ->searchable(query: function ($query, string $search) {
                    return $query->whereHas('quotation', function ($q) use ($search) {
                        $q->where('quotation_number', 'like', "%{$search}%");
                    });
                })
                ->sortable(query: function ($query, string $direction) {
                    return $query->leftJoin('quotations', 'purchase_orders.quotation_id', '=', 'quotations.id')
                        ->orderBy('quotations.quotation_number', $direction)
                        ->select('purchase_orders.*');
                }),

            TextColumn::make('po_number')
                ->label('PO #')
                ->searchable()
                ->sortable()
                ->weight('bold')
                ->copyable()
                ->tooltip('Click to copy Purchase Order #'),

            TextColumn::make('customer_name')
                ->label('Customer')
                ->searchable()
                ->sortable()
                ->tooltip(fn(PurchaseOrder $record): string => "Customer: {$record->customer_name}"),

            TextColumn::make('salesAgent.name')
                ->label('Agent')
                ->sortable()
                ->tooltip(fn(PurchaseOrder $record): string => "Sales executive credited: " . ($record->salesAgent?->name ?? 'Unassigned')),

            TextColumn::make('project.name')
                ->label('Project')
                ->sortable()
                ->default('—')
                ->tooltip(fn(PurchaseOrder $record): string => "Project Site: " . ($record->project?->name ?? 'General / None')),

            TextColumn::make('is_conforme_po')
                ->label('PO Type')
                ->badge()
                ->formatStateUsing(fn(bool $state) => $state ? 'Conforme PO' : 'Normal PO')
                ->color(fn(bool $state) => $state ? 'info' : 'gray'),



            TextColumn::make('order_amount')
                ->label('Order Amount')
                ->money('PHP')
                ->sortable()
                ->tooltip(fn(PurchaseOrder $record): string => "Gross order amount (includes 12% VAT): ₱" . number_format((float) $record->order_amount, 2)),

            TextColumn::make('realized_profit')
                ->label('Profit')
                ->money('PHP')
                ->sortable()
                ->color(fn($state) => $state > 0 ? 'success' : 'danger')
                ->tooltip(fn(PurchaseOrder $record): string => "Realized net profit (Order Amount minus Total Cost ₱" . number_format((float) $record->total_cost, 2) . ")"),

            TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->formatStateUsing(fn(string $state): string => match ($state) {
                    PurchaseOrder::STATUS_PENDING => 'Pending Delivery',
                    PurchaseOrder::STATUS_DELIVERED => 'Delivered',
                    PurchaseOrder::STATUS_CANCELLED => 'Cancelled',
                    PurchaseOrder::STATUS_REJECTED => 'Rejected',
                    default => ucfirst(str_replace('_', ' ', $state)),
                })
                ->colors([
                    'warning' => PurchaseOrder::STATUS_PENDING,
                    'success' => PurchaseOrder::STATUS_DELIVERED,
                    'danger' => fn($state) => in_array($state, [PurchaseOrder::STATUS_CANCELLED, PurchaseOrder::STATUS_REJECTED]),
                ]),

            TextColumn::make('delivery_status')
                ->label('Delivery')
                ->badge()
                ->formatStateUsing(fn(string $state): string => match ($state) {
                    PurchaseOrder::DELIVERY_PENDING => 'Pending',
                    PurchaseOrder::DELIVERY_TRANSIT => 'In Transit',
                    PurchaseOrder::DELIVERY_DELIVERED => 'Delivered',
                    PurchaseOrder::DELIVERY_OVERDUE => 'Overdue',
                    default => $state,
                })
                ->color(fn(string $state): string => match ($state) {
                    PurchaseOrder::DELIVERY_PENDING => 'warning',
                    PurchaseOrder::DELIVERY_TRANSIT => 'info',
                    PurchaseOrder::DELIVERY_DELIVERED => 'success',
                    PurchaseOrder::DELIVERY_OVERDUE => 'danger',
                    default => 'gray',
                }),

            TextColumn::make('fulfillment_status')
                ->label('Fulfillment')
                ->badge()
                ->state(fn(PurchaseOrder $record): string => match (true) {
                    $record->isCompleted() => 'Completed & Realized',
                    $record->delivery_status === PurchaseOrder::DELIVERY_DELIVERED => 'Delivered (Awaiting DR & SI)',
                    $record->isApproved() => 'Approved (Pending Delivery)',
                    default => 'Pending Review',
                })
                ->color(fn(string $state): string => match ($state) {
                    'Completed & Realized' => 'success',
                    'Delivered (Awaiting DR & SI)' => 'warning',
                    'Approved (Pending Delivery)' => 'info',
                    default => 'gray',
                })
                ->tooltip(fn(PurchaseOrder $record): string => match (true) {
                    $record->isCompleted() => 'DR & SI verified. Stocks deducted and sales realized in analytics.',
                    $record->delivery_status === PurchaseOrder::DELIVERY_DELIVERED => 'Delivered physically. Upload DR & SI to deduct stock and reflect sales.',
                    default => 'Pending order fulfillment lifecycle',
                }),

            TextColumn::make('expected_delivery_date')
                ->label('Est. Delivery')
                ->date('M j, Y')
                ->sortable()
                ->color(fn(PurchaseOrder $record): ?string => $record->is_overdue ? 'danger' : null)
                ->tooltip(fn(PurchaseOrder $record): string => $record->is_overdue ? 'Delivery is past the estimated arrival date' : 'Expected delivery schedule'),

            TextColumn::make('actual_delivery_date')
                ->label('Delivered On')
                ->date('M j, Y')
                ->placeholder('—')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('delivery_receipt_no')
                ->label('DR #')
                ->searchable()
                ->placeholder('—')
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('warranty_status')
                ->label('Warranty')
                ->badge()
                ->formatStateUsing(fn(string $state): string => match ($state) {
                    PurchaseOrder::WARRANTY_ACTIVE => 'Active',
                    PurchaseOrder::WARRANTY_EXPIRING => 'Expiring Soon',
                    PurchaseOrder::WARRANTY_EXPIRED => 'Expired',
                    PurchaseOrder::WARRANTY_NONE => 'No Warranty',
                    default => $state,
                })
                ->color(fn(string $state): string => match ($state) {
                    PurchaseOrder::WARRANTY_ACTIVE => 'success',
                    PurchaseOrder::WARRANTY_EXPIRING => 'warning',
                    PurchaseOrder::WARRANTY_EXPIRED => 'danger',
                    PurchaseOrder::WARRANTY_NONE => 'gray',
                    default => 'gray',
                })
                ->tooltip(function (PurchaseOrder $record): string {
                    $status = $record->warranty_status;
                    if ($record->warranty_end_date) {
                        $formatted = \Carbon\Carbon::parse($record->warranty_end_date)->format('M d, Y');
                        return "Warranty status: {$status} (Expires {$formatted})";
                    }
                    return "Warranty status: {$status}";
                }),

            TextColumn::make('warranty_period')
                ->label('Warranty Period')
                ->formatStateUsing(fn(string $state): string => PurchaseOrder::getWarrantyPeriodOptions()[$state] ?? $state)
                ->toggleable(isToggledHiddenByDefault: true)
                ->tooltip('Chosen warranty coverage duration'),

            TextColumn::make('order_date')
                ->label('Order Date')
                ->date('M j, Y')
                ->sortable(),

            TextColumn::make('warranty_end_date')
                ->label('Warranty Expiry')
                ->date('M j, Y')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true)
                ->tooltip('Exact expiration date of product warranty coverage'),
        ];
    }

    protected static function getTableFilters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('delivery_status')
                ->label('Delivery Status')
                ->options([
                    PurchaseOrder::DELIVERY_PENDING => 'Pending',
                    PurchaseOrder::DELIVERY_TRANSIT => 'In Transit',
                    PurchaseOrder::DELIVERY_DELIVERED => 'Delivered',
                    PurchaseOrder::DELIVERY_OVERDUE => 'Overdue',
                ]),

            Tables\Filters\SelectFilter::make('warranty_status')
                ->label('Warranty Status')
                ->options([
                    PurchaseOrder::WARRANTY_ACTIVE => 'Active',
                    PurchaseOrder::WARRANTY_EXPIRING => 'Expiring Soon',
                    PurchaseOrder::WARRANTY_EXPIRED => 'Expired',
                    PurchaseOrder::WARRANTY_NONE => 'No Warranty',
                ]),

            Tables\Filters\SelectFilter::make('sales_agent_id')
                ->label('Sales Agent')
                ->options(User::whereIn('role', [
                    User::ROLE_SALES_EXECUTIVE,
                    User::ROLE_ADMIN,
                    User::ROLE_OPERATIONS_MANAGER,
                    User::ROLE_CEO,
                ])->pluck('name', 'id')),

            TrashedFilter::make(),
        ];
    }

    public static function getLinkToQuotationAction(): Action
    {
        return Action::make('link_to_quotation')
            ->label(fn(?PurchaseOrder $record): string => ($record && $record->quotation_id) ? 'Change Linked Quotation' : 'Link to Approved Quotation')
            ->icon('heroicon-m-link')
            ->color(fn(?PurchaseOrder $record): string => ($record && $record->quotation_id) ? 'gray' : 'info')
            ->visible(fn(?PurchaseOrder $record): bool => !($record && $record->quotation_id && !$record->hasLineItemDiscrepancies()))
            ->tooltip(fn(?PurchaseOrder $record): string => ($record && $record->quotation) ? "Currently linked to Quotation {$record->quotation->quotation_number}. Click to change or unlink." : 'Link this PO to an approved quotation')
            ->modalHeading(fn(?PurchaseOrder $record): string => "Link PO #" . ($record?->po_number ?? '') . " to Approved Quotation")
            ->modalDescription('Select an approved quotation to link to this purchase order. This connects the quotation and PO, allows line-item cross-verification, and fills missing customer details.')
            ->modalSubmitActionLabel('Save Link')
            ->form([
                Select::make('quotation_id')
                    ->label('Approved Quotation')
                    ->options(function (?PurchaseOrder $record) {
                        $query = Quotation::query();
                        if ($record && $record->quotation_id) {
                            $query->where(function ($q) use ($record) {
                                $q->where('status', Quotation::STATUS_APPROVED)
                                    ->orWhere('status', Quotation::STATUS_CONVERTED)
                                    ->orWhere('id', $record->quotation_id);
                            });
                        } else {
                            $query->where(function ($q) {
                                $q->where('status', Quotation::STATUS_APPROVED)
                                    ->orWhere(function ($sub) {
                                        $sub->where('status', Quotation::STATUS_CONVERTED)
                                            ->whereDoesntHave('purchaseOrders');
                                    });
                            });
                        }

                        if ($record && $record->customer_name) {
                            $cName = strtolower(trim($record->customer_name));
                            $query->orderByRaw("CASE WHEN LOWER(customer_name) LIKE ? THEN 0 ELSE 1 END", ["%{$cName}%"]);
                        }

                        $query->orderBy('quotation_date', 'desc')->orderBy('id', 'desc');

                        return $query->get()->mapWithKeys(fn(Quotation $q) => [
                            $q->id => "{$q->quotation_number} — {$q->customer_name} (₱" . number_format((float) $q->total_amount, 2) . ") — " . ($q->quotation_date ? \Carbon\Carbon::parse($q->quotation_date)->format('M j, Y') : 'No Date')
                        ]);
                    })
                    ->searchable()
                    ->nullable()
                    ->placeholder('Select an approved quotation (or clear to unlink)')
                    ->default(fn(PurchaseOrder $record) => $record->quotation_id)
                    ->helperText('Quotations matching this PO customer name appear first. Leave empty to unlink.'),

                Toggle::make('sync_missing_details')
                    ->label('Fill missing PO fields from Quotation')
                    ->default(true)
                    ->helperText('Fills customer name, project site, payment terms, delivery terms, and notes if currently empty on this PO.'),

                Toggle::make('verify_line_items')
                    ->label('Cross-Verify Line Items & Pricing')
                    ->default(true)
                    ->helperText('Compares line items and prices between the PO and Quotation, notifying you of any discrepancies.'),
            ])
            ->action(function (PurchaseOrder $record, array $data) {
                if (empty($data['quotation_id'])) {
                    $oldQuot = $record->quotation;
                    $record->quotation_id = null;
                    $record->save();
                    if ($oldQuot && $oldQuot->purchaseOrders()->count() === 0) {
                        $oldQuot->update(['status' => Quotation::STATUS_APPROVED]);
                    }
                    Notification::make()
                        ->title('Quotation Unlinked')
                        ->info()
                        ->body("PO #{$record->po_number} is no longer linked to any quotation.")
                        ->send();
                    return;
                }

                $oldQuotId = $record->quotation_id;
                $quotation = Quotation::findOrFail($data['quotation_id']);

                // If switching from a previously linked quotation, restore previous quotation status
                if ($oldQuotId && (int)$oldQuotId !== (int)$quotation->id) {
                    $prevQuot = Quotation::find($oldQuotId);
                    if ($prevQuot && $prevQuot->purchaseOrders()->where('id', '!=', $record->id)->count() === 0) {
                        $prevQuot->update(['status' => Quotation::STATUS_APPROVED]);
                    }
                }

                $record->quotation_id = $quotation->id;
                $record->unsetRelation('quotation');
                $record->setRelation('quotation', $quotation);

                if (!empty($data['sync_missing_details'])) {
                    if (empty($record->customer_name) || $record->customer_name === 'Valued Customer') {
                        $record->customer_name = $quotation->customer_name;
                    }
                    if (empty($record->project_id) && $quotation->project_id) {
                        $record->project_id = $quotation->project_id;
                    }
                    if (empty($record->payment_terms) && $quotation->payment_terms) {
                        $record->payment_terms = $quotation->payment_terms;
                    }
                    if (empty($record->delivery_terms) && $quotation->delivery_terms) {
                        $record->delivery_terms = $quotation->delivery_terms;
                    }
                    if (empty($record->terms_and_conditions) && $quotation->terms_and_conditions) {
                        $record->terms_and_conditions = $quotation->terms_and_conditions;
                    }
                }

                $record->save();
                $record->unsetRelation('quotation');
                $record->unsetRelation('lineItems');
                $record->setRelation('quotation', $quotation);

                if ($quotation->status === Quotation::STATUS_APPROVED) {
                    $quotation->update(['status' => Quotation::STATUS_CONVERTED]);
                }

                if ($record->document_id && $quotation->document_id) {
                    $trx = \App\Models\Transaction::where('purchase_order_document_id', $record->document_id)->first();
                    if ($trx) {
                        $trx->update(['quotation_document_id' => $quotation->document_id]);
                    }
                }

                $reconciliation = $record->getReconciliationReport($quotation);

                if ($reconciliation['has_discrepancies']) {
                    $summaryParts = [];
                    if ($reconciliation['qty_mismatches_count'] > 0) $summaryParts[] = "{$reconciliation['qty_mismatches_count']} Qty mismatches";
                    if ($reconciliation['price_mismatches_count'] > 0) $summaryParts[] = "{$reconciliation['price_mismatches_count']} Price mismatches";
                    if ($reconciliation['missing_in_quotation_count'] > 0) $summaryParts[] = "{$reconciliation['missing_in_quotation_count']} Unquoted items";
                    if ($reconciliation['missing_in_po_count'] > 0) $summaryParts[] = "{$reconciliation['missing_in_po_count']} Items missing from PO";

                    Notification::make()
                        ->title('Linked with Line Item Discrepancies')
                        ->warning()
                        ->body("PO #{$record->po_number} was linked to Quotation #{$quotation->quotation_number}.\nDetected " . implode(', ', $summaryParts) . ".\nUse the 'Line Item Discrepancies' action or check the PO View page to review detailed line-by-line comparison.")
                        ->persistent()
                        ->send();
                } else {
                    Notification::make()
                        ->title('Successfully Linked to Approved Quotation')
                        ->success()
                        ->body("PO #{$record->po_number} is now linked to Quotation #{$quotation->quotation_number}. All line items and pricing match 100%.")
                        ->send();
                }
            });
    }

    public static function getTableActions(): array
    {
        return [
            ActionGroup::make([
                static::getLinkToQuotationAction(),

                Action::make('view_discrepancies')
                    ->label(fn(PurchaseOrder $r) => $r->hasLineItemDiscrepancies() ? 'Line Item Discrepancies' : 'Reconciliation Report')
                    ->icon('heroicon-m-scale')
                    ->color(fn(PurchaseOrder $r) => $r->hasLineItemDiscrepancies() ? 'warning' : 'info')
                    ->tooltip('View side-by-side line item comparison and discrepancy analysis against linked quotation')
                    ->visible(fn(PurchaseOrder $r): bool => (bool) $r->quotation_id)
                    ->modalHeading(fn(PurchaseOrder $r): string => "PO #{$r->po_number} vs Quotation #{$r->quotation?->quotation_number} Line Item Reconciliation")
                    ->modalDescription('Detailed comparison of quantities, unit prices, and line items between this Purchase Order and its connected Quotation.')
                    ->modalWidth('5xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalContent(fn(PurchaseOrder $record) => view('filament.infolists.po-quotation-reconciliation', [
                        'reconciliation' => $record->getReconciliationReport(),
                        'getRecord' => fn() => $record,
                    ])),

                Action::make('toggle_conforme')
                    ->label(fn(PurchaseOrder $r) => $r->is_conforme_po ? 'Switch to Normal PO' : 'Switch to Conforme PO')
                    ->icon('heroicon-m-arrows-right-left')
                    ->color('gray')
                    ->tooltip(fn(PurchaseOrder $r) => $r->is_conforme_po
                        ? 'Convert to Normal PO (requires linking to an approved quotation)'
                        : 'Convert to Conforme PO (exempt from quotation matching)')
                    ->visible(fn(PurchaseOrder $r): bool => !$r->trashed() && !$r->isApproved() && $r->status !== PurchaseOrder::STATUS_CANCELLED && $r->status !== PurchaseOrder::STATUS_REJECTED)
                    ->requiresConfirmation()
                    ->modalHeading(fn(PurchaseOrder $r) => $r->is_conforme_po ? 'Switch to Normal Purchase Order' : 'Switch to Conforme Purchase Order')
                    ->modalDescription(fn(PurchaseOrder $r) => $r->is_conforme_po
                        ? 'Switching to Normal PO will require this purchase order to be linked to an approved quotation before Review and Approval.'
                        : 'Switching to Conforme PO exempts this purchase order from quotation matching, immediately unlocking Review and Approval.')
                    ->action(function (PurchaseOrder $record) {
                        $newVal = !$record->is_conforme_po;
                        $record->update(['is_conforme_po' => $newVal]);
                        $typeLabel = $newVal ? 'Conforme PO' : 'Normal PO';
                        Notification::make()->title("PO Classification Updated to {$typeLabel}")->success()->send();
                    }),

                Action::make('approve_po')
                    ->label('Approve PO')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->tooltip(function (PurchaseOrder $record): string {
                        if (!$record->is_conforme_po && !$record->quotation_id) {
                            return 'Normal PO must be linked to an approved quotation first before approval.';
                        }
                        return 'Approve purchase order to authorize fulfillment and delivery';
                    })
                    ->visible(fn(PurchaseOrder $r): bool => !$r->trashed() && !$r->isApproved() && $r->status !== PurchaseOrder::STATUS_CANCELLED && $r->status !== PurchaseOrder::STATUS_REJECTED && ($r->is_conforme_po || (bool) $r->quotation_id) && !$r->hasLineItemDiscrepancies())
                    ->disabled(fn(PurchaseOrder $r): bool => (!$r->is_conforme_po && !$r->quotation_id) || $r->hasLineItemDiscrepancies())
                    ->requiresConfirmation(fn(PurchaseOrder $r): bool => $r->is_conforme_po || (bool) $r->quotation_id)
                    ->action(function (PurchaseOrder $record) {
                        if (!$record->is_conforme_po && !$record->quotation_id) {
                            Notification::make()
                                ->title('Quotation Link Required')
                                ->body("PO {$record->po_number} is a normal purchase order and must be linked to an approved quotation first.")
                                ->warning()
                                ->send();
                            return;
                        }
                        if ($record->hasLineItemDiscrepancies()) {
                            Notification::make()
                                ->title('Approval Restricted: Line Item Discrepancies')
                                ->body("PO {$record->po_number} has line item discrepancies with linked Quotation #{$record->quotation?->quotation_number}. Discrepancies must be resolved before approval.")
                                ->danger()
                                ->send();
                            return;
                        }
                        $record->update(['status' => PurchaseOrder::STATUS_APPROVED]);
                        if ($record->document) {
                            $record->document->update(['status' => \App\Models\Document::STATUS_VERIFIED]);
                        }
                        Notification::make()->title('Purchase Order Approved')->body("PO {$record->po_number} is now approved and verified for delivery.")->success()->send();
                    }),

                Action::make('review')
                    ->label('Review & Verify')
                    ->icon('heroicon-m-clipboard-document-check')
                    ->color('warning')
                    ->tooltip(function (PurchaseOrder $record): string {
                        if (!$record->is_conforme_po && !$record->quotation_id) {
                            return 'Normal PO must be linked to an approved quotation first before review & verification.';
                        }
                        return 'Review, verify math and reconcile purchase order line items';
                    })
                    ->visible(fn(PurchaseOrder $r): bool => !$r->trashed() && !$r->isReviewed() && !$r->isApproved() && $r->status !== PurchaseOrder::STATUS_CANCELLED && $r->status !== PurchaseOrder::STATUS_REJECTED && ($r->is_conforme_po || (bool) $r->quotation_id))
                    ->disabled(fn(PurchaseOrder $r): bool => !$r->is_conforme_po && !$r->quotation_id)
                    ->url(function (PurchaseOrder $record) {
                        if (!$record->is_conforme_po && !$record->quotation_id) {
                            return null;
                        }
                        if ($record->document_id) {
                            return ReviewQueuePage::getUrl(['document_id' => $record->document_id]);
                        }
                        return null;
                    })
                    ->action(function (PurchaseOrder $record) {
                        if (!$record->is_conforme_po && !$record->quotation_id) {
                            Notification::make()
                                ->title('Quotation Link Required')
                                ->body("PO {$record->po_number} is a normal purchase order and must be linked to an approved quotation first.")
                                ->warning()
                                ->send();
                            return;
                        }
                        if ($record->document_id) {
                            return redirect(ReviewQueuePage::getUrl(['document_id' => $record->document_id]));
                        }
                        Notification::make()->title('No attached document for review')->info()->send();
                    }),

                Action::make('mark_delivered')
                    ->label('Mark as Delivered')
                    ->icon('heroicon-m-check-badge')
                    ->color('success')
                    ->tooltip('DR & SI are verified and attached. Mark this purchase order as delivered to deduct inventory and realize sales.')
                    ->visible(fn(PurchaseOrder $r): bool => !$r->trashed() && $r->isApproved() && $r->hasBothDrAndSi() && $r->delivery_status !== PurchaseOrder::DELIVERY_DELIVERED)
                    ->modalHeading(fn(PurchaseOrder $record): string => "Mark as Delivered: PO #{$record->po_number}")
                    ->modalDescription('Both Delivery Receipt (DR) and Sales Invoice (SI) are verified and attached. Confirming delivery will finalize this order, deduct stock from the product catalog/BOM, and record sales in the dashboard.')
                    ->form([
                        DatePicker::make('actual_delivery_date')
                            ->label('Actual Delivery Date')
                            ->default(now())
                            ->required(),
                        Toggle::make('has_warranty')
                            ->label('Include Warranty')
                            ->default(fn($record) => $record->has_warranty ?? true)
                            ->live(),
                        Select::make('warranty_period')
                            ->label('Warranty Period')
                            ->options(PurchaseOrder::getWarrantyPeriodOptions())
                            ->default(fn($record) => $record->warranty_period ?? PurchaseOrder::WARRANTY_1_YEAR)
                            ->visible(fn($get) => (bool) $get('has_warranty')),
                    ])
                    ->action(function (PurchaseOrder $record, array $data) {
                        try {
                            app(OrderFulfillmentService::class)->completeDelivery($record, $data);
                            Notification::make()
                                ->title('PO Marked as Delivered')
                                ->body("PO {$record->po_number} marked as Delivered. Stocks deducted from catalog and sales realized.")
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Delivery Confirmation Failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('upload_dr_si')
                    ->label('Upload DR & SI')
                    ->icon('heroicon-m-arrow-up-tray')
                    ->color('primary')
                    ->tooltip('Upload physical Delivery Receipt (DR) and Sales Invoice (SI) hard copies (Images/PDF)')
                    ->visible(fn(PurchaseOrder $r): bool => !$r->trashed() && $r->isApproved() && !$r->isCompleted())
                    ->modalHeading(fn(PurchaseOrder $record): string => "Upload Hard Copies (DR & SI): PO #{$record->po_number}")
                    ->modalDescription('Upload physical hard copies of both Delivery Receipt (DR) and Sales Invoice (SI) in PDF or Image format.')
                    ->modalWidth('4xl')
                    ->form([
                        Section::make('Delivery Receipt (DR) Details & Upload')
                            ->description('Attach physical/scanned delivery receipt (PDF, JPG, PNG, WEBP)')
                            ->icon('heroicon-o-truck')
                            ->schema([
                                Grid::make(2)->schema([
                                    FileUpload::make('dr_file')
                                        ->label('Delivery Receipt File (PDF / Image)')
                                        ->required()
                                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                                        ->maxSize(25600)
                                        ->disk('local')
                                        ->directory('documents/dr')
                                        ->preserveFilenames()
                                        ->helperText('Supported formats: PDF, JPG, PNG, WEBP (Max 25MB)')
                                        ->columnSpan(2),

                                    TextInput::make('dr_number')
                                        ->label('DR Number')
                                        ->default(fn() => DeliveryReceipt::generateNumber())
                                        ->required(),

                                    DatePicker::make('delivery_date')
                                        ->label('Delivery Date')
                                        ->default(fn(PurchaseOrder $record) => $record->actual_delivery_date ?? now())
                                        ->required(),

                                    TextInput::make('delivered_by')
                                        ->label('Delivered By')
                                        ->placeholder('Driver or logistics personnel'),

                                    TextInput::make('received_by')
                                        ->label('Received By (Client / Site Receiver)')
                                        ->default(fn(PurchaseOrder $record) => $record->customer_name)
                                        ->placeholder('Customer site receiver name')
                                        ->helperText('Name of the client or site personnel who received the delivery'),
                                ]),
                            ]),

                        Section::make('Sales Invoice (SI) Details & Upload')
                            ->description('Attach physical/scanned sales invoice (PDF, JPG, PNG, WEBP)')
                            ->icon('heroicon-o-receipt-percent')
                            ->schema([
                                Grid::make(2)->schema([
                                    FileUpload::make('si_file')
                                        ->label('Sales Invoice File (PDF / Image)')
                                        ->required()
                                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                                        ->maxSize(25600)
                                        ->disk('local')
                                        ->directory('documents/si')
                                        ->preserveFilenames()
                                        ->helperText('Supported formats: PDF, JPG, PNG, WEBP (Max 25MB)')
                                        ->columnSpan(2),

                                    TextInput::make('si_number')
                                        ->label('SI Number')
                                        ->default(fn() => SalesInvoice::generateNumber())
                                        ->required(),

                                    DatePicker::make('invoice_date')
                                        ->label('Invoice Date')
                                        ->default(now())
                                        ->required(),

                                    Select::make('payment_status')
                                        ->label('Payment Status')
                                        ->options([
                                            SalesInvoice::STATUS_PAID => 'Paid',
                                            SalesInvoice::STATUS_UNPAID => 'Unpaid',
                                            SalesInvoice::STATUS_PARTIAL => 'Partial',
                                        ])
                                        ->default(SalesInvoice::STATUS_PAID)
                                        ->required(),

                                    TextInput::make('total_amount')
                                        ->label('Invoice Total (₱)')
                                        ->numeric()
                                        ->prefix('₱')
                                        ->default(fn(PurchaseOrder $record) => (float) $record->order_amount)
                                        ->required(),
                                ]),
                            ]),

                        Toggle::make('auto_mark_delivered')
                            ->label('Mark order as Delivered immediately upon upload')
                            ->helperText('If enabled, will immediately deduct stock and realize sales. If disabled, DR & SI will be attached and verified, unlocking the "Mark as Delivered" action button.')
                            ->default(true),
                    ])
                    ->action(function (PurchaseOrder $record, array $data) {
                        try {
                            if (!empty($data['auto_mark_delivered'])) {
                                $result = app(OrderFulfillmentService::class)->fulfillOrder($record, $data);
                                $drNo = $result['delivery_receipt']->dr_number;
                                $siNo = $result['sales_invoice']->si_number;

                                Notification::make()
                                    ->title('Order Delivered & Completed')
                                    ->body("PO {$record->po_number} fulfilled with DR #{$drNo} and SI #{$siNo}. Stocks deducted from catalog and sales reflected across dashboards.")
                                    ->success()
                                    ->send();
                            } else {
                                $result = app(OrderFulfillmentService::class)->attachFulfillmentDocuments($record, $data);
                                $drNo = $result['delivery_receipt']->dr_number;
                                $siNo = $result['sales_invoice']->si_number;

                                Notification::make()
                                    ->title('DR & SI Uploaded and Verified')
                                    ->body("DR #{$drNo} and SI #{$siNo} attached. 'Mark as Delivered' is now available to finalize the order.")
                                    ->success()
                                    ->send();
                            }
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Fulfillment Failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('delivery_tracker')
                    ->label('Delivery Tracker')
                    ->icon('heroicon-m-clock')
                    ->color('info')
                    ->tooltip('Open Delivery & Warranty Tracker for this purchase order')
                    ->visible(fn(PurchaseOrder $r): bool => !$r->trashed() && $r->isApproved() && ($r->delivery_status === PurchaseOrder::DELIVERY_DELIVERED || $r->status === PurchaseOrder::STATUS_DELIVERED))
                    ->url(fn() => DeliveryMonitoringPage::getUrl()),

                Action::make('cancel_po')
                    ->label('Cancel PO')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->visible(fn(PurchaseOrder $r): bool => !$r->trashed() && $r->status !== PurchaseOrder::STATUS_CANCELLED && $r->status !== PurchaseOrder::STATUS_DELIVERED)
                    ->requiresConfirmation()
                    ->action(function (PurchaseOrder $record) {
                        $record->update(['status' => PurchaseOrder::STATUS_CANCELLED]);
                        Notification::make()->title('PO Cancelled')->warning()->send();
                    }),

                ViewAction::make(),
                DeleteAction::make()->requiresConfirmation(),
                RestoreAction::make()->requiresConfirmation()->visible(fn(PurchaseOrder $record): bool => $record->trashed()),
                ForceDeleteAction::make()->requiresConfirmation()->visible(fn(PurchaseOrder $record): bool => $record->trashed() && (auth()->user()?->canDeleteRecords() ?? false)),
            ]),
        ];
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    protected static function getTableBulkActions(): array
    {
        return [
            BulkActionGroup::make([
                DeleteBulkAction::make()->requiresConfirmation(),
                RestoreBulkAction::make()->requiresConfirmation(),
                ForceDeleteBulkAction::make()->requiresConfirmation()->visible(fn(): bool => auth()->user()?->canDeleteRecords() ?? false),
            ]),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchaseOrders::route('/'),
            'view' => Pages\ViewPurchaseOrder::route('/{record}'),
        ];
    }
}
