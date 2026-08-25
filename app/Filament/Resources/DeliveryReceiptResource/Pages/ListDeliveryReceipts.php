<?php

namespace App\Filament\Resources\DeliveryReceiptResource\Pages;

use App\Filament\Pages\UploadFulfillmentDocumentsPage;
use App\Filament\Resources\DeliveryReceiptResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListDeliveryReceipts extends ListRecords
{
    protected static string $resource = DeliveryReceiptResource::class;

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
