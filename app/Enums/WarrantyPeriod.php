<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum WarrantyPeriod: string implements HasLabel
{
    case OneYear = '1_year';
    case TwoYearsSixMonths = '2_years_6_months';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::OneYear => '1 Year',
            self::TwoYearsSixMonths => '2 Years and 6 Months',
        };
    }

    public function getMonths(): int
    {
        return match ($this) {
            self::OneYear => 12,
            self::TwoYearsSixMonths => 30,
        };
    }
}
