<?php

namespace App\Filament\Resources\PurchaseOrderResource\Pages;

use App\Filament\Resources\PurchaseOrderResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreatePurchaseOrder extends CreateRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Auto-compute VAT and profit
        $data['computed_vat']    = round((float) ($data['order_amount'] ?? 0) / 1.12 * 0.12, 2);
        $data['realized_profit'] = round((float) ($data['order_amount'] ?? 0) - (float) ($data['total_cost'] ?? 0), 2);
        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->record->quotation_id && $quotation = \App\Models\Quotation::find($this->record->quotation_id)) {
            $quotation->update(['status' => \App\Models\Quotation::STATUS_CONVERTED]);
        }

        Notification::make()
            ->title('Purchase Order Created')
            ->body("PO #{$this->record->po_number} has been created.")
            ->success()
            ->send();
    }
}
