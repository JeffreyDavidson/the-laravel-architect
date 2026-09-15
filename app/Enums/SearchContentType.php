<?php

namespace App\Enums;

enum SearchContentType: string
{
    case Writing = 'writing';
    case Projects = 'projects';
    case Podcasts = 'podcasts';
    case Newsletter = 'newsletter';
    case Episodes = 'episodes';
    case Videos = 'videos';

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type): array => [$type->value => $type->label()])
            ->all();
    }

    public function label(): string
    {
        return match ($this) {
            self::Writing => 'Writing',
            self::Projects => 'Projects',
            self::Podcasts => 'Podcasts',
            self::Newsletter => 'Newsletter',
            self::Episodes => 'Episodes',
            self::Videos => 'Videos',
        };
    }
}
