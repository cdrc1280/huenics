<?php

namespace App\Filament\Widgets;

use App\Models\Document;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ReviewQueueStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $stats = \Illuminate\Support\Facades\Cache::remember('widget_review_queue_stats', 30, function () {
            $pendingCount = Document::where('status', Document::STATUS_REQUIRES_REVIEW)->count();
            $verifiedCount = Document::where('status', Document::STATUS_VERIFIED)->count();
            
            $mismatchCount = Document::where('status', Document::STATUS_REQUIRES_REVIEW)
                ->whereHas('totals', function ($q) {
                    $q->where('vat_mismatch', true)->orWhere('total_mismatch', true);
                })
                ->count();

            return [
                'pending' => $pendingCount,
                'verified' => $verifiedCount,
                'mismatches' => $mismatchCount,
            ];
        });

        $pendingCount = $stats['pending'];
        $verifiedCount = $stats['verified'];
        $mismatchCount = $stats['mismatches'];

        return [
            Stat::make('Pending Review', "{$pendingCount} Documents")
                ->description($pendingCount > 0 ? 'Requires human verification' : 'Queue is all clear')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingCount > 0 ? 'warning' : 'success')
                ->extraAttributes(['title' => 'Total vendor PDF documents waiting for review & verification']),

            Stat::make('Math & VAT Issues', "{$mismatchCount} Flagged")
                ->description($mismatchCount > 0 ? 'Line arithmetic or VAT errors detected' : 'All line items reconciled')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($mismatchCount > 0 ? 'danger' : 'success')
                ->extraAttributes(['title' => 'Documents flagged with .85 arithmetic errors or 12% Philippine VAT deviations']),

            Stat::make('Extraction Engine', 'Dynamic Parser')
                ->description('Layout-driven with auto-aliasing')
                ->descriptionIcon('heroicon-m-cpu-chip')
                ->color('primary')
                ->extraAttributes(['title' => 'Zero-code layout coordinate extraction engine with SHA-256 vault protection']),

            Stat::make('Verified Ledger', "{$verifiedCount} Verified")
                ->description('Committed to master ledger')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success')
                ->extraAttributes(['title' => 'Reconciled documents successfully committed to master financial ledger']),
        ];
    }

}
