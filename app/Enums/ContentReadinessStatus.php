<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ContentReadinessStatus: string implements HasColor, HasLabel
{
    case Ready = 'Ready';
    case NeedsAttention = 'Needs attention';

    public function getLabel(): string
    {
        return $this->value;
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Ready => 'success',
            self::NeedsAttention => 'warning',
        };
    }
}
