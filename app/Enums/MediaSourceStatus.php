<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum MediaSourceStatus: string implements HasColor, HasLabel
{
    case Optimized = 'Optimized';
    case NeedsOptimization = 'Needs optimization';
    case Missing = 'Missing';
    case Unreadable = 'Unreadable';

    public function getLabel(): string
    {
        return $this->value;
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
