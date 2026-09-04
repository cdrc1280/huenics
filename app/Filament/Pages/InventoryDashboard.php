<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\InventoryAlertsWidget;
use App\Models\InventoryItem;
use App\Models\User;
use App\Services\InventoryService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Query\Builder;

class InventoryDashboard extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-archive-box';
    protected static \UnitEnum|string|null $navigationGroup = 'Dashboards & Analytics';
    protected static ?string $navigationLabel = 'Inventory & Stock Analytics';
    protected static ?string $title = 'Inventory Management';
    protected string $view = 'filament.pages.inventory-dashboard';
    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        return auth()->user()?->canManageInventory() ?? false;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            InventoryAlertsWidget::class,
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                InventoryItem::query()
                    ->with(['product.components'])
                    ->join('products', 'inventory_items.product_id', '=', 'products.id')
                    ->select('inventory_items.*')
                    ->orderBy('products.canonical_name')
            )
            ->columns([
                TextColumn::make('product.product_code')
                    ->label('SKU / Code')
                    ->searchable()
                    ->sortable()
                    ->default('—')
                    ->tooltip(fn(InventoryItem $record): string => "Product Code: " . ($record->product?->product_code ?? 'N/A')),

                TextColumn::make('product.canonical_name')
                    ->label('Product Name')
                    ->searchable()
                    ->weight('bold')
                    ->tooltip(fn(InventoryItem $record): string => "Canonical: {$record->product?->canonical_name}"),

                TextColumn::make('product.category')
                    ->label('Category')
                    ->badge()
                    ->default('—')
                    ->tooltip(fn(InventoryItem $record): string => "Category: " . ($record->product?->category ?? 'General')),

                TextColumn::make('quantity_on_hand')
                    ->label('On Hand')
                    ->numeric(0)
                    ->color(fn(InventoryItem $record): string => match (true) {
                        $record->quantity_on_hand <= 0 => 'danger',
                        $record->reorder_point && $record->quantity_on_hand <= $record->reorder_point => 'warning',
                        default => 'success',
                    })
                    ->tooltip(fn(InventoryItem $record): string => "Physical stock on hand: {$record->quantity_on_hand} {$record->unit}"),

                TextColumn::make('bom_hierarchy')
                    ->label('BOM / Assembly')
                    ->badge()
                    ->state(function (InventoryItem $record): string {
                        $parentCount = $record->product?->components?->count() ?? 0;
                        if ($parentCount > 0) {
                            return "Parent ({$parentCount})";
                        }
                        $usages = \App\Models\ProductComponent::where('component_product_id', $record->product_id)->count();
                        if ($usages > 0) {
                            return "Sub-Part ({$usages})";
                        }
                        return 'Standard';
                    })
                    ->color(fn (string $state): string => match (true) {
                        str_starts_with($state, 'Parent') => 'info',
                        str_starts_with($state, 'Sub-Part') => 'warning',
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

                TextColumn::make('quantity_reserved')
                    ->label('Reserved')
                    ->numeric(0)
                    ->tooltip(fn(InventoryItem $record): string => "Stock allocated/reserved for pending POs: {$record->quantity_reserved} {$record->unit}"),

                TextColumn::make('quantity_available')
                    ->label('Available')
                    ->state(fn(InventoryItem $r): float => (float) $r->quantity_on_hand - (float) $r->quantity_reserved)
                    ->numeric(0)
                    ->color(fn($state): string => $state <= 0 ? 'danger' : 'success')
                    ->tooltip(fn(InventoryItem $r): string => "Net available for new quotations (On Hand minus Reserved): " . ((float) $r->quantity_on_hand - (float) $r->quantity_reserved) . " {$r->unit}"),

                TextColumn::make('reorder_point')
                    ->label('Reorder Point')
                    ->numeric(0)
                    ->placeholder('—')
                    ->tooltip(fn(InventoryItem $record): string => "Minimum safety stock threshold: {$record->reorder_point} {$record->unit}"),

                TextColumn::make('unit')
                    ->label('Unit'),

                TextColumn::make('location')
                    ->label('Location')
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable()
                    ->default('—')
                    ->tooltip(fn(InventoryItem $record): string => "Storage Location: " . ($record->location ?: 'Unassigned')),

                TextColumn::make('supplier_name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->default('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('po_number')
                    ->label('P.O. Nos.')
                    ->searchable()
                    ->sortable()
                    ->default('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('low_stock_flag')
                    ->label('Low Stock?')
                    ->state(fn(InventoryItem $r): bool => $r->reorder_point && $r->quantity_on_hand <= $r->reorder_point)
                    ->boolean()
                    ->trueIcon('heroicon-m-exclamation-triangle')
                    ->falseIcon('heroicon-m-check-circle')
                    ->trueColor('warning')
                    ->falseColor('success')
                    ->tooltip(fn(InventoryItem $r): string => $r->reorder_point && $r->quantity_on_hand <= $r->reorder_point ? 'Low stock warning: stock level is at or below reorder threshold' : 'Stock level is healthy'),
            ])
            ->headerActions([
                Action::make('download_template')
                    ->label('Download Template')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->url(route('inventory.download-template'))
                    ->openUrlInNewTab(false)
                    ->tooltip('Download sample Inventory Report CSV template'),

                Action::make('export_csv')
                    ->label('Export Inventory Report')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(route('inventory.export-report'))
                    ->openUrlInNewTab(false)
                    ->tooltip('Export the current inventory report matching reference ledger format'),
            ])
            ->filters([
                Filter::make('low_stock')
                    ->label('Low Stock Only')
                    ->query(fn(Builder $query) => $query->whereNotNull('reorder_point')->whereColumn('quantity_on_hand', '<=', 'reorder_point')),

                Filter::make('zero_stock')
                    ->label('Zero Stock Only')
                    ->query(fn(Builder $query) => $query->where('quantity_on_hand', '<=', 0)),
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

                    Action::make('view_history')
                        ->label('Transaction History')
                        ->icon('heroicon-m-clock')
                        ->color('gray')
                        ->modalHeading(fn(InventoryItem $record) => "Inventory History: {$record->product->canonical_name}")
                        ->modalContent(function (InventoryItem $record) {
                            $transactions = $record->transactions()->with('performer')->latest()->take(20)->get();
                            return view('filament.modals.inventory-history', compact('transactions'));
                        })
                        ->modalSubmitAction(false),
                ]),
            ], position: RecordActionsPosition::BeforeColumns)
            ->heading('Stock Ledger')
            ->emptyStateHeading('No inventory records found')
            ->emptyStateDescription('Inventory items are created automatically when products with is_huenics_owned = true are added.')
            ->emptyStateIcon('heroicon-o-archive-box');
    }
}
