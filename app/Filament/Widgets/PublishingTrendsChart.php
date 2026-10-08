<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Queries\AdminMetricsQuery;
use App\Support\DisplayTimezone;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

final class PublishingTrendsChart extends ChartWidget
{
    #[\Override]
    protected static ?int $sort = 2;

    #[\Override]
    protected int|string|array $columnSpan = 'full';

    #[\Override]
    protected ?string $heading = 'Publishing activity';

    #[\Override]
    protected ?string $description = 'Published content over the last six months.';

    /**
     * @return array{
     *     datasets: array<int, array{label: string, data: list<int>}>,
     *     labels: array<int, string>,
     * }
     */
    protected function getData(): array
    {
        $months = array_map(
            fn (int $monthsAgo): Carbon => DisplayTimezone::convert(now())
                ->startOfMonth()
                ->subMonths($monthsAgo),
            range(5, 0),
        );
        $published = app(AdminMetricsQuery::class)->publishedPerMonth($months);

        return [
            'datasets' => [
                ['label' => 'Posts', 'data' => $published['posts']],
                ['label' => 'Episodes', 'data' => $published['episodes']],
                ['label' => 'Newsletter issues', 'data' => $published['newsletterIssues']],
            ],
            'labels' => array_map(fn (Carbon $month): string => $month->format('M Y'), $months),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
