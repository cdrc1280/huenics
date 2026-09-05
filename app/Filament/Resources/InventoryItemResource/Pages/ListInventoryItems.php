<?php

namespace App\Filament\Resources\InventoryItemResource\Pages;

use App\Filament\Resources\InventoryItemResource;
use App\Services\InventoryReportService;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;

class ListInventoryItems extends ListRecords
{
    protected static string $resource = InventoryItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Stock Record'),

            Actions\Action::make('sync_bom_to_inventory')
                ->label('Sync BOM Parts to Inventory')
                ->icon('heroicon-o-puzzle-piece')
                ->color('warning')
                ->tooltip('Ensure all Bill of Materials (BOM) child components are registered in the warehouse inventory ledger')
                ->requiresConfirmation()
                ->modalHeading('Synchronize Sub-Components to Inventory')
                ->modalDescription('This will scan all sub-components defined across product BOMs and guarantee an inventory stock record exists for every catalogued part.')
                ->action(function (): void {
                    $components = \App\Models\ProductComponent::whereNotNull('component_product_id')->get();
                    $created = 0;
                    foreach ($components as $comp) {
                        $exists = \App\Models\InventoryItem::where('product_id', $comp->component_product_id)->exists();
                        if (!$exists) {
                            \App\Models\InventoryItem::create([
                                'product_id' => $comp->component_product_id,
                                'quantity_on_hand' => 0,
                                'reorder_point' => 10,
                                'unit' => $comp->unit ?: 'pcs',
                                'is_owned' => true,
                                'location' => 'BOM Parts Warehouse',
                                'remarks' => 'Auto-synced from Product BOM',
                            ]);
                            $created++;
                        }
                    }

                    \Filament\Notifications\Notification::make()
                        ->title('BOM Parts Synchronized')
                        ->body("Checked {$components->count()} sub-components. Created {$created} new inventory stock records.")
                        ->success()
                        ->send();
                }),

            Actions\Action::make('download_template')
                ->label('Download Template')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->url(route('inventory.download-template'))
                ->openUrlInNewTab(false)
                ->tooltip('Download sample Inventory Report CSV template matching the reference ledger format'),

            Actions\Action::make('export_csv')
                ->label('Export Inventory Report')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(route('inventory.export-report'))
                ->openUrlInNewTab(false)
                ->tooltip('Export the entire inventory ledger into CSV matching the official reference format'),

            Actions\Action::make('import_csv')
                ->label('Import Inventory Report')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->modalHeading('Import Inventory Report from CSV / Excel')
                ->modalDescription('Upload an Inventory Report CSV file (matching Date, P.O. Nos., Suppliers Name, S.K.U., Item Code, Particulars, Transit In/Out, Balance, Location, Customer, Project, Remarks).')
                ->modalSubmitActionLabel('Run Import')
                ->form([
                    FileUpload::make('csv_file')
                        ->label('Inventory CSV File')
                        ->disk('local')
                        ->directory('temp-imports')
                        ->acceptedFileTypes(['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel', 'text/x-csv'])
                        ->required()
                        ->helperText('Upload CSV report. Products and stock balances will be mapped automatically.'),

                    Toggle::make('update_existing')
                        ->label('Update Existing Items')
                        ->helperText('If enabled, existing stock records matching SKU/Item Code will have their balance, location, and latest dispatch details updated.')
                        ->default(true),
                ])
                ->action(function (array $data): void {
                    $disk = Storage::disk('local');
                    $filePath = $disk->path($data['csv_file']);
                    $updateExisting = (bool) ($data['update_existing'] ?? true);

                    try {
                        $service = app(InventoryReportService::class);
                        $result = $service->importInventoryReport($filePath, $updateExisting);

                        $msg = "Imported {$result['imported']} new item(s), updated {$result['updated']} existing item(s).";
                        if (!empty($result['errors'])) {
                            $msg .= " (" . count($result['errors']) . " row(s) had notes or warnings)";
                        }

                        Notification::make()
                            ->title('Inventory Import Completed')
                            ->body($msg)
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Inventory Import Failed')
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
