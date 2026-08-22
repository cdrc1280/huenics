<?php

namespace App\Filament\Resources\PurchaseOrderResource\Pages;

use App\Filament\Pages\DeliveryMonitoringPage;
use App\Filament\Resources\PurchaseOrderResource;
use App\Models\PurchaseOrder;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPurchaseOrder extends EditRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve_po')
                ->label('Approve PO')
                ->icon('heroicon-m-check-circle')
                ->color('success')
                ->tooltip('Approve purchase order to authorize fulfillment and delivery')
                ->visible(fn(): bool => !$this->record->trashed() && !$this->record->isApproved() && $this->record->status !== PurchaseOrder::STATUS_CANCELLED && $this->record->status !== PurchaseOrder::STATUS_REJECTED)
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update(['status' => PurchaseOrder::STATUS_APPROVED]);
                    if ($this->record->document) {
                        $this->record->document->update(['status' => \App\Models\Document::STATUS_VERIFIED]);
                    }
                    $this->refreshFormData(['status']);
                    Notification::make()->title('Purchase Order Approved')->body("PO {$this->record->po_number} is now approved and verified for delivery.")->success()->send();
                }),

            Action::make('mark_delivered')
                ->label('Mark Delivered')
                ->icon('heroicon-m-check-badge')
                ->color('success')
                ->tooltip('Mark this purchase order as delivered')
                ->visible(fn(): bool => !$this->record->trashed() && $this->record->isApproved() && $this->record->delivery_status !== PurchaseOrder::DELIVERY_DELIVERED && $this->record->status !== PurchaseOrder::STATUS_DELIVERED)
                ->form([
                    DatePicker::make('actual_delivery_date')
                        ->label('Actual Delivery Date')
                        ->default(now())
                        ->required(),
                    TextInput::make('delivery_receipt_no')
                        ->label('DR # (Delivery Receipt No.)')
                        ->nullable(),
                    Toggle::make('has_warranty')
                        ->label('Include Warranty')
                        ->default(fn() => $this->record->has_warranty ?? true)
                        ->live(),
                    Select::make('warranty_period')
                        ->label('Warranty Period')
                        ->options(PurchaseOrder::getWarrantyPeriodOptions())
                        ->default(fn() => $this->record->warranty_period ?? PurchaseOrder::WARRANTY_1_YEAR)
                        ->visible(fn($get) => (bool) $get('has_warranty')),
                ])
                ->action(function (array $data) {
                    if (!$this->record->isApproved()) {
                        Notification::make()->title('Action Blocked')->body('Purchase Order must be approved before delivery can be marked.')->danger()->send();
                        return;
                    }

                    $this->record->update([
                        'delivery_status' => PurchaseOrder::DELIVERY_DELIVERED,
                        'status' => PurchaseOrder::STATUS_DELIVERED,
                        'delivery_receipt_no' => $data['delivery_receipt_no'] ?? null,
                        'actual_delivery_date' => $data['actual_delivery_date'],
                        'has_warranty' => $data['has_warranty'] ?? true,
                        'warranty_period' => $data['warranty_period'] ?? PurchaseOrder::WARRANTY_1_YEAR,
                    ]);

                    $this->refreshFormData(['delivery_status', 'status', 'delivery_receipt_no', 'actual_delivery_date', 'has_warranty', 'warranty_period']);

                    Notification::make()
                        ->title('Delivery Confirmed')
                        ->body("PO {$this->record->po_number} marked as delivered. Inventory deducted & warranty activated.")
                        ->success()
                        ->send();
                }),

            Action::make('delivery_tracker')
                ->label('Delivery Tracker')
                ->icon('heroicon-m-truck')
                ->color('info')
                ->tooltip('Open Delivery & Warranty Tracker for this purchase order')
                ->visible(fn(): bool => !$this->record->trashed() && $this->record->isApproved() && ($this->record->delivery_status === PurchaseOrder::DELIVERY_DELIVERED || $this->record->status === PurchaseOrder::STATUS_DELIVERED))
                ->url(fn() => DeliveryMonitoringPage::getUrl()),

            DeleteAction::make()->requiresConfirmation(),
            RestoreAction::make()->requiresConfirmation()->visible(fn(): bool => $this->record->trashed()),
            ForceDeleteAction::make()->requiresConfirmation()->visible(fn(): bool => $this->record->trashed() && (auth()->user()?->canDeleteRecords() ?? false)),
        ];
    }

    protected function afterSave(): void
    {
        if ($this->record->quotation_id && $quotation = \App\Models\Quotation::find($this->record->quotation_id)) {
            $quotation->update(['status' => \App\Models\Quotation::STATUS_CONVERTED]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

