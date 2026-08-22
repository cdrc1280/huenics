<?php

namespace App\Filament\Resources\QuotationResource\Pages;

use App\Actions\IngestDocumentAction;
use App\Filament\Pages\ReviewQueuePage;
use App\Filament\Resources\QuotationResource;
use App\Models\Document;
use App\Models\Project;
use App\Models\Vendor;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListQuotations extends ListRecords
{
    protected static string $resource = QuotationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('upload_quotation')
                ->label('Upload Quotation')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('warning')
                ->tooltip('Upload a vendor or customer quotation PDF or image for dynamic template parsing and verification')
                ->modalHeading('Upload & Ingest Quotation')
                ->modalDescription('Upload a quotation PDF or image (JPG, PNG, WEBP). The system will use dynamic templates and OCR to extract line items, prices, and arithmetic.')
                ->form([
                    FileUpload::make('disk_path')
                        ->label('Quotation File (PDF or Image)')
                        ->required()
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                        ->maxSize(25600)
                        ->maxFiles(1)
                        ->rules(['file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:25600'])
                        ->helperText('Supported formats: PDF, JPG, PNG, WEBP. Maximum file size: 25 MB.')
                        ->disk('local')
                        ->directory('documents/uploads')
                        ->preserveFilenames()
                        ->storeFileNamesIn('original_filename'),

                    Hidden::make('original_filename'),
                ])
                ->action(function (array $data) {
                    try {
                        $document = app(IngestDocumentAction::class)->execute(
                            diskPath: $data['disk_path'],
                            originalFilename: $data['original_filename'] ?? basename($data['disk_path']),
                            documentType: Document::TYPE_VENDORS_AGREEMENT,
                            vendorId: null,
                            projectId: null
                        );

                        Notification::make()
                            ->title('Quotation Uploaded & Added to List')
                            ->body("Quotation extracted and added with status 'For Review'. Click 'Review & Verify' to inspect.")
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Storage::disk('local')->delete($data['disk_path']);

                        Notification::make()
                            ->title('Upload Rejected')
                            ->body($e->getMessage())
                            ->danger()
                            ->duration(10000)
                            ->send();
                    }
                }),

            Actions\CreateAction::make()->label('New Quotation'),
        ];
    }
}
