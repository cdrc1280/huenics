<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseOrderResource\Pages;
use App\Models\Product;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Enums\RecordActionsPosition;
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
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-shopping-cart';
    protected static \UnitEnum|string|null $navigationGroup = 'Sales & Quotations';
    protected static ?string $navigationLabel = 'Purchase Orders';
    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return auth()->user()?->canManageQuotations() ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['salesAgent', 'project', 'quotation']);
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
                ->columns(2)
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
                        ->required(),

                    Select::make('status')
                        ->label('PO Status')
                        ->options([
                            PurchaseOrder::STATUS_PENDING => 'Pending Delivery',
                            PurchaseOrder::STATUS_DELIVERED => 'Delivered',
                            PurchaseOrder::STATUS_CANCELLED => 'Cancelled',
                        ])
                        ->default(PurchaseOrder::STATUS_PENDING)
                        ->required(),

                    Textarea::make('notes')
                        ->label('Notes')
                        ->nullable()
                        ->columnSpanFull(),
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
                                ->columnSpan(1),

                            Select::make('product_id')
                                ->label('Product')
                                ->options(Product::active()->pluck('canonical_name', 'id'))
                                ->searchable()
                                ->nullable()
                                ->live()
                                ->afterStateUpdated(function ($state, $set) {
                                    if ($state) {
                                        $product = Product::find($state);
                                        if ($product) {
                                            $set('description', $product->canonical_name);
                                            $set('unit_price', $product->selling_price ?? $product->default_price ?? 0);
                                            $set('base_cost', $product->base_cost_price ?? 0);
                                            $set('unit', $product->unit_default ?? 'pcs');
                                        }
                                    }
                                })
                                ->columnSpan(3),

                            TextInput::make('description')
                                ->label('Description')
                                ->required()
                                ->columnSpan(4),

                            TextInput::make('qty')
                                ->label('Qty')
                                ->numeric()
                                ->default(1)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn($state, $set, $get) => $set('line_total', round((float) $state * (float) $get('unit_price'), 2)))
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
                                ->afterStateUpdated(fn($state, $set, $get) => $set('line_total', round((float) $get('qty') * (float) $state, 2)))
                                ->columnSpan(2),

                            TextInput::make('base_cost')
                                ->label('Base Cost (₱)')
                                ->numeric()
                                ->prefix('₱')
                                ->default(0)
                                ->columnSpan(2),

                            TextInput::make('line_total')
                                ->label('Line Total (₱)')
                                ->numeric()
                                ->prefix('₱')
                                ->disabled()
                                ->dehydrated()
                                ->columnSpan(2),
                        ])
                        ->columns(16)
                        ->reorderable('line_no')
                        ->addActionLabel('+ Add Line Item')
                        ->defaultItems(1),
                ]),

            Section::make('Delivery & Warranty')
                ->icon('heroicon-o-truck')
                ->columns(2)
                ->schema([
                    DatePicker::make('actual_delivery_date')
                        ->label('Actual Delivery Date')
                        ->nullable(),

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
                ->columns(3)
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
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('mark_delivered')
                        ->label('Mark Delivered')
                        ->icon('heroicon-m-truck')
                        ->color('success')
                        ->tooltip('Confirm order delivery with DR#, auto-deduct BOM components & activate warranty clock')
                        ->visible(fn(PurchaseOrder $r): bool => $r->delivery_status !== PurchaseOrder::DELIVERY_DELIVERED)
                        ->form([
                            DatePicker::make('actual_delivery_date')
                                ->label('Actual Delivery Date')
                                ->required()
                                ->default(now()),

                            TextInput::make('delivery_receipt_no')
                                ->label('DR# (Delivery Receipt No.)')
                                ->nullable(),
                        ])
                        ->action(function (PurchaseOrder $record, array $data) {
                            $record->update([
                                'delivery_status' => PurchaseOrder::DELIVERY_DELIVERED,
                                'actual_delivery_date' => $data['actual_delivery_date'],
                                'delivery_receipt_no' => $data['delivery_receipt_no'] ?? null,
                                'status' => PurchaseOrder::STATUS_DELIVERED,
                            ]);

                            Notification::make()
                                ->title('Delivery Confirmed')
                                ->body('Inventory deducted and warranty clock started.')
                                ->success()
                                ->send();
                        }),

                    Action::make('cancel_po')
                        ->label('Cancel PO')
                        ->icon('heroicon-m-x-circle')
                        ->color('danger')
                        ->visible(fn(PurchaseOrder $r): bool => $r->status === PurchaseOrder::STATUS_PENDING)
                        ->requiresConfirmation()
                        ->action(function (PurchaseOrder $record) {
                            $record->update(['status' => PurchaseOrder::STATUS_CANCELLED]);
                            Notification::make()->title('PO Cancelled')->warning()->send();
                        }),

                    EditAction::make(),
                    ViewAction::make(),
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
            'index' => Pages\ListPurchaseOrders::route('/'),
            'create' => Pages\CreatePurchaseOrder::route('/create'),
            'edit' => Pages\EditPurchaseOrder::route('/{record}/edit'),
            'view' => Pages\ViewPurchaseOrder::route('/{record}'),
        ];
    }
}
