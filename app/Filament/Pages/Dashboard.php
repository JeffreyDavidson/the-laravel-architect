<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Widgets\ContentReadinessWidget;
use App\Filament\Widgets\QuickLinksWidget;
use App\Filament\Widgets\RecentActivityWidget;
use App\Filament\Widgets\WelcomeWidget;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\Widget;
use Filament\Widgets\WidgetConfiguration;

class Dashboard extends BaseDashboard
{
    #[\Override]
    protected static bool $isDiscovered = false;

    /**
     * @return array<class-string<Widget>|WidgetConfiguration>
     */
    public function getWidgets(): array
    {
        return [
            WelcomeWidget::class,
            QuickLinksWidget::class,
            RecentActivityWidget::class,
            ContentReadinessWidget::class,
        ];
    }

    /** @return array<string, int> */
    public function getColumns(): array
    {
        return [
            'default' => 1,
            'lg' => 2,
        ];
    }
}
