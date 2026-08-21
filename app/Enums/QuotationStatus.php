<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum QuotationStatus: string implements HasLabel, HasColor, HasIcon
{
    case Pending = 'pending';
    case Reviewed = 'reviewed';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case ConvertedToPo = 'converted_to_po';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Reviewed => 'Reviewed',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::ConvertedToPo => 'Converted to PO',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Reviewed => 'info',
            self::Approved => 'success',
            self::Rejected => 'danger',
            self::ConvertedToPo => 'primary',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Pending => 'heroicon-m-clock',
            self::Reviewed => 'heroicon-m-eye',
            self::Approved => 'heroicon-m-check-circle',
            self::Rejected => 'heroicon-m-x-circle',
            self::ConvertedToPo => 'heroicon-m-arrow-right-circle',
        };
    }
}
