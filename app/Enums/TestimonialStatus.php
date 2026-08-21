<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum TestimonialStatus: string implements HasLabel
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            self::Pending->value => 'Pending',
            self::Approved->value => 'Approved',
            self::Rejected->value => 'Rejected',
        ];
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Approved => 'success',
            self::Rejected => 'danger',
        };
    }

    public function getLabel(): string
    {
        return self::labels()[$this->value];
    }
}
