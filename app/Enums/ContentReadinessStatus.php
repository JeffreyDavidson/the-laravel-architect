<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ContentReadinessStatus: string implements HasColor, HasLabel
{
    case Ready = 'ready';
    case NeedsAttention = 'needs_attention';

    public function getLabel(): string
    {
        return match ($this) {
            self::Ready => 'Ready',
            self::NeedsAttention => 'Needs attention',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Ready => 'success',
            self::NeedsAttention => 'warning',
        };
    }
}
