<?php

namespace App\Filament\Resources\PurchaseOrderResource\Pages;

use App\Actions\IngestDocumentAction;
use App\Filament\Pages\ReviewQueuePage;
use App\Filament\Resources\PurchaseOrderResource;
use App\Models\Document;
use App\Models\Project;
use App\Models\Quotation;
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
                ->tooltip('Upload a customer Purchase Order PDF or image for dynamic template parsing and verification')
                ->modalHeading('Upload & Ingest Purchase Order')
                ->modalDescription('Upload a Purchase Order PDF or image (JPG, PNG, WEBP). You can optionally select an approved quotation to link this PO to.')
                ->form([
                    FileUpload::make('disk_path')
                        ->label('Purchase Order File (PDF or Image)')
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

                    Toggle::make('is_conforme_po')
                        ->label('Conforme PO (Exempt from Quotation Matching)')
                        ->helperText('Check if this is a signed conforme purchase order that does not require a matching quotation.')
                        ->live(),

                    Select::make('quotation_id')
                        ->label('Link to Approved Quotation')
                        ->options(function () {
                            return Quotation::whereDoesntHave('purchaseOrders')
                                ->where('status', Quotation::STATUS_APPROVED)
                                ->get()
                                ->mapWithKeys(fn (Quotation $q) => [
                                    $q->id => "{$q->quotation_number} - {$q->customer_name} (" . ($q->project?->name ?? $q->project_name ?? 'No Project') . ") - ₱" . number_format((float) $q->total_amount, 2)
                                ]);
                        })
                        ->searchable()
                        ->nullable()
                        ->visible(fn($get) => !(bool) $get('is_conforme_po'))
                        ->placeholder('Select an approved quotation to link, or leave blank')
                        ->helperText('Optional: Select an approved quotation without an existing PO to automatically link and convert.'),

                    Hidden::make('original_filename'),
                ])
                ->action(function (array $data) {
                    try {
                        $document = app(IngestDocumentAction::class)->execute(
                            diskPath: $data['disk_path'],
                            originalFilename: $data['original_filename'] ?? basename($data['disk_path']),
                            documentType: Document::TYPE_PURCHASE_ORDER,
                            vendorId: null,
                            projectId: null,
                            userId: auth()->id(),
                            quotationId: !empty($data['quotation_id']) ? (int) $data['quotation_id'] : null,
                            isConformePo: (bool) ($data['is_conforme_po'] ?? false)
                        );

                        Notification::make()
                            ->title('Purchase Order Uploaded & Added to List')
                            ->body("Purchase Order extracted and added with status 'For Review'. Click 'Review & Verify' to inspect.")
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
        ];
    }
}
