<?php

namespace App\Filament\Resources\DocumentResource\Pages;

use App\Actions\ReconcileDocumentTotals;
use App\Filament\Resources\DocumentResource;
use App\Models\Document;
use App\Services\DocumentParsers\DynamicDocumentParser;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListDocuments extends ListRecords
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Upload New PDF')
                ->icon('heroicon-o-arrow-up-tray'),
        ];
    }
}
