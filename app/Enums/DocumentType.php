<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum DocumentType: string implements HasLabel, HasColor, HasIcon
{
    case VendorsAgreement = 'vendors_agreement';
    case PurchaseOrder = 'purchase_order';
    case OrderSlip = 'order_slip';
    case DeliveryReceipt = 'delivery_receipt';
    case SalesInvoice = 'sales_invoice';
    case Other = 'other';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::VendorsAgreement => 'Vendor\'s Agreement / Quotation',
            self::PurchaseOrder => 'Purchase Order (P.O.)',
            self::OrderSlip => 'Official Order Slip',
            self::DeliveryReceipt => 'Delivery Receipt (D.R.)',
            self::SalesInvoice => 'Sales Invoice (S.I.)',
            self::Other => 'Other Document',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::VendorsAgreement => 'primary',
            self::PurchaseOrder => 'success',
            self::OrderSlip => 'warning',
            self::DeliveryReceipt => 'info',
            self::SalesInvoice => 'secondary',
            self::Other => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::VendorsAgreement => 'heroicon-m-document-text',
            self::PurchaseOrder => 'heroicon-m-shopping-cart',
            self::OrderSlip => 'heroicon-m-clipboard-document-list',
            self::DeliveryReceipt => 'heroicon-m-truck',
            self::SalesInvoice => 'heroicon-m-banknotes',
            self::Other => 'heroicon-m-document',
        };
    }
}
