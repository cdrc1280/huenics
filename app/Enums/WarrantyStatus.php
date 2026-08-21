<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum WarrantyStatus: string implements HasLabel, HasColor, HasIcon
{
    case Active = 'active';
    case ExpiringSoon = 'expiring_soon';
    case Expired = 'expired';
    case NoWarranty = 'no_warranty';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Active => 'Active',
            self::ExpiringSoon => 'Expiring Soon',
            self::Expired => 'Expired',
            self::NoWarranty => 'No Warranty',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Active => 'success',
            self::ExpiringSoon => 'warning',
            self::Expired => 'danger',
            self::NoWarranty => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Active => 'heroicon-m-shield-check',
            self::ExpiringSoon => 'heroicon-m-clock',
            self::Expired => 'heroicon-m-shield-exclamation',
            self::NoWarranty => 'heroicon-m-minus-circle',
        };
    }
}
