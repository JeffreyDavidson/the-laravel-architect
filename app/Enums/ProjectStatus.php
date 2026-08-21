<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ProjectStatus: string implements HasLabel
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

    public function getLabel(): string
    {
        return self::labels()[$this->value];
    }
}
