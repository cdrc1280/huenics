<?php

namespace App\Filament\Pages;

use App\Models\PurchaseOrder;
use App\Models\DeliveryReceipt;
use App\Models\SalesInvoice;
use Filament\Pages\Page;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;

use Filament\Actions\ActionGroup;
use Filament\Tables\Enums\RecordActionsPosition;
use BackedEnum;
use UnitEnum;

class DeliveryMonitoringPage extends Page implements HasTable, HasForms
{
    use InteractsWithTable, InteractsWithForms;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-map-pin';
    protected static ?string $navigationLabel = 'Fleet & Delivery Tracker';
    protected static UnitEnum|string|null $navigationGroup = 'Sales & Order Lifecycle';
    protected string $view = 'filament.pages.delivery-monitoring-page';
    protected static ?int $navigationSort = 5;

    public function table(Table $table): Table
    {
        return $table
            ->query(PurchaseOrder::query()->orderBy('expected_delivery_date', 'asc'))
            ->columns([
                TextColumn::make('po_number')
                    ->label('PO #')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer_name')
                    ->searchable(),
                TextColumn::make('project.name')
                    ->label('Project'),
                TextColumn::make('order_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('expected_delivery_date')
                    ->date()
                    ->sortable()
                    ->color(fn ($record) => $record->is_overdue ? 'danger' : (now()->diffInDays($record->expected_delivery_date, false) < 3 && $record->delivery_status !== 'delivered' ? 'warning' : null)),
                TextColumn::make('delivery_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'in_transit' => 'warning',
                        'delivered' => 'success',
                        'overdue' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('delivery_receipt_no')
                    ->label('DR #'),
                TextColumn::make('realized_profit')
                    ->money('PHP')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('mark_delivered')
                        ->label('Mark Delivered')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn ($record) => $record->delivery_status !== 'delivered')
                        ->form([
                            TextInput::make('delivery_receipt_no')
                                ->label('DR #')
                                ->required(),
                            DatePicker::make('actual_delivery_date')
                                ->label('Actual Delivery Date')
                                ->default(now())
                                ->required(),
                        ])
                        ->action(function ($record, array $data) {
                            $record->update([
                                'delivery_status' => 'delivered',
                                'delivery_receipt_no' => $data['delivery_receipt_no'],
                                'actual_delivery_date' => $data['actual_delivery_date'],
                            ]);
                        }),
                    Action::make('create_dr')
                        ->label('Create DR')
                        ->icon('heroicon-o-document-text')
                        ->color('info')
                        ->url(fn ($record) => url('/admin/delivery-receipts/create?purchase_order_id=' . $record->id)),
                    Action::make('create_si')
                        ->label('Create SI')
                        ->icon('heroicon-o-currency-dollar')
                        ->color('warning')
                        ->url(fn ($record) => url('/admin/sales-invoices/create?purchase_order_id=' . $record->id)),
                ]),
            ], position: RecordActionsPosition::BeforeColumns);
    }
}
