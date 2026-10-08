<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum MediaSourceStatus: string implements HasColor, HasLabel
{
    case Optimized = 'optimized';
    case NeedsOptimization = 'needs_optimization';
    case Missing = 'missing';
    case Unreadable = 'unreadable';

    public function getLabel(): string
    {
        return match ($this) {
            self::Optimized => 'Optimized',
            self::NeedsOptimization => 'Needs optimization',
            self::Missing => 'Missing',
            self::Unreadable => 'Unreadable',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Optimized => 'success',
            self::NeedsOptimization => 'warning',
            self::Missing,
            self::Unreadable => 'danger',
        };
    }
}
