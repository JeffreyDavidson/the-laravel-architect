<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum MediaHealthType: string implements HasLabel
{
    case Project = 'project';
    case Post = 'post';
    case Podcast = 'podcast';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }
}
