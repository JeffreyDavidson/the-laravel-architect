<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum SocialPlatform: string implements HasLabel
{
    case GitHub = 'github';
    case X = 'x';
    case YouTube = 'youtube';
    case Bluesky = 'bluesky';
    case Instagram = 'instagram';
    case Facebook = 'facebook';
    case LinkedIn = 'linkedin';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::GitHub => 'GitHub',
            self::X => 'X / Twitter',
            self::YouTube => 'YouTube',
            self::Bluesky => 'Bluesky',
            self::Instagram => 'Instagram',
            self::Facebook => 'Facebook',
            self::LinkedIn => 'LinkedIn',
            self::Other => 'Other',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::GitHub => 'github',
            self::X => 'x-twitter',
            self::YouTube => 'youtube',
            self::Bluesky => 'bluesky',
            self::Instagram => 'instagram',
            self::Facebook => 'facebook',
            self::LinkedIn => 'linkedin',
            self::Other => 'external-link',
        };
    }

    public function hoverClasses(): string
    {
        return match ($this) {
            self::YouTube => 'hover:text-red-500 hover:border-red-500/50 hover:bg-red-500/5',
            self::Bluesky => 'hover:text-blue-400 hover:border-blue-400/50 hover:bg-blue-400/5',
            self::Instagram => 'hover:text-pink-400 hover:border-pink-400/50 hover:bg-pink-400/5',
            self::Facebook => 'hover:text-blue-500 hover:border-blue-500/50 hover:bg-blue-500/5',
            self::LinkedIn => 'hover:text-blue-600 hover:border-blue-600/50 hover:bg-blue-600/5',
            default => 'hover:text-gray-900 dark:hover:text-white hover:border-brand-600/50 hover:bg-brand-600/5',
        };
    }
}
