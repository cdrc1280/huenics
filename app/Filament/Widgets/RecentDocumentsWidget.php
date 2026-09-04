<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\ReviewQueuePage;
use App\Models\Document;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Tables;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentDocumentsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Document::query()->latest()->limit(10))
            ->heading('Recent Document Ingestions')
            ->columns([
                Tables\Columns\TextColumn::make('document_number')
                    ->label('Doc #')
                    ->default('—')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('document_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        Document::TYPE_PURCHASE_ORDER => 'Purchase Order',
                        Document::TYPE_VENDORS_AGREEMENT => 'Quotation',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        Document::TYPE_PURCHASE_ORDER => 'primary',
                        Document::TYPE_VENDORS_AGREEMENT => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('vendor.name')
                    ->label('Vendor')
                    ->default('—'),

                Tables\Columns\TextColumn::make('project.name')
                    ->label('Project')
                    ->default('—'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        Document::STATUS_UPLOADED => 'gray',
                        Document::STATUS_PROCESSING => 'info',
                        Document::STATUS_REQUIRES_REVIEW => 'warning',
                        Document::STATUS_VERIFIED => 'success',
                        Document::STATUS_FAILED => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\IconColumn::make('mismatch')
                    ->label('Reconciled')
                    ->state(fn(Document $record): bool => !$record->hasMismatches())
                    ->boolean()
                    ->trueIcon('heroicon-s-check-circle')
                    ->falseIcon('heroicon-s-exclamation-triangle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('totals.printed_total')
                    ->label('Printed Total (₱)')
                    ->money('PHP')
                    ->default('—'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Uploaded')
                    ->since(),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('verify')
                        ->label('Review')
                        ->icon('heroicon-o-eye')
                        ->color('warning')
                        ->url(fn(Document $record): string => ReviewQueuePage::getUrl(['document_id' => $record->id])),
                ]),
            ], position: RecordActionsPosition::BeforeColumns);
    }
}
