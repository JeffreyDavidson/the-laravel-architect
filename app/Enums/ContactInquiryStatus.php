<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ContactInquiryStatus: string implements HasLabel
{
    case New = 'new';
    case InProgress = 'in_progress';
    case Resolved = 'resolved';

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::New->value => 'New',
            self::InProgress->value => 'In progress',
            self::Resolved->value => 'Resolved',
        ];
    }

    public function color(): string
    {
        return match ($this) {
            self::New => 'info',
            self::InProgress => 'warning',
            self::Resolved => 'success',
        };
    }

    public function getLabel(): string
    {
        return self::labels()[$this->value];
    }
}
