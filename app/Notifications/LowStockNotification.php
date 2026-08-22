<?php

namespace App\Notifications;

use App\Models\InventoryItem;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly InventoryItem $item) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $product = $this->item->product;

        return [
            'title'       => '⚠️ Low Stock Alert',
            'body'        => "\"{$product->canonical_name}\" is running low. Current stock: {$this->item->quantity_on_hand} {$this->item->unit} (Reorder at: {$this->item->reorder_point})",
            'action_url'  => '/admin/inventory-dashboard',
            'action_text' => 'View Inventory',
            'type'        => 'low_stock',
            'product_id'  => $product->id,
            'quantity'    => (float) $this->item->quantity_on_hand,
            'reorder'     => (float) $this->item->reorder_point,
        ];
    }
}
s