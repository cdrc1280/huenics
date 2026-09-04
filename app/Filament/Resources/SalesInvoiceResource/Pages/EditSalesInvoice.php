<?php

namespace App\Filament\Resources\SalesInvoiceResource\Pages;

use App\Filament\Resources\SalesInvoiceResource;
use App\Models\SalesInvoice;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSalesInvoice extends EditRecord
{
    protected static string $resource = SalesInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
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
