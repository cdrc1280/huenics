<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum PurchaseOrderStatus: string implements HasLabel, HasColor, HasIcon
{
    case Pending = 'pending';
    case Approved = 'approved';
    case PendingDelivery = 'pending_delivery';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
    case Rejected = 'rejected';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Pending => 'Pending Approval',
            self::Approved => 'Approved (Ready to Deliver)',
            self::PendingDelivery => 'Pending Delivery',
            self::Delivered => 'Delivered',
            self::Cancelled => 'Cancelled',
            self::Rejected => 'Rejected',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Approved => 'info',
            self::PendingDelivery => 'primary',
            self::Delivered => 'success',
            self::Cancelled, self::Rejected => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Pending => 'heroicon-m-clock',
            self::Approved => 'heroicon-m-check-circle',
            self::PendingDelivery => 'heroicon-m-truck',
            self::Delivered => 'heroicon-m-check-badge',
            self::Cancelled => 'heroicon-m-no-symbol',
            self::Rejected => 'heroicon-m-x-circle',
        };
    }
}
