<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CalendarEntryType: string implements HasLabel
{
    case Post = 'post';
    case Episode = 'episode';

    public function getLabel(): string
    {
        return match ($this) {
            self::Post => 'Post',
            self::Episode => 'Episode',
        };
    }
}
