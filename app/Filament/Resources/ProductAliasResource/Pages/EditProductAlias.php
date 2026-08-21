<?php

namespace App\Filament\Resources\ProductAliasResource\Pages;

use App\Filament\Resources\ProductAliasResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductAlias extends EditRecord
{
    protected static string $resource = ProductAliasResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
