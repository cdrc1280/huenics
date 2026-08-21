<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum PurchaseOrderStatus: string implements HasLabel, HasColor, HasIcon
{
    case PendingDelivery = 'pending_delivery';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
    case Rejected = 'rejected';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PendingDelivery => 'Pending Delivery',
            self::Delivered => 'Delivered',
            self::Cancelled => 'Cancelled',
            self::Rejected => 'Rejected',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PendingDelivery => 'warning',
            self::Delivered => 'success',
            self::Cancelled, self::Rejected => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::PendingDelivery => 'heroicon-m-clock',
            self::Delivered => 'heroicon-m-check-badge',
            self::Cancelled => 'heroicon-m-no-symbol',
            self::Rejected => 'heroicon-m-x-circle',
        };
    }
}
