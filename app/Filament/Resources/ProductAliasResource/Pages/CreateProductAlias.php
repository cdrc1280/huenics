<?php

namespace App\Filament\Resources\ProductAliasResource\Pages;

use App\Filament\Resources\ProductAliasResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProductAlias extends CreateRecord
{
    protected static string $resource = ProductAliasResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
