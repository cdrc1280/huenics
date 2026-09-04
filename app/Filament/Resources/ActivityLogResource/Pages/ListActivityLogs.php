<?php

namespace App\Filament\Resources\ActivityLogResource\Pages;

use App\Filament\Resources\ActivityLogResource;
use App\Models\AuditLog;
use App\Models\DeliveryReceipt;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\SalesInvoice;
use App\Models\Transaction;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListActivityLogs extends ListRecords
{
    protected static string $resource = ActivityLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('purgeNonTransactions')
                ->label('Purge Non-Transaction Logs')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Purge Non-Transaction Activity Logs')
                ->modalDescription('This will permanently delete all audit logs for non-commercial models (such as products, inventory snapshots, and aliases) to immediately reclaim database storage. Only Transaction, Purchase Order, Quotation, Sales Invoice, and Delivery Receipt logs will be preserved.')
                ->modalSubmitActionLabel('Purge Non-Transaction Logs')
                ->action(function () {
                    $commercialTypes = [
                        Transaction::class,
                        PurchaseOrder::class,
                        Quotation::class,
                        SalesInvoice::class,
                        DeliveryReceipt::class,
                    ];

                    $deletedCount = AuditLog::whereNotIn('auditable_type', $commercialTypes)->delete();

                    Notification::make()
                        ->title('Storage Cleaned')
                        ->success()
                        ->body("Successfully purged {$deletedCount} non-transaction audit log records from storage.")
                        ->send();
                }),

            Action::make('pruneOldLogs')
                ->label('Prune Old Logs')
                ->icon('heroicon-o-archive-box-x-mark')
                ->color('gray')
                ->modalHeading('Prune Historical Audit Logs')
                ->modalDescription('Permanently delete audit logs older than a specific timeframe to save storage space.')
                ->form([
                    Select::make('days')
                        ->label('Prune logs older than')
                        ->options([
                            '30' => 'Older than 30 days',
                            '60' => 'Older than 60 days',
                            '90' => 'Older than 90 days',
                            '180' => 'Older than 180 days',
                        ])
                        ->default('30')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $days = (int) ($data['days'] ?? 30);
                    $cutoff = now()->subDays($days);

                    $deletedCount = AuditLog::where('created_at', '<', $cutoff)->delete();

                    Notification::make()
                        ->title('Logs Pruned')
                        ->success()
                        ->body("Permanently deleted {$deletedCount} audit log records older than {$days} days.")
                        ->send();
                }),
        ];
    }
}
