<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\ContentReadinessArea;
use App\Filament\Resources\Episodes\EpisodeResource;
use App\Filament\Resources\NewsletterIssues\NewsletterIssueResource;
use App\Filament\Resources\Podcasts\PodcastResource;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\Projects\ProjectResource;
use App\Filament\Resources\Videos\VideoResource;
use App\Queries\ContentReadinessSummaryQuery;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;

class ContentReadinessWidget extends Widget
{
    private const int CACHE_SECONDS = 60;

    #[\Override]
    protected string $view = 'filament.widgets.content-readiness-widget';

    #[\Override]
    protected int|string|array $columnSpan = 'full';

    #[\Override]
    protected static ?int $sort = -2;

    /**
     * @return array{items: list<array{label: string, description: string, count: int, url: string}>, outstandingCount: int}
     */
    protected function getViewData(): array
    {
        /** @var array{items: list<array{label: string, description: string, count: int, url: string}>, outstandingCount: int} $cached */
        $cached = Cache::remember(
            'filament.dashboard.content-readiness',
            now()->addSeconds(self::CACHE_SECONDS),
            fn (): array => $this->buildViewData(),
        );

        return $cached;
    }

    /**
     * @return array{items: list<array{label: string, description: string, count: int, url: string}>, outstandingCount: int}
     */
    private function buildViewData(): array
    {
        $summary = app(ContentReadinessSummaryQuery::class);
        $items = [];

        foreach (ContentReadinessArea::cases() as $area) {
            $count = $summary->count($area);

            if ($count === 0) {
                continue;
            }

            $items[] = [
                'label' => $area->getLabel(),
                'description' => $area->getDescription(),
                'count' => $count,
                'url' => $this->url($area),
            ];
        }

        return [
            'items' => array_slice($items, 0, 4),
            'outstandingCount' => array_sum(array_column($items, 'count')),
        ];
    }

    private function url(ContentReadinessArea $area): string
    {
        return match ($area) {
            ContentReadinessArea::ProjectPreviews, ContentReadinessArea::ProjectStories => ProjectResource::getUrl('index'),
            ContentReadinessArea::PodcastLinks => PodcastResource::getUrl('index'),
            ContentReadinessArea::EpisodeDetails => EpisodeResource::getUrl('index'),
            ContentReadinessArea::PostContent => PostResource::getUrl('index'),
            ContentReadinessArea::NewsletterIssues => NewsletterIssueResource::getUrl('index'),
            ContentReadinessArea::VideoMetadata => VideoResource::getUrl('index'),
        };
    }
}
