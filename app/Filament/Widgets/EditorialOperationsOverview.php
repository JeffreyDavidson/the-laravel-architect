<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Resources\ContactInquiries\ContactInquiryResource;
use App\Filament\Resources\Episodes\EpisodeResource;
use App\Filament\Resources\Posts\PostResource;
use App\Queries\AdminMetricsQuery;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use JeffreyDavidson\CreatorKit\Enums\PublicationState;
use JeffreyDavidson\CreatorKit\Filament\Resources\NewsletterIssues\NewsletterIssueResource;
use JeffreyDavidson\CreatorKit\Filament\Resources\Subscribers\SubscriberResource;

final class EditorialOperationsOverview extends StatsOverviewWidget
{
    #[\Override]
    protected ?string $pollingInterval = null;

    #[\Override]
    protected static ?int $sort = 3;

    #[\Override]
    protected ?string $heading = 'Editorial operations';

    #[\Override]
    protected ?string $description = 'Work that needs a decision or follow-up.';

    /** @return list<Stat> */
    protected function getStats(): array
    {
        $metrics = app(AdminMetricsQuery::class);

        return [
            Stat::make('New inquiries', $metrics->newContactInquiries())
                ->description('Private inbox')
                ->descriptionIcon(Heroicon::OutlinedChatBubbleLeftRight)
                ->color('info')
                ->url(ContactInquiryResource::getUrl('index')),
            Stat::make('Posts in review', $metrics->postsInReview())
                ->description('Awaiting approval')
                ->descriptionIcon(Heroicon::OutlinedEye)
                ->color('warning')
                ->url(PostResource::getUrl('index')),
            Stat::make('Scheduled posts', $metrics->scheduledPosts())
                ->description('Ready to publish')
                ->descriptionIcon(Heroicon::OutlinedCalendar)
                ->color('success')
                ->url(PostResource::getUrl('index', ['filters' => ['publication' => ['value' => PublicationState::Scheduled->value]]])),
            Stat::make('Episode queue', $metrics->unpublishedEpisodes())
                ->description('Unpublished episodes')
                ->descriptionIcon(Heroicon::OutlinedMusicalNote)
                ->color('primary')
                ->url(EpisodeResource::getUrl('index', ['filters' => ['publication' => ['value' => PublicationState::Unpublished->value]]])),
            Stat::make('Newsletter queue', $metrics->unpublishedNewsletterIssues())
                ->description('Unpublished issues')
                ->descriptionIcon(Heroicon::OutlinedNewspaper)
                ->color('gray')
                ->url(NewsletterIssueResource::getUrl('index', ['tab' => 'unpublished'])),
            Stat::make('Active subscribers', $metrics->activeSubscribers())
                ->description('Confirmed audience')
                ->descriptionIcon(Heroicon::OutlinedEnvelope)
                ->color('success')
                ->url(SubscriberResource::getUrl('index')),
        ];
    }
}
