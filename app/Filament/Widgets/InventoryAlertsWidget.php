<?php

namespace App\Filament\Widgets;

use App\Models\InventoryItem;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InventoryAlertsWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        $stats = \Illuminate\Support\Facades\Cache::remember('widget_inventory_alerts_stats', 60, function () {
            $totalSkus = InventoryItem::count();

            $lowStock = InventoryItem::whereNotNull('reorder_point')
                ->whereColumn('quantity_on_hand', '<=', 'reorder_point')
                ->count();

            $totalValue = (float) InventoryItem::with('product')
                ->get()
                ->sum(fn ($item) => (float) $item->quantity_on_hand * (float) ($item->product?->base_cost_price ?? 0));

            $zeroStock = InventoryItem::where('quantity_on_hand', '<=', 0)->count();

            return [
                'total_skus'  => $totalSkus,
                'low_stock'   => $lowStock,
                'total_value' => $totalValue,
                'zero_stock'  => $zeroStock,
            ];
        });

        return [
            Stat::make('Total SKUs in Stock', $stats['total_skus'])
                ->description('Huenics-owned products')
                ->descriptionIcon('heroicon-m-cube')
                ->color('info')
                ->extraAttributes(['title' => 'Total catalog products tracked for in-house inventory']),

            Stat::make('Low Stock Items', $stats['low_stock'])
                ->description('At or below reorder point')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($stats['low_stock'] > 0 ? 'warning' : 'success')
                ->extraAttributes(['title' => 'Products with stock on hand at or below defined minimum reorder threshold']),

            Stat::make('Zero Stock Items', $stats['zero_stock'])
                ->description('Needs immediate restock')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color($stats['zero_stock'] > 0 ? 'danger' : 'success')
                ->extraAttributes(['title' => 'Out-of-stock items requiring replenishment']),

            Stat::make('Inventory Value', '₱' . number_format($stats['total_value'], 2))
                ->description('Estimated at base cost')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary')
                ->extraAttributes(['title' => 'Total monetary valuation of current stock on hand calculated at product base cost']),
        ];
    }

}
