<?php

namespace App\Filament\Resources\PurchaseOrderResource\Pages;

use App\Actions\IngestDocumentAction;
use App\Filament\Pages\ReviewQueuePage;
use App\Filament\Resources\PurchaseOrderResource;
use App\Models\Document;
use App\Models\Project;
use App\Models\Vendor;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListPurchaseOrders extends ListRecords
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('upload_po')
                ->label('Upload Purchase Order')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('warning')
                ->tooltip('Upload a customer Purchase Order or Order Slip PDF or image for dynamic template parsing and verification')
                ->modalHeading('Upload & Ingest Purchase Order')
                ->modalDescription('Upload a Purchase Order or Order Slip PDF or image (JPG, PNG, WEBP). The system will use dynamic templates and OCR to extract line items, prices, VAT, and check for arithmetic discrepancies.')
                ->form([
                    FileUpload::make('disk_path')
                        ->label('Purchase Order File (PDF or Image)')
                        ->required()
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                        ->disk('local')
                        ->directory('documents/uploads')
                        ->preserveFilenames()
                        ->storeFileNamesIn('original_filename'),

                    Select::make('document_type')
                        ->label('PO Sub-type')
                        ->options([
                            Document::TYPE_PURCHASE_ORDER => 'Purchase Order (Customer PO)',
                            Document::TYPE_ORDER_SLIP => 'Order Slip (Internal Order)',
                        ])
                        ->default(Document::TYPE_PURCHASE_ORDER)
                        ->required(),

                    Hidden::make('original_filename'),
                ])
                ->action(function (array $data) {
                    try {
                        $document = app(IngestDocumentAction::class)->execute(
                            diskPath: $data['disk_path'],
                            originalFilename: $data['original_filename'] ?? basename($data['disk_path']),
                            documentType: $data['document_type'] ?? Document::TYPE_PURCHASE_ORDER,
                            vendorId: null,
                            projectId: null
                        );

                        Notification::make()
                            ->title('Purchase Order Ingested & Extracted')
                            ->body("Extracted line items and arithmetic checks completed.")
                            ->success()
                            ->send();

                        $this->redirect(ReviewQueuePage::getUrl(['document_id' => $document->id]));
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Upload Notice')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\CreateAction::make()->label('New Purchase Order'),
        ];
    }
}
