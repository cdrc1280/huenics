<?php

namespace App\Filament\Resources\DeliveryReceiptResource\Pages;

use App\Filament\Resources\DeliveryReceiptResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDeliveryReceipts extends ListRecords
{
    protected static string $resource = DeliveryReceiptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
