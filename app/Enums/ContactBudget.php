<?php

namespace App\Enums;

enum ContactBudget: string
{
    case Small = 'small';
    case Medium = 'medium';
    case Large = 'large';
    case Enterprise = 'enterprise';

    public function getLabel(): string
    {
        return match ($this) {
            self::Small => 'Under $5,000',
            self::Medium => '$5,000 to $15,000',
            self::Large => '$15,000 to $50,000',
            self::Enterprise => '$50,000+',
        };
    }
}
