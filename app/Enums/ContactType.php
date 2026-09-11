<?php

namespace App\Enums;

enum ContactType: string
{
    case Freelance = 'freelance';
    case Consulting = 'consulting';
    case Modernization = 'modernization';
    case Collaboration = 'collaboration';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::Freelance => 'Freelance Project',
            self::Consulting => 'Consulting / Code Review',
            self::Modernization => 'Legacy Modernization',
            self::Collaboration => 'Collaboration',
            self::Other => 'Just Saying Hi',
        };
    }
}
