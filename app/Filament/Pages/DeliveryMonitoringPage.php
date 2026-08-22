<?php

namespace App\Filament\Pages;

use App\Models\DeliveryReceipt;
use App\Models\PurchaseOrder;
use App\Models\SalesInvoice;
use BackedEnum;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class DeliveryMonitoringPage extends Page implements HasTable, HasForms
{
    use InteractsWithTable, InteractsWithForms;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'Delivery & Warranty Tracker';
    protected static UnitEnum|string|null $navigationGroup = 'Sales & Order Lifecycle';
    protected string $view = 'filament.pages.delivery-monitoring-page';
    protected static ?int $navigationSort = 7;

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
                    ->color(fn($record) => $record->is_overdue ? 'danger' : (now()->diffInDays($record->expected_delivery_date, false) < 3 && $record->delivery_status !== 'delivered' ? 'warning' : null)),
                TextColumn::make('delivery_status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'gray',
                        'in_transit' => 'warning',
                        'delivered' => 'success',
                        'overdue' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('delivery_receipt_no')
                    ->label('DR #'),
                TextColumn::make('warranty_status')
                    ->label('Warranty')
                    ->badge()
                    ->formatStateUsing(fn(?string $state, $record): string => match ($state) {
                        PurchaseOrder::WARRANTY_ACTIVE => 'Active (' . ($record->warranty_period === PurchaseOrder::WARRANTY_2_YEARS_6_MONTHS || $record->warranty_period === '2_years' ? '2.5 yrs' : '1 yr') . ')',
                        PurchaseOrder::WARRANTY_EXPIRING => 'Expiring Soon',
                        PurchaseOrder::WARRANTY_EXPIRED => 'Expired',
                        default => 'No Warranty',
                    })
                    ->color(fn(?string $state): string => match ($state) {
                        PurchaseOrder::WARRANTY_ACTIVE => 'success',
                        PurchaseOrder::WARRANTY_EXPIRING => 'warning',
                        PurchaseOrder::WARRANTY_EXPIRED => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('realized_profit')
                    ->money('PHP')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('approve_po')
                        ->label('Approve PO')
                        ->icon('heroicon-o-check')
                        ->color('info')
                        ->visible(fn($record) => !$record->isApproved())
                        ->requiresConfirmation()
                        ->action(function (PurchaseOrder $record) {
                            $record->update(['status' => PurchaseOrder::STATUS_APPROVED]);
                            Notification::make()->title('Purchase Order Approved')->body("PO {$record->po_number} is now approved for delivery.")->success()->send();
                        }),

                    Action::make('mark_delivered')
                        ->label('Mark Delivered')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn($record) => $record->isApproved() && $record->delivery_status !== PurchaseOrder::DELIVERY_DELIVERED)
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
                                ->default(fn($record) => $record->has_warranty ?? true)
                                ->live(),
                            Select::make('warranty_period')
                                ->label('Warranty Period')
                                ->options(PurchaseOrder::getWarrantyPeriodOptions())
                                ->default(fn($record) => $record->warranty_period ?? PurchaseOrder::WARRANTY_1_YEAR)
                                ->visible(fn($get) => (bool) $get('has_warranty')),
                        ])
                        ->action(function (PurchaseOrder $record, array $data) {
                            if (!$record->isApproved()) {
                                Notification::make()->title('Action Blocked')->body('Purchase Order must be approved before delivery can be marked.')->danger()->send();
                                return;
                            }

                            $record->update([
                                'delivery_status' => PurchaseOrder::DELIVERY_DELIVERED,
                                'status' => PurchaseOrder::STATUS_DELIVERED,
                                'delivery_receipt_no' => $data['delivery_receipt_no'] ?? null,
                                'actual_delivery_date' => $data['actual_delivery_date'],
                                'has_warranty' => $data['has_warranty'] ?? true,
                                'warranty_period' => $data['warranty_period'] ?? PurchaseOrder::WARRANTY_1_YEAR,
                            ]);

                            Notification::make()
                                ->title('Delivery Confirmed')
                                ->body("PO {$record->po_number} marked as delivered. Inventory deducted & warranty activated.")
                                ->success()
                                ->send();
                        }),
                    Action::make('create_dr')
                        ->label('Create DR')
                        ->icon('heroicon-o-document-text')
                        ->color('info')
                        ->visible(fn($record) => $record->isApproved())
                        ->url(fn($record) => url('/admin/delivery-receipts/create?purchase_order_id=' . $record->id)),
                    Action::make('create_si')
                        ->label('Create SI')
                        ->icon('heroicon-o-currency-dollar')
                        ->color('warning')
                        ->visible(fn($record) => $record->isApproved())
                        ->url(fn($record) => url('/admin/sales-invoices/create?purchase_order_id=' . $record->id)),
                ]),
            ], position: RecordActionsPosition::BeforeColumns);
    }
}
