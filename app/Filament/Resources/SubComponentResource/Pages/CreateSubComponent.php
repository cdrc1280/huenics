<?php

namespace App\Filament\Resources\SubComponentResource\Pages;

use App\Filament\Resources\SubComponentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSubComponent extends CreateRecord
{
    protected static string $resource = SubComponentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['parent_product_id'] = null;
        $data['quantity'] = 1.0000;
        $data['component_group'] = $data['category'] ?: ($data['component_group'] ?? 'General');
        $data['option_name'] = $data['component_name'] ?: ($data['option_name'] ?? 'Part');
        $data['additional_cost'] = $data['cost_price'] ?? 0.00;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
