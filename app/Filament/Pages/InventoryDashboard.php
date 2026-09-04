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
    protected static \UnitEnum|string|null $navigationGroup = 'Inventory & Operations';
    protected static ?string $navigationLabel = 'Inventory & Stock Analytics';
    protected static ?string $title = 'Inventory Management';
    protected string $view = 'filament.pages.inventory-dashboard';
    protected static ?int $navigationSort = 1;

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
                    ->with(['product'])
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
                    ->default('—')
                    ->tooltip(fn(InventoryItem $record): string => "Minimum safety stock threshold: {$record->reorder_point} {$record->unit}"),

                TextColumn::make('unit')
                    ->label('Unit'),

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

                    Action::make('adjust_stock')
                        ->label('Adjust Stock')
                        ->icon('heroicon-m-adjustments-horizontal')
                        ->color('primary')
                        ->tooltip('Perform manual stock adjustment (Initial Stock, Purchase In, Adjustment Up/Down)')
                        ->form([

                            Select::make('type')
                                ->label('Adjustment Type')
                                ->options([
                                    'initial_stock' => 'Initial Stock',
                                    'purchase_in' => 'Purchase In (Received)',
                                    'adjustment_up' => 'Adjustment — Add Stock',
                                    'adjustment_down' => 'Adjustment — Remove Stock',
                                ])
                                ->required(),

                            TextInput::make('quantity')
                                ->label('Quantity')
                                ->numeric()
                                ->minValue(0.0001)
                                ->required(),

                            Textarea::make('notes')
                                ->label('Reason / Notes')
                                ->required(),
                        ])
                        ->action(function (InventoryItem $record, array $data) {
                            try {
                                app(InventoryService::class)->adjustStock(
                                    $record,
                                    (float) $data['quantity'],
                                    $data['type'],
                                    $data['notes'],
                                    auth()->user() ?? User::first()
                                );

                                Notification::make()
                                    ->title('Stock Adjusted')
                                    ->body("Stock updated for {$record->product->canonical_name}. Transaction recorded in Activity Log.")
                                    ->success()
                                    ->send();
                            } catch (\Throwable $e) {
                                Notification::make()
                                    ->title('Adjustment Failed')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
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
                ])
            ], position: RecordActionsPosition::BeforeColumns)
            ->heading('Stock Ledger')
            ->emptyStateHeading('No inventory records found')
            ->emptyStateDescription('Inventory items are created automatically when products with is_huenics_owned = true are added.')
            ->emptyStateIcon('heroicon-o-archive-box');
    }
}
