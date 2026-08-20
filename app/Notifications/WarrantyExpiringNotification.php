<?php

namespace App\Notifications;

use App\Models\PurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WarrantyExpiringNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly PurchaseOrder $po) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $daysLeft = (int) now()->diffInDays($this->po->warranty_end_date, false);

        return [
            'title'       => '🔔 Warranty Expiring Soon',
            'body'        => "PO {$this->po->po_number} for \"{$this->po->customer_name}\" expires in {$daysLeft} day(s) on {$this->po->warranty_end_date->format('M d, Y')}. Period: {$this->po->warranty_period_label}.",
            'action_url'  => '/admin/purchase-orders/' . $this->po->id,
            'action_text' => 'View PO',
            'type'        => 'warranty_expiring',
            'po_id'       => $this->po->id,
            'po_number'   => $this->po->po_number,
            'days_left'   => $daysLeft,
            'expires_on'  => $this->po->warranty_end_date->toDateString(),
        ];
    }
}
