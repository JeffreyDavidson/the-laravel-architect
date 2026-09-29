<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Freshness of a post's official source, separate from editorial approval.
 */
enum SourceReviewStatus: string implements HasColor, HasLabel
{
    case NotTracked = 'not_tracked';
    case Current = 'current';
    case ReviewDue = 'review_due';

    public function getLabel(): string
    {
        return match ($this) {
            self::NotTracked => 'Not tracked',
            self::Current => 'Current',
            self::ReviewDue => 'Review due',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::NotTracked => 'gray',
            self::Current => 'success',
            self::ReviewDue => 'warning',
        };
    }
}
