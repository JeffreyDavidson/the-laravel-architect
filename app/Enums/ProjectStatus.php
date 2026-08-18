<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case Draft = 'draft';
    case Published = 'published';

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            self::Draft->value => 'Draft',
            self::Published->value => 'Published',
        ];
    }
}
