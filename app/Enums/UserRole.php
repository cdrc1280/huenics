<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum UserRole: string implements HasLabel, HasColor, HasIcon
{
    case Admin = 'admin';
    case OperationsManager = 'operations_manager';
    case SalesExecutive = 'sales_executive';
    case Ceo = 'ceo';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::OperationsManager => 'Operations Manager',
            self::SalesExecutive => 'Sales Executive',
            self::Ceo => 'CEO / Executive',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Admin => 'danger',
            self::OperationsManager => 'warning',
            self::SalesExecutive => 'info',
            self::Ceo => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Admin => 'heroicon-m-shield-check',
            self::OperationsManager => 'heroicon-m-cog-6-tooth',
            self::SalesExecutive => 'heroicon-m-user',
            self::Ceo => 'heroicon-m-star',
        };
    }
}
