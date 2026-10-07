<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * The admin panel's sidebar groups. AdminPanelProvider builds the sidebar in
 * this order, and resources and pages reference a case as their group.
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
