<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum MediaVariantStatus: string implements HasColor, HasLabel
{
    case Ready = 'ready';
    case Missing = 'missing';
    case Unavailable = 'unavailable';

    public function getLabel(): string
    {
        return match ($this) {
            self::Ready => 'Ready',
            self::Missing => 'Missing',
            self::Unavailable => 'Unavailable',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Ready => 'success',
            self::Missing,
            self::Unavailable => 'warning',
        };
    }
}
