<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum SubscriberStatus: string implements HasColor, HasIcon, HasLabel
{
    case Active = 'active';
    case Pending = 'pending';
    case Unsubscribed = 'unsubscribed';
    case Suppressed = 'suppressed';

    public function getLabel(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Pending => 'Pending confirmation',
            self::Unsubscribed => 'Unsubscribed',
            self::Suppressed => 'Suppressed',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Pending => 'warning',
            self::Unsubscribed => 'gray',
            self::Suppressed => 'danger',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Active => Heroicon::OutlinedCheckCircle,
            self::Pending => Heroicon::OutlinedClock,
            self::Unsubscribed => Heroicon::OutlinedXCircle,
            self::Suppressed => Heroicon::OutlinedNoSymbol,
        };
    }
}
