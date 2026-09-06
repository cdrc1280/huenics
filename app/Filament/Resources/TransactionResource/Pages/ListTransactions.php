<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Transaction'),

            Actions\Action::make('export_all_csv')
                ->label('Export Ledger (CSV)')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->tooltip('Export all transactions to CSV')
                ->url(route('transactions.export-csv'))
                ->openUrlInNewTab(false),
        ];
    }
}
