<?php

namespace App\Filament\Resources\SubComponentResource\Pages;

use App\Filament\Resources\SubComponentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSubComponents extends ListRecords
{
    protected static string $resource = SubComponentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add Single Sub-Component')
                ->icon('heroicon-o-plus'),
        ];
    }
}
