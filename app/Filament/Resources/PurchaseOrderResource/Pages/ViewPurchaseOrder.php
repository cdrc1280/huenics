<?php

namespace App\Filament\Resources\PurchaseOrderResource\Pages;

use App\Filament\Resources\PurchaseOrderResource;
use App\Models\PurchaseOrder;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewPurchaseOrder extends ViewRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Purchase Order Details')
                ->columns(3)
                ->schema([
                    TextEntry::make('po_number')->label('PO #')->weight('bold'),
                    TextEntry::make('customer_name')->label('Customer'),
                    TextEntry::make('salesAgent.name')->label('Sales Agent'),
                    TextEntry::make('quotation.quotation_number')->label('Linked Quotation')->default('—'),
                    TextEntry::make('project.name')->label('Project')->default('—'),
                    TextEntry::make('order_date')->label('Order Date')->date('M j, Y'),
                    TextEntry::make('status')->label('Status')->badge()
                        ->color(fn(string $state) => match ($state) {
                            PurchaseOrder::STATUS_DELIVERED => 'success',
                            PurchaseOrder::STATUS_CANCELLED => 'danger',
                            default => 'warning',
                        }),
                ]),

            Section::make('Financials')
                ->columns(3)
                ->schema([
                    TextEntry::make('order_amount')->label('Order Amount')->money('PHP'),
                    TextEntry::make('computed_vat')->label('Computed VAT (12%)')->money('PHP'),
                    TextEntry::make('realized_profit')->label('Realized Profit')->money('PHP')
                        ->color(fn($state) => $state > 0 ? 'success' : 'danger'),
                ]),

            Section::make('Delivery & Warranty')
                ->columns(3)
                ->schema([
                    TextEntry::make('delivery_status')->label('Delivery Status')->badge()
                        ->color(fn(string $state) => match ($state) {
                            PurchaseOrder::DELIVERY_DELIVERED => 'success',
                            PurchaseOrder::DELIVERY_OVERDUE => 'danger',
                            PurchaseOrder::DELIVERY_TRANSIT => 'info',
                            default => 'warning',
                        }),
                    TextEntry::make('actual_delivery_date')->label('Delivered On')->date('M j, Y')->placeholder('Not yet delivered'),
                    TextEntry::make('delivery_receipt_no')->label('DR #')->placeholder('—'),
                    TextEntry::make('warranty_status')->label('Warranty Status')->badge()
                        ->color(fn(string $state) => match ($state) {
                            PurchaseOrder::WARRANTY_ACTIVE => 'success',
                            PurchaseOrder::WARRANTY_EXPIRING => 'warning',
                            PurchaseOrder::WARRANTY_EXPIRED => 'danger',
                            default => 'gray',
                        }),
                    TextEntry::make('warranty_period')
                        ->label('Warranty Period')
                        ->formatStateUsing(fn(string $state): string => PurchaseOrder::getWarrantyPeriodOptions()[$state] ?? $state),
                    TextEntry::make('warranty_end_date')->label('Warranty Expires')->date('M j, Y')->placeholder('—'),
                ]),
        ]);
    }
}
