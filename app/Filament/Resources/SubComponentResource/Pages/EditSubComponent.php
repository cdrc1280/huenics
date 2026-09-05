<?php

namespace App\Filament\Resources\SubComponentResource\Pages;

use App\Filament\Resources\SubComponentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSubComponent extends EditRecord
{
    protected static string $resource = SubComponentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->requiresConfirmation(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['component_group'] = $data['category'] ?: ($data['component_group'] ?? 'General');
        $data['option_name'] = $data['component_name'] ?: ($data['option_name'] ?? 'Part');
        $data['additional_cost'] = $data['cost_price'] ?? 0.00;
        return $data;
    }
}
