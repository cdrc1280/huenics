<?php

declare(strict_types=1);

namespace App\Filament\Resources\RequestedQuotationResource\Pages;

use App\Filament\Resources\RequestedQuotationResource;
use Filament\Resources\Pages\ViewRecord;

class ViewRequestedQuotation extends ViewRecord
{
    protected static string $resource = RequestedQuotationResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
