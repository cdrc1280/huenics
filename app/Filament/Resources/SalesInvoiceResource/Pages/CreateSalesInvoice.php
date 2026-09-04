<?php

namespace App\Filament\Resources\SalesInvoiceResource\Pages;

use App\Filament\Resources\SalesInvoiceResource;
use App\Models\PurchaseOrder;
use App\Models\SalesInvoice;
use Filament\Resources\Pages\CreateRecord;

class CreateSalesInvoice extends CreateRecord
{
    protected static string $resource = SalesInvoiceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['si_number'])) {
            $data['si_number'] = SalesInvoice::generateNumber();
        }

        if (!empty($data['purchase_order_id'])) {
            $po = PurchaseOrder::find($data['purchase_order_id']);
            if ($po) {
                if (empty($data['customer_name'])) {
                    $data['customer_name'] = $po->customer_name;
                }
                if (empty($data['business_style'])) {
                    $data['business_style'] = $po->customer_name;
                }
                if (empty($data['billing_address'])) {
                    $data['billing_address'] = $po->project?->location;
                }
            }
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var SalesInvoice $record */
        $record = $this->record;
        if ($record->purchase_order_id && $po = $record->purchaseOrder) {
            $allSiNumbers = $po->salesInvoices()->pluck('si_number')->filter()->unique()->implode(', ');
            $po->update([
                'sales_invoice_no' => $allSiNumbers,
            ]);
        }
    }
}
