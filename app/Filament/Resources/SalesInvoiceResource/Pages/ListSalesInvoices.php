<?php

namespace App\Filament\Resources\SalesInvoiceResource\Pages;

use App\Filament\Pages\UploadFulfillmentDocumentsPage;
use App\Filament\Resources\SalesInvoiceResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListSalesInvoices extends ListRecords
{
    protected static string $resource = SalesInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('upload_dr_si')
                ->label('Upload DR & SI (Attach Hard Copy)')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->url(UploadFulfillmentDocumentsPage::getUrl()),
        ];
    }
}
