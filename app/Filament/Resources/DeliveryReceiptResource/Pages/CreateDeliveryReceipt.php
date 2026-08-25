<?php

namespace App\Filament\Resources\DeliveryReceiptResource\Pages;

use App\Filament\Pages\UploadFulfillmentDocumentsPage;
use App\Filament\Resources\DeliveryReceiptResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDeliveryReceipt extends CreateRecord
{
    protected static string $resource = DeliveryReceiptResource::class;

    public function mount(): void
    {
        $poId = request()->query('purchase_order_id');
        $params = $poId ? ['purchase_order_id' => $poId] : [];
        $this->redirect(UploadFulfillmentDocumentsPage::getUrl($params));
    }
}
