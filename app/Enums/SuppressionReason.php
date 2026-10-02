<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum SuppressionReason: string implements HasColor, HasIcon, HasLabel
{
    case Bounced = 'bounced';
    case Complained = 'complained';

    public function getLabel(): string
    {
        return match ($this) {
            self::Bounced => 'Bounced',
            self::Complained => 'Marked as spam',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Bounced => 'danger',
            self::Complained => 'warning',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Bounced => Heroicon::OutlinedExclamationTriangle,
            self::Complained => Heroicon::OutlinedShieldExclamation,
        };
    }
}
