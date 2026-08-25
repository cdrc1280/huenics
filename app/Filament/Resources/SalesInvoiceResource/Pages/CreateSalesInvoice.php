<?php

namespace App\Filament\Resources\SalesInvoiceResource\Pages;

use App\Filament\Pages\UploadFulfillmentDocumentsPage;
use App\Filament\Resources\SalesInvoiceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSalesInvoice extends CreateRecord
{
    protected static string $resource = SalesInvoiceResource::class;

    public function mount(): void
    {
        $poId = request()->query('purchase_order_id');
        $params = $poId ? ['purchase_order_id' => $poId] : [];
        $this->redirect(UploadFulfillmentDocumentsPage::getUrl($params));
    }
}
