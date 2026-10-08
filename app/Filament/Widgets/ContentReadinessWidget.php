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
use App\Publishing\ContentReadinessSummaryQuery;
use Filament\Widgets\Widget;

final class ContentReadinessWidget extends Widget
{
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
        $counts = app(ContentReadinessSummaryQuery::class)->outstandingCounts();
        $items = [];

        foreach (ContentReadinessArea::cases() as $area) {
            $count = $counts[$area->value] ?? 0;

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
