<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum MediaVariantStatus: string implements HasColor, HasLabel
{
    case Ready = 'Ready';
    case Missing = 'Missing';
    case Unavailable = 'Unavailable';

    public function getLabel(): string
    {
        return $this->value;
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
