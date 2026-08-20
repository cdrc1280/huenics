<?php

namespace App\Filament\Resources\QuotationResource\Pages;

use App\Filament\Resources\QuotationResource;
use App\Services\QuotationService;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateQuotation extends CreateRecord
{
    protected static string $resource = QuotationResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['sales_agent_id'] = $data['sales_agent_id'] ?? auth()->id();
        return $data;
    }

    protected function afterCreate(): void
    {
        // Increment quota quotation count
        $agent = auth()->user();
        if ($agent) {
            \App\Models\SalesQuota::firstOrCreate(
                ['user_id' => $agent->id, 'month' => now()->month, 'year' => now()->year],
                ['target_amount' => 0, 'achieved_amount' => 0]
            )->increment('total_quotations');
        }

        Notification::make()
            ->title('Quotation Created')
            ->body("Quotation #{$this->record->quotation_number} has been created.")
            ->success()
            ->send();
    }
}
