<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum WarrantyPeriod: string implements HasLabel
{
    case SixMonths = '6_months';
    case OneYear = '1_year';
    case TwoYears = '2_years';
    case TwoYearsSixMonths = '2_years_6_months';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::SixMonths => '6 Months',
            self::OneYear => '1 Year',
            self::TwoYears => '2 Years',
            self::TwoYearsSixMonths => '2 Years and 6 Months',
        };
    }

    public function getMonths(): int
    {
        return match ($this) {
            self::SixMonths => 6,
            self::OneYear => 12,
            self::TwoYears => 24,
            self::TwoYearsSixMonths => 30,
        };
    }
}

