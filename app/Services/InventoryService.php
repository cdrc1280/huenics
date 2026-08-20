<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\PoItemSelectedComponent;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Adjust stock for an inventory item and log the transaction.
     */
    public function adjustStock(
        InventoryItem $item,
        float $quantity,
        string $type,
        string $notes,
        User $user,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): InventoryTransaction {
        return DB::transaction(function () use ($item, $quantity, $type, $notes, $user, $referenceType, $referenceId) {
            // Adjust quantity_on_hand based on type
            if (in_array($type, ['initial_stock', 'purchase_in', 'adjustment_up'])) {
                $item->increment('quantity_on_hand', $quantity);
            } elseif (in_array($type, ['component_deduct', 'sales_out', 'adjustment_down'])) {
                if ($item->quantity_on_hand < $quantity) {
                    throw new \RuntimeException(
                        "Insufficient stock for [{$item->product->canonical_name}]. Available: {$item->quantity_on_hand}"
                    );
                }
                $item->decrement('quantity_on_hand', $quantity);
            }

            $transaction = InventoryTransaction::create([
                'inventory_item_id' => $item->id,
                'transaction_type'  => $type,
                'reference_type'    => $referenceType,
                'reference_id'      => $referenceId,
                'quantity'          => $quantity,
                'notes'             => $notes,
                'performed_by'      => $user->id,
            ]);

            // Check and notify low stock
            $item->refresh();
            if ($item->reorder_point && $item->quantity_on_hand <= $item->reorder_point) {
                $this->triggerLowStockNotification($item);
            }

            return $transaction;
        });
    }

    /**
     * Deduct Huenics-owned inventory components for all line items in a PO.
     */
    public function deductComponents(PurchaseOrder $po): void
    {
        DB::transaction(function () use ($po) {
            $user = $po->salesAgent ?? \App\Models\User::first();

            foreach ($po->lineItems()->with('selectedComponents.component.componentProduct.inventoryItem')->get() as $lineItem) {
                // Deduct selected BOM components
                foreach ($lineItem->selectedComponents()->where('is_deducted_from_inventory', false)->get() as $selected) {
                    $component = $selected->component;
                    if (!$component || !$component->componentProduct) {
                        continue;
                    }

                    $product = $component->componentProduct;
                    if (!$product->is_huenics_owned) {
                        continue; // Only deduct Huenics-owned items
                    }

                    $invItem = $product->inventoryItem;
                    if (!$invItem) {
                        continue; // No inventory record
                    }

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
                    } catch (\RuntimeException $e) {
                        \Illuminate\Support\Facades\Log::warning("Inventory deduction skipped: " . $e->getMessage());
                    }
                }

                // Also deduct the main product if Huenics-owned and no BOM
                if ($lineItem->product && $lineItem->product->is_huenics_owned && !$lineItem->product->is_composite) {
                    $invItem = $lineItem->product->inventoryItem;
                    if ($invItem) {
                        try {
                            $this->adjustStock(
                                $invItem,
                                (float) $lineItem->qty,
                                'sales_out',
                                "Sales deduction for PO {$po->po_number}",
                                $user,
                                'purchase_order',
                                $po->id
                            );
                        } catch (\RuntimeException $e) {
                            \Illuminate\Support\Facades\Log::warning("Inventory deduction skipped: " . $e->getMessage());
                        }
                    }
                }
            }
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
