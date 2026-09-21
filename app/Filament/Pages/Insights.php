<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Widgets\ContentPerformanceOverview;
use App\Filament\Widgets\EditorialOperationsOverview;
use App\Filament\Widgets\PublishingTrendsChart;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\Widget;
use Filament\Widgets\WidgetConfiguration;
use UnitEnum;

class Insights extends Page
{
    #[\Override]
    protected static ?string $title = 'Insights';

    #[\Override]
    protected static ?string $navigationLabel = 'Insights';

    #[\Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBarSquare;

    #[\Override]
    protected static string|UnitEnum|null $navigationGroup = 'Operations';

    #[\Override]
    protected static ?int $navigationSort = 0;

    #[\Override]
    protected string $view = 'filament.pages.insights';

    #[\Override]
    protected ?string $subheading = 'Review publishing output, audience growth, and editorial workload without crowding the daily workspace.';

    /**
     * @return array<class-string<Widget>|WidgetConfiguration>
     */
    protected function getHeaderWidgets(): array
    {
        return [
            EditorialOperationsOverview::class,
            ContentPerformanceOverview::class,
            PublishingTrendsChart::class,
        ];
    }

    /** @return array<string, int> */
    public function getHeaderWidgetsColumns(): array
    {
        return [
            'default' => 1,
        ];
    }
}
