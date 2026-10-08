<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Resources\Episodes\EpisodeResource;
use App\Filament\Resources\NewsletterIssues\NewsletterIssueResource;
use App\Filament\Resources\Podcasts\PodcastResource;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\Subscribers\SubscriberResource;
use App\Filament\Resources\Videos\VideoResource;
use App\Queries\AdminMetricsQuery;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

final class ContentPerformanceOverview extends StatsOverviewWidget
{
    #[\Override]
    protected ?string $pollingInterval = null;

    #[\Override]
    protected static ?int $sort = 1;

    #[\Override]
    protected ?string $heading = 'Content performance';

    #[\Override]
    protected ?string $description = 'A snapshot of the published library, audience, and video engagement.';

    /** @return list<Stat> */
    protected function getStats(): array
    {
        $metrics = app(AdminMetricsQuery::class);

        return [
            Stat::make('Published posts', $metrics->publishedPosts())
                ->description('Live articles')
                ->descriptionIcon(Heroicon::OutlinedDocumentText)
                ->color('info')
                ->url(PostResource::getUrl('index')),
            Stat::make('Published episodes', $metrics->publishedEpisodesOfActivePodcasts())
                ->description('Across active shows')
                ->descriptionIcon(Heroicon::OutlinedMusicalNote)
                ->color('success')
                ->url(EpisodeResource::getUrl('index')),
            Stat::make('Newsletter subscribers', $metrics->activeSubscribers())
                ->description('Active audience')
                ->descriptionIcon(Heroicon::OutlinedEnvelope)
                ->color('warning')
                ->url(SubscriberResource::getUrl('index')),
            Stat::make('Published issues', $metrics->publishedNewsletterIssues())
                ->description('Newsletter archive')
                ->descriptionIcon(Heroicon::OutlinedNewspaper)
                ->color('gray')
                ->url(NewsletterIssueResource::getUrl('index')),
            Stat::make('Active podcasts', $metrics->activePodcasts())
                ->description('Shows marked active')
                ->descriptionIcon(Heroicon::OutlinedMicrophone)
                ->color('primary')
                ->url(PodcastResource::getUrl('index')),
            Stat::make('YouTube views', Number::format($metrics->youTubeViews()))
                ->description('From the latest sync')
                ->descriptionIcon(Heroicon::OutlinedPlayCircle)
                ->color('danger')
                ->url(VideoResource::getUrl('index')),
        ];
    }
}
