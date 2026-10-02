<?php

declare(strict_types=1);

namespace App\Enums;

use App\Models\Subscriber;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

enum SubscriberStatus: string implements HasColor, HasIcon, HasLabel
{
    case Active = 'active';
    case Pending = 'pending';
    case Unsubscribed = 'unsubscribed';
    case Suppressed = 'suppressed';

    public static function for(Subscriber $subscriber): self
    {
        return match (true) {
            $subscriber->suppressed_at !== null => self::Suppressed,
            $subscriber->unsubscribed_at !== null => self::Unsubscribed,
            $subscriber->verified_at === null => self::Pending,
            default => self::Active,
        };
    }

    /** @param Builder<Subscriber> $query */
    public function scope(Builder $query): void
    {
        match ($this) {
            self::Active => $query->active(),
            self::Pending => $query->whereNull('verified_at')
                ->whereNull('unsubscribed_at'),
            self::Unsubscribed => $query->whereNotNull('unsubscribed_at')
                ->whereNull('suppressed_at'),
            self::Suppressed => $query->whereNotNull('suppressed_at'),
        };
    }

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
