<?php

namespace App\Filament\Widgets;

use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Post;
use DateTimeInterface;
use Filament\Widgets\ChartWidget;
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

    protected function getData(): array
    {
        $months = collect(range(5, 0))
            ->map(fn (int $monthsAgo): Carbon => now()->startOfMonth()->subMonths($monthsAgo));
        $monthKeys = $months->map(fn (Carbon $month): string => $month->format('Y-m'))->all();
        $start = $months->first();

        $counts = [
            'Posts' => array_fill_keys($monthKeys, 0),
            'Episodes' => array_fill_keys($monthKeys, 0),
            'Newsletter issues' => array_fill_keys($monthKeys, 0),
        ];

        foreach (Post::query()->published()->where('published_at', '>=', $start)->pluck('published_at') as $publishedAt) {
            if (! is_string($publishedAt) && ! $publishedAt instanceof DateTimeInterface) {
                continue;
            }

            $monthKey = Carbon::parse($publishedAt)->format('Y-m');

            if (array_key_exists($monthKey, $counts['Posts'])) {
                $counts['Posts'][$monthKey]++;
            }
        }

        foreach (Episode::query()->published()->where('published_at', '>=', $start)->pluck('published_at') as $publishedAt) {
            if (! is_string($publishedAt) && ! $publishedAt instanceof DateTimeInterface) {
                continue;
            }

            $monthKey = Carbon::parse($publishedAt)->format('Y-m');

            if (array_key_exists($monthKey, $counts['Episodes'])) {
                $counts['Episodes'][$monthKey]++;
            }
        }

        foreach (NewsletterIssue::query()->published()->where('published_at', '>=', $start)->pluck('published_at') as $publishedAt) {
            if (! is_string($publishedAt) && ! $publishedAt instanceof DateTimeInterface) {
                continue;
            }

            $monthKey = Carbon::parse($publishedAt)->format('Y-m');

            if (array_key_exists($monthKey, $counts['Newsletter issues'])) {
                $counts['Newsletter issues'][$monthKey]++;
            }
        }

        return [
            'datasets' => collect($counts)
                ->map(fn (array $data, string $label): array => [
                    'label' => $label,
                    'data' => array_values($data),
                ])
                ->values()
                ->all(),
            'labels' => $months->map(fn (Carbon $month): string => $month->format('M Y'))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
