<?php

namespace App\Filament\Pages;

use App\Filament\Resources\DocumentResource;
use App\Filament\Widgets\DocumentStatsOverview;
use App\Filament\Widgets\RecentDocumentsWidget;
use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-home';

    protected static ?string $title = 'Dashboard';

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Action::make('review_queue')
    //             ->label('Open Review Queue')
    //             ->icon('heroicon-o-check-badge')
    //             ->color('warning')
    //             ->tooltip('Navigate to the active document verification & review workspace')
    //             ->url(ReviewQueuePage::getUrl()),
    //     ];
    // }


    public function getWidgets(): array
    {
        return [
            DocumentStatsOverview::class,
            RecentDocumentsWidget::class,
        ];
    }
}
