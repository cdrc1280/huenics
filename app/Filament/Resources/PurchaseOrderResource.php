<?php

namespace App\Filament\Resources;

use App\Filament\Pages\DeliveryMonitoringPage;
use App\Filament\Pages\ReviewQueuePage;
use App\Filament\Resources\PurchaseOrderResource\Pages;
use App\Models\Product;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\User;
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

                            return $query->get()->mapWithKeys(fn (Quotation $q) => [
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
                        ->options([
                            PurchaseOrder::DELIVERY_PENDING => 'Pending',
                            PurchaseOrder::DELIVERY_TRANSIT => 'In Transit',
                            PurchaseOrder::DELIVERY_DELIVERED => 'Delivered',
                            PurchaseOrder::DELIVERY_OVERDUE => 'Overdue',
                        ])
                        ->default(PurchaseOrder::DELIVERY_PENDING)
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state, $set, $get) {
                            if ($state === PurchaseOrder::DELIVERY_DELIVERED) {
                                if (!$get('actual_delivery_date')) {
                                    $set('actual_delivery_date', now()->toDateString());
                                }
                                $set('status', PurchaseOrder::STATUS_DELIVERED);
                            } else {
                                $set('actual_delivery_date', null);
                                $set('status', PurchaseOrder::STATUS_PENDING);
                            }
                        }),

                    Select::make('status')
                        ->label('PO Status')
                        ->options([
                            PurchaseOrder::STATUS_PENDING          => 'Pending Approval',
                            PurchaseOrder::STATUS_APPROVED         => 'Approved (Ready to Deliver)',
                            PurchaseOrder::STATUS_PENDING_DELIVERY => 'Pending Delivery',
                            PurchaseOrder::STATUS_DELIVERED        => 'Delivered',
                            PurchaseOrder::STATUS_CANCELLED        => 'Cancelled',
                            PurchaseOrder::STATUS_REJECTED         => 'Rejected',
                        ])
                        ->default(PurchaseOrder::STATUS_APPROVED)
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state, $set, $get) {
                            if ($state === PurchaseOrder::STATUS_DELIVERED) {
                                $set('delivery_status', PurchaseOrder::DELIVERY_DELIVERED);
                                if (!$get('actual_delivery_date')) {
                                    $set('actual_delivery_date', now()->toDateString());
                                }
                            } elseif (in_array($state, [PurchaseOrder::STATUS_PENDING, PurchaseOrder::STATUS_APPROVED, PurchaseOrder::STATUS_PENDING_DELIVERY])) {
                                if ($get('delivery_status') === PurchaseOrder::DELIVERY_DELIVERED) {
                                    $set('delivery_status', PurchaseOrder::DELIVERY_PENDING);
                                }
                                $set('actual_delivery_date', null);
                            }
                        }),

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
                            ->live()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                if (empty($state)) {
                                    $set('delivery_status', PurchaseOrder::DELIVERY_PENDING);
                                    $set('status', PurchaseOrder::STATUS_PENDING);
                                } else {
                                    $set('delivery_status', PurchaseOrder::DELIVERY_DELIVERED);
                                    $set('status', PurchaseOrder::STATUS_DELIVERED);
                                }
                            }),

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

                TextColumn::make('quotation.quotation_number')
                    ->label('Quotation #')
                    ->sortable()
                    ->searchable()
                    ->default('—')
                    ->tooltip(fn(PurchaseOrder $record): string => $record->quotation ? "Linked Quotation: {$record->quotation->quotation_number}" : 'No linked quotation')
                    ->toggleable(isToggledHiddenByDefault: true),

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
                    ->color(fn(string $state): string => match ($state) {
                        PurchaseOrder::STATUS_PENDING => 'warning',
                        PurchaseOrder::STATUS_DELIVERED => 'success',
                        PurchaseOrder::STATUS_CANCELLED, PurchaseOrder::STATUS_REJECTED => 'danger',
                        default => 'gray',
                    }),

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
                    })
                    ->tooltip(fn(PurchaseOrder $record): string => "Delivery status: {$record->delivery_status}" . ($record->delivery_receipt_no ? " (DR# {$record->delivery_receipt_no})" : '')),

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
                    ->tooltip(fn(PurchaseOrder $record): string => "Warranty status: {$record->warranty_status}" . ($record->warranty_end_date ? " (Expires {$record->warranty_end_date->format('M d, Y')})" : '')),

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
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
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
                    ])->pluck('name', 'id')),

                TrashedFilter::make(),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('approve_po')
                        ->label('Approve PO')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->tooltip('Approve purchase order to authorize fulfillment and delivery')
                        ->visible(fn(PurchaseOrder $r): bool => !$r->trashed() && !$r->isApproved() && $r->status !== PurchaseOrder::STATUS_CANCELLED && $r->status !== PurchaseOrder::STATUS_REJECTED)
                        ->requiresConfirmation()
                        ->action(function (PurchaseOrder $record) {
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
                        ->tooltip('Review, verify math and reconcile purchase order line items')
                        ->visible(fn(PurchaseOrder $r): bool => !$r->trashed() && !$r->isReviewed() && !$r->isApproved() && $r->status !== PurchaseOrder::STATUS_CANCELLED && $r->status !== PurchaseOrder::STATUS_REJECTED)
                        ->url(function (PurchaseOrder $record) {
                            if ($record->document_id) {
                                return ReviewQueuePage::getUrl(['document_id' => $record->document_id]);
                            }
                            return null;
                        })
                        ->action(function (PurchaseOrder $record) {
                            if ($record->document_id) {
                                return redirect(ReviewQueuePage::getUrl(['document_id' => $record->document_id]));
                            }
                            Notification::make()->title('No attached document for review')->info()->send();
                        }),

                    Action::make('mark_delivered')
                        ->label('Mark Delivered')
                        ->icon('heroicon-m-check-badge')
                        ->color('success')
                        ->tooltip('Mark this purchase order as delivered')
                        ->visible(fn(PurchaseOrder $r): bool => !$r->trashed() && $r->isApproved() && $r->delivery_status !== PurchaseOrder::DELIVERY_DELIVERED && $r->status !== PurchaseOrder::STATUS_DELIVERED)
                        ->form([
                            DatePicker::make('actual_delivery_date')
                                ->label('Actual Delivery Date')
                                ->default(now())
                                ->required(),
                            TextInput::make('delivery_receipt_no')
                                ->label('DR # (Delivery Receipt No.)')
                                ->nullable(),
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
                            if (!$record->isApproved()) {
                                Notification::make()->title('Action Blocked')->body('Purchase Order must be approved before delivery can be marked.')->danger()->send();
                                return;
                            }

                            $record->update([
                                'delivery_status' => PurchaseOrder::DELIVERY_DELIVERED,
                                'status' => PurchaseOrder::STATUS_DELIVERED,
                                'delivery_receipt_no' => $data['delivery_receipt_no'] ?? null,
                                'actual_delivery_date' => $data['actual_delivery_date'],
                                'has_warranty' => $data['has_warranty'] ?? true,
                                'warranty_period' => $data['warranty_period'] ?? PurchaseOrder::WARRANTY_1_YEAR,
                            ]);

                            Notification::make()
                                ->title('Delivery Confirmed')
                                ->body("PO {$record->po_number} marked as delivered. Inventory deducted & warranty activated.")
                                ->success()
                                ->send();
                        }),

                    Action::make('delivery_tracker')
                        ->label('Delivery Tracker')
                        ->icon('heroicon-m-truck')
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

                    EditAction::make(),
                    ViewAction::make(),
                    DeleteAction::make()->requiresConfirmation(),
                    RestoreAction::make()->requiresConfirmation()->visible(fn(PurchaseOrder $record): bool => $record->trashed()),
                    ForceDeleteAction::make()->requiresConfirmation()->visible(fn(PurchaseOrder $record): bool => $record->trashed() && (auth()->user()?->isAdmin() ?? false)),
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
            'index' => Pages\ListPurchaseOrders::route('/'),
            'edit' => Pages\EditPurchaseOrder::route('/{record}/edit'),
            'view' => Pages\ViewPurchaseOrder::route('/{record}'),
        ];
    }
}
