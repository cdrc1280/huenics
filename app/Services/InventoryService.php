<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\PoItemSelectedComponent;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryService
{
    /**
     * Add stock for a given product and log the addition to the Activity Log.
     */
    public function addStock(
        Product $product,
        float $quantity,
        string $type = 'purchase_in',
        string $notes = '',
        ?string $reference = null,
        ?User $user = null
    ): InventoryTransaction {
        $invItem = $product->inventoryItem ?? InventoryItem::firstOrCreate(
            ['product_id' => $product->id],
            [
                'quantity_on_hand' => 0,
                'quantity_reserved' => 0,
                'reorder_point' => 10,
                'unit' => $product->unit_default ?: 'pcs',
            ]
        );

        return $this->adjustStock(
            item: $invItem,
            quantity: $quantity,
            type: $type,
            notes: $notes,
            user: $user,
            referenceType: $reference ? 'reference' : null,
            referenceId: null
        );
    }

    /**
     * Adjust stock for an inventory item and log the transaction.
     */
    public function adjustStock(
        InventoryItem $item,
        float $quantity,
        string $type,
        string $notes,
        ?User $user = null,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): InventoryTransaction {
        return DB::transaction(function () use ($item, $quantity, $type, $notes, $user, $referenceType, $referenceId) {
            $oldStock = (float) $item->quantity_on_hand;
            $isAddition = in_array($type, ['initial_stock', 'purchase_in', 'adjustment_up', 'returned_items']);

            // Adjust quantity_on_hand based on transaction type
            if ($isAddition) {
                $item->increment('quantity_on_hand', $quantity);
            } elseif (in_array($type, ['component_deduct', 'sales_out', 'adjustment_down'])) {
                $item->decrement('quantity_on_hand', $quantity);
            }

            $userId = $user?->id ?? auth()->id() ?? User::first()?->id ?? 1;

            $transaction = InventoryTransaction::create([
                'inventory_item_id' => $item->id,
                'transaction_type'  => $type,
                'reference_type'    => $referenceType,
                'reference_id'      => $referenceId,
                'quantity'          => $quantity,
                'notes'             => $notes,
                'performed_by'      => $userId,
                'created_at'        => now(),
            ]);

            // Invalidate dashboard stats cache so stock changes reflect immediately
            Cache::forget('widget_inventory_alerts_stats');

            // Check and notify low stock
            $item->refresh();
            $newStock = (float) $item->quantity_on_hand;

            if ($item->reorder_point && $item->quantity_on_hand <= $item->reorder_point) {
                $this->triggerLowStockNotification($item);
            }

            // Record to immutable Activity Log & Audit Trail
            try {
                $product = $item->product ?: Product::find($item->product_id);
                $actor = $user ?? auth()->user() ?? User::find($userId);
                $unit = $item->unit ?: ($product?->unit_default ?: 'pcs');
                $prodName = $product?->canonical_name ?? "Product #{$item->product_id}";

                if ($isAddition) {
                    $logEvent = AuditLog::EVENT_STOCK_ADDED;
                    $logAction = 'stock_added';
                    $summary = "Added " . number_format($quantity, 2) . " {$unit} to stock for {$prodName} (Stock: " . number_format($oldStock, 2) . " → " . number_format($newStock, 2) . "). Notes: {$notes}";
                } else {
                    $logEvent = AuditLog::EVENT_STOCK_DEDUCTED;
                    $logAction = 'stock_deducted';
                    $summary = "Deducted " . number_format($quantity, 2) . " {$unit} from stock for {$prodName} (Stock: " . number_format($oldStock, 2) . " → " . number_format($newStock, 2) . "). Notes: {$notes}";
                }

                AuditLog::logActivity(
                    description: $summary,
                    auditable: $product ?? $item,
                    event: $logEvent,
                    oldValue: ['quantity_on_hand' => $oldStock],
                    newValue: [
                        'quantity_on_hand' => $newStock,
                        'quantity_changed' => $quantity,
                        'transaction_type' => $type,
                        'reference_type'   => $referenceType,
                        'reference_id'     => $referenceId,
                        'notes'            => $notes,
                    ],
                    properties: [
                        'product_id'        => $product?->id,
                        'product_code'      => $product?->product_code,
                        'sku'               => $product?->sku,
                        'unit'              => $unit,
                        'inventory_item_id' => $item->id,
                        'transaction_id'    => $transaction->id,
                    ],
                    user: $actor,
                    action: $logAction
                );
            } catch (\Throwable $ex) {
                Log::warning("Activity log failed for stock adjustment: " . $ex->getMessage());
            }

            return $transaction;
        });
    }

    /**
     * Automatically deduct inventory for all products and BOM components used in a Purchase Order.
     */
    public function deductPurchaseOrderStock(PurchaseOrder $po): void
    {
        if ($po->is_inventory_deducted || (bool) $po->fresh()?->is_inventory_deducted) {
            return; // Already deducted, prevent duplicate deduction
        }

        DB::transaction(function () use ($po) {
            $user = $po->salesAgent ?? User::where('role', User::ROLE_ADMIN)->first() ?? User::first() ?? User::factory()->create();

            $lineItems = $po->lineItems()->with(['product.inventoryItem', 'selectedComponents.component.componentProduct.inventoryItem'])->get();

            if ($lineItems->isEmpty()) {
                return;
            }

            foreach ($lineItems as $lineItem) {
                // 1. Deduct selected BOM components if any
                if ($lineItem->selectedComponents()->count() > 0) {
                    foreach ($lineItem->selectedComponents()->where('is_deducted_from_inventory', false)->get() as $selected) {
                        $component = $selected->component;
                        if (!$component || !$component->componentProduct) {
                            continue;
                        }

                        $compProduct = $component->componentProduct;
                        $invItem = $compProduct->inventoryItem ?? InventoryItem::firstOrCreate(
                            ['product_id' => $compProduct->id],
                            [
                                'quantity_on_hand' => 0,
                                'quantity_reserved' => 0,
                                'reorder_point' => 10,
                                'unit' => $compProduct->unit_default ?: 'pcs',
                            ]
                        );

                        try {
                            $this->adjustStock(
                                $invItem,
                                (float) $lineItem->qty,
                                'component_deduct',
                                "BOM deduction for PO {$po->po_number} — {$selected->selected_option_name}",
                                $user,
                                'purchase_order',
                                $po->id
                            );
                            $selected->update(['is_deducted_from_inventory' => true]);
                        } catch (\Throwable $e) {
                            Log::warning("BOM component deduction warning: " . $e->getMessage());
                        }
                    }
                }

                // 2. Deduct main product stock
                if ($lineItem->product_id) {
                    $product = $lineItem->product ?: Product::find($lineItem->product_id);
                    if ($product) {
                        $invItem = $product->inventoryItem ?? InventoryItem::firstOrCreate(
                            ['product_id' => $product->id],
                            [
                                'quantity_on_hand' => 0,
                                'quantity_reserved' => 0,
                                'reorder_point' => 10,
                                'unit' => $product->unit_default ?: ($lineItem->unit ?: 'pcs'),
                            ]
                        );

                        try {
                            $this->adjustStock(
                                $invItem,
                                (float) $lineItem->qty,
                                'sales_out',
                                "Sales deduction for PO {$po->po_number} ({$lineItem->description})",
                                $user,
                                'purchase_order',
                                $po->id
                            );
                        } catch (\Throwable $e) {
                            Log::warning("Product stock deduction warning: " . $e->getMessage());
                        }
                    }
                }
            }

            $po->is_inventory_deducted = true;
            $po->updateQuietly(['is_inventory_deducted' => true]);
            Cache::forget('widget_inventory_alerts_stats');
        });
    }

    /**
     * Alias for backward compatibility.
     */
    public function deductComponents(PurchaseOrder $po): void
    {
        $this->deductPurchaseOrderStock($po);
    }

    /**
     * Restore stock if a purchase order is cancelled or deleted.
     */
    public function restorePurchaseOrderStock(PurchaseOrder $po): void
    {
        if (!$po->is_inventory_deducted && !(bool) $po->fresh()?->is_inventory_deducted) {
            return;
        }

        DB::transaction(function () use ($po) {
            $user = $po->salesAgent ?? User::first() ?? User::factory()->create();

            $lineItems = $po->lineItems()->with(['product.inventoryItem', 'selectedComponents.component.componentProduct.inventoryItem'])->get();

            foreach ($lineItems as $lineItem) {
                if ($lineItem->product_id && $lineItem->product && $lineItem->product->inventoryItem) {
                    try {
                        $this->adjustStock(
                            $lineItem->product->inventoryItem,
                            (float) $lineItem->qty,
                            'adjustment_up',
                            "Stock reversal for cancelled/deleted PO {$po->po_number}",
                            $user,
                            'purchase_order',
                            $po->id
                        );
                    } catch (\Throwable $e) {
                        Log::warning("Stock restore warning: " . $e->getMessage());
                    }
                }
            }

            $po->is_inventory_deducted = false;
            $po->updateQuietly(['is_inventory_deducted' => false]);
            Cache::forget('widget_inventory_alerts_stats');
        });
    }

    /**
     * Return all inventory items below their reorder point.
     */
    public function getLowStockItems(): Collection
    {
        return InventoryItem::with('product')
            ->whereNotNull('reorder_point')
            ->whereColumn('quantity_on_hand', '<=', 'reorder_point')
            ->get();
    }

    protected function triggerLowStockNotification(InventoryItem $item): void
    {
        $admins = \App\Models\User::whereIn('role', [
            \App\Models\User::ROLE_ADMIN,
            \App\Models\User::ROLE_OPERATIONS_MANAGER,
        ])->get();

        foreach ($admins as $user) {
            $user->notify(new \App\Notifications\LowStockNotification($item));
        }
    }
}

