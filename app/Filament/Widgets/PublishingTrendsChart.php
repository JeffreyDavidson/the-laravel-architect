<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Post;
use App\Support\DisplayTimezone;
use DateTimeInterface;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class PublishingTrendsChart extends ChartWidget
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

        return [
            'datasets' => [
                ['label' => 'Posts', 'data' => $this->monthlyCounts(Post::query()->published(), $months)],
                ['label' => 'Episodes', 'data' => $this->monthlyCounts(Episode::query()->published(), $months)],
                ['label' => 'Newsletter issues', 'data' => $this->monthlyCounts(NewsletterIssue::query()->published(), $months)],
            ],
            'labels' => array_map(fn (Carbon $month): string => $month->format('M Y'), $months),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    /**
     * Count the query's records per display-timezone month, starting from the first month.
     *
     * @param  Builder<Post>|Builder<Episode>|Builder<NewsletterIssue>  $query
     * @param  list<Carbon>  $months
     * @return list<int>
     */
    private function monthlyCounts(Builder $query, array $months): array
    {
        $counts = array_fill_keys(array_map(fn (Carbon $month): string => $month->format('Y-m'), $months), 0);
        $start = ($months[0] ?? null)
            ?->copy()
            ->utc();

        foreach ($query->where('published_at', '>=', $start)
            ->pluck('published_at') as $publishedAt) {
            if (! is_string($publishedAt) && ! $publishedAt instanceof DateTimeInterface) {
                continue;
            }

            $monthKey = DisplayTimezone::convert(Carbon::parse($publishedAt))
                ->format('Y-m');

            if (array_key_exists($monthKey, $counts)) {
                $counts[$monthKey]++;
            }
        }

        return array_values($counts);
    }
}
