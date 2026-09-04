<?php

namespace App\Filament\Resources\DeliveryReceiptResource\Pages;

use App\Filament\Resources\DeliveryReceiptResource;
use App\Models\DeliveryReceipt;
use App\Models\PurchaseOrder;
use Filament\Resources\Pages\CreateRecord;

class CreateDeliveryReceipt extends CreateRecord
{
    protected static string $resource = DeliveryReceiptResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['dr_number'])) {
            $data['dr_number'] = DeliveryReceipt::generateNumber();
        }

        if (!empty($data['purchase_order_id'])) {
            $po = PurchaseOrder::find($data['purchase_order_id']);
            if ($po) {
                if (empty($data['customer_name'])) {
                    $data['customer_name'] = $po->customer_name;
                }
                if (empty($data['delivery_address'])) {
                    $data['delivery_address'] = $po->project?->location;
                }
                if (empty($data['project_name'])) {
                    $data['project_name'] = $po->project?->name;
                }
            }
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var DeliveryReceipt $record */
        $record = $this->record;
        if ($record->purchase_order_id && $po = $record->purchaseOrder) {
            $allDrNumbers = $po->deliveryReceipts()->pluck('dr_number')->filter()->unique()->implode(', ');
            $po->update([
                'delivery_receipt_no'  => $allDrNumbers,
                'actual_delivery_date' => $record->delivery_date ?? $po->actual_delivery_date ?? now(),
            ]);
        }
    }
}
