<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * The admin panel's sidebar groups. Resources and pages reference a case as
 * their $navigationGroup, and Filament shows the groups in case order.
 */
enum NavigationGroup: string implements HasLabel
{
    case Publish = 'publish';
    case Library = 'library';
    case Audience = 'audience';
    case Operations = 'operations';

    public function getLabel(): string
    {
        return match ($this) {
            self::Publish => 'Publish',
            self::Library => 'Library',
            self::Audience => 'Audience',
            self::Operations => 'Operations',
        };
    }
}
