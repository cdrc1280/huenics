<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum DocumentStatus: string implements HasLabel, HasColor, HasIcon
{
    case Uploaded = 'uploaded';
    case Processing = 'processing';
    case RequiresReview = 'requires_review';
    case Verified = 'verified';
    case Failed = 'failed';
    case Rejected = 'rejected';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Uploaded => 'Uploaded',
            self::Processing => 'Processing',
            self::RequiresReview => 'Requires Review',
            self::Verified => 'Verified',
            self::Failed => 'Failed / Error',
            self::Rejected => 'Rejected',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Uploaded => 'gray',
            self::Processing => 'info',
            self::RequiresReview => 'warning',
            self::Verified => 'success',
            self::Failed, self::Rejected => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Uploaded => 'heroicon-m-arrow-up-tray',
            self::Processing => 'heroicon-m-arrow-path',
            self::RequiresReview => 'heroicon-m-clock',
            self::Verified => 'heroicon-m-check-badge',
            self::Failed => 'heroicon-m-exclamation-triangle',
            self::Rejected => 'heroicon-m-x-circle',
        };
    }
}
