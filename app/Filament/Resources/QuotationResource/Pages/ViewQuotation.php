<?php

namespace App\Filament\Resources\QuotationResource\Pages;

use App\Filament\Resources\PurchaseOrderResource;
use App\Filament\Resources\QuotationResource;
use App\Models\Quotation;
use App\Services\QuotationService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewQuotation extends ViewRecord
{
    protected static string $resource = QuotationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('convert_to_po')
                ->label('Convert to PO')
                ->icon('heroicon-m-shopping-cart')
                ->color('primary')
                ->tooltip('Convert this approved quotation into an active Purchase Order')
                ->visible(fn(): bool => !$this->record->trashed() && $this->record->isReadyForConversion() && !$this->record->isConverted())
                ->modalHeading('Convert Quotation to Purchase Order')
                ->modalDescription('Are you sure you want to convert this quotation into an active Purchase Order? All line items, pricing, and project details will be transferred.')
                ->modalSubmitActionLabel('Convert to PO')
                ->form([
                    Textarea::make('notes')
                        ->label('PO Notes / Instructions (Optional)')
                        ->placeholder('Enter any instructions, remarks, or reference notes...')
                        ->rows(3)
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

                        return redirect(PurchaseOrderResource::getUrl('index'));
                    } catch (\Throwable $e) {
                        Notification::make()->title('Conversion Failed')->body($e->getMessage())->danger()->send();
                    }
                }),

            Action::make('export_pdf')
                ->label('Export PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->tooltip('Download Quotation PDF with e-signatures')
                ->visible(fn(): bool => !$this->record->trashed())
                ->url(fn() => route('quotations.export-pdf', $this->record))
                ->openUrlInNewTab(),

            Action::make('preview_pdf')
                ->label('Preview PDF')
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->tooltip('Preview Quotation PDF in browser')
                ->visible(fn(): bool => !$this->record->trashed())
                ->url(fn() => route('quotations.preview-pdf', $this->record))
                ->openUrlInNewTab(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Quotation Details')
                ->columns(3)
                ->schema([
                    TextEntry::make('quotation_number')->label('Quotation #')->weight('bold'),
                    TextEntry::make('customer_name')->label('Customer Name'),
                    TextEntry::make('customer_company')->label('Company')->default('—'),
                    TextEntry::make('salesAgent.name')->label('Sales Agent')->default('Unassigned'),
                    TextEntry::make('project.name')->label('Project Site')->default(fn(Quotation $r) => $r->project_name ?? '—'),
                    TextEntry::make('phone_no')->label('Contact No.')->default('—'),
                    TextEntry::make('quotation_date')->label('Quotation Date')->date('M j, Y'),
                    TextEntry::make('status')->label('Status')->badge()
                        ->color(fn(string $state) => match ($state) {
                            Quotation::STATUS_APPROVED => 'success',
                            Quotation::STATUS_CONVERTED => 'info',
                            Quotation::STATUS_REJECTED => 'danger',
                            default => 'warning',
                        }),
                ]),

            Section::make('Financial Summary')
                ->columns(3)
                ->schema([
                    TextEntry::make('total_amount')->label('Total Amount')->money('PHP'),
                    TextEntry::make('negotiated_amount')->label('Negotiated Amount')->money('PHP')->placeholder('—'),
                    TextEntry::make('estimated_profit')->label('Estimated Profit')->money('PHP')
                        ->color(fn($state) => $state > 0 ? 'success' : 'danger'),
                ]),

            Section::make('Terms, Payment & Delivery')
                ->columns(3)
                ->schema([
                    TextEntry::make('payment_terms')->label('Payment Terms')->default('—'),
                    TextEntry::make('delivery_terms')->label('Delivery Terms')->default('—'),
                    TextEntry::make('terms_and_conditions')->label('Terms & Conditions')->columnSpanFull()->default('—'),
                ]),

            Section::make('Line Items')
                ->schema([
                    RepeatableEntry::make('lineItems')
                        ->label('')
                        ->columns(6)
                        ->schema([
                            TextEntry::make('line_no')->label('#')->columnSpan(1),
                            TextEntry::make('item_code')->label('Item Code')->default('—')->columnSpan(1),
                            TextEntry::make('description')->label('Product / Description')->columnSpan(2),
                            TextEntry::make('qty')->label('Qty')->columnSpan(1),
                            TextEntry::make('unit')->label('Unit')->columnSpan(1),
                            TextEntry::make('unit_price')->label('Unit Price')->money('PHP')->columnSpan(1),
                            TextEntry::make('discounted_price')->label('Discounted Price')->money('PHP')->placeholder('—')->columnSpan(1),
                            TextEntry::make('line_total')->label('Total')->money('PHP')->columnSpan(1),
                        ]),
                ]),
        ]);
    }
}
