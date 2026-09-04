<?php

namespace App\Filament\Resources\DeliveryReceiptResource\Pages;

use App\Filament\Resources\DeliveryReceiptResource;
use App\Models\DeliveryReceipt;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDeliveryReceipt extends EditRecord
{
    protected static string $resource = DeliveryReceiptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
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
