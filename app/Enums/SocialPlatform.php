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
}
