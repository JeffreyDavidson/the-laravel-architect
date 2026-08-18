<?php

namespace App\Enums;

enum TestimonialStatus: string
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
}
