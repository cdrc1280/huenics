<?php

namespace App\Notifications;

use App\Models\PurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DeliveryOverdueNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly PurchaseOrder $po) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $daysOverdue = (int) now()->diffInDays($this->po->expected_delivery_date);

        return [
            'title'        => '🚨 Delivery Overdue',
            'body'         => "PO {$this->po->po_number} for \"{$this->po->customer_name}\" was expected on {$this->po->expected_delivery_date->format('M d, Y')} ({$daysOverdue} day(s) overdue).",
            'action_url'   => '/admin/purchase-orders/' . $this->po->id,
            'action_text'  => 'View PO',
            'type'         => 'delivery_overdue',
            'po_id'        => $this->po->id,
            'po_number'    => $this->po->po_number,
            'days_overdue' => $daysOverdue,
        ];
    }
}
