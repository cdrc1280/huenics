<?php

namespace App\Filament\Resources\PurchaseOrderResource\Pages;

use App\Filament\Resources\PurchaseOrderResource;
use App\Models\PurchaseOrder;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
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
                    $this->refreshFormData(['status']);
                    Notification::make()->title('Purchase Order Approved')->body("PO {$this->record->po_number} is now approved for delivery.")->success()->send();
                }),

            DeleteAction::make()->requiresConfirmation(),
            RestoreAction::make()->requiresConfirmation()->visible(fn(): bool => $this->record->trashed()),
            ForceDeleteAction::make()->requiresConfirmation()->visible(fn(): bool => $this->record->trashed() && (auth()->user()?->isAdmin() ?? false)),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

