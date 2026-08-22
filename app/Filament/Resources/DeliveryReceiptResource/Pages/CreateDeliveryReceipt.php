<?php

namespace App\Filament\Resources\DeliveryReceiptResource\Pages;

use App\Filament\Resources\DeliveryReceiptResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDeliveryReceipt extends CreateRecord
{
    protected static string $resource = DeliveryReceiptResource::class;

    public function mount(): void
    {
        parent::mount();

        $poId = request()->query('purchase_order_id');
        if ($poId) {
            $po = \App\Models\PurchaseOrder::with('lineItems.product')->find($poId);
            if ($po) {
                $items = $po->lineItems->map(fn($line) => [
                    'product_id' => $line->product_id,
                    'qty_delivered' => (float) $line->qty,
                    'unit' => $line->unit ?: 'pcs',
                    'remarks' => $line->description,
                ])->toArray();

                $this->form->fill([
                    'purchase_order_id' => $po->id,
                    'delivery_date' => now()->toDateString(),
                    'status' => \App\Enums\DeliveryReceiptStatus::Draft->value,
                    'items' => $items,
                ]);
            }
        }
    }
}
