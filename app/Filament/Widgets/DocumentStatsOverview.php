<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\ReviewQueuePage;
use App\Models\Document;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DocumentStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $pendingCount = Document::where('status', Document::STATUS_REQUIRES_REVIEW)->count();
        $verifiedCount = Document::where('status', Document::STATUS_VERIFIED)->count();
        $totalDocs = Document::count();

        $monthlyVerifiedAmount = Transaction::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('final_amount');

        // Mismatch rate calculation
        $allDocs = Document::with('totals', 'lineItems')->get();
        $mismatchDocsCount = $allDocs->filter(fn($doc) => $doc->hasMismatches())->count();
        $mismatchRate = $totalDocs > 0 ? round(($mismatchDocsCount / $totalDocs) * 100, 1) : 0.0;

        return [
            Stat::make('Pending Verification', $pendingCount)
                ->description($pendingCount > 0 ? 'Requires attention in Review Queue' : 'Queue is all clear')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingCount > 0 ? 'warning' : 'success'),

            Stat::make('Verified Transaction Volume', '₱' . number_format($monthlyVerifiedAmount, 2))
                ->description('This month\'s confirmed transactions')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Extraction Mismatch Rate', "{$mismatchRate}%")
                ->description("{$mismatchDocsCount} of {$totalDocs} docs with arithmetic discrepancies")
                ->descriptionIcon('heroicon-m-calculator')
                ->color($mismatchRate > 20 ? 'danger' : 'info'),

            Stat::make('Total Ingested Documents', $totalDocs)
                ->description("{$verifiedCount} verified and archived")
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),
        ];
    }
}
