<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class QuickLinksWidget extends Widget
{
    #[\Override]
    protected string $view = 'filament.widgets.quick-links-widget';

    #[\Override]
    protected int|string|array $columnSpan = 1;

    #[\Override]
    protected static ?int $sort = -6;
}
