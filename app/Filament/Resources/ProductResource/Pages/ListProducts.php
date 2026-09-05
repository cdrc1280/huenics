<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Services\ProductImportExportService;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Add Product'),

            Actions\Action::make('download_template')
                ->label('Download Template')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->url(route('products.download-template-excel'))
                ->openUrlInNewTab(false)
                ->tooltip('Download sample Excel (.xlsx) import template matching PRICELIST structure'),

            Actions\Action::make('export_csv')
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(route('products.export-excel'))
                ->openUrlInNewTab(false)
                ->tooltip('Export entire products catalog to Excel (.xlsx) format'),

            Actions\Action::make('import_csv')
                ->label('Import Excel / CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->modalHeading('Import Products Catalog (Excel / CSV)')
                ->modalDescription('Upload an Excel (.xlsx, .xls) or CSV (.csv) file containing product records matching the PRICELIST structure. Existing products can be updated automatically.')
                ->modalSubmitActionLabel('Start Import')
                ->form([
                    FileUpload::make('file')
                        ->label('Product Catalog File (.xlsx, .xls, .csv)')
                        ->disk('local')
                        ->directory('temp-imports')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                            'application/octet-stream',
                            'text/csv',
                            'text/plain',
                            'application/csv',
                            'text/x-csv',
                        ])
                        ->required()
                        ->helperText('Upload an Excel (.xlsx, .xls) or CSV (.csv) file matching the PRICELIST columns (CODE, WATTAGE, DESCRIPTION, VOLTAGE, COLOR, PRICE, UNIT).'),

                    Toggle::make('update_existing')
                        ->label('Update Existing Products')
                        ->helperText('When enabled, existing products matching Code or Name will be updated with the latest specifications and prices.')
                        ->default(true),
                ])
                ->action(function (array $data): void {
                    $disk = Storage::disk('local');
                    $fileKey = $data['file'] ?? $data['csv_file'] ?? null;
                    if (!$fileKey) {
                        return;
                    }
                    $filePath = $disk->path($fileKey);
                    $updateExisting = (bool) ($data['update_existing'] ?? true);

                    try {
                        $service = app(ProductImportExportService::class);
                        $result = $service->importFile($filePath, $updateExisting);

                        $msg = "Imported {$result['imported']} new product(s), updated {$result['updated']} product(s).";
                        if (!empty($result['errors'])) {
                            $msg .= " (" . count($result['errors']) . " row(s) had notes or warnings)";
                        }

                        Notification::make()
                            ->title('Catalog Import Completed')
                            ->body($msg)
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Import Failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    } finally {
                        if (file_exists($filePath)) {
                            @unlink($filePath);
                        }
                    }
                }),
        ];
    }
}
