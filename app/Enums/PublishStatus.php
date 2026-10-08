<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PublishStatus: string implements HasColor, HasLabel
{
    case Draft = 'draft';
    case InReview = 'in_review';
    case Published = 'published';
    case Scheduled = 'scheduled';

    public function getColor(): string
    {
        return match ($this) {
            self::Published => 'success',
            self::Draft => 'gray',
            self::InReview => 'info',
            self::Scheduled => 'warning',
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::InReview => 'In Review',
            self::Published => 'Published',
            self::Scheduled => 'Scheduled',
        };
    }
}
