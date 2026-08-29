<?php

namespace App\Filament\Resources\DocumentResource\Pages;

use App\Actions\IngestDocumentAction;
use App\Filament\Pages\ReviewQueuePage;
use App\Filament\Resources\DocumentResource;
use App\Models\Document;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateDocument extends CreateRecord
{
    protected static string $resource = DocumentResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        try {
            $record = app(IngestDocumentAction::class)->execute(
                diskPath: $data['disk_path'],
                originalFilename: $data['original_filename'] ?? basename($data['disk_path']),
                documentType: $data['document_type'] ?? Document::TYPE_PURCHASE_ORDER,
                vendorId: !empty($data['vendor_id']) ? (int) $data['vendor_id'] : null,
                projectId: !empty($data['project_id']) ? (int) $data['project_id'] : null,
                userId: auth()->id() ?: 1,
                quotationId: null,
                isConformePo: (bool) ($data['is_conforme_po'] ?? false)
            );

            if (!empty($record->is_duplicate)) {
                $docRef = $record->document_number ? " (Reference: {$record->document_number})" : '';
                Notification::make()
                    ->title('Duplicate Document Detected')
                    ->body("This file has already been ingested previously as '{$record->original_filename}'{$docRef}. Redirecting to the existing document.")
                    ->warning()
                    ->duration(8000)
                    ->send();
            } else {
                Notification::make()
                    ->title('PDF Ingested & Extracted')
                    ->body("Extracted line items and arithmetic checks completed.")
                    ->success()
                    ->send();
            }

            return $record;
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Ingestion Notice')
                ->body($e->getMessage())
                ->warning()
                ->send();

            $this->halt();
        }
    }

    protected function getRedirectUrl(): string
    {
        return ReviewQueuePage::getUrl(['document_id' => $this->record->id]);
    }
}
