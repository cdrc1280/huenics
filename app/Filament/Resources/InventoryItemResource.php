<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryItemResource\Pages;
use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\User;
use App\Services\InventoryReportService;
use App\Services\InventoryService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InventoryItemResource extends Resource
{
    protected static ?string $model = InventoryItem::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-archive-box';
    protected static \UnitEnum|string|null $navigationGroup = 'Warehouse & Inventory';
    protected static ?string $navigationLabel = 'Inventory Stock Ledger';
    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->with([
                'product.components.componentProduct',
                'movements' => fn ($q) => $q->latest('created_at')->limit(3),
            ]);
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->canManageInventory() ?? true;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->canManageInventory() ?? true;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->canDeleteRecords() ?? true;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Product & Identification')
                    ->description('Link item to catalogue product, SKU, and primary supply details')
                    ->components([
                        Select::make('product_id')
                            ->label('Catalogue Product')
                            ->options(Product::pluck('canonical_name', 'id'))
                            ->searchable()
                            ->required()
                            ->reactive()
                            ->columnSpanFull(),

                        TextInput::make('location')
                            ->label('Storage Location')
                            ->placeholder('e.g. Mam CBS ROOM INSIDE CABINET, TECHNICAL ROOM SHELF 3 LEVEL 2')
                            ->maxLength(255),

                        TextInput::make('supplier_name')
                            ->label('Suppliers Name')
                            ->placeholder('e.g. SUPREME COMPONENTS INTL PTE.LTD')
                            ->maxLength(255),

                        TextInput::make('po_number')
                            ->label('P.O. Nos.')
                            ->placeholder('e.g. 2022-3263, 20-00260, 241000010-M')
                            ->maxLength(100),

                        DatePicker::make('inbound_date')
                            ->label('Inbound / Log Date')
                            ->default(now()),
                    ])
                    ->columns(2),

                Section::make('Stock Balances & Units')
                    ->description('Current quantities on hand, safety reorder thresholds, and unit of measure')
                    ->components([
                        TextInput::make('quantity_on_hand')
                            ->label('Balance (Quantity On Hand)')
                            ->numeric()
                            ->required()
                            ->default(0),

                        TextInput::make('quantity_reserved')
                            ->label('Quantity Reserved')
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('Stock reserved by unfulfilled Purchase Orders'),

                        TextInput::make('reorder_point')
                            ->label('Safety Reorder Threshold')
                            ->numeric()
                            ->default(10),

                        TextInput::make('unit')
                            ->label('Unit of Measure')
                            ->default('pcs')
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Outbound Release & Dispatch Tracking')
                    ->description('Details of latest customer delivery or site issuance')
                    ->components([
                        TextInput::make('customer_name')
                            ->label('Customer Name')
                            ->placeholder('e.g. FOOTACTION INTERNATIONAL MANUFACTURING CORP.')
                            ->maxLength(255),

                        TextInput::make('project_name')
                            ->label('Project Name')
                            ->placeholder('e.g. FAIRVIEW MERRELL STORE')
                            ->maxLength(255),

                        DatePicker::make('date_released')
                            ->label('Date Released'),

                        Textarea::make('remarks')
                            ->label('Remarks / Status')
                            ->placeholder('e.g. COMPLETE DELIVERY, Released to Joshua')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('inbound_date')
                    ->label('Date')
                    ->state(fn(InventoryItem $r) => $r->inbound_date ?: $r->created_at)
                    ->date('m/d/Y')
                    ->sortable()
                    ->placeholder('—')
                    ->tooltip('Inbound shipment or inventory log date'),

                TextColumn::make('po_number')
                    ->label('P.O. Nos.')
                    ->searchable()
                    ->sortable()
                    ->default('—')
                    ->weight('medium')
                    ->tooltip(fn(InventoryItem $r) => "PO Reference: " . ($r->po_number ?: 'None')),

                TextColumn::make('supplier_name')
                    ->label('Suppliers Name')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->default('—')
                    ->tooltip(fn(InventoryItem $r) => "Supplier: " . ($r->supplier_name ?: 'None')),

                TextColumn::make('product.sku')
                    ->label('S.K.U.')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->default('—')
                    ->tooltip(fn(InventoryItem $r) => "SKU: " . ($r->product?->sku ?: 'N/A')),

                TextColumn::make('product.product_code')
                    ->label('Item Code')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->default('—')
                    ->tooltip(fn(InventoryItem $r) => "Product Code: " . ($r->product?->product_code ?: 'N/A')),

                ImageColumn::make('product.image_path')
                    ->label('Pictures')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(url('/images/placeholder-product.png')),

                TextColumn::make('product.canonical_name')
                    ->label('Particulars')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->tooltip(fn(InventoryItem $r) => "Particulars: " . ($r->product?->canonical_name ?: 'N/A')),

                TextColumn::make('quantity_on_hand')
                    ->label('Balance')
                    ->numeric(0)
                    ->sortable()
                    ->badge()
                    ->color(fn(InventoryItem $record): string => match (true) {
                        $record->quantity_on_hand <= 0 => 'danger',
                        $record->reorder_point && $record->quantity_on_hand <= $record->reorder_point => 'warning',
                        default => 'success',
                    })
                    ->formatStateUsing(fn($state, InventoryItem $r) => number_format((float) $state, 0) . ' ' . ($r->unit ?: 'pcs'))
                    ->tooltip(fn(InventoryItem $r) => "Current stock balance: {$r->quantity_on_hand} {$r->unit}"),

                TextColumn::make('bom_hierarchy')
                    ->label('BOM / Assembly')
                    ->badge()
                    ->state(function (InventoryItem $record): string {
                        $parentCount = $record->product?->components?->count() ?? 0;
                        if ($parentCount > 0) {
                            return "Parent ({$parentCount} Parts)";
                        }
                        $usages = \App\Models\ProductComponent::where('component_product_id', $record->product_id)->count();
                        if ($usages > 0) {
                            return "Sub-Component ({$usages})";
                        }
                        return 'Standard';
                    })
                    ->color(fn (string $state): string => match (true) {
                        str_starts_with($state, 'Parent') => 'info',
                        str_starts_with($state, 'Sub-Component') => 'warning',
                        default => 'gray',
                    })
                    ->tooltip(function (InventoryItem $record): string {
                        if ($record->product && $record->product->components->isNotEmpty()) {
                            $partNames = $record->product->components->pluck('component_name')->filter()->take(3)->implode(', ');
                            return "Parent assembly with sub-components: " . ($partNames ?: 'Multiple parts');
                        }
                        $parents = \App\Models\Product::whereHas('components', fn($q) => $q->where('component_product_id', $record->product_id))->pluck('canonical_name')->take(2)->implode(', ');
                        if ($parents) {
                            return "Sub-component used in: " . $parents;
                        }
                        return 'Standard standalone inventory product';
                    }),

                TextColumn::make('location')
                    ->label('Location')
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->default('—')
                    ->tooltip(fn(InventoryItem $r) => "Storage Location: " . ($r->location ?: 'Unassigned')),

                TextColumn::make('customer_name')
                    ->label('Customer Name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->default('—')
                    ->wrap(),

                TextColumn::make('project_name')
                    ->label('Project Name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->default('—')
                    ->wrap(),

                TextColumn::make('date_released')
                    ->label('Date Released')
                    ->date('m/d/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->placeholder('—'),

                TextColumn::make('remarks')
                    ->label('Remarks')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->default('—')
                    ->wrap(),
            ])
            ->filters([
                SelectFilter::make('location')
                    ->label('Filter by Location')
                    ->options(fn () => InventoryItem::whereNotNull('location')->where('location', '!=', '')->distinct()->pluck('location', 'location')),

                SelectFilter::make('supplier_name')
                    ->label('Filter by Supplier')
                    ->options(fn () => InventoryItem::whereNotNull('supplier_name')->where('supplier_name', '!=', '')->distinct()->pluck('supplier_name', 'supplier_name')),

                Filter::make('low_stock')
                    ->label('Low Stock Warning')
                    ->query(fn (Builder $query) => $query->whereNotNull('reorder_point')->whereColumn('quantity_on_hand', '<=', 'reorder_point')),

                Filter::make('zero_stock')
                    ->label('Zero Stock / Depleted')
                    ->query(fn (Builder $query) => $query->where('quantity_on_hand', '<=', 0)),

                SelectFilter::make('bom_type')
                    ->label('Filter by BOM / Hierarchy')
                    ->options([
                        'parent' => 'Assembly Parents (Has Sub-Components)',
                        'component' => 'Sub-Components / Child Parts',
                        'standalone' => 'Standard / Standalone Products',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (empty($data['value'])) {
                            return;
                        }
                        if ($data['value'] === 'parent') {
                            $query->whereHas('product.components');
                        } elseif ($data['value'] === 'component') {
                            $query->whereHas('product', function ($pq) {
                                $pq->whereIn('id', \App\Models\ProductComponent::whereNotNull('component_product_id')->select('component_product_id'));
                            });
                        } elseif ($data['value'] === 'standalone') {
                            $query->whereDoesntHave('product.components')
                                  ->whereHas('product', function ($pq) {
                                      $pq->whereNotIn('id', \App\Models\ProductComponent::whereNotNull('component_product_id')->select('component_product_id'));
                                  });
                        }
                    }),

                TrashedFilter::make(),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('view_bom')
                        ->label('View BOM Hierarchy')
                        ->icon('heroicon-o-puzzle-piece')
                        ->color('info')
                        ->visible(fn(InventoryItem $record): bool => 
                            ($record->product && $record->product->components()->exists()) ||
                            \App\Models\ProductComponent::where('component_product_id', $record->product_id)->exists()
                        )
                        ->modalHeading(fn(InventoryItem $record): string => "BOM Hierarchy: {$record->product?->canonical_name}")
                        ->modalDescription('Parent-child assembly relationship, component stock availability, and unit cost breakdown')
                        ->modalWidth('5xl')
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Close')
                        ->modalContent(function (InventoryItem $record) {
                            $parentComponents = $record->product 
                                ? $record->product->components()->with(['componentProduct.inventoryItem'])->get() 
                                : collect();
                            $usedInParents = \App\Models\Product::whereHas('components', fn($q) => $q->where('component_product_id', $record->product_id))
                                ->with([
                                    'inventoryItem',
                                    'components' => fn($q) => $q->where('component_product_id', $record->product_id),
                                ])->get();

                            return view('filament.components.inventory-bom-modal', [
                                'record' => $record,
                                'parentComponents' => $parentComponents,
                                'usedInParents' => $usedInParents,
                            ]);
                        }),
                    EditAction::make(),
                    DeleteAction::make()->requiresConfirmation(),
                    RestoreAction::make()->requiresConfirmation()->visible(fn(InventoryItem $record): bool => $record->trashed()),
                ]),
            ], position: RecordActionsPosition::BeforeColumns)
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('export_selected')
                        ->label('Export Selected to Report (CSV)')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->action(function (Collection $records) {
                            $csv = app(InventoryReportService::class)->exportInventoryReport($records);
                            return response()->streamDownload(function () use ($csv) {
                                echo $csv;
                            }, 'huenics-inventory-selected-' . date('Ymd-His') . '.csv', [
                                'Content-Type' => 'text/csv; charset=UTF-8',
                            ]);
                        }),
                    DeleteBulkAction::make()->requiresConfirmation(),
                    RestoreBulkAction::make()->requiresConfirmation(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListInventoryItems::route('/'),
            'create' => Pages\CreateInventoryItem::route('/create'),
            'edit'   => Pages\EditInventoryItem::route('/{record}/edit'),
        ];
    }
}
