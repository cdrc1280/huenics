<?php

namespace App\Filament\Resources\QuotationResource\Pages;

use App\Filament\Resources\QuotationResource;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Services\QuotationService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditQuotation extends EditRecord
{
    protected static string $resource = QuotationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve')
                ->label('Approve Quotation')
                ->icon('heroicon-m-check-circle')
                ->color('success')
                ->tooltip('Approve quotation estimate')
                ->visible(fn(): bool => !$this->record->isApproved() && !$this->record->isConverted() && !$this->record->isRejected())
                ->requiresConfirmation()
                ->action(function () {
                    app(QuotationService::class)->approve($this->record);
                    $this->refreshFormData(['status', 'approved_by', 'approved_at']);
                    Notification::make()->title('Quotation Approved')->success()->send();
                }),

            Action::make('convert_to_po')
                ->label('Convert to PO')
                ->icon('heroicon-m-shopping-cart')
                ->color('primary')
                ->tooltip('Convert this quotation into an active Purchase Order')
                ->visible(fn(): bool => $this->record->isReadyForConversion() && !$this->record->isConverted())
                ->form([
                    DatePicker::make('order_date')
                        ->label('Order Date / PO Date')
                        ->required()
                        ->default(now()),

                    Textarea::make('notes')
                        ->label('PO Notes / Instructions')
                        ->nullable(),
                ])
                ->action(function (array $data) {
                    try {
                        $po = app(QuotationService::class)->convertToPO($this->record, $data);
                        Notification::make()
                            ->title('Converted to Purchase Order')
                            ->body("PO {$po->po_number} created successfully.")
                            ->success()
                            ->send();

                        return redirect(QuotationResource::getUrl('index'));
                    } catch (\Throwable $e) {
                        Notification::make()->title('Conversion Failed')->body($e->getMessage())->danger()->send();
                    }
                }),

            Action::make('export_pdf')
                ->label('Export PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->url(fn() => route('quotations.export-pdf', $this->record))
                ->openUrlInNewTab(),

            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
